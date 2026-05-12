<?php
/**
 * FILE:        app/Http/Controllers/UserDb/SystemProfileController.php
 * VERSION:     1.0.0
 *
 * FUNCTIONS:   edit()            — Loads SystUser by _syst_id from session;
 *                                  returns system.profile with $user.
 *                                  Reads: userdb.syst_user.syst_id, syst_uname,
 *                                         syst_email, syst_tel, syst_firstname,
 *                                         syst_lastname
 *              updateProfile()   — Validates and updates syst_uname, syst_email,
 *                                  syst_tel, syst_firstname, syst_lastname.
 *                                  Reads:  userdb.syst_user.syst_id
 *                                  Writes: userdb.syst_user.syst_uname, syst_email,
 *                                          syst_tel, syst_firstname, syst_lastname
 *              updatePassword()  — Verifies current_password against syst_pw_hash;
 *                                  updates syst_pw_hash on success.
 *                                  Reads:  userdb.syst_user.syst_id, syst_pw_hash
 *                                  Writes: userdb.syst_user.syst_pw_hash
 *
 * CALLS:       App\Models\UserDb\SystUser::findOrFail()
 *              Illuminate\Support\Facades\Hash::check()
 *              Illuminate\Support\Facades\Hash::make()
 *
 * DB ACCESS:   userdb.syst_user.syst_id, syst_uname, syst_email, syst_tel,
 *              syst_firstname, syst_lastname, syst_pw_hash
 */

namespace App\Http\Controllers\UserDb;

use App\Models\UserDb\SystUser;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class SystemProfileController extends UserDbController
{
    public function edit(Request $request): View
    {
        $systId = $request->session()->get('_syst_id');
        $user   = $systId ? SystUser::find($systId) : null;

        return view('system.profile', compact('user'));
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $systId = $request->session()->get('_syst_id');
        $user   = SystUser::findOrFail($systId);

        $request->validate([
            'syst_uname'     => ['required', 'string', "unique:userdb.syst_user,syst_uname,{$user->syst_id},syst_id"],
            'syst_email'     => ['required', 'email',  "unique:userdb.syst_user,syst_email,{$user->syst_id},syst_id"],
            'syst_tel'       => ['required', 'string'],
            'syst_firstname' => ['required', 'string'],
            'syst_lastname'  => ['required', 'string'],
        ]);

        $user->update($request->only([
            'syst_uname',
            'syst_email',
            'syst_tel',
            'syst_firstname',
            'syst_lastname',
        ]));

        return redirect()->route('system.profile')->with('status', 'Profil gespeichert.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => ['required'],
            'password'         => ['required', 'min:12', 'confirmed'],
        ]);

        $systId = $request->session()->get('_syst_id');
        $user   = SystUser::findOrFail($systId);

        if (! Hash::check($request->current_password, $user->syst_pw_hash)) {
            return back()->withErrors(['current_password' => 'Das aktuelle Passwort ist falsch.']);
        }

        $user->update(['syst_pw_hash' => Hash::make($request->password)]);

        return redirect()->route('system.profile')->with('status', 'Passwort geändert.');
    }
}
