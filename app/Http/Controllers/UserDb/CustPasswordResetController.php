<?php
/**
 * FILE:        app/Http/Controllers/UserDb/CustPasswordResetController.php
 * VERSION:     1.0.0
 * DATE:        2026-06-12
 *
 * FUNCTIONS:   showResetRequest()  — Shows email-input form for password-reset request.
 *                                    Reads: (none)
 *              sendResetLink()     — Validates email, creates pw_reset invite, sends InviteMail.
 *                                    Reads:  userdb.cust_user.cust_id, cust_email
 *                                    Writes: userdb.invite.*
 *              showResetForm()     — Validates pw_reset token; returns password-form view.
 *                                    Reads: userdb.invite.*
 *              handleReset()       — Updates cust_pw_hash from pw_reset invite; deletes invite.
 *                                    Reads:  userdb.invite.*, userdb.cust_user.cust_id
 *                                    Writes: userdb.cust_user.cust_pw_hash, userdb.invite (DELETE)
 *
 * CALLS:       App\Models\UserDb\CustUser::where()->first()
 *              App\Models\UserDb\CustUser::findOrFail()
 *              App\Models\UserDb\Invite::where()->valid()->first()
 *              App\Models\UserDb\Invite::create()
 *              App\Mail\InviteMail
 *              Illuminate\Support\Facades\Hash::make()
 *              Illuminate\Support\Facades\Mail::to()->send()
 *              Illuminate\Support\Str::random()
 *
 * DB ACCESS:   userdb.cust_user.cust_id, cust_email, cust_pw_hash
 *              userdb.invite.*
 */

namespace App\Http\Controllers\UserDb;

use App\Mail\InviteMail;
use App\Models\UserDb\CustUser;
use App\Models\UserDb\Invite;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class CustPasswordResetController extends UserDbController
{
    public function showResetRequest(): View
    {
        return view('customer.auth.password_reset_request');
    }

    public function sendResetLink(Request $request): RedirectResponse
    {
        $request->validate([
            'cust_email' => ['required', 'email'],
        ]);

        $user = CustUser::where('cust_email', $request->cust_email)->first();

        if ($user) {
            $token = Str::random(64);

            Invite::create([
                'inv_email'      => $user->cust_email,
                'inv_token_hash' => hash('sha256', $token),
                'inv_type'       => 'pw_reset',
                'inv_user_type'  => 'cust',
                'inv_user_id'    => $user->cust_id,
                'inv_mand_id'    => null,
                'created_at'     => now(),
                'expires_at'     => now()->addHours(24),
            ]);

            $url = route('customer.password.reset', ['token' => $token]);

            Mail::to($user->cust_email)->send(new InviteMail($url, 'pw_reset', 'cust'));
        }

        return redirect()->route('customer.password.reset.request')
            ->with('status', 'Falls ein Konto mit dieser E-Mail-Adresse existiert, wurde ein Link zum Passwort-Zurücksetzen gesendet.');
    }

    public function showResetForm(string $token): View
    {
        $invite = Invite::where('inv_token_hash', hash('sha256', $token))
            ->where('inv_type', 'pw_reset')
            ->where('inv_user_type', 'cust')
            ->valid()
            ->first();

        if (! $invite) {
            abort(404);
        }

        return view('customer.auth.password_reset', compact('token'));
    }

    public function handleReset(Request $request, string $token): RedirectResponse
    {
        $invite = Invite::where('inv_token_hash', hash('sha256', $token))
            ->where('inv_type', 'pw_reset')
            ->where('inv_user_type', 'cust')
            ->valid()
            ->first();

        if (! $invite) {
            abort(404);
        }

        $request->validate([
            'password' => ['required', 'min:10', 'confirmed'],
        ]);

        $user = CustUser::findOrFail($invite->inv_user_id);
        $user->update(['cust_pw_hash' => Hash::make($request->password)]);

        $invite->delete();

        return redirect()->route('home')
            ->with('status', 'Ihr Passwort wurde erfolgreich zurückgesetzt. Bitte melden Sie sich jetzt an.')
            ->with('open_login_modal', 'cust');
    }
}
