<?php
/**
 * FILE:        app/Http/Controllers/UserDb/CustLoginController.php
 * VERSION:     1.0.0
 * AUTOR:       Martin Wagner
 * DATUM:       2026-06-03
 *
 * ZWECK:       Cust-Login — registrierter Login mit optionaler 2FA,
 *              anonymer Login per Passwort-Sequenz, Logout.
 *
 * FUNCTIONS:   showLogin()       — Zeigt das Cust-Login-Formular an.
 *                                  Reads: —
 *              handleLogin()     — [Stub] Validiert cust_email + password;
 *                                  prüft 2FA-Pflicht via mand_cust_2fa.
 *                                  Reads: —
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
 * CALLS:       (none — alle Methoden sind Stubs)
 *
 * DB ACCESS:   (none — alle Methoden sind Stubs)
 */

namespace App\Http\Controllers\UserDb;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CustLoginController extends UserDbController
{
    public function showLogin(): Response
    {
        return response('cust login ok');
    }

    public function handleLogin(Request $request): Response
    {
        return response('handleLogin ok');
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
