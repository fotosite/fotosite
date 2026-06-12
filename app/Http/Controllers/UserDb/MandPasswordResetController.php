<?php
/**
 * FILE:        app/Http/Controllers/UserDb/MandPasswordResetController.php
 * VERSION:     1.0.0
 * DATE:        2026-06-12
 *
 * FUNCTIONS:   showResetRequest()  — Shows email-input form for password-reset request.
 *                                    Reads: (none)
 *              sendResetLink()     — Validates email, creates pw_reset invite, sends InviteMail.
 *                                    Reads:  userdb.mand_user.mand_id, mand_email
 *                                    Writes: userdb.invite.*
 *              showResetForm()     — Validates pw_reset token; returns password-form view.
 *                                    Reads: userdb.invite.*
 *              handleReset()       — Updates mand_pw_hash from pw_reset invite; deletes invite.
 *                                    Reads:  userdb.invite.*, userdb.mand_user.mand_id
 *                                    Writes: userdb.mand_user.mand_pw_hash, userdb.invite (DELETE)
 *
 * CALLS:       App\Models\UserDb\MandUser::where()->first()
 *              App\Models\UserDb\MandUser::findOrFail()
 *              App\Models\UserDb\Invite::where()->valid()->first()
 *              App\Models\UserDb\Invite::create()
 *              App\Mail\InviteMail
 *              Illuminate\Support\Facades\Hash::make()
 *              Illuminate\Support\Facades\Mail::to()->send()
 *              Illuminate\Support\Str::random()
 *              Illuminate\Validation\Rules\Password
 *
 * DB ACCESS:   userdb.mand_user.mand_id, mand_email, mand_pw_hash
 *              userdb.invite.*
 */

namespace App\Http\Controllers\UserDb;

use App\Mail\InviteMail;
use App\Models\UserDb\Invite;
use App\Models\UserDb\MandUser;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class MandPasswordResetController extends UserDbController
{
    public function showResetRequest(): View
    {
        return view('mandant.auth.password_reset_request');
    }

    public function sendResetLink(Request $request): RedirectResponse
    {
        $request->validate([
            'mand_email' => ['required', 'email'],
        ]);

        $user = MandUser::where('mand_email', $request->mand_email)->first();

        if ($user) {
            $token = Str::random(64);

            Invite::create([
                'inv_email'      => $user->mand_email,
                'inv_token_hash' => hash('sha256', $token),
                'inv_type'       => 'pw_reset',
                'inv_user_type'  => 'mand',
                'inv_user_id'    => $user->mand_id,
                'inv_mand_id'    => null,
                'created_at'     => now(),
                'expires_at'     => now()->addHours(24),
            ]);

            $url = route('mandant.password.reset', ['token' => $token]);

            Mail::to($user->mand_email)->send(new InviteMail($url, 'pw_reset', 'mand'));
        }

        return redirect()->route('home')
            ->with('status', 'Falls diese Email-Adresse registriert ist, wurde eine Email mit einem Link zum Zurücksetzen verschickt.')
            ->with('open_login_modal', 'mand');
    }

    public function showResetForm(string $token): View
    {
        $invite = Invite::where('inv_token_hash', hash('sha256', $token))
            ->where('inv_type', 'pw_reset')
            ->where('inv_user_type', 'mand')
            ->valid()
            ->first();

        if (! $invite) {
            abort(404);
        }

        return view('mandant.auth.password_reset', compact('token'));
    }

    public function handleReset(Request $request, string $token): RedirectResponse
    {
        $invite = Invite::where('inv_token_hash', hash('sha256', $token))
            ->where('inv_type', 'pw_reset')
            ->where('inv_user_type', 'mand')
            ->valid()
            ->first();

        if (! $invite) {
            abort(404);
        }

        $request->validate([
            'password' => [
                'required',
                'confirmed',
                Password::min(12)->mixedCase()->numbers()->symbols()->uncompromised(),
            ],
        ]);

        $user = MandUser::findOrFail($invite->inv_user_id);
        $user->update(['mand_pw_hash' => Hash::make($request->password)]);

        $invite->delete();

        return redirect()->route('home')
            ->with('open_login_modal', 'mand');
    }
}
