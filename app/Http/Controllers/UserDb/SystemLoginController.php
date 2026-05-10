<?php
/**
 * FILE:        app/Http/Controllers/UserDb/SystemLoginController.php
 * VERSION:     1.0.0
 *
 * FUNCTIONS:   login()             — Zeigt das System-Login-Formular an.
 *                                    Reads: —
 *              handleLogin()       — Prüft E-Mail + Passwort; generiert 6-stelligen
 *                                    2FA-Code; schreibt 2fa_code / 2fa_expires_at /
 *                                    2fa_syst_id in die Session; sendet Code per E-Mail;
 *                                    leitet mit show_2fa-Flash zurück zum Formular.
 *                                    Reads:  userdb.syst_user.syst_id, syst_email,
 *                                            syst_pw_hash
 *              verifyTwoFactor()   — Vergleicht Code aus Request mit session('2fa_code')
 *                                    und prüft Ablaufzeit (2fa_expires_at). Bei Fehler:
 *                                    Redirect zurück mit Fehlermeldung + show_2fa-Flash.
 *                                    Bei Erfolg: Session regenerieren, _user_type und
 *                                    _syst_id schreiben, 2FA-Keys löschen, Redirect
 *                                    zu /system/dashboard.
 *                                    Reads:  —
 *              sendTwoFactorEmail()— Privater Stub: schreibt Code ins Log.
 *                                    Reads:  —
 *
 * CALLS:       App\Models\UserDb\SystUser::where()->first()
 *              Illuminate\Support\Facades\Hash::check()
 *              Illuminate\Support\Facades\Log::info()
 *              Illuminate\Http\Request::session()::put()
 *              Illuminate\Http\Request::session()::get()
 *              Illuminate\Http\Request::session()::forget()
 *              Illuminate\Http\Request::session()::regenerate()
 *
 * DB ACCESS:   userdb.syst_user.syst_id, syst_email, syst_pw_hash
 */

namespace App\Http\Controllers\UserDb;

use App\Models\UserDb\SystUser;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class SystemLoginController extends UserDbController
{
    public function login(): View
    {
        return view('system.login');
    }

    public function handleLogin(Request $request): RedirectResponse
    {
        $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = SystUser::where('syst_email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->syst_pw_hash)) {
            return back()
                ->withErrors(['credentials' => 'Ungültige Anmeldedaten.'])
                ->withInput(['email' => $request->email]);
        }

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $request->session()->put('2fa_code', $code);
        $request->session()->put('2fa_expires_at', now()->addMinutes(5)->timestamp);
        $request->session()->put('2fa_syst_id', $user->syst_id);

        $this->sendTwoFactorEmail($user->syst_email, $code);

        return back()->with('show_2fa', true);
    }

    public function verifyTwoFactor(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        $storedCode = $request->session()->get('2fa_code');
        $expiresAt  = $request->session()->get('2fa_expires_at');
        $systId     = $request->session()->get('2fa_syst_id');

        if (
            ! $storedCode ||
            ! $expiresAt ||
            time() > $expiresAt ||
            ! hash_equals($storedCode, $request->string('code')->toString())
        ) {
            return back()
                ->withErrors(['code' => 'Ungültiger oder abgelaufener Code.'])
                ->with('show_2fa', true);
        }

        $request->session()->regenerate();

        $request->session()->put('_user_type', 'syst');
        $request->session()->put('_syst_id', $systId);

        $request->session()->forget(['2fa_code', '2fa_expires_at', '2fa_syst_id']);

        return redirect('/system/dashboard');
    }

    private function sendTwoFactorEmail(string $email, string $code): void
    {
        Log::info('2FA-Code für System-Login', ['email' => $email, 'code' => $code]);
        // TODO: E-Mail mit 2FA-Code an $email senden
    }
}
