<?php
/**
 * FILE:        app/Http/Controllers/UserDb/MandantSelfController.php
 * VERSION:     1.2.0
 * AUTOR:       Martin Wagner
 * DATUM:       2026-05-29
 *
 * ZWECK:       Mandant Eigenverwaltung — Kontodaten und Passwort bearbeiten.
 *
 * FUNCTIONS:   edit()           — Lädt MandUser via _mand_id aus Session;
 *                                  gibt mandant.konto mit $mand zurück.
 *                                  Reads: userdb.mand_user.mand_id, alle Felder
 *              update()         — Validiert und speichert Kontaktdaten.
 *                                  Reads:  userdb.mand_user.mand_id
 *                                  Writes: userdb.mand_user.mand_uname, mand_email,
 *                                          mand_tel, mand_firstname, mand_lastname,
 *                                          mand_street+nr, mand_postcode+city,
 *                                          mand_company, mand_2fa_opt_in
 *              updatePassword() — Speichert neues Passwort (Hash). (Stub)
 *                                  Writes: userdb.mand_user.mand_pw_hash
 *
 * CALLS:       App\Models\UserDb\MandUser::find()
 *
 * DB ACCESS:   userdb.mand_user.mand_id, mand_uname, mand_email, mand_tel,
 *              mand_firstname, mand_lastname, mand_street+nr, mand_postcode+city,
 *              mand_company, mand_2fa_opt_in, mand_pw_hash
 */

namespace App\Http\Controllers\UserDb;

use App\Models\UserDb\MandUser;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class MandantSelfController extends UserDbController
{
    public function edit(Request $request): View|RedirectResponse
    {
        $mandId = $request->session()->get('_mand_id');
        $mand   = $mandId ? MandUser::find($mandId) : null;

        if (! $mand) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('mandant.login');
        }

        return view('mandant.konto', compact('mand'));
    }

    public function update(Request $request): RedirectResponse
    {
        $mandId = $request->session()->get('_mand_id');
        $mand   = $mandId ? MandUser::find($mandId) : null;

        if (! $mand) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('mandant.login');
        }

        $validated = $request->validate([
            'mand_uname'         => ['nullable', 'string', 'max:255', "unique:userdb.mand_user,mand_uname,{$mandId},mand_id"],
            'mand_email'         => ['required', 'email',  'max:255', "unique:userdb.mand_user,mand_email,{$mandId},mand_id"],
            'mand_tel'           => ['required', 'string', 'max:255', "unique:userdb.mand_user,mand_tel,{$mandId},mand_id"],
            'mand_firstname'     => ['required', 'string', 'max:255'],
            'mand_lastname'      => ['required', 'string', 'max:255'],
            'mand_street+nr'     => ['required', 'string', 'max:255'],
            'mand_postcode+city' => ['required', 'string', 'max:255'],
            'mand_company'       => ['required', 'string', 'max:255'],
            'mand_2fa_opt_in'    => ['sometimes', 'boolean'],
        ]);

        $validated['mand_2fa_opt_in'] = $request->boolean('mand_2fa_opt_in');

        $mand->update($validated);

        return redirect()->route('mandant.konto')
            ->with('status', 'Kontodaten erfolgreich gespeichert.');
    }

    public function updatePassword(): Response
    {
        return response('konto password ok');
    }
}
