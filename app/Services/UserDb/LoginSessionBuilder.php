<?php
/**
 * FILE:        app/Services/UserDb/LoginSessionBuilder.php
 * VERSION:     1.0.0
 * AUTOR:       Martin Wagner
 * DATUM:       2026-07-17
 *
 * ZWECK:       Zentralisiert den Session-Aufbau für cust und mand nach
 *              erfolgreicher Authentifizierung (Passwort-Login, 2FA-
 *              Verifikation oder — künftig — Trusted-Device-Auto-Login).
 *              Reine Extraktion aus CustLoginController::handleLogin()
 *              bzw. MandantLoginController::buildMandSession(), keine
 *              Verhaltensänderung.
 *
 * FUNCTIONS:   buildForCust(Request, CustUser, CustPcode): RedirectResponse
 *                  — Baut die Cust-Session auf (Session-Regeneration,
 *                  _user_type/_cust_id/_mand_id/_sec_level/_last_activity,
 *                  OS-/Passkey-Prompt-Logik, Session-Cleanup), Redirect zu
 *                  customer.content.
 *                  Reads:  userdb.passkey.user_type, user_id
 *                          userdb.passkey_dismissed.user_type, user_id, os, ua_hash
 *                  Writes: sessiondb.session (via Session-Regeneration)
 *
 *              buildForMand(Request, MandUser): RedirectResponse
 *                  — Baut die Mand-Session auf (Session-Regeneration,
 *                  _user_type/_mand_id, OS-/Passkey-Prompt-Logik,
 *                  Session-Cleanup), Redirect zu mandant.dashboard.
 *                  Reads:  userdb.passkey.user_type, user_id
 *                          userdb.passkey_dismissed.user_type, user_id, os, ua_hash
 *                  Writes: sessiondb.session (via Session-Regeneration)
 *
 * CALLS:       App\Services\SessionDb\SessionIntegrityService::buildSessionData()
 *              App\Models\UserDb\Passkey::where()
 *              App\Models\UserDb\PasskeyDismissed::where()
 *              App\Models\SessionDb\Session::where()->delete()
 *
 * DB ACCESS:   userdb.passkey, userdb.passkey_dismissed,
 *              sessiondb.session (expired cleanup)
 */

namespace App\Services\UserDb;

use App\Models\UserDb\CustUser;
use App\Models\UserDb\CustPcode;
use App\Models\UserDb\MandUser;
use App\Models\UserDb\Passkey;
use App\Services\SessionDb\SessionIntegrityService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class LoginSessionBuilder
{
    public function __construct(
        private readonly SessionIntegrityService $sessionIntegrityService,
    ) {}

    /**
     * Baut die Cust-Session auf (nach Passwort-Login, 2FA-Verifikation,
     * oder Trusted-Device-Auto-Login). Benötigt sowohl das CustUser- als
     * auch das CustPcode-Objekt (bevorzugter Mandant + Sicherheitsstufe).
     */
    public function buildForCust(Request $request, CustUser $cust, CustPcode $pcode): RedirectResponse
    {
        $sessionData = $this->sessionIntegrityService->buildSessionData('cust', $cust->cust_id);

        $request->session()->regenerate(true);
        $request->session()->put('_user_type',     $sessionData['user_type']);
        $request->session()->put('_cust_id',       $cust->cust_id);
        $request->session()->put('_mand_id',       $pcode->mand_id);
        $request->session()->put('_sec_level',     $pcode->cust_passcode);
        $request->session()->put('_last_activity', time());

        $newSessionId = substr($request->session()->getId(), 0, 128);
        $custId       = $cust->cust_id;
        $mandId       = $pcode->mand_id;

        app()->terminating(function () use ($newSessionId, $custId, $mandId) {
            DB::connection('sessiondb')->table('session')
                ->where('sess_token', $newSessionId)
                ->update([
                    'user_type' => 'cust',
                    'cust_id'   => $custId,
                    'mand_id'   => $mandId,
                ]);
        });

        // OS erkennen
        $os = detectOsPlatform($request->userAgent());

        // Passkey für dieses OS und diesen User bereits vorhanden?
        $hasPasskey = Passkey::where('user_type', 'cust')
            ->where('user_id', $cust->cust_id)
            ->exists();

        // ua_hash berechnen (analog SessionHijackProtection)
        $uaHash = hash('sha256', $request->userAgent() ?? '');

        // "Nie wieder fragen" für dieses Gerät + OS gesetzt?
        // Auf iOS teilen sich alle Browser den iCloud-Schlüsselbund,
        // daher dort ua_hash NICHT in den Abgleich einbeziehen.
        $neverAskQuery = \App\Models\UserDb\PasskeyDismissed::where('user_type', 'cust')
            ->where('user_id', $cust->cust_id)
            ->where('os', $os);

        if ($os !== 'ios') {
            $neverAskQuery->where('ua_hash', $uaHash);
        }

        $neverAsk = $neverAskQuery->exists();

        // Prompt setzen
        session([
            '_prompt_passkey'  => !$hasPasskey && !$neverAsk && $os !== 'unknown',
            '_passkey_os'      => $os,
            '_passkey_browser' => detectBrowser($request->userAgent()),
            '_passkey_uahash'  => $uaHash,
        ]);

        $this->cleanupExpiredRecords();

        return redirect()->route('customer.content');
    }

    /**
     * Baut die Mand-Session auf (nach Passwort-Login, 2FA-Verifikation,
     * oder Trusted-Device-Auto-Login).
     */
    public function buildForMand(Request $request, MandUser $mand): RedirectResponse
    {
        $sessionData = $this->sessionIntegrityService->buildSessionData('mand', $mand->mand_id);

        $request->session()->regenerate(true);
        $request->session()->put('_user_type', $sessionData['user_type']);
        $request->session()->put('_mand_id',   $sessionData['mand_id']);
        $request->session()->forget('pending_mand_id');

        $newSessionId = substr($request->session()->getId(), 0, 128);
        $mandId       = $mand->mand_id;

        app()->terminating(function () use ($newSessionId, $mandId) {
            DB::connection('sessiondb')->table('session')
                ->where('sess_token', $newSessionId)
                ->update([
                    'user_type' => 'mand',
                    'mand_id'   => $mandId,
                ]);
        });

        // OS erkennen
        $os = detectOsPlatform($request->userAgent());

        // Passkey für dieses OS und diesen User bereits vorhanden?
        $hasPasskey = Passkey::where('user_type', 'mand')
            ->where('user_id', $mand->mand_id)
            ->exists();

        // ua_hash berechnen (analog SessionHijackProtection)
        $uaHash = hash('sha256', $request->userAgent() ?? '');

        // "Nie wieder fragen" für dieses Gerät + OS gesetzt?
        // Auf iOS teilen sich alle Browser den iCloud-Schlüsselbund,
        // daher dort ua_hash NICHT in den Abgleich einbeziehen.
        $neverAskQuery = \App\Models\UserDb\PasskeyDismissed::where('user_type', 'mand')
            ->where('user_id', $mand->mand_id)
            ->where('os', $os);

        if ($os !== 'ios') {
            $neverAskQuery->where('ua_hash', $uaHash);
        }

        $neverAsk = $neverAskQuery->exists();

        // Prompt setzen
        session([
            '_prompt_passkey'  => !$hasPasskey && !$neverAsk && $os !== 'unknown',
            '_passkey_os'      => $os,
            '_passkey_browser' => detectBrowser($request->userAgent()),
            '_passkey_uahash'  => $uaHash,
        ]);

        $this->cleanupExpiredRecords();

        return redirect()->route('mandant.dashboard');
    }

    /**
     * Bereinigt global (kein user_id/cust_id-Bezug) abgelaufene Datensätze
     * aus allen Tabellen mit Verfallsdatum. userdb.cust_invite bleibt
     * bewusst ausgenommen (dokumentiertes, ungenutztes Relikt).
     */
    private function cleanupExpiredRecords(): void
    {
        \App\Models\SessionDb\Session::where('expires_at', '<', now())
            ->delete();

        \App\Models\UserDb\Invite::where('expires_at', '<', now())
            ->delete();

        \App\Models\SessionDb\CustInvite::where('expires_at', '<', now())
            ->delete();

        \App\Models\SessionDb\TrustedDevice::where('expires_at', '<', now())
            ->delete();

        \App\Models\SessionDb\TwofaCode::where('tfa_expires_at', '<', now())
            ->delete();
    }
}
