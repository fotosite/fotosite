<?php
/**
 * FILE:        app/Http/Controllers/UserDb/SystemMandantController.php
 * VERSION:     1.3.0
 *
 * FUNCTIONS:   index()          — Lists all MandUser records ordered by mand_lastname.
 *                                 Reads: userdb.mand_user.*
 *              invite()         — Validates email, creates register invite, sends InviteMail.
 *                                 Writes: userdb.invite.*
 *              show()           — Loads MandUser by $id; returns detail view.
 *                                 Reads: userdb.mand_user.*
 *              edit()           — Loads MandUser by $id; returns edit form.
 *                                 Reads: userdb.mand_user.*
 *              update()         — Validates and updates active, valid_to,
 *                                 has_public_content, mand_cust_2fa.
 *                                 Reads:  userdb.mand_user.mand_id
 *                                 Writes: userdb.mand_user.active, valid_to,
 *                                         has_public_content, mand_cust_2fa
 *              destroy()        — Deletes MandUser by $id.
 *                                 Writes: userdb.mand_user (DELETE)
 *              showRegister()   — Validates mand register token; returns register form.
 *                                 Reads: userdb.invite.*
 *              handleRegister() — Creates MandUser from register invite; deletes invite.
 *                                 Reads:  userdb.invite.*
 *                                 Writes: userdb.mand_user.*, userdb.invite (DELETE)
 *
 * CALLS:       App\Models\UserDb\MandUser::orderBy()->get()
 *              App\Models\UserDb\MandUser::find()
 *              App\Models\UserDb\MandUser::create()
 *              App\Models\UserDb\SystUser::find()
 *              App\Models\UserDb\Invite::where()->valid()->first()
 *              App\Models\UserDb\Invite::create()
 *              App\Mail\InviteMail
 *              Illuminate\Support\Facades\Hash::make()
 *              Illuminate\Support\Facades\Mail::to()->send()
 *              Illuminate\Support\Str::random()
 *
 * DB ACCESS:   userdb.mand_user.mand_id, mand_uname, mand_email, mand_tel,
 *              mand_firstname, mand_lastname, mand_company, mand_pw_hash,
 *              mand_street+nr, mand_postcode+city, mand_prefstat,
 *              mand_cust_2fa, active, has_public_content, valid_to
 *              userdb.invite.*
 *              userdb.syst_user.syst_id, syst_uname
 */

namespace App\Http\Controllers\UserDb;

use App\Mail\InviteMail;
use App\Models\UserDb\Invite;
use App\Models\UserDb\MandUser;
use App\Models\UserDb\SystUser;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class SystemMandantController extends UserDbController
{
    private function currentUserName(Request $request): string
    {
        $id = $request->session()->get('_syst_id');
        return $id ? (SystUser::find($id)?->syst_uname ?? 'System') : 'System';
    }

    public function index(Request $request): View
    {
        $mandanten       = MandUser::orderBy('mand_lastname')->get();
        $currentUserName = $this->currentUserName($request);

        return view('system.mandanten.index', compact('mandanten', 'currentUserName'));
    }

    public function invite(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email', 'unique:userdb.mand_user,mand_email'],
        ]);

        $token = Str::random(64);

        Invite::create([
            'inv_email'      => $request->email,
            'inv_token_hash' => hash('sha256', $token),
            'inv_type'       => 'register',
            'inv_user_type'  => 'mand',
            'inv_user_id'    => null,
            'inv_mand_id'    => null,
            'created_at'     => now(),
            'expires_at'     => now()->addHours(24),
        ]);

        $url = route('system.mand.register', ['token' => $token]);

        Mail::to($request->email)->send(new InviteMail($url, 'register', 'mand'));

        return redirect()->route('system.mandanten.index')
            ->with('status', 'Einladung wurde gesendet.');
    }

    public function show(Request $request, int $id): View
    {
        $mandant = MandUser::find($id);

        if (! $mandant) {
            abort(404);
        }

        $currentUserName = $this->currentUserName($request);

        return view('system.mandanten.show', compact('mandant', 'currentUserName'));
    }

    public function edit(Request $request, int $id): View
    {
        $mandant = MandUser::find($id);

        if (! $mandant) {
            abort(404);
        }

        $currentUserName = $this->currentUserName($request);

        return view('system.mandanten.edit', compact('mandant', 'currentUserName'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $mandant = MandUser::find($id);

        if (! $mandant) {
            abort(404);
        }

        $request->validate([
            'active'             => ['required', 'boolean'],
            'valid_to'           => ['nullable', 'date'],
            'has_public_content' => ['required', 'boolean'],
            'mand_cust_2fa'      => ['required', 'boolean'],
        ]);

        $mandant->update($request->only([
            'active',
            'valid_to',
            'has_public_content',
            'mand_cust_2fa',
        ]));

        return redirect()->route('system.mandanten.show', $id)
            ->with('status', 'Einstellungen gespeichert.');
    }

    public function destroy(Request $request, int $id): RedirectResponse
    {
        $mandant = MandUser::find($id);

        if (! $mandant) {
            abort(404);
        }

        $mandant->delete();

        return redirect()->route('system.mandanten.index')
            ->with('status', 'Mandant wurde gelöscht.');
    }

    public function showRegister(Request $request, string $token): View
    {
        $invite = Invite::where('inv_token_hash', hash('sha256', $token))
            ->where('inv_type', 'register')
            ->where('inv_user_type', 'mand')
            ->valid()
            ->first();

        if (! $invite) {
            abort(404);
        }

        return view('system.mandanten.register', compact('invite', 'token'));
    }

    public function handleRegister(Request $request, string $token): RedirectResponse
    {
        $invite = Invite::where('inv_token_hash', hash('sha256', $token))
            ->where('inv_type', 'register')
            ->where('inv_user_type', 'mand')
            ->valid()
            ->first();

        if (! $invite) {
            abort(404);
        }

        $request->validate([
            'mand_uname'     => ['required', 'string', 'unique:userdb.mand_user,mand_uname'],
            'mand_firstname' => ['required', 'string'],
            'mand_lastname'  => ['required', 'string'],
            'mand_tel'       => ['required', 'string'],
            'mand_company'   => ['required', 'string'],
            'password'       => ['required', 'min:12', 'confirmed'],
        ]);

        MandUser::create([
            'mand_uname'         => $request->mand_uname,
            'mand_email'         => $invite->inv_email,
            'mand_firstname'     => $request->mand_firstname,
            'mand_lastname'      => $request->mand_lastname,
            'mand_tel'           => $request->mand_tel,
            'mand_company'       => $request->mand_company,
            'mand_pw_hash'       => Hash::make($request->password),
            'mand_street+nr'     => '',
            'mand_postcode+city' => '',
            'mand_prefstat'      => 0,
            'active'             => true,
            'has_public_content' => false,
            'mand_cust_2fa'      => false,
            'valid_to'           => null,
        ]);

        $invite->delete();

        return redirect()->route('login')
            ->with('status', 'Mandanten-Account erstellt. Bitte melden Sie sich an.');
    }
}
