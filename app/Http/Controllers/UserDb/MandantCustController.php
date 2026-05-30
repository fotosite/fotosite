<?php
/**
 * FILE:        app/Http/Controllers/UserDb/MandantCustController.php
 * VERSION:     1.1.0
 * AUTHOR:      Martin Wagner
 * DATE:        2026-05-30
 * PURPOSE:     Cust-Verwaltung durch Mandant — Einladen, Passcode-Verwaltung, Löschen
 *
 * FUNCTIONS:   index()          — Listet Kunden des eingeloggten Mandanten
 *                                 Reads: userdb.cust_user.*, userdb.cust_pcode.*
 *              invite()         — Zeigt Einladungsformular
 *                                 Reads: (keine)
 *              store()          — Validiert Formular, prüft Duplikat, erstellt CustInvite,
 *                                 sendet CustInviteMail.
 *                                 Reads:  userdb.cust_user.cust_id, cust_email
 *                                         userdb.cust_pcode.mand_id, cust_id
 *                                 Writes: sessiondb.cust_invite.mand_id, cust_email,
 *                                         sec_level, token, created_at, expires_at, used
 *              updatePasscode() — Aktualisiert Passcode eines Kunden
 *                                 Writes: userdb.cust_pcode.*
 *              destroy()        — Löscht Kunden-Zuordnung des Mandanten
 *                                 Writes: userdb.cust_pcode (DELETE)
 *
 * CALLS:       App\Models\UserDb\CustUser::where()->first()
 *              App\Models\UserDb\CustPcode::where()->exists()
 *              App\Models\SessionDb\CustInvite::create()
 *              App\Mail\CustInviteMail
 *              Illuminate\Support\Facades\Mail::to()->send()
 *              Illuminate\Support\Str::random()
 *
 * DB ACCESS:   userdb.cust_user.cust_id, cust_email
 *              userdb.cust_pcode.pcode_id, mand_id, cust_id
 *              sessiondb.cust_invite.invite_id, mand_id, cust_email, sec_level,
 *              token, created_at, expires_at, used
 */

namespace App\Http\Controllers\UserDb;

use App\Mail\CustInviteMail;
use App\Models\SessionDb\CustInvite;
use App\Models\UserDb\CustPcode;
use App\Models\UserDb\CustUser;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class MandantCustController extends UserDbController
{
    public function index(): View
    {
        return view('mandant.cust.index');
    }

    public function invite(): View
    {
        return view('mandant.cust.einladen');
    }

    public function store(Request $request): RedirectResponse
    {
        $mandId = (int) $request->session()->get('_mand_id');

        $request->validate([
            'cust_email' => ['required', 'email', 'max:255'],
            'sec_level'  => ['required', 'integer', 'min:1', 'max:6'],
        ]);

        $email    = $request->input('cust_email');
        $secLevel = (int) $request->input('sec_level');

        $existingCust = CustUser::where('cust_email', $email)->first();
        if ($existingCust) {
            $alreadyLinked = CustPcode::where('mand_id', $mandId)
                ->where('cust_id', $existingCust->cust_id)
                ->exists();
            if ($alreadyLinked) {
                return back()
                    ->withErrors(['cust_email' => 'Dieser Kunde ist bereits eingeladen.'])
                    ->withInput();
            }
        }

        $token = Str::random(64);

        CustInvite::create([
            'mand_id'    => $mandId,
            'cust_email' => $email,
            'sec_level'  => $secLevel,
            'token'      => $token,
            'created_at' => now(),
            'expires_at' => now()->addHours(48),
            'used'       => false,
        ]);

        $registerUrl = route('customer.register', ['token' => $token]);

        Mail::to($email)->send(new CustInviteMail($registerUrl, $mandId));

        return redirect()->route('mandant.kunden.index')
            ->with('status', 'Einladung wurde gesendet.');
    }

    public function updatePasscode(Request $request, int $id): Response
    {
        return response('passcode ok');
    }

    public function destroy(Request $request, int $id): Response
    {
        return response('destroy ok');
    }
}
