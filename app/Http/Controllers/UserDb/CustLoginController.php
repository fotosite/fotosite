<?php
/**
 * FILE:        app/Http/Controllers/UserDb/CustLoginController.php
 * VERSION:     1.1.0
 * AUTOR:       Martin Wagner
 * DATUM:       2026-06-03
 *
 * ZWECK:       Cust-Login — registrierter Login mit optionaler 2FA,
 *              anonymer Login per Passwort-Sequenz, Logout.
 *
 * FUNCTIONS:   showLogin()       — Zeigt das Cust-Login-Formular an.
 *                                  Reads: —
 *              handleLogin()     — Validiert cust_email + password; sucht CustUser;
 *                                  ermittelt bevorzugten Mandanten via CustPcode;
 *                                  prüft 2FA-Pflicht via mand_cust_2fa; baut Session
 *                                  direkt oder leitet zu customer.login.2fa.
 *                                  Reads: userdb.cust_user.cust_id, cust_email,
 *                                         cust_pw_hash, cust_firstname
 *                                         userdb.cust_pcode.pcode_id, cust_id, mand_id,
 *                                         cust_passcode, pcode_prefstat
 *                                         userdb.mand_user.mand_id, mand_cust_2fa
 *              handleAnonLogin() — [Stub] Prüft Passwort-Sequenz gegen pw_list;
 *                                  setzt anonyme Session.
 *                                  Reads: —
 *              showTwoFactor()   — [Stub] Zeigt 2FA-Formular für Cust-Login.
 *                                  Reads: —
 *              verifyTwoFactor() — [Stub] Verifiziert 2FA-Code; schreibt Session.
 *                                  Reads: —
 *              logout()          — [Stub] Invalidiert Session, Redirect zu login.
 *                                  Reads: —
 *
 * CALLS:       App\Models\UserDb\CustUser::where()->first()
 *              App\Models\UserDb\CustPcode::where()->orderByDesc()->first()
 *              App\Models\UserDb\MandUser::find()
 *              App\Mail\TwoFactorCodeMail
 *              App\Services\SessionDb\TwofaService::generateCode()
 *              App\Services\SessionDb\SessionIntegrityService::buildSessionData()
 *              Illuminate\Support\Facades\Hash::check()
 *              Illuminate\Support\Facades\Mail::to()->send()
 *
 * DB ACCESS:   userdb.cust_user.cust_id, cust_email, cust_pw_hash, cust_firstname
 *              userdb.cust_pcode.pcode_id, cust_id, mand_id, cust_passcode, pcode_prefstat
 *              userdb.mand_user.mand_id, mand_cust_2fa
 */

namespace App\Http\Controllers\UserDb;

use App\Mail\TwoFactorCodeMail;
use App\Models\UserDb\CustPcode;
use App\Models\UserDb\CustUser;
use App\Models\UserDb\MandUser;
use App\Services\SessionDb\SessionIntegrityService;
use App\Services\SessionDb\TwofaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class CustLoginController extends UserDbController
{
    public function __construct(
        private readonly TwofaService $twofaService,
        private readonly SessionIntegrityService $sessionIntegrityService,
    ) {}

    public function showLogin(): Response
    {
        return response('cust login ok');
    }

    public function handleLogin(Request $request): RedirectResponse
    {
        $request->validate([
            'cust_email' => ['required', 'email'],
            'password'   => ['required', 'string'],
        ]);

        $cust = CustUser::where('cust_email', $request->cust_email)->first();

        if (! $cust || ! Hash::check($request->password, $cust->cust_pw_hash)) {
            return back()
                ->withErrors(['credentials' => 'Diese Zugangsdaten sind uns nicht bekannt.'])
                ->with('login_page', 'cust')
                ->with('cust_tab', 'reg');
        }

        $pcode = CustPcode::where('cust_id', $cust->cust_id)
            ->orderByDesc('pcode_prefstat')
            ->orderByDesc('pcode_id')
            ->first();

        if (! $pcode) {
            return back()
                ->withErrors(['credentials' => 'Kein Mandant zugeordnet.'])
                ->with('login_page', 'cust')
                ->with('cust_tab', 'reg');
        }

        $mand = MandUser::find($pcode->mand_id);

        if (! $mand) {
            return back()
                ->withErrors(['credentials' => 'Kein Mandant zugeordnet.'])
                ->with('login_page', 'cust')
                ->with('cust_tab', 'reg');
        }

        $secLevel  = (int) $pcode->cust_passcode;
        $threshold = $mand->mand_cust_2fa; // cast to int via model

        if ($secLevel >= $threshold && $threshold < 7) {
            $code = $this->twofaService->generateCode('cust', $cust->cust_id, 0);

            Mail::to($cust->cust_email)
                ->send(new TwoFactorCodeMail($code, $cust->cust_firstname ?? ''));

            $request->session()->put('pending_cust_id',   $cust->cust_id);
            $request->session()->put('pending_mand_id',   $pcode->mand_id);
            $request->session()->put('pending_sec_level', $pcode->cust_passcode);

            return redirect()->route('customer.login.2fa');
        }

        $sessionData = $this->sessionIntegrityService->buildSessionData('cust', $cust->cust_id);

        $request->session()->regenerate();
        $request->session()->put('_user_type',     $sessionData['user_type']);
        $request->session()->put('_cust_id',       $cust->cust_id);
        $request->session()->put('_mand_id',       $pcode->mand_id);
        $request->session()->put('_sec_level',     $pcode->cust_passcode);
        $request->session()->put('_last_activity', time());

        return redirect()->route('customer.dashboard');
    }

    public function handleAnonLogin(Request $request): Response
    {
        return response('handleAnonLogin ok');
    }

    public function showTwoFactor(Request $request): Response
    {
        return response('showTwoFactor ok');
    }

    public function verifyTwoFactor(Request $request): Response
    {
        return response('verifyTwoFactor ok');
    }

    public function logout(Request $request): Response
    {
        return response('logout ok');
    }
}
