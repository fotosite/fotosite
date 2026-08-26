<?php
/**
 * FILE:        app/Http/Controllers/UserDb/CustRegisterController.php
 * VERSION:     1.11.0
 * AUTHOR:      Martin Wagner
 * DATE:        2026-08-26
 * PURPOSE:     Mitglieder-Registrierung per Einladungs-Token
 *
 * FUNCTIONS:   show()   — Validiert Token; prüft ob E-Mail bereits in cust_user existiert;
 *                          übergibt $alreadyExists an View.
 *                          Reads: sessiondb.cust_invite.token, expires_at, used, cust_email
 *                                 userdb.cust_user.cust_email
 *              store()  — Zwei Pfade über Hidden Field 'existing':
 *                          existing=1: nur Email validieren, vorhandenen CustUser nutzen.
 *                          existing=0: volle Validierung (inkl. cust_uname, Adressfelder)
 *                                      + CustUser::create() inkl. ds_accepted_at,
 *                                      ds_version (Datenschutz-Checkbox). cust_tel/
 *                                      cust_street+nr/cust_postcode_city/cust_company
 *                                      per istPflichtfeld('cust', ...) dynamisch aus
 *                                      pflichtfelder.txt: Pflicht → required im
 *                                      validate()-Array; optional → Feld komplett aus
 *                                      dem validate()-Array entfernt und beim Anlegen
 *                                      explizit null gespeichert (statt 'nicht vorhanden').
 *                          Danach CustPcode erstellen, Einladung als verwendet markieren.
 *                          Reads:  sessiondb.cust_invite.*
 *                                  userdb.cust_user.cust_email
 *                          Writes: userdb.cust_user.* (nur bei Neu-Registrierung)
 *                                  userdb.cust_pcode.mand_id, cust_id, cust_passcode,
 *                                  pcode_prefstat, cust_alias
 *                                  sessiondb.cust_invite.used (UPDATE)
 *
 * CALLS:       App\Models\SessionDb\CustInvite::where()->first()
 *              App\Models\SessionDb\CustInvite::update()
 *              App\Models\UserDb\CustUser::where()->first()
 *              App\Models\UserDb\CustUser::create()
 *              App\Models\UserDb\CustPcode::create()
 *              Illuminate\Support\Facades\Hash::make()
 *              Illuminate\Validation\Rules\Password
 *
 * DB ACCESS:   sessiondb.cust_invite.invite_id, token, expires_at, used, mand_id,
 *              cust_email, sec_level
 *              userdb.cust_user.cust_id, cust_uname, cust_firstname, cust_lastname,
 *              cust_email, cust_tel, cust_company, cust_street+nr, cust_postcode_city,
 *              cust_pw_hash, cust_2fa_opt_in, ds_accepted_at, ds_version
 *              userdb.cust_pcode.pcode_id, mand_id, cust_id, cust_passcode,
 *              pcode_prefstat
 *
 * CHANGES:     1.11.0 (2026-08-26) store() (existing=0) — ds_version beim
 *              CustUser::create() liest jetzt PolicyVersion::get('ds_version')
 *              (userdb.policy_versions, die von CheckPolicyVersion laufend
 *              geprüfte Referenz) statt config('datenschutz.version') (nur ein
 *              separater, nicht synchronisierter Startwert).
 *              1.10.0 (2026-08-26) store() (existing=0) — validate()-Regeln für
 *              cust_tel/cust_street+nr/cust_postcode_city/cust_company jetzt
 *              dynamisch per istPflichtfeld('cust', ...) aus
 *              storage/app/private/pflichtfelder.txt abgeleitet; optionale Felder
 *              werden komplett aus dem validate()-Array entfernt statt als
 *              'nullable' geführt; beim CustUser::create() wird für nicht
 *              abgefragte (optionale) Felder explizit null statt 'nicht vorhanden'
 *              gespeichert.
 *              1.9.0 (2026-06-18) store() — Erfolgsmeldung nach Kontoerstellung
 *              aktualisiert ("Konto erfolgreich angelegt..."). Redirect bewusst auf
 *              route('home') statt route('customer.login') belassen: 'customer.login'
 *              (CustLoginController::showLogin()) erzeugt einen eigenen Redirect zu
 *              'home' mit einem anderen Flash-Key (open_login_modal) und würde die
 *              'status'-Flash-Message beim Session-Flash-Aging verwerfen, bevor die
 *              Login-Seite (auth/login-modal.blade.php, gerendert von Route 'home')
 *              tatsächlich angezeigt wird.
 *              1.8.0 (2026-06-18) store() — cust_uname als Pflichtfeld ergänzt
 *              (mit unique-Pruefung — cust_user.cust_uname hat laut DDL einen
 *              UNIQUE-Constraint, ohne Validierungsregel würde ein Duplikat eine
 *              ungefilterte SQL-Exception statt einer Formularfehlermeldung
 *              auslösen); cust_street+nr/cust_postcode_city bereits Pflicht
 *              (unverändert); cust_tel auf nullable umgestellt (Fallback
 *              'nicht vorhanden'); cust_company-Fallback von '' auf
 *              'nicht vorhanden' umgestellt.
 *              1.7.0 (2026-06-17) Deutschsprachige Fehlermeldung für ds_accepted.accepted
 *              1.6.0 (2026-06-16) ds_accepted (Pflicht-Checkbox) + ds_accepted_at/ds_version
 *              beim CustUser::create() gespeichert (Datenschutz-Feature)
 */

namespace App\Http\Controllers\UserDb;

use App\Models\SessionDb\CustInvite;
use App\Models\UserDb\CustPcode;
use App\Models\UserDb\CustUser;
use App\Models\UserDb\PolicyVersion;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class CustRegisterController extends UserDbController
{
    private function findValidInvite(string $token): ?CustInvite
    {
        return CustInvite::where('token', $token)
            ->where('used', false)
            ->where('expires_at', '>', now())
            ->first();
    }

    public function show(Request $request, string $token): View|RedirectResponse
    {
        $invite = $this->findValidInvite($token);

        if (! $invite) {
            return redirect()->route('home')
                ->withErrors(['token' => 'Einladungslink ungültig oder abgelaufen.']);
        }

        $existingCust = CustUser::where('cust_email', $invite->cust_email)->first();

        return view('customer.auth.register', [
            'token'         => $token,
            'cust_email'    => $invite->cust_email,
            'alreadyExists' => $existingCust !== null,
        ]);
    }

    public function store(Request $request, string $token): RedirectResponse
    {
        $invite = $this->findValidInvite($token);

        if (! $invite) {
            return redirect()->route('home')
                ->withErrors(['token' => 'Einladungslink ungültig oder abgelaufen.']);
        }

        if ($request->input('existing') === '1') {
            $request->validate([
                'cust_email' => ['required', 'email', 'max:255'],
            ]);

            $cust = CustUser::where('cust_email', $request->input('cust_email'))->first();

            if (! $cust) {
                return redirect()->route('home')
                    ->withErrors(['token' => 'Einladungslink ungültig oder abgelaufen.']);
            }
        } else {
            $rules = [
                'cust_uname'     => ['required', 'string', 'max:255', 'unique:userdb.cust_user,cust_uname'],
                'cust_firstname' => ['required', 'string', 'max:255'],
                'cust_lastname'  => ['required', 'string', 'max:255'],
                'cust_email'     => ['required', 'email', 'max:255'],
                'password'       => ['required', 'confirmed',
                    Password::min(10)->mixedCase()->numbers()],
                'ds_accepted'    => ['accepted'],
            ];

            $pflichtfelder = [
                'cust_tel'           => 'Telefon',
                'cust_street+nr'     => 'Strasse',
                'cust_postcode_city' => 'PLZOrt',
                'cust_company'       => 'Firma',
            ];

            foreach ($pflichtfelder as $field => $feldKey) {
                if (istPflichtfeld('cust', $feldKey)) {
                    $rules[$field] = ['required', 'string', 'max:255'];
                }
            }

            $validated = $request->validate($rules, [
                'ds_accepted.accepted' => 'Um ein Mitglieder-Konto zu erstellen, musst du der Datenschutzerklärung zustimmen.',
            ]);

            $cust = CustUser::create([
                'cust_uname'         => $validated['cust_uname'],
                'cust_firstname'     => $validated['cust_firstname'],
                'cust_lastname'      => $validated['cust_lastname'],
                'cust_email'         => $validated['cust_email'],
                'cust_tel'           => $validated['cust_tel'] ?? null,
                'cust_company'       => $validated['cust_company'] ?? null,
                'cust_street+nr'     => $validated['cust_street+nr'] ?? null,
                'cust_postcode_city' => $validated['cust_postcode_city'] ?? null,
                'cust_pw_hash'       => Hash::make($validated['password']),
                'cust_2fa_opt_in'    => false,
                'ds_accepted_at'     => now(),
                'ds_version'         => PolicyVersion::get('ds_version'),
            ]);
        }

        CustPcode::create([
            'mand_id'        => $invite->mand_id,
            'cust_id'        => $cust->cust_id,
            'cust_passcode'  => $invite->sec_level,
            'cust_alias'     => $invite->cust_alias ?? '',
            'pcode_prefstat' => 1,
        ]);

        $invite->update(['used' => true]);

        return redirect()->route('home')
            ->with('status', 'Konto erfolgreich angelegt. Bitte melde dich jetzt als Mitglied an.');
    }
}
