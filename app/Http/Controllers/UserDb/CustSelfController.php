<?php
/**
 * FILE:        app/Http/Controllers/UserDb/CustSelfController.php
 * VERSION:     1.0.0
 * AUTOR:       Martin Wagner
 * DATUM:       2026-06-12
 *
 * ZWECK:       Customer Eigenverwaltung — Kontaktdaten und Passwort bearbeiten.
 *
 * FUNCTIONS:   edit()           — Lädt CustUser via _cust_id aus Session;
 *                                  gibt customer.konto mit $cust zurück.
 *                                  Reads: userdb.cust_user.cust_id, alle Felder
 *              update()         — Validiert und speichert Kontaktdaten.
 *                                  Reads:  userdb.cust_user.cust_id
 *                                  Writes: userdb.cust_user.cust_email, cust_firstname,
 *                                          cust_lastname, cust_tel, cust_street+nr,
 *                                          cust_postcode_city, cust_company
 *              updatePassword() — Validiert aktuelles + neues Passwort (Policy: min 10
 *                                  Zeichen); prüft ob Benutzername im Passwort enthalten
 *                                  ist; speichert neuen Hash; invalidiert Session;
 *                                  Redirect zu home mit Login-Modal-Hint.
 *                                  Reads:  userdb.cust_user.cust_id, cust_uname,
 *                                          cust_pw_hash
 *                                  Writes: userdb.cust_user.cust_pw_hash
 *
 * CALLS:       App\Models\UserDb\CustUser::find()
 *              Illuminate\Support\Facades\Hash::check()
 *              Illuminate\Support\Facades\Hash::make()
 *              Illuminate\Validation\Rules\Password::min()
 *
 * DB ACCESS:   userdb.cust_user.cust_id, cust_uname, cust_email, cust_tel,
 *              cust_firstname, cust_lastname, cust_street+nr, cust_postcode_city,
 *              cust_company, cust_pw_hash
 */

namespace App\Http\Controllers\UserDb;

use App\Models\UserDb\CustUser;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class CustSelfController extends UserDbController
{
    public function edit(Request $request): View|RedirectResponse
    {
        $custId = $request->session()->get('_cust_id');
        $cust   = $custId ? CustUser::find($custId) : null;

        if (! $cust) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('customer.login');
        }

        return view('customer.konto', compact('cust'));
    }

    public function update(Request $request): RedirectResponse
    {
        $custId = $request->session()->get('_cust_id');
        $cust   = $custId ? CustUser::find($custId) : null;

        if (! $cust) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('customer.login');
        }

        $validated = $request->validate([
            'cust_email'         => ['required', 'email', 'max:255', "unique:userdb.cust_user,cust_email,{$custId},cust_id"],
            'cust_firstname'     => ['nullable', 'string', 'max:255'],
            'cust_lastname'      => ['nullable', 'string', 'max:255'],
            'cust_tel'           => ['nullable', 'string', 'max:255'],
            'cust_street+nr'     => ['nullable', 'string', 'max:255'],
            'cust_postcode_city' => ['nullable', 'string', 'max:255'],
            'cust_company'       => ['nullable', 'string', 'max:255'],
        ]);

        $cust->update($validated);

        return redirect()->route('customer.konto')
            ->with('status', 'Kontaktdaten gespeichert.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $custId = $request->session()->get('_cust_id');
        $cust   = $custId ? CustUser::find($custId) : null;

        if (! $cust) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('customer.login');
        }

        $request->validate([
            'current_password' => ['required'],
            'password'         => [
                'required',
                'confirmed',
                Password::min(10),
            ],
        ]);

        if ($cust->cust_uname && str_contains($request->password, $cust->cust_uname)) {
            return back()->withErrors(['password' => 'Das Passwort darf den Benutzernamen nicht enthalten.']);
        }

        if (! Hash::check($request->current_password, $cust->cust_pw_hash)) {
            return back()->withErrors(['current_password' => 'Das aktuelle Passwort ist falsch.']);
        }

        $cust->update(['cust_pw_hash' => Hash::make($request->password)]);

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')
            ->with('status', 'Passwort geändert. Bitte melden Sie sich erneut an.')
            ->with('open_login_modal', 'cust');
    }
}
