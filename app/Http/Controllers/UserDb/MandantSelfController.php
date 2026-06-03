<?php
/**
 * FILE:        app/Http/Controllers/UserDb/MandantSelfController.php
 * VERSION:     1.4.0
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
 *                                          mand_company, mand_2fa_opt_in, mand_cust_2fa
 *              updatePassword() — Validiert aktuelles + neues Passwort (Policy:
 *                                  min 12, gemischte Groß-/Kleinschreibung, Ziffern,
 *                                  Sonderzeichen, uncompromised); prüft ob Benutzername
 *                                  im Passwort enthalten ist; speichert neuen Hash;
 *                                  invalidiert Session; Redirect zu mandant.login.
 *                                  Reads:  userdb.mand_user.mand_id, mand_uname,
 *                                          mand_pw_hash
 *                                  Writes: userdb.mand_user.mand_pw_hash
 *
 * CALLS:       App\Models\UserDb\MandUser::find()
 *              Illuminate\Support\Facades\Hash::check()
 *              Illuminate\Support\Facades\Hash::make()
 *              Illuminate\Validation\Rules\Password::min()
 *
 * DB ACCESS:   userdb.mand_user.mand_id, mand_uname, mand_email, mand_tel,
 *              mand_firstname, mand_lastname, mand_street+nr, mand_postcode+city,
 *              mand_company, mand_2fa_opt_in, mand_cust_2fa, mand_pw_hash
 */

namespace App\Http\Controllers\UserDb;

use App\Models\UserDb\MandUser;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

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
            'mand_cust_2fa'      => ['required', 'integer', 'min:0', 'max:7'],
        ]);

        $validated['mand_2fa_opt_in'] = $request->boolean('mand_2fa_opt_in');
        $validated['mand_cust_2fa']   = (int) $validated['mand_cust_2fa'];

        $mand->update($validated);

        return redirect()->route('mandant.konto')
            ->with('status', 'Kontodaten erfolgreich gespeichert.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $mandId = $request->session()->get('_mand_id');
        $mand   = $mandId ? MandUser::find($mandId) : null;

        if (! $mand) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('mandant.login');
        }

        $request->validate([
            'current_password' => ['required'],
            'password'         => [
                'required',
                'confirmed',
                Password::min(12)->mixedCase()->numbers()->symbols()->uncompromised(),
            ],
        ]);

        if ($mand->mand_uname && str_contains($request->password, $mand->mand_uname)) {
            return back()->withErrors(['password' => 'Das Passwort darf den Benutzernamen nicht enthalten.']);
        }

        if (! Hash::check($request->current_password, $mand->mand_pw_hash)) {
            return back()->withErrors(['current_password' => 'Das aktuelle Passwort ist nicht korrekt.']);
        }

        $mand->update(['mand_pw_hash' => Hash::make($request->password)]);

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('mandant.login')
            ->with('status', 'Passwort geändert. Bitte melden Sie sich erneut an.');
    }
}
