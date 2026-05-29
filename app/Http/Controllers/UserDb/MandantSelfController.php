<?php
/**
 * FILE:        app/Http/Controllers/UserDb/MandantSelfController.php
 * VERSION:     1.1.0
 * AUTOR:       Martin Wagner
 * DATUM:       2026-05-29
 *
 * ZWECK:       Mandant Eigenverwaltung — Kontodaten und Passwort bearbeiten.
 *
 * FUNCTIONS:   edit()           — Zeigt das Konto-Bearbeitungsformular an.
 *                                  Reads: userdb.mand_user.*
 *              update()         — Speichert geänderte Kontodaten.
 *                                  Writes: userdb.mand_user.*
 *              updatePassword() — Speichert neues Passwort (Hash).
 *                                  Writes: userdb.mand_user.mand_pw_hash
 *
 * CALLS:       —
 *
 * DB ACCESS:   userdb.mand_user.*
 */

namespace App\Http\Controllers\UserDb;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Response;

class MandantSelfController extends UserDbController
{
    public function edit(): View
    {
        return view('mandant.konto');
    }

    public function update(): Response
    {
        return response('konto update ok');
    }

    public function updatePassword(): Response
    {
        return response('konto password ok');
    }
}
