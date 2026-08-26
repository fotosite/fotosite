<?php
/**
 * FILE:        app/Http/Controllers/UserDb/CustSelfController.php
 * VERSION:     1.7.0
 * AUTOR:       Martin Wagner
 * DATUM:       2026-08-26
 *
 * ZWECK:       Customer Eigenverwaltung — Kontaktdaten, Passwort, E-Mail-Adresse,
 *              Galerien verwalten, Konto löschen.
 *
 * FUNCTIONS:   edit()              — Lädt CustUser via _cust_id aus Session;
 *                                     gibt customer.konto mit $cust zurück.
 *                                     Reads: userdb.cust_user.cust_id, alle Felder
 *              update()            — Validiert und speichert Kontaktdaten. cust_email
 *                                     wird NICHT aus dem Request übernommen (Aenderung
 *                                     läuft über requestEmailChange()/confirmEmailChange()).
 *                                     cust_uname/cust_firstname/cust_lastname bleiben
 *                                     Pflicht. cust_tel/cust_street+nr/cust_postcode_city/
 *                                     cust_company per istPflichtfeld('cust', ...) aus
 *                                     pflichtfelder.txt: Pflicht → required, sonst
 *                                     nullable (Feld bleibt immer im validate()-Array,
 *                                     da im Formular immer angezeigt); leeres optionales
 *                                     Feld wird als null gespeichert (kein Fallback-Text
 *                                     mehr).
 *                                     Reads:  userdb.cust_user.cust_id
 *                                     Writes: userdb.cust_user.cust_uname, cust_firstname,
 *                                             cust_lastname, cust_tel, cust_street+nr,
 *                                             cust_postcode_city, cust_company
 *              updatePassword()    — Validiert aktuelles + neues Passwort (Policy: min 10
 *                                     Zeichen); prüft ob Benutzername im Passwort enthalten
 *                                     ist; speichert neuen Hash; invalidiert Session;
 *                                     Redirect zu home mit Login-Modal-Hint.
 *                                     Reads:  userdb.cust_user.cust_id, cust_uname,
 *                                             cust_pw_hash
 *                                     Writes: userdb.cust_user.cust_pw_hash
 *              requestEmailChange() — Validiert neue Adresse (unique); löscht offene
 *                                     email_change-Invites des Cust; legt neuen Invite an
 *                                     (inv_email = neue Adresse); sendet EmailChangeMail.
 *                                     Reads:  userdb.cust_user.cust_id, cust_firstname
 *                                     Writes: userdb.invite.* (DELETE alter Token,
 *                                             INSERT neuer Token)
 *              confirmEmailChange() — Sucht gültigen email_change-Invite per Token;
 *                                     übernimmt inv_email als neue cust_email; löscht
 *                                     den Invite (Single-Use, analog pw_reset-Flow).
 *                                     Keine Session-/Login-Pflicht.
 *                                     Reads:  userdb.invite.*
 *                                     Writes: userdb.cust_user.cust_email,
 *                                             userdb.invite (DELETE)
 *              galerien()          — Lädt alle cust_pcode-Einträge des Cust mit mandUser;
 *                                     gibt customer.galerien zurück.
 *                                     Reads: userdb.cust_pcode.*, userdb.mand_user.*
 *              reorderGalerie()    — Tauscht pcode_prefstat zweier benachbarter Einträge
 *                                     (up/down); weist sequenzielle Werte zu. Gibt bei
 *                                     AJAX/JSON-Request (Accept: application/json) JSON
 *                                     statt Redirect zurück (sofortiges Speichern ohne
 *                                     Page-Reload).
 *                                     Reads/Writes: userdb.cust_pcode.pcode_prefstat
 *              saveSettings()      — Setzt cust_mailrequest für GENAU EINEN pcode-Eintrag
 *                                     (AJAX-Einzelspeicherung pro Checkbox-Toggle, kein
 *                                     Formular/Button mehr). Erwartet JSON-Body
 *                                     {pcode_id, mailrequest}; gibt JSON zurück.
 *                                     Reads:  userdb.cust_pcode.pcode_id, cust_id
 *                                     Writes: userdb.cust_pcode.cust_mailrequest
 *              removeGalerie()     — Löscht einen pcode-Eintrag; bei letztem Eintrag:
 *                                     destroyAccount(). Redirect zu galerien oder home.
 *                                     Reads:  userdb.cust_pcode.pcode_id, cust_id
 *                                     Deletes: userdb.cust_pcode (ggf. passkey,
 *                                              passkey_dismissed, cust_user via helper)
 *              deleteAccount()     — Löscht das gesamte Konto via destroyAccount();
 *                                     invalidiert Session; Redirect zu home.
 *                                     Deletes: userdb.passkey, passkey_dismissed,
 *                                              cust_pcode, cust_user
 *              destroyAccount()    — Private Helper: löscht passkey, passkey_dismissed,
 *                                     alle cust_pcode-Einträge und cust_user für $custId.
 *
 * CALLS:       App\Models\UserDb\CustUser::find()
 *              App\Models\UserDb\CustPcode::where()
 *              App\Models\UserDb\Passkey::where()
 *              App\Models\UserDb\PasskeyDismissed::where()
 *              App\Models\UserDb\Invite::where()->valid()->first()
 *              App\Models\UserDb\Invite::create()
 *              App\Mail\EmailChangeMail
 *              Illuminate\Support\Facades\Hash::check()
 *              Illuminate\Support\Facades\Hash::make()
 *              Illuminate\Support\Facades\Mail::to()->send()
 *              Illuminate\Support\Str::random()
 *              Illuminate\Validation\Rules\Password::min()
 *
 * DB ACCESS:   userdb.cust_user.cust_id, cust_uname, cust_email, cust_tel,
 *              cust_firstname, cust_lastname, cust_street+nr, cust_postcode_city,
 *              cust_company, cust_pw_hash
 *              userdb.cust_pcode.pcode_id, cust_id, mand_id, pcode_prefstat,
 *              cust_mailrequest (READ/WRITE/DELETE)
 *              userdb.passkey.user_type, user_id (DELETE)
 *              userdb.passkey_dismissed.user_type, user_id (DELETE)
 *              userdb.invite.inv_id, inv_email, inv_token_hash, inv_type,
 *              inv_user_type, inv_user_id, expires_at (email_change-Einträge)
 *
 * CHANGES:     1.7.0 (2026-08-26) update() — validate()-Regeln für cust_tel/
 *              cust_street+nr/cust_postcode_city/cust_company jetzt dynamisch per
 *              istPflichtfeld('cust', ...) aus storage/app/private/pflichtfelder.txt
 *              (required statt fest 'required'/'nullable'); Fallback-Zuweisung
 *              'nicht vorhanden' bei leerem cust_tel/cust_company entfernt — leere
 *              optionale Felder werden jetzt als null gespeichert (ConvertEmptyStringsToNull
 *              wandelt leeren Input bereits vor der Validierung in null um).
 *              1.6.0 (2026-06-29) updatePassword() — deutschsprachige Fehlermeldungen
 *              für password.confirmed, password.min, current_password ergänzt.
 *              1.5.0 (2026-06-19) saveSettings() von Multi-Checkbox-Form-Submit
 *              (Redirect+Flash) auf AJAX-Einzelspeicherung pro Checkbox umgestellt
 *              (JSON-Request/-Response, kein "Einstellungen speichern"-Button mehr
 *              auf customer/galerien.blade.php); reorderGalerie() gibt bei
 *              AJAX-Requests jetzt JSON statt Redirect zurück, damit auch die
 *              Reihenfolge-Aenderung ohne Page-Reload sofort speichert.
 *              1.4.0 (2026-06-18) update() — cust_uname als Pflichtfeld ergänzt (mit
 *              unique-Pruefung — cust_user.cust_uname hat laut DDL einen UNIQUE-
 *              Constraint); cust_firstname/cust_lastname/cust_street+nr/
 *              cust_postcode_city von nullable auf required umgestellt; cust_tel/
 *              cust_company bleiben nullable, jedoch mit Fallback 'nicht vorhanden'
 *              bei leerem Wert (statt rohem leerem String).
 *              1.3.0 (2026-06-18) update() — cust_email aus Validierung/Speicherung
 *              entfernt (nicht editierbar); requestEmailChange()/confirmEmailChange()
 *              ergänzt — E-Mail-Aenderung per Bestaetigungsmail (invite-Tabelle,
 *              inv_type='email_change'); alte Adresse bleibt bis Bestaetigung aktiv.
 */

namespace App\Http\Controllers\UserDb;

use App\Mail\EmailChangeMail;
use App\Models\UserDb\CustPcode;
use App\Models\UserDb\CustUser;
use App\Models\UserDb\Invite;
use App\Models\UserDb\Passkey;
use App\Models\UserDb\PasskeyDismissed;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class CustSelfController extends UserDbController
{
    public function edit(Request $request): View|RedirectResponse
    {
        $custId = $request->session()->get('_cust_id');
        $cust   = $custId ? CustUser::find($custId) : null;

        if (! $cust) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('customer.login');
        }

        return view('customer.konto', compact('cust'));
    }

    public function update(Request $request): RedirectResponse
    {
        $custId = $request->session()->get('_cust_id');
        $cust   = $custId ? CustUser::find($custId) : null;

        if (! $cust) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('customer.login');
        }

        $rules = [
            'cust_uname'     => ['required', 'string', 'max:255', "unique:userdb.cust_user,cust_uname,{$custId},cust_id"],
            'cust_firstname' => ['required', 'string', 'max:255'],
            'cust_lastname'  => ['required', 'string', 'max:255'],
        ];

        $pflichtfelder = [
            'cust_tel'           => 'Telefon',
            'cust_street+nr'     => 'Strasse',
            'cust_postcode_city' => 'PLZOrt',
            'cust_company'       => 'Firma',
        ];

        foreach ($pflichtfelder as $field => $feldKey) {
            $rules[$field] = istPflichtfeld('cust', $feldKey)
                ? ['required', 'string', 'max:255']
                : ['nullable', 'string', 'max:255'];
        }

        $validated = $request->validate($rules);

        $cust->update($validated);

        return redirect()->route('customer.konto')
            ->with('status', 'Kontaktdaten gespeichert.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $custId = $request->session()->get('_cust_id');
        $cust   = $custId ? CustUser::find($custId) : null;

        if (! $cust) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('customer.login');
        }

        $request->validate([
            'current_password' => ['required'],
            'password'         => [
                'required',
                'confirmed',
                Password::min(10),
            ],
        ], [
            'password.confirmed' => 'Die eingegebenen Passwörter stimmen nicht überein.',
            'password.min'       => 'Das Passwort erfüllt nicht die Mindestanforderungen.',
            'current_password'   => 'Das eingegebene Passwort ist nicht korrekt.',
        ]);

        if ($cust->cust_uname && str_contains($request->password, $cust->cust_uname)) {
            return back()->withErrors(['password' => 'Das Passwort darf den Benutzernamen nicht enthalten.']);
        }

        if (! Hash::check($request->current_password, $cust->cust_pw_hash)) {
            return back()->withErrors(['current_password' => 'Das aktuelle Passwort ist falsch.']);
        }

        $cust->update(['cust_pw_hash' => Hash::make($request->password)]);

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')
            ->with('status', 'Passwort geändert. Bitte melden Sie sich erneut an.')
            ->with('open_login_modal', 'cust');
    }

    public function requestEmailChange(Request $request): RedirectResponse
    {
        $custId = $request->session()->get('_cust_id');
        $cust   = $custId ? CustUser::find($custId) : null;

        if (! $cust) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('customer.login');
        }

        $request->validate([
            'email' => ['required', 'email', 'max:255', 'unique:userdb.cust_user,cust_email'],
        ]);

        Invite::where('inv_type', 'email_change')
            ->where('inv_user_type', 'cust')
            ->where('inv_user_id', $custId)
            ->delete();

        $token = Str::random(64);

        Invite::create([
            'inv_email'      => $request->email,
            'inv_token_hash' => hash('sha256', $token),
            'inv_type'       => 'email_change',
            'inv_user_type'  => 'cust',
            'inv_user_id'    => $custId,
            'inv_mand_id'    => null,
            'created_at'     => now(),
            'expires_at'     => now()->addHours(24),
        ]);

        $confirmUrl = route('customer.konto.email-bestaetigen', ['token' => $token]);

        Mail::to($request->email)->send(new EmailChangeMail($confirmUrl, $request->email, $cust->cust_firstname));

        return redirect()->route('customer.dashboard')
            ->with('email_change_status', "Bestätigungsmail wurde an {$request->email} gesendet.");
    }

    public function confirmEmailChange(Request $request, string $token): RedirectResponse
    {
        $invite = Invite::where('inv_token_hash', hash('sha256', $token))
            ->where('inv_type', 'email_change')
            ->where('inv_user_type', 'cust')
            ->valid()
            ->first();

        if (! $invite) {
            return redirect()->route('customer.login')
                ->with('status', 'Der Bestätigungslink ist ungültig oder abgelaufen.');
        }

        $cust = CustUser::find($invite->inv_user_id);

        if (! $cust) {
            $invite->delete();
            return redirect()->route('customer.login');
        }

        $cust->update(['cust_email' => $invite->inv_email]);
        $invite->delete();

        return redirect()->route('customer.login')
            ->with('status', 'E-Mail-Adresse erfolgreich geändert. Bitte melden Sie sich an.');
    }

    public function galerien(Request $request): View|RedirectResponse
    {
        $custId = $request->session()->get('_cust_id');
        $cust   = $custId ? CustUser::find($custId) : null;

        if (! $cust) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('customer.login');
        }

        $pcodes = CustPcode::where('cust_id', $custId)
            ->orderBy('pcode_prefstat')
            ->orderBy('pcode_id')
            ->with('mandUser')
            ->get();

        return view('customer.galerien', compact('pcodes'));
    }

    public function reorderGalerie(Request $request, int $pcodeId, string $direction): RedirectResponse|JsonResponse
    {
        $custId = $request->session()->get('_cust_id');
        $cust   = $custId ? CustUser::find($custId) : null;

        if (! $cust) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('customer.login');
        }

        $pcodes = CustPcode::where('cust_id', $custId)
            ->orderBy('pcode_prefstat')
            ->orderBy('pcode_id')
            ->get();

        $index = $pcodes->search(fn($p) => $p->pcode_id === $pcodeId);
        if ($index === false) {
            return $request->wantsJson()
                ? response()->json(['success' => false], 404)
                : redirect()->route('customer.galerien');
        }

        $swapIndex = $direction === 'up' ? $index - 1 : $index + 1;
        if ($swapIndex < 0 || $swapIndex >= $pcodes->count()) {
            return $request->wantsJson()
                ? response()->json(['success' => false], 422)
                : redirect()->route('customer.galerien');
        }

        // Reorder collection and assign sequential pcode_prefstat values
        $items = $pcodes->values()->all();
        [$items[$index], $items[$swapIndex]] = [$items[$swapIndex], $items[$index]];

        foreach ($items as $i => $pcode) {
            if ($pcode->pcode_prefstat !== $i) {
                $pcode->update(['pcode_prefstat' => $i]);
            }
        }

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('customer.galerien');
    }

    public function saveSettings(Request $request): JsonResponse
    {
        $custId = $request->session()->get('_cust_id');
        $cust   = $custId ? CustUser::find($custId) : null;

        if (! $cust) {
            return response()->json(['success' => false], 401);
        }

        $validated = $request->validate([
            'pcode_id'    => ['required', 'integer'],
            'mailrequest' => ['required', 'boolean'],
        ]);

        $pcode = CustPcode::where('cust_id', $custId)
            ->where('pcode_id', $validated['pcode_id'])
            ->first();

        if (! $pcode) {
            return response()->json(['success' => false], 404);
        }

        $pcode->cust_mailrequest = $validated['mailrequest'];
        $pcode->save();

        return response()->json(['success' => true]);
    }

    public function removeGalerie(Request $request, int $pcodeId): RedirectResponse
    {
        $custId = $request->session()->get('_cust_id');
        $cust   = $custId ? CustUser::find($custId) : null;

        if (! $cust) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('customer.login');
        }

        $pcodes = CustPcode::where('cust_id', $custId)->get();
        $target = $pcodes->firstWhere('pcode_id', $pcodeId);

        if (! $target) {
            abort(404);
        }

        if ($pcodes->count() === 1) {
            $this->destroyAccount($custId);
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('home')
                ->with('status', 'Ihr Konto wurde gelöscht.');
        }

        $target->delete();

        return redirect()->route('customer.galerien')
            ->with('status', 'Galerie entfernt.');
    }

    public function deleteAccount(Request $request): RedirectResponse
    {
        $custId = $request->session()->get('_cust_id');
        $cust   = $custId ? CustUser::find($custId) : null;

        if (! $cust) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('customer.login');
        }

        $this->destroyAccount($custId);

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')
            ->with('status', 'Ihr Konto wurde gelöscht.');
    }

    private function destroyAccount(int $custId): void
    {
        Passkey::where('user_type', 'cust')->where('user_id', $custId)->delete();
        PasskeyDismissed::where('user_type', 'cust')->where('user_id', $custId)->delete();
        CustPcode::where('cust_id', $custId)->delete();
        CustUser::find($custId)?->delete();
    }
}
