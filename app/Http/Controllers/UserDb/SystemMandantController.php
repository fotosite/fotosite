<?php
/**
 * FILE:        app/Http/Controllers/UserDb/SystemMandantController.php
 * VERSION:     1.14.0
 *
 * FUNCTIONS:   index()          — Lists all MandUser records ordered by mand_lastname.
 *                                 Reads: userdb.mand_user.*
 *              invite()         — Validates email, creates register invite, sends InviteMail.
 *                                 Writes: userdb.invite.*
 *              show()           — Loads MandUser by $id; returns detail view.
 *                                 Reads: userdb.mand_user.*
 *              edit()           — Loads MandUser by $id; returns edit form.
 *                                 Reads: userdb.mand_user.*
 *              update()         — Validates and updates valid_to, has_public_content,
 *                                 mand_cust_2fa. active wird seit 1.14.0 nicht mehr
 *                                 hier, sondern ausschließlich über toggleActive()
 *                                 gesteuert.
 *                                 Reads:  userdb.mand_user.mand_id
 *                                 Writes: userdb.mand_user.valid_to, has_public_content,
 *                                         mand_cust_2fa
 *              toggleActive()   — Deaktiviert bzw. reaktiviert einen Mandanten (active-
 *                                 Toggle); setzt oder löscht mand_deactivated_at; sendet
 *                                 MandDeactivatedMail bzw. MandActivatedMail.
 *                                 Reads:  userdb.mand_user.mand_id, active, mand_email,
 *                                         mand_firstname
 *                                 Writes: userdb.mand_user.active, mand_deactivated_at
 *              destroy()        — Cust-Kaskade (analog MandantCustController@destroy)
 *                                 vor der mand-Löschung: entfernt alle cust_pcode-
 *                                 Einträge dieses Mandanten; verwaiste cust_user
 *                                 (kein cust_pcode mehr übrig) erhalten
 *                                 CustAccountDeletedMail und werden inkl.
 *                                 passkey/passkey_dismissed gelöscht; löscht
 *                                 sessiondb.cust_invite-Einträge dieses Mandanten;
 *                                 löscht abschließend MandUser by $id.
 *                                 Reads:  userdb.cust_pcode.pcode_id, mand_id, cust_id
 *                                         userdb.cust_user.cust_id, cust_email,
 *                                         cust_firstname, cust_uname
 *                                 Writes: userdb.cust_pcode (DELETE)
 *                                         userdb.cust_user (DELETE, nur wenn verwaist)
 *                                         userdb.passkey (DELETE, nur wenn verwaist)
 *                                         userdb.passkey_dismissed (DELETE, nur wenn verwaist)
 *                                         sessiondb.cust_invite (DELETE)
 *                                         userdb.mand_user (DELETE)
 *              showRegister()   — Validates mand register token; returns register form.
 *                                 Reads: userdb.invite.*
 *              handleRegister() — Creates MandUser from register invite inkl.
 *                                 ds_accepted_at, ds_version, upload_terms_accepted_at,
 *                                 upload_terms_version; deletes invite. mand_tel/
 *                                 mand_street+nr/mand_postcode+city/mand_company per
 *                                 istPflichtfeld('mand', ...) aus pflichtfelder.txt:
 *                                 Pflicht → required im validate()-Array; optional →
 *                                 Feld komplett aus dem validate()-Array entfernt und
 *                                 beim Anlegen explizit null gespeichert (statt
 *                                 'nicht vorhanden').
 *                                 Reads:  userdb.invite.*
 *                                 Writes: userdb.mand_user.*, userdb.invite (DELETE)
 *
 * CALLS:       App\Models\UserDb\MandUser::orderBy()->get()
 *              App\Models\UserDb\MandUser::find()
 *              App\Models\UserDb\MandUser::create()
 *              App\Models\UserDb\SystUser::find()
 *              App\Models\UserDb\Invite::where()->valid()->first()
 *              App\Models\UserDb\Invite::create()
 *              App\Models\UserDb\CustPcode::where()->get()
 *              App\Models\UserDb\CustPcode::where()->count()
 *              App\Models\UserDb\CustUser::find()->delete()
 *              App\Models\UserDb\Passkey::where()->delete()
 *              App\Models\UserDb\PasskeyDismissed::where()->delete()
 *              App\Models\SessionDb\CustInvite::where()->delete()
 *              App\Mail\InviteMail
 *              App\Mail\CustAccountDeletedMail
 *              App\Mail\MandAccountDeletedMail
 *              App\Mail\MandDeactivatedMail
 *              App\Mail\MandActivatedMail
 *              Illuminate\Support\Facades\Hash::make()
 *              Illuminate\Support\Facades\Mail::to()->send()
 *              Illuminate\Support\Str::random()
 *
 * DB ACCESS:   userdb.mand_user.mand_id, mand_uname, mand_email, mand_tel,
 *              mand_firstname, mand_lastname, mand_company, mand_pw_hash,
 *              mand_street+nr, mand_postcode+city, mand_prefstat,
 *              mand_cust_2fa, active, has_public_content, valid_to,
 *              ds_accepted_at, ds_version, upload_terms_accepted_at, upload_terms_version
 *              mand_deactivated_at
 *              userdb.invite.*
 *              userdb.syst_user.syst_id, syst_uname
 *              userdb.cust_pcode.pcode_id, mand_id, cust_id (DELETE)
 *              userdb.cust_user.cust_id, cust_email, cust_firstname, cust_uname (DELETE)
 *              userdb.passkey.pk_id, user_type, user_id (DELETE)
 *              userdb.passkey_dismissed.pd_id, user_type, user_id (DELETE)
 *              sessiondb.cust_invite.invite_id, mand_id (DELETE)
 *
 * CHANGES:     1.14.0 (2026-09-04) update() — active aus validate() und
 *              $mandant->update()-Array entfernt (Checkbox in edit.blade.php
 *              entfernt); Steuerung von active läuft jetzt ausschließlich über
 *              toggleActive().
 *              1.13.0 (2026-09-04) toggleActive() ergänzt: Galerist deaktivieren/aktivieren
 *              mit E-Mail-Benachrichtigung und Karenzzeit-Zeitstempel.
 *              1.12.0 (2026-08-26) handleRegister() — ds_version/
 *              upload_terms_version beim MandUser::create() lesen jetzt
 *              PolicyVersion::get('ds_version') bzw.
 *              PolicyVersion::get('upload_version') (userdb.policy_versions,
 *              die von CheckPolicyVersion laufend geprüfte Referenz) statt
 *              beide fälschlich denselben config('datenschutz.version')-Wert
 *              (nur ein separater, nicht synchronisierter Startwert) —
 *              upload_terms_version bekam bisher versehentlich den
 *              ds_version-Wert.
 *              1.11.0 (2026-08-26) handleRegister() — validate()-Regeln für mand_tel/
 *              mand_street+nr/mand_postcode+city/mand_company jetzt dynamisch per
 *              istPflichtfeld('mand', ...) aus storage/app/private/pflichtfelder.txt
 *              abgeleitet; optionale Felder werden komplett aus dem validate()-Array
 *              entfernt statt als 'nullable' geführt; MandUser::create() liest jetzt
 *              durchgehend aus $validated statt gemischt aus $request; für nicht
 *              abgefragte (optionale) Felder wird explizit null statt
 *              'nicht vorhanden' gespeichert.
 *              1.10.0 (2026-06-29) handleRegister() — deutschsprachige Fehlermeldungen
 *              für password.confirmed und password.min in bestehendem messages-Array
 *              ergänzt.
 *              1.9.0 (2026-06-29) destroy() — MandAccountDeletedMail an Mandant
 *              vor der Löschung ergänzt.
 *              1.8.0 (2026-06-22) destroy() — Cust-Kaskade ergänzt (analog
 *              MandantCustController@destroy): verwaiste cust_user werden vor der
 *              mand-Löschung per CustAccountDeletedMail benachrichtigt und gelöscht
 *              (inkl. passkey/passkey_dismissed); cust_pcode- und
 *              sessiondb.cust_invite-Einträge dieses Mandanten werden entfernt.
 *              1.7.0 (2026-06-18) handleRegister() — Erfolgsmeldung nach Kontoerstellung
 *              aktualisiert ("Konto erfolgreich angelegt..."); login_page='mand'
 *              zusätzlich geflasht, damit das Login-Modal (auth/login-modal.blade.php)
 *              direkt die Galerist:innen-Seite zeigt statt auf den Cust-Tab zu fallen
 *              (Default war session('login_page', 'cust')).
 *              1.6.0 (2026-06-18) handleRegister() — Adressfelder (mand_street+nr,
 *              mand_postcode+city) als Pflichtfelder ergänzt; mand_tel/mand_company
 *              auf nullable umgestellt (Fallback 'nicht vorhanden' bei leerem Wert)
 *              1.5.0 (2026-06-17) handleRegister() — deutschsprachige Fehlermeldungen für
 *              ds_accepted.accepted und upload_terms_accepted.accepted
 *              1.4.0 (2026-06-16) handleRegister() — zwei DS-Checkboxen (ds_accepted,
 *              upload_terms_accepted) + Speicherung in mand_user (Datenschutz-Feature)
 */

namespace App\Http\Controllers\UserDb;

use App\Mail\CustAccountDeletedMail;
use App\Mail\MandAccountDeletedMail;
use App\Mail\MandActivatedMail;
use App\Mail\MandDeactivatedMail;
use App\Mail\InviteMail;
use App\Models\SessionDb\CustInvite;
use App\Models\UserDb\CustPcode;
use App\Models\UserDb\CustUser;
use App\Models\UserDb\Invite;
use App\Models\UserDb\MandUser;
use App\Models\UserDb\Passkey;
use App\Models\UserDb\PasskeyDismissed;
use App\Models\UserDb\PolicyVersion;
use App\Models\UserDb\SystUser;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class SystemMandantController extends UserDbController
{
    private function currentUserName(Request $request): string
    {
        $id = $request->session()->get('_syst_id');
        return $id ? (SystUser::find($id)?->syst_uname ?? 'System') : 'System';
    }

    public function index(Request $request): View
    {
        $mandanten       = MandUser::orderBy('mand_lastname')->get();
        $currentUserName = $this->currentUserName($request);

        return view('system.mandanten.index', compact('mandanten', 'currentUserName'));
    }

    public function invite(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email', 'unique:userdb.mand_user,mand_email'],
        ]);

        $token = Str::random(64);

        Invite::create([
            'inv_email'      => $request->email,
            'inv_token_hash' => hash('sha256', $token),
            'inv_type'       => 'register',
            'inv_user_type'  => 'mand',
            'inv_user_id'    => null,
            'inv_mand_id'    => null,
            'created_at'     => now(),
            'expires_at'     => now()->addHours(24),
        ]);

        $url = route('system.mand.register', ['token' => $token]);

        Mail::to($request->email)->send(new InviteMail($url, 'register', 'mand'));

        return redirect()->route('system.mandanten.index')
            ->with('status', 'Einladung wurde gesendet.');
    }

    public function show(Request $request, int $id): View
    {
        $mandant = MandUser::find($id);

        if (! $mandant) {
            abort(404);
        }

        $currentUserName = $this->currentUserName($request);

        return view('system.mandanten.show', compact('mandant', 'currentUserName'));
    }

    public function edit(Request $request, int $id): View
    {
        $mandant = MandUser::find($id);

        if (! $mandant) {
            abort(404);
        }

        $currentUserName = $this->currentUserName($request);

        return view('system.mandanten.edit', compact('mandant', 'currentUserName'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $mandant = MandUser::find($id);

        if (! $mandant) {
            abort(404);
        }

        $request->validate([
            'valid_to'           => ['nullable', 'date'],
            'has_public_content' => ['required', 'boolean'],
            'mand_cust_2fa'      => ['required', 'boolean'],
        ]);

        $mandant->update($request->only([
            'valid_to',
            'has_public_content',
            'mand_cust_2fa',
        ]));

        return redirect()->route('system.mandanten.show', $id)
            ->with('status', 'Einstellungen gespeichert.');
    }

    public function toggleActive(Request $request, int $id): RedirectResponse
    {
        $mandant = MandUser::findOrFail($id);
        $mandName = $mandant->mand_firstname;

        if ($mandant->active) {
            $mandant->update([
                'active'               => false,
                'mand_deactivated_at'  => now(),
            ]);
            Mail::to($mandant->mand_email)->send(new MandDeactivatedMail($mandName));
            $status = 'Galerist:in wurde deaktiviert.';
        } else {
            $mandant->update([
                'active'               => true,
                'mand_deactivated_at'  => null,
            ]);
            Mail::to($mandant->mand_email)->send(new MandActivatedMail($mandName));
            $status = 'Galerist:in wurde wieder aktiviert.';
        }

        return redirect()
            ->route('system.mandanten.edit', $mandant->mand_id)
            ->with('status', $status);
    }

    public function destroy(Request $request, int $id): RedirectResponse
    {
        $mandant = MandUser::find($id);

        if (! $mandant) {
            abort(404);
        }

        $pcodes = CustPcode::where('mand_id', $id)->get();

        foreach ($pcodes as $pcode) {
            $custId = $pcode->cust_id;

            $pcode->delete();

            $remaining = CustPcode::where('cust_id', $custId)->count();
            if ($remaining === 0) {
                $cust = CustUser::find($custId);

                if ($cust) {
                    $custEmail = $cust->cust_email;
                    $custName  = $cust->cust_firstname ?: ($cust->cust_uname ?: 'Hallo');

                    Mail::to($custEmail)->send(new CustAccountDeletedMail($custName));

                    Passkey::where('user_type', 'cust')->where('user_id', $custId)->delete();
                    PasskeyDismissed::where('user_type', 'cust')->where('user_id', $custId)->delete();

                    $cust->delete();
                }
            }
        }

        CustInvite::where('mand_id', $id)->delete();

        Mail::to($mandant->mand_email)
            ->send(new MandAccountDeletedMail($mandant->mand_firstname));

        $mandant->delete();

        return redirect()->route('system.mandanten.index')
            ->with('status', 'Mandant wurde gelöscht.');
    }

    public function showRegister(Request $request, string $token): View
    {
        $invite = Invite::where('inv_token_hash', hash('sha256', $token))
            ->where('inv_type', 'register')
            ->where('inv_user_type', 'mand')
            ->valid()
            ->first();

        if (! $invite) {
            abort(404);
        }

        return view('system.mandanten.register', compact('invite', 'token'));
    }

    public function handleRegister(Request $request, string $token): RedirectResponse
    {
        $invite = Invite::where('inv_token_hash', hash('sha256', $token))
            ->where('inv_type', 'register')
            ->where('inv_user_type', 'mand')
            ->valid()
            ->first();

        if (! $invite) {
            abort(404);
        }

        $rules = [
            'mand_uname'            => ['required', 'string', 'unique:userdb.mand_user,mand_uname'],
            'mand_firstname'        => ['required', 'string'],
            'mand_lastname'         => ['required', 'string'],
            'password'              => ['required', 'min:12', 'confirmed'],
            'ds_accepted'           => ['accepted'],
            'upload_terms_accepted' => ['accepted'],
        ];

        $pflichtfelder = [
            'mand_tel'           => 'Telefon',
            'mand_street+nr'     => 'Strasse',
            'mand_postcode+city' => 'PLZOrt',
            'mand_company'       => 'Firma',
        ];

        foreach ($pflichtfelder as $field => $feldKey) {
            if (istPflichtfeld('mand', $feldKey)) {
                $rules[$field] = ['required', 'string', 'max:255'];
            }
        }

        $validated = $request->validate($rules, [
            'password.confirmed'             => 'Die eingegebenen Passwörter stimmen nicht überein.',
            'password.min'                   => 'Das Passwort erfüllt nicht die Mindestanforderungen.',
            'ds_accepted.accepted'           => 'Um ein Galerist:innen-Konto zu erstellen, musst du der Datenschutzerklärung sowie den Bedingungen für den Upload von Inhalten zustimmen.',
            'upload_terms_accepted.accepted' => 'Um ein Galerist:innen-Konto zu erstellen, musst du der Datenschutzerklärung sowie den Bedingungen für den Upload von Inhalten zustimmen.',
        ]);

        MandUser::create([
            'mand_uname'               => $validated['mand_uname'],
            'mand_email'               => $invite->inv_email,
            'mand_firstname'           => $validated['mand_firstname'],
            'mand_lastname'            => $validated['mand_lastname'],
            'mand_tel'                 => $validated['mand_tel'] ?? null,
            'mand_company'             => $validated['mand_company'] ?? null,
            'mand_pw_hash'             => Hash::make($validated['password']),
            'mand_street+nr'           => $validated['mand_street+nr'] ?? null,
            'mand_postcode+city'       => $validated['mand_postcode+city'] ?? null,
            'mand_prefstat'            => 0,
            'active'                   => true,
            'has_public_content'       => false,
            'mand_cust_2fa'            => false,
            'valid_to'                 => null,
            'ds_accepted_at'           => now(),
            'ds_version'               => PolicyVersion::get('ds_version'),
            'upload_terms_accepted_at' => now(),
            'upload_terms_version'     => PolicyVersion::get('upload_version'),
        ]);

        $invite->delete();

        return redirect()->route('mandant.login')
            ->with('status', 'Konto erfolgreich angelegt. Bitte melde dich jetzt als Galerist:in an.')
            ->with('login_page', 'mand');
    }
}
