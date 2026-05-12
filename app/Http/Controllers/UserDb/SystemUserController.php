<?php
/**
 * FILE:        app/Http/Controllers/UserDb/SystemUserController.php
 * VERSION:     1.1.0
 *
 * FUNCTIONS:   index()               — Lists all SystUser records ordered by syst_lastname.
 *                                      Reads: userdb.syst_user.*
 *              invite()              — Validates email, creates register invite, sends InviteMail.
 *                                      Writes: userdb.invite.*
 *              sendPasswordReset()   — Creates pw_reset invite for user $id, sends InviteMail.
 *                                      Reads:  userdb.syst_user.syst_id, syst_email
 *                                      Writes: userdb.invite.*
 *              destroy()             — Deletes SystUser by $id (guards against self-delete).
 *                                      Reads:  userdb.syst_user.syst_id
 *                                      Writes: userdb.syst_user (DELETE)
 *              showRegister()        — Validates register token; returns register form view.
 *                                      Reads: userdb.invite.*
 *              handleRegister()      — Creates SystUser from register invite; deletes invite.
 *                                      Reads:  userdb.invite.*
 *                                      Writes: userdb.syst_user.*, userdb.invite (DELETE)
 *              showPasswordReset()   — Validates pw_reset token; returns password form view.
 *                                      Reads: userdb.invite.*
 *              handlePasswordReset() — Updates syst_pw_hash from pw_reset invite; deletes invite.
 *                                      Reads:  userdb.invite.*, userdb.syst_user.syst_id
 *                                      Writes: userdb.syst_user.syst_pw_hash, userdb.invite (DELETE)
 *
 * CALLS:       App\Models\UserDb\SystUser::orderBy()->get()
 *              App\Models\UserDb\SystUser::find()
 *              App\Models\UserDb\SystUser::findOrFail()
 *              App\Models\UserDb\SystUser::create()
 *              App\Models\UserDb\Invite::where()->valid()->first()
 *              App\Models\UserDb\Invite::create()
 *              App\Mail\InviteMail
 *              Illuminate\Support\Facades\Hash::make()
 *              Illuminate\Support\Facades\Mail::to()->send()
 *              Illuminate\Support\Str::random()
 *
 * DB ACCESS:   userdb.syst_user.syst_id, syst_uname, syst_email, syst_tel,
 *              syst_firstname, syst_lastname, syst_pw_hash, syst_street+nr,
 *              syst_pcode+city, syst_company
 *              userdb.invite.*
 */

namespace App\Http\Controllers\UserDb;

use App\Mail\InviteMail;
use App\Models\UserDb\Invite;
use App\Models\UserDb\SystUser;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class SystemUserController extends UserDbController
{
    public function index(Request $request): View
    {
        $users         = SystUser::orderBy('syst_lastname')->get();
        $currentSystId = $request->session()->get('_syst_id');

        return view('system.users.index', compact('users', 'currentSystId'));
    }

    public function invite(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email', 'unique:userdb.syst_user,syst_email'],
        ]);

        $token = Str::random(64);

        Invite::create([
            'inv_email'      => $request->email,
            'inv_token_hash' => hash('sha256', $token),
            'inv_type'       => 'register',
            'inv_user_type'  => 'syst',
            'inv_user_id'    => null,
            'inv_mand_id'    => null,
            'created_at'     => now(),
            'expires_at'     => now()->addHours(24),
        ]);

        $url = route('system.register', ['token' => $token]);

        Mail::to($request->email)->send(new InviteMail($url, 'register', 'syst'));

        return redirect()->route('system.users.index')
            ->with('status', 'Einladung wurde gesendet.');
    }

    public function sendPasswordReset(Request $request, int $id): RedirectResponse
    {
        $user = SystUser::find($id);

        if (! $user) {
            abort(404);
        }

        $token = Str::random(64);

        Invite::create([
            'inv_email'      => $user->syst_email,
            'inv_token_hash' => hash('sha256', $token),
            'inv_type'       => 'pw_reset',
            'inv_user_type'  => 'syst',
            'inv_user_id'    => $id,
            'inv_mand_id'    => null,
            'created_at'     => now(),
            'expires_at'     => now()->addHours(24),
        ]);

        $url = route('system.password.reset', ['token' => $token]);

        Mail::to($user->syst_email)->send(new InviteMail($url, 'pw_reset'));

        return redirect()->route('system.users.index')
            ->with('status', 'Passwort-Reset-Email wurde gesendet.');
    }

    public function destroy(Request $request, int $id): RedirectResponse
    {
        $user = SystUser::find($id);

        if (! $user) {
            abort(404);
        }

        if ($id === (int) $request->session()->get('_syst_id')) {
            return back()->withErrors(['delete' => 'Sie können sich nicht selbst löschen.']);
        }

        $user->delete();

        return redirect()->route('system.users.index')
            ->with('status', 'System-User wurde gelöscht.');
    }

    public function showRegister(Request $request, string $token): View
    {
        $invite = Invite::where('inv_token_hash', hash('sha256', $token))
            ->where('inv_type', 'register')
            ->where('inv_user_type', 'syst')
            ->valid()
            ->first();

        if (! $invite) {
            abort(404);
        }

        return view('system.users.register', compact('invite', 'token'));
    }

    public function handleRegister(Request $request, string $token): RedirectResponse
    {
        $invite = Invite::where('inv_token_hash', hash('sha256', $token))
            ->where('inv_type', 'register')
            ->where('inv_user_type', 'syst')
            ->valid()
            ->first();

        if (! $invite) {
            abort(404);
        }

        $request->validate([
            'syst_uname'     => ['required', 'string', 'unique:userdb.syst_user,syst_uname'],
            'syst_firstname' => ['required', 'string'],
            'syst_lastname'  => ['required', 'string'],
            'syst_tel'       => ['required', 'string'],
            'password'       => ['required', 'min:12', 'confirmed'],
        ]);

        SystUser::create([
            'syst_uname'      => $request->syst_uname,
            'syst_email'      => $invite->inv_email,
            'syst_firstname'  => $request->syst_firstname,
            'syst_lastname'   => $request->syst_lastname,
            'syst_tel'        => $request->syst_tel,
            'syst_pw_hash'    => Hash::make($request->password),
            'syst_street+nr'  => '',
            'syst_pcode+city' => '',
            'syst_company'    => '',
        ]);

        $invite->delete();

        return redirect('/backstage')
            ->with('status', 'Account erstellt. Bitte melden Sie sich an.');
    }

    public function showPasswordReset(Request $request, string $token): View
    {
        $invite = Invite::where('inv_token_hash', hash('sha256', $token))
            ->where('inv_type', 'pw_reset')
            ->where('inv_user_type', 'syst')
            ->valid()
            ->first();

        if (! $invite) {
            abort(404);
        }

        return view('system.users.password_reset', compact('token'));
    }

    public function handlePasswordReset(Request $request, string $token): RedirectResponse
    {
        $invite = Invite::where('inv_token_hash', hash('sha256', $token))
            ->where('inv_type', 'pw_reset')
            ->where('inv_user_type', 'syst')
            ->valid()
            ->first();

        if (! $invite) {
            abort(404);
        }

        $request->validate([
            'password' => ['required', 'min:12', 'confirmed'],
        ]);

        $user = SystUser::findOrFail($invite->inv_user_id);
        $user->update(['syst_pw_hash' => Hash::make($request->password)]);

        $invite->delete();

        return redirect('/backstage')
            ->with('status', 'Passwort wurde geändert. Bitte melden Sie sich an.');
    }
}
