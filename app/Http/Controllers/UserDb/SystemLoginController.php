<?php
/**
 * FILE:        app/Http/Controllers/UserDb/SystemLoginController.php
 * VERSION:     1.1.0
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
 *                                  _user_type und _syst_id schreiben,
 *                                  2fa_syst_id löschen, Redirect zu /system/dashboard.
 *                                  Reads: —
 *
 * CALLS:       App\Models\UserDb\SystUser::where()->first()
 *              App\Services\SessionDb\TwofaService::generate()
 *              App\Services\SessionDb\TwofaService::verify()
 *              App\Mail\TwoFactorCodeMail
 *              Illuminate\Support\Facades\Hash::check()
 *              Illuminate\Support\Facades\Mail::to()->send()
 *
 * DB ACCESS:   userdb.syst_user.syst_id, syst_email, syst_pw_hash, syst_firstname
 *              sessiondb.twofa_code.* (via TwofaService)
 */

namespace App\Http\Controllers\UserDb;

use App\Mail\TwoFactorCodeMail;
use App\Models\UserDb\SystUser;
use App\Services\SessionDb\TwofaService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

        $code = $this->twofaService->generate('syst', $user->syst_id, 'login');

        Mail::to($user->syst_email)
            ->send(new TwoFactorCodeMail($code, $user->syst_firstname ?? ''));

        $request->session()->put('2fa_syst_id', $user->syst_id);

        return back()->with('show_2fa', true);
    }

    public function verifyTwoFactor(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        $systId = $request->session()->get('2fa_syst_id');

        if (! $systId || ! $this->twofaService->verify('syst', $systId, 'login', $request->string('code')->toString())) {
            return back()
                ->withErrors(['code' => 'Ungültiger oder abgelaufener Code.'])
                ->with('show_2fa', true);
        }

        $request->session()->regenerate();

        $request->session()->put('_user_type', 'syst');
        $request->session()->put('_syst_id', $systId);
        $request->session()->forget('2fa_syst_id');

        return redirect('/system/dashboard');
    }
}
