<?php
/**
 * FILE:        app/Http/Controllers/UserDb/MandantSelfController.php
 * VERSION:     1.6.0
 * AUTOR:       Martin Wagner
 * DATUM:       2026-06-18
 *
 * ZWECK:       Mandant Eigenverwaltung — Kontodaten, Passwort und E-Mail-Adresse
 *              bearbeiten.
 *
 * FUNCTIONS:   edit()               — Lädt MandUser via _mand_id aus Session;
 *                                      gibt mandant.konto mit $mand zurück.
 *                                      Reads: userdb.mand_user.mand_id, alle Felder
 *              update()             — Validiert und speichert Kontaktdaten. mand_email
 *                                      wird NICHT aus dem Request übernommen (Aenderung
 *                                      läuft über requestEmailChange()/
 *                                      confirmEmailChange()). mand_tel/mand_company
 *                                      sind optional, bei leer Fallback 'nicht vorhanden'.
 *                                      Reads:  userdb.mand_user.mand_id
 *                                      Writes: userdb.mand_user.mand_uname, mand_tel,
 *                                              mand_firstname, mand_lastname,
 *                                              mand_street+nr, mand_postcode+city,
 *                                              mand_company, mand_2fa_opt_in, mand_cust_2fa
 *              updatePassword()     — Validiert aktuelles + neues Passwort (Policy:
 *                                      min 12, gemischte Groß-/Kleinschreibung, Ziffern,
 *                                      Sonderzeichen, uncompromised); prüft ob Benutzername
 *                                      im Passwort enthalten ist; speichert neuen Hash;
 *                                      invalidiert Session; Redirect zu mandant.login.
 *                                      Reads:  userdb.mand_user.mand_id, mand_uname,
 *                                              mand_pw_hash
 *                                      Writes: userdb.mand_user.mand_pw_hash
 *              requestEmailChange() — Validiert neue Adresse (unique); löscht offene
 *                                      email_change-Invites des Mandanten; legt neuen
 *                                      Invite an (inv_email = neue Adresse); sendet
 *                                      EmailChangeMail an neue Adresse.
 *                                      Reads:  userdb.mand_user.mand_id, mand_firstname
 *                                      Writes: userdb.invite.* (DELETE alter Token,
 *                                              INSERT neuer Token)
 *              confirmEmailChange() — Sucht gültigen email_change-Invite per Token;
 *                                      übernimmt inv_email als neue mand_email;
 *                                      löscht den Invite (Single-Use, analog
 *                                      pw_reset-Flow). Keine Session-/Login-Pflicht —
 *                                      Link kann auf anderem Gerät geklickt werden.
 *                                      Reads:  userdb.invite.*
 *                                      Writes: userdb.mand_user.mand_email,
 *                                              userdb.invite (DELETE)
 *
 * CALLS:       App\Models\UserDb\MandUser::find()
 *              App\Models\UserDb\Invite::where()->valid()->first()
 *              App\Models\UserDb\Invite::create()
 *              App\Mail\EmailChangeMail
 *              Illuminate\Support\Facades\Hash::check()
 *              Illuminate\Support\Facades\Hash::make()
 *              Illuminate\Support\Facades\Mail::to()->send()
 *              Illuminate\Support\Str::random()
 *              Illuminate\Validation\Rules\Password::min()
 *
 * DB ACCESS:   userdb.mand_user.mand_id, mand_uname, mand_email, mand_tel,
 *              mand_firstname, mand_lastname, mand_street+nr, mand_postcode+city,
 *              mand_company, mand_2fa_opt_in, mand_cust_2fa, mand_pw_hash
 *              userdb.invite.inv_id, inv_email, inv_token_hash, inv_type,
 *              inv_user_type, inv_user_id, expires_at (email_change-Einträge)
 *
 * CHANGES:     1.6.0 (2026-06-18) requestEmailChange()/confirmEmailChange() ergänzt —
 *              E-Mail-Aenderung per Bestaetigungsmail (invite-Tabelle,
 *              inv_type='email_change'); alte Adresse bleibt bis Bestaetigung aktiv.
 *              1.5.0 (2026-06-18) update() — mand_email aus Validierung/Speicherung
 *              entfernt (nicht editierbar); mand_tel/mand_company auf nullable
 *              umgestellt (Fallback 'nicht vorhanden'); unique-Pruefung auf mand_tel
 *              entfernt (Mehrfachvorkommen von 'nicht vorhanden' sonst nicht moeglich)
 */

namespace App\Http\Controllers\UserDb;

use App\Mail\EmailChangeMail;
use App\Models\UserDb\Invite;
use App\Models\UserDb\MandUser;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class MandantSelfController extends UserDbController
{
    public function edit(Request $request): View|RedirectResponse
    {
        $mandId = $request->session()->get('_mand_id');
        $mand   = $mandId ? MandUser::find($mandId) : null;

        if (! $mand) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('mandant.login');
        }

        return view('mandant.konto', compact('mand'));
    }

    public function update(Request $request): RedirectResponse
    {
        $mandId = $request->session()->get('_mand_id');
        $mand   = $mandId ? MandUser::find($mandId) : null;

        if (! $mand) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('mandant.login');
        }

        $validated = $request->validate([
            'mand_uname'         => ['required', 'string', 'max:255', "unique:userdb.mand_user,mand_uname,{$mandId},mand_id"],
            'mand_tel'           => ['nullable', 'string', 'max:255'],
            'mand_firstname'     => ['required', 'string', 'max:255'],
            'mand_lastname'      => ['required', 'string', 'max:255'],
            'mand_street+nr'     => ['required', 'string', 'max:255'],
            'mand_postcode+city' => ['required', 'string', 'max:255'],
            'mand_company'       => ['nullable', 'string', 'max:255'],
            'mand_2fa_opt_in'    => ['sometimes', 'boolean'],
            'mand_cust_2fa'      => ['required', 'integer', 'min:0', 'max:7'],
        ]);

        $validated['mand_tel']        = $validated['mand_tel'] ?? 'nicht vorhanden';
        $validated['mand_company']    = $validated['mand_company'] ?? 'nicht vorhanden';
        $validated['mand_2fa_opt_in'] = $request->boolean('mand_2fa_opt_in');
        $validated['mand_cust_2fa']   = (int) $validated['mand_cust_2fa'];

        $mand->update($validated);

        return redirect()->route('mandant.konto')
            ->with('status', 'Kontodaten erfolgreich gespeichert.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $mandId = $request->session()->get('_mand_id');
        $mand   = $mandId ? MandUser::find($mandId) : null;

        if (! $mand) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('mandant.login');
        }

        $request->validate([
            'current_password' => ['required'],
            'password'         => [
                'required',
                'confirmed',
                Password::min(12)->mixedCase()->numbers()->symbols()->uncompromised(),
            ],
        ]);

        if ($mand->mand_uname && str_contains($request->password, $mand->mand_uname)) {
            return back()->withErrors(['password' => 'Das Passwort darf den Benutzernamen nicht enthalten.']);
        }

        if (! Hash::check($request->current_password, $mand->mand_pw_hash)) {
            return back()->withErrors(['current_password' => 'Das aktuelle Passwort ist nicht korrekt.']);
        }

        $mand->update(['mand_pw_hash' => Hash::make($request->password)]);

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('mandant.login')
            ->with('status', 'Passwort geändert. Bitte melden Sie sich erneut an.');
    }

    public function requestEmailChange(Request $request): RedirectResponse
    {
        $mandId = $request->session()->get('_mand_id');
        $mand   = $mandId ? MandUser::find($mandId) : null;

        if (! $mand) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('mandant.login');
        }

        $request->validate([
            'email' => ['required', 'email', 'max:255', 'unique:userdb.mand_user,mand_email'],
        ]);

        Invite::where('inv_type', 'email_change')
            ->where('inv_user_type', 'mand')
            ->where('inv_user_id', $mandId)
            ->delete();

        $token = Str::random(64);

        Invite::create([
            'inv_email'      => $request->email,
            'inv_token_hash' => hash('sha256', $token),
            'inv_type'       => 'email_change',
            'inv_user_type'  => 'mand',
            'inv_user_id'    => $mandId,
            'inv_mand_id'    => null,
            'created_at'     => now(),
            'expires_at'     => now()->addHours(24),
        ]);

        $confirmUrl = route('mandant.konto.email-bestaetigen', ['token' => $token]);

        Mail::to($request->email)->send(new EmailChangeMail($confirmUrl, $request->email, $mand->mand_firstname));

        return redirect()->route('mandant.dashboard')
            ->with('email_change_status', "Bestätigungsmail wurde an {$request->email} gesendet.");
    }

    public function confirmEmailChange(Request $request, string $token): RedirectResponse
    {
        $invite = Invite::where('inv_token_hash', hash('sha256', $token))
            ->where('inv_type', 'email_change')
            ->where('inv_user_type', 'mand')
            ->valid()
            ->first();

        if (! $invite) {
            return redirect()->route('mandant.login')
                ->with('status', 'Der Bestätigungslink ist ungültig oder abgelaufen.');
        }

        $mand = MandUser::find($invite->inv_user_id);

        if (! $mand) {
            $invite->delete();
            return redirect()->route('mandant.login');
        }

        $mand->update(['mand_email' => $invite->inv_email]);
        $invite->delete();

        return redirect()->route('mandant.login')
            ->with('status', 'E-Mail-Adresse erfolgreich geändert. Bitte melden Sie sich an.');
    }
}
