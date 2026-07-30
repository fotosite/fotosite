<?php
/**
 * FILE:        app/Http/Controllers/UserDb/SystemLoginController.php
 * VERSION:     1.6.0
 * DATUM:       2026-07-30
 *
 * FUNCTIONS:   login()           — Zeigt das System-Login-Formular an.
 *                                  Reads: —
 *              handleLogin()     — Prüft E-Mail + Passwort; delegiert Code-Erzeugung
 *                                  an TwofaService::generate(); sendet Code per
 *                                  TwoFactorCodeMail; speichert 2fa_syst_id in Session;
 *                                  leitet mit show_2fa-Flash zurück zum Formular.
 *                                  Reads: userdb.syst_user.syst_id, syst_email,
 *                                         syst_pw_hash, syst_firstname
 *              verifyTwoFactor() — Delegiert Prüfung an TwofaService::verify();
 *                                  bei Fehler: Redirect zurück ohne Details +
 *                                  show_2fa-Flash; bei Erfolg: Session regenerieren,
 *                                  _user_type, _syst_id und _is_primary schreiben,
 *                                  2fa_syst_id löschen, Redirect zu /system/dashboard.
 *                                  Reads: userdb.syst_user.is_primary
 *              logout()          — Session invalidieren + Token regenerieren;
 *                                  Redirect zu route('home') (Login-Modal).
 *                                  Reads: —
 *
 * CALLS:       App\Models\UserDb\SystUser::where()->first()
 *              App\Models\UserDb\SystUser::find()
 *              checkLoginThrottle()/recordFailedLoginAttempt()/clearLoginThrottle()
 *              (app/helpers.php)
 *              App\Services\SessionDb\TwofaService::generate()
 *              App\Services\SessionDb\TwofaService::verify()
 *              App\Mail\TwoFactorCodeMail
 *              Illuminate\Support\Facades\Hash::check()
 *              Illuminate\Support\Facades\Mail::to()->send()
 *              Illuminate\Support\Facades\DB::connection('sessiondb')->table()->delete()
 *              App\Models\SessionDb\Session::where()->delete()
 *
 * DB ACCESS:   userdb.syst_user.syst_id, syst_email, syst_pw_hash, syst_firstname,
 *              is_primary
 *              sessiondb.twofa_code.* (via TwofaService)
 *              sessiondb.session.expires_at (DELETE abgelaufene Sessions bei Login)
 *              sessiondb.session.sess_token (DELETE eigene Session bei Logout)
 *
 * CHANGES:     1.6.0 (2026-07-30) Einheitliche, IP-basierte, rollenübergreifende
 *              Login-Sperre ergänzt (checkLoginThrottle()/recordFailedLoginAttempt()/
 *              clearLoginThrottle() aus app/helpers.php) in handleLogin() und
 *              verifyTwoFactor() — es existierte bisher kein RateLimiter für
 *              syst-Login-Routen, daher rein additiv.
 *              1.5.0 (2026-06-22) verifyTwoFactor() schreibt zusätzlich _is_primary
 *              in die Session (aus userdb.syst_user.is_primary) — Grundlage für
 *              Berechtigungsprüfungen rund um primäre System-User.
 */

namespace App\Http\Controllers\UserDb;

use App\Mail\TwoFactorCodeMail;
use App\Models\UserDb\SystUser;
use App\Services\SessionDb\TwofaService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class SystemLoginController extends UserDbController
{
    public function __construct(private readonly TwofaService $twofaService) {}

    public function login(): View
    {
        return view('system.login');
    }

    public function handleLogin(Request $request): RedirectResponse
    {
        if ($lockMsg = checkLoginThrottle($request)) {
            return back()
                ->withErrors(['credentials' => $lockMsg], 'syst')
                ->withInput(['email' => $request->email]);
        }

        $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = SystUser::where('syst_email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->syst_pw_hash)) {
            if ($lockMsg = recordFailedLoginAttempt($request)) {
                return back()
                    ->withErrors(['credentials' => $lockMsg], 'syst')
                    ->withInput(['email' => $request->email]);
            }

            return back()
                ->withErrors(['credentials' => 'Ungültige Anmeldedaten.'], 'syst')
                ->withInput(['email' => $request->email]);
        }

        $code = $this->twofaService->generate('syst', $user->syst_id, 'login');

        Mail::to($user->syst_email)
            ->send(new TwoFactorCodeMail($code, $user->syst_firstname ?? ''));

        $request->session()->put('2fa_syst_id', $user->syst_id);

        return back()->with('show_2fa', true);
    }

    public function verifyTwoFactor(Request $request): RedirectResponse
    {
        if ($lockMsg = checkLoginThrottle($request)) {
            return back()
                ->withErrors(['code' => $lockMsg], 'syst')
                ->with('show_2fa', true);
        }

        $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        $systId = $request->session()->get('2fa_syst_id');

        if (! $systId || ! $this->twofaService->verify('syst', $systId, 'login', $request->string('code')->toString())) {
            if ($lockMsg = recordFailedLoginAttempt($request)) {
                return back()
                    ->withErrors(['code' => $lockMsg], 'syst')
                    ->with('show_2fa', true);
            }

            return back()
                ->withErrors(['code' => 'Ungültiger oder abgelaufener Code.'], 'syst')
                ->with('show_2fa', true);
        }

        clearLoginThrottle($request);

        $request->session()->regenerate(true);

        $request->session()->put('_user_type', 'syst');
        $request->session()->put('_syst_id', $systId);
        $request->session()->put('_is_primary', (bool) SystUser::find($systId)?->is_primary);
        $request->session()->forget('2fa_syst_id');

        $newSessionId = substr($request->session()->getId(), 0, 128);

        app()->terminating(function () use ($newSessionId, $systId) {
            DB::connection('sessiondb')->table('session')
                ->where('sess_token', $newSessionId)
                ->update([
                    'user_type' => 'syst',
                    'syst_id'   => $systId,
                ]);
        });

        DB::connection('sessiondb')
            ->table('session')
            ->where('expires_at', '<', now())
            ->delete();

        return redirect('/system/dashboard');
    }

    public function logout(Request $request): RedirectResponse
    {
        // Eigene Session aus DB löschen
        $sessToken = session()->getId();
        \App\Models\SessionDb\Session::where('sess_token', $sessToken)
            ->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
