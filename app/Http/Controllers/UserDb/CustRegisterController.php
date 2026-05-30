<?php
/**
 * FILE:        app/Http/Controllers/UserDb/CustRegisterController.php
 * VERSION:     1.1.0
 * AUTHOR:      Martin Wagner
 * DATE:        2026-05-30
 * PURPOSE:     Kunden-Registrierung per Einladungs-Token
 *
 * FUNCTIONS:   show()   — Validiert Token, zeigt Registrierungsformular
 *                          Reads: sessiondb.cust_invite.token, expires_at, used, cust_email
 *              store()  — Validiert Token + Formular, erstellt CustUser + CustPcode,
 *                          markiert Einladung als verwendet.
 *                          Reads:  sessiondb.cust_invite.*
 *                          Writes: userdb.cust_user.cust_firstname, cust_lastname,
 *                                  cust_email, cust_tel, cust_company, cust_street+nr,
 *                                  cust_postcode_city, cust_pw_hash, cust_2fa_opt_in
 *                                  userdb.cust_pcode.mand_id, cust_id, cust_passcode,
 *                                  pcode_prefstat
 *                                  sessiondb.cust_invite.used (UPDATE)
 *
 * CALLS:       App\Models\SessionDb\CustInvite::where()->first()
 *              App\Models\SessionDb\CustInvite::update()
 *              App\Models\UserDb\CustUser::create()
 *              App\Models\UserDb\CustPcode::create()
 *              Illuminate\Support\Facades\Hash::make()
 *              Illuminate\Validation\Rules\Password
 *
 * DB ACCESS:   sessiondb.cust_invite.invite_id, token, expires_at, used, mand_id,
 *              cust_email, sec_level
 *              userdb.cust_user.cust_id, cust_firstname, cust_lastname, cust_email,
 *              cust_tel, cust_company, cust_street+nr, cust_postcode_city,
 *              cust_pw_hash, cust_2fa_opt_in
 *              userdb.cust_pcode.pcode_id, mand_id, cust_id, cust_passcode,
 *              pcode_prefstat
 */

namespace App\Http\Controllers\UserDb;

use App\Models\SessionDb\CustInvite;
use App\Models\UserDb\CustPcode;
use App\Models\UserDb\CustUser;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class CustRegisterController extends UserDbController
{
    private function findValidInvite(string $token): ?CustInvite
    {
        return CustInvite::where('token', $token)
            ->where('used', false)
            ->where('expires_at', '>', now())
            ->first();
    }

    public function show(Request $request, string $token): View|RedirectResponse
    {
        $invite = $this->findValidInvite($token);

        if (! $invite) {
            return redirect()->route('home')
                ->withErrors(['token' => 'Einladungslink ungültig oder abgelaufen.']);
        }

        return view('customer.auth.register', [
            'token'      => $token,
            'cust_email' => $invite->cust_email,
        ]);
    }

    public function store(Request $request, string $token): RedirectResponse
    {
        $invite = $this->findValidInvite($token);

        if (! $invite) {
            return redirect()->route('home')
                ->withErrors(['token' => 'Einladungslink ungültig oder abgelaufen.']);
        }

        $validated = $request->validate([
            'cust_firstname'     => ['required', 'string', 'max:255'],
            'cust_lastname'      => ['required', 'string', 'max:255'],
            'cust_email'         => ['required', 'email', 'max:255'],
            'cust_tel'           => ['required', 'string', 'max:255'],
            'cust_company'       => ['nullable', 'string', 'max:255'],
            'cust_street+nr'     => ['required', 'string', 'max:255'],
            'cust_postcode_city' => ['required', 'string', 'max:255'],
            'password'           => ['required', 'confirmed',
                Password::min(10)->mixedCase()->numbers()],
        ]);

        $cust = CustUser::create([
            'cust_firstname'     => $validated['cust_firstname'],
            'cust_lastname'      => $validated['cust_lastname'],
            'cust_email'         => $validated['cust_email'],
            'cust_tel'           => $validated['cust_tel'],
            'cust_company'       => $validated['cust_company'] ?? '',
            'cust_street+nr'     => $validated['cust_street+nr'],
            'cust_postcode_city' => $validated['cust_postcode_city'],
            'cust_pw_hash'       => Hash::make($validated['password']),
            'cust_2fa_opt_in'    => false,
        ]);

        CustPcode::create([
            'mand_id'        => $invite->mand_id,
            'cust_id'        => $cust->cust_id,
            'cust_passcode'  => $invite->sec_level,
            'pcode_prefstat' => 1,
        ]);

        $invite->update(['used' => true]);

        return redirect()->route('home')
            ->with('status', 'Registrierung erfolgreich. Bitte melden Sie sich an.');
    }
}
