<?php
/**
 * FILE:        app/Http/Controllers/UserDb/MandantLoginController.php
 * VERSION:     1.3
 * AUTOR:       Martin Wagner
 * DATUM:       2026-05-28
 *
 * ZWECK:       Mand-Login mit 2FA — Formular anzeigen, Credentials prüfen,
 *              2FA-Code verifizieren, Session schreiben, Logout.
 *
 * FUNCTIONS:   showLogin()       — Zeigt das Mandant-Login-Formular an.
 *                                  Reads: —
 *              handleLogin()     — Stub; folgt in Abschnitt 5.
 *                                  Reads: —
 *              showTwoFactor()   — Prüft pending_mand_id in Session;
 *                                  zeigt mandant.auth.two-factor View an.
 *                                  Reads: —
 *              verifyTwoFactor() — Validiert tfa_code (digits:6); liest
 *                                  pending_mand_id aus Session; delegiert Prüfung
 *                                  an TwofaService::verify(); bei Erfolg: Session
 *                                  regenerieren, _user_type und _mand_id schreiben,
 *                                  pending_mand_id löschen, Redirect zu mandant.dashboard.
 *                                  Reads: sessiondb.twofa_code.* (via TwofaService)
 *              logout()          — Invalidiert die Session und leitet zu mandant.login
 *                                  mit status-Flash 'Sie wurden erfolgreich abgemeldet.'
 *                                  Reads: —
 *
 * CALLS:       App\Services\SessionDb\TwofaService::verify()
 *              App\Services\SessionDb\SessionIntegrityService::buildSessionData()
 *
 * DB ACCESS:   sessiondb.twofa_code.* (via TwofaService)
 */

namespace App\Http\Controllers\UserDb;

use App\Services\SessionDb\SessionIntegrityService;
use App\Services\SessionDb\TwofaService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MandantLoginController extends UserDbController
{
    public function __construct(
        private readonly TwofaService $twofaService,
        private readonly SessionIntegrityService $sessionIntegrityService,
    ) {}

    public function showLogin(): View
    {
        return view('auth.login-modal');
    }

    public function handleLogin(Request $request): RedirectResponse
    {
        // Stub — wird in Abschnitt 5 implementiert
        return redirect()->route('mandant.login');
    }

    public function showTwoFactor(Request $request): View|RedirectResponse
    {
        if (! $request->session()->has('pending_mand_id')) {
            return redirect()->route('mandant.login');
        }

        return view('mandant.auth.two-factor');
    }

    public function verifyTwoFactor(Request $request): RedirectResponse
    {
        $request->validate([
            'tfa_code' => ['required', 'digits:6'],
        ]);

        $mandId = $request->session()->get('pending_mand_id');

        if (! $mandId) {
            return redirect()->route('mandant.login');
        }

        $verified = $this->twofaService->verify(
            'mand',
            (int) $mandId,
            'login',
            $request->string('tfa_code')->toString()
        );

        if (! $verified) {
            return back()->withErrors(['tfa_code' => 'Ungültiger oder abgelaufener Code.']);
        }

        $sessionData = $this->sessionIntegrityService->buildSessionData('mand', (int) $mandId);

        $request->session()->regenerate();
        $request->session()->put('_user_type', $sessionData['user_type']);
        $request->session()->put('_mand_id',   $sessionData['mand_id']);
        $request->session()->forget('pending_mand_id');

        return redirect()->route('mandant.dashboard');
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('mandant.login')
            ->with('status', 'Sie wurden erfolgreich abgemeldet.');
    }
}
