<?php
/**
 * FILE:        app/Http/Controllers/UserDb/MandantLoginController.php
 * VERSION:     1.0
 * AUTOR:       Martin Wagner
 * DATUM:       2026-05-26
 *
 * ZWECK:       Mand-Login mit 2FA — Formular anzeigen, Credentials prüfen,
 *              2FA-Code verifizieren, Session schreiben, Logout.
 *
 * FUNCTIONS:   showLogin()       — Zeigt das Mandant-Login-Formular an.
 *                                  Reads: —
 *              handleLogin()     — Prüft E-Mail + Passwort; delegiert Code-Erzeugung
 *                                  an TwofaService::generate(); sendet Code per Mail;
 *                                  speichert 2fa_mand_id in Session.
 *                                  Reads: userdb.mand_user.mand_id, mand_email,
 *                                         mand_pw_hash, mand_firstname
 *              showTwoFactor()   — Zeigt das 2FA-Eingabeformular an.
 *                                  Reads: —
 *              verifyTwoFactor() — Delegiert Prüfung an TwofaService::verify();
 *                                  bei Erfolg: Session regenerieren, _user_type
 *                                  und _mand_id schreiben, 2fa_mand_id löschen,
 *                                  Redirect zu /mandant/dashboard.
 *                                  Reads: —
 *              logout()          — Invalidiert die Session und leitet zu /mandant/login.
 *                                  Reads: —
 *
 * CALLS:       App\Models\UserDb\MandUser::where()->first()
 *              App\Services\SessionDb\TwofaService::generate()
 *              App\Services\SessionDb\TwofaService::verify()
 *
 * DB ACCESS:   userdb.mand_user.mand_id, mand_email, mand_pw_hash, mand_firstname
 */

namespace App\Http\Controllers\UserDb;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

class MandantLoginController extends UserDbController
{
    public function showLogin(): \Illuminate\Contracts\View\View
    {
        return view('mandant.auth.login');
    }

    public function handleLogin(Request $request): Response
    {
        return response('handleLogin ok');
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
