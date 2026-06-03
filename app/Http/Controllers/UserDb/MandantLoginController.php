<?php
/**
 * FILE:        app/Http/Controllers/UserDb/MandantLoginController.php
 * VERSION:     1.7
 * AUTOR:       Martin Wagner
 * DATUM:       2026-05-28
 *
 * ZWECK:       Mand-Login mit 2FA — Formular anzeigen, Credentials prüfen,
 *              2FA-Code verifizieren, Session schreiben, Logout.
 *
 * FUNCTIONS:   showLogin()       — Zeigt das Mandant-Login-Formular an.
 *                                  Reads: —
 *              handleLogin()     — Validiert mand_email + password; sucht MandUser
 *                                  per mand_email; prüft Hash; erzeugt 2FA-Code via
 *                                  TwofaService::generate(); sendet Code per
 *                                  TwoFactorCodeMail; speichert pending_mand_id in
 *                                  Session; leitet zu mandant.login.2fa.
 *                                  Reads: userdb.mand_user.mand_id, mand_email,
 *                                         mand_pw_hash, mand_firstname
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
 * CALLS:       App\Models\UserDb\MandUser::where()->first()
 *              App\Mail\TwoFactorCodeMail
 *              App\Services\SessionDb\TwofaService::generate()
 *              App\Services\SessionDb\TwofaService::verify()
 *              App\Services\SessionDb\SessionIntegrityService::buildSessionData()
 *              Illuminate\Support\Facades\Hash::check()
 *              Illuminate\Support\Facades\Mail::to()->send()
 *              Illuminate\Support\Facades\DB::connection('sessiondb')->table()->delete()
 *
 * DB ACCESS:   userdb.mand_user.mand_id, mand_email, mand_pw_hash, mand_firstname
 *              sessiondb.twofa_code.* (via TwofaService)
 *              sessiondb.session.expires_at (DELETE bei Login)
 */

namespace App\Http\Controllers\UserDb;

use App\Mail\TwoFactorCodeMail;
use App\Models\UserDb\MandUser;
use App\Services\SessionDb\SessionIntegrityService;
use App\Services\SessionDb\TwofaService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

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
        $request->validate([
            'mand_email' => ['required', 'email'],
            'password'   => ['required', 'string'],
        ]);

        $mand = MandUser::where('mand_email', $request->mand_email)->first();

        if (! $mand || ! Hash::check($request->password, $mand->mand_pw_hash)) {
            return back()
                ->withErrors(['credentials' => 'Diese Zugangsdaten sind uns nicht bekannt.'])
                ->withInput(['mand_email' => $request->mand_email])
                ->with('login_page', 'mand');
        }

        $code = $this->twofaService->generate('mand', $mand->mand_id, 'login');

        Mail::to($mand->mand_email)
            ->send(new TwoFactorCodeMail($code, $mand->mand_firstname ?? ''));

        $request->session()->put('pending_mand_id', $mand->mand_id);

        return redirect()->route('mandant.login.2fa');
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

        DB::connection('sessiondb')
            ->table('session')
            ->where('expires_at', '<', now())
            ->delete();

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
