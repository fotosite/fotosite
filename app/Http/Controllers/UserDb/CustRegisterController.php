<?php
/**
 * FILE:        app/Http/Controllers/UserDb/CustRegisterController.php
 * VERSION:     1.0.0
 * AUTHOR:      Martin Wagner
 * DATE:        2026-05-30
 * PURPOSE:     Kunden-Registrierung per Einladungs-Token (Stub)
 *
 * FUNCTIONS:   show()   — Validiert Token, zeigt Registrierungsformular
 *                          Reads: sessiondb.cust_invite.token, expires_at, used
 *              store()  — Verarbeitet Registrierung, erstellt CustUser + CustPcode
 *                          Reads:  sessiondb.cust_invite.*
 *                          Writes: userdb.cust_user.*, userdb.cust_pcode.*
 *                                  sessiondb.cust_invite.used (UPDATE)
 *
 * CALLS:       (Stubs — noch keine Service-Aufrufe)
 *
 * DB ACCESS:   sessiondb.cust_invite.invite_id, token, expires_at, used, mand_id,
 *              cust_email, sec_level
 *              userdb.cust_user.*
 *              userdb.cust_pcode.*
 */

namespace App\Http\Controllers\UserDb;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CustRegisterController extends UserDbController
{
    public function show(Request $request, string $token): Response
    {
        return response('register show ok');
    }

    public function store(Request $request, string $token): Response
    {
        return response('register store ok');
    }
}
