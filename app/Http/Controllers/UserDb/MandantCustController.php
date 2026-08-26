<?php
/**
 * FILE:        app/Http/Controllers/UserDb/MandantCustController.php
 * VERSION:     1.9.0
 * AUTHOR:      Martin Wagner
 * DATE:        2026-08-26
 * PURPOSE:     Cust-Verwaltung durch Mandant — Einladen, Alias/Passcode-Verwaltung, Löschen
 *
 * FUNCTIONS:   index()   — Listet Mitglieder des eingeloggten Mandanten
 *                           Reads: userdb.cust_pcode.mand_id, cust_id, cust_passcode,
 *                                  pcode_prefstat, cust_alias
 *                                  userdb.cust_user.cust_id, cust_firstname,
 *                                  cust_lastname, cust_email
 *              show()    — Read-only Detailseite eines Mitglieds. Sicherheitscheck:
 *                           CustPcode muss zu $id (pcode_id) UND zum eingeloggten
 *                           mand_id gehören, sonst abort(404) (kein 403, analog zu
 *                           update()/destroy()).
 *                           Reads:  userdb.cust_pcode.pcode_id, mand_id, cust_id,
 *                                   cust_alias
 *                                   userdb.cust_user.* (via custUser-Relation)
 *              invite()  — Zeigt Einladungsformular
 *                           Reads: (keine)
 *              store()   — Validiert Formular (inkl. cust_alias), prüft Duplikat,
 *                           erstellt CustInvite (inkl. cust_alias), sendet CustInviteMail.
 *                           cust_alias fließt via CustInvite zu CustPcode::create()
 *                           in CustRegisterController::store().
 *                           Reads:  userdb.mand_user.mand_id, mand_uname
 *                                   userdb.cust_user.cust_id, cust_email
 *                                   userdb.cust_pcode.mand_id, cust_id
 *                           Writes: sessiondb.cust_invite.mand_id, cust_email, cust_alias,
 *                                   sec_level, token, created_at, expires_at, used
 *              update()  — Aktualisiert cust_alias + Sicherheitsstufe (cust_passcode)
 *                           Reads:  userdb.cust_pcode.pcode_id, mand_id
 *                           Writes: userdb.cust_pcode.cust_passcode, cust_alias
 *              destroy() — Entfernt Mitglieder-Zuordnung des Mandanten; löscht CustUser
 *                           (inkl. Passkey, PasskeyDismissed) wenn keine weitere
 *                           Mand-Zuordnung existiert. Sendet vor der Löschung
 *                           CustAccountDeletedMail an die hinterlegte cust_email —
 *                           nur wenn der Account wirklich verwaist ist (letzte Referenz).
 *                           Reads:  userdb.cust_pcode.pcode_id, mand_id, cust_id
 *                                   userdb.cust_user.cust_id, cust_email, cust_firstname,
 *                                   cust_uname
 *                           Writes: userdb.cust_pcode (DELETE)
 *                                   userdb.cust_user (DELETE, nur wenn $remaining === 0)
 *                                   userdb.passkey (DELETE, nur wenn $remaining === 0)
 *                                   userdb.passkey_dismissed (DELETE, nur wenn $remaining === 0)
 *
 * CALLS:       App\Models\UserDb\MandUser::find()
 *              App\Models\UserDb\CustUser::where()->first()
 *              App\Models\UserDb\CustUser::find()->delete()
 *              App\Models\UserDb\CustPcode::where()->exists()
 *              App\Models\UserDb\CustPcode::where()->with()->get()
 *              App\Models\UserDb\CustPcode::where()->first()
 *              App\Models\UserDb\CustPcode::where()->count()
 *              App\Models\UserDb\Passkey::where()->delete()
 *              App\Models\UserDb\PasskeyDismissed::where()->delete()
 *              App\Models\SessionDb\CustInvite::create()
 *              App\Mail\CustInviteMail
 *              App\Mail\CustAccountDeletedMail
 *              Illuminate\Support\Facades\Mail::to()->send()
 *              Illuminate\Support\Str::random()
 *
 * DB ACCESS:   userdb.mand_user.mand_id, mand_uname
 *              userdb.cust_user.cust_id, cust_firstname, cust_lastname, cust_uname,
 *              cust_email (DELETE), cust_tel, cust_company, cust_street+nr,
 *              cust_postcode_city (READ, via show())
 *              userdb.cust_pcode.pcode_id, mand_id, cust_id, cust_passcode,
 *              pcode_prefstat, cust_alias
 *              userdb.passkey.pk_id, user_type, user_id (DELETE)
 *              userdb.passkey_dismissed.pd_id, user_type, user_id (DELETE)
 *              sessiondb.cust_invite.invite_id, mand_id, cust_email, cust_alias,
 *              sec_level, token, created_at, expires_at, used
 *
 * CHANGES:     1.9.0 (2026-08-26) show() ergänzt — Read-only Detailseite eines
 *              Mitglieds (analog SystemMandantController::show()); Sicherheitscheck
 *              per CustPcode::where('pcode_id', $id)->where('mand_id', $mandId),
 *              sonst abort(404).
 */

namespace App\Http\Controllers\UserDb;

use App\Mail\CustAccountDeletedMail;
use App\Mail\CustInviteMail;
use App\Models\SessionDb\CustInvite;
use App\Models\UserDb\CustPcode;
use App\Models\UserDb\CustUser;
use App\Models\UserDb\MandUser;
use App\Models\UserDb\Passkey;
use App\Models\UserDb\PasskeyDismissed;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class MandantCustController extends UserDbController
{
    public function index(Request $request): View
    {
        $mandId = (int) $request->session()->get('_mand_id');

        $custs = CustPcode::where('mand_id', $mandId)
            ->with('custUser')
            ->get();

        return view('mandant.cust.index', ['custs' => $custs]);
    }

    public function show(Request $request, int $id): View
    {
        $mandId = (int) $request->session()->get('_mand_id');

        $pcode = CustPcode::where('pcode_id', $id)
            ->where('mand_id', $mandId)
            ->with('custUser')
            ->first();

        if (! $pcode) {
            abort(404);
        }

        return view('mandant.cust.show', ['cust' => $pcode]);
    }

    public function invite(): View
    {
        return view('mandant.cust.einladen');
    }

    public function store(Request $request): RedirectResponse
    {
        $mandId   = (int) $request->session()->get('_mand_id');
        $mand     = MandUser::find($mandId);
        $mandUname = $mand ? $mand->mand_uname : 'Fotosite';

        $validated = $request->validate([
            'cust_email' => ['required', 'email', 'max:255'],
            'cust_alias' => ['required', 'string', 'max:255'],
            'sec_level'  => ['required', 'integer', 'min:1', 'max:6'],
        ]);

        $email    = $validated['cust_email'];
        $secLevel = (int) $validated['sec_level'];

        $existingCust = CustUser::where('cust_email', $email)->first();
        if ($existingCust) {
            $alreadyLinked = CustPcode::where('mand_id', $mandId)
                ->where('cust_id', $existingCust->cust_id)
                ->exists();
            if ($alreadyLinked) {
                return back()
                    ->withErrors(['cust_email' => 'Dieses Mitglied ist bereits eingeladen.'])
                    ->withInput();
            }
        }

        $token = Str::random(64);

        $invite = CustInvite::create([
            'mand_id'    => $mandId,
            'cust_email' => $email,
            'cust_alias' => $validated['cust_alias'],
            'sec_level'  => $secLevel,
            'token'      => $token,
            'created_at' => now(),
            'expires_at' => now()->addHours(48),
            'used'       => false,
        ]);

        $registerUrl = route('customer.register', ['token' => $token]);

        Mail::to($email)->send(new CustInviteMail($invite, $registerUrl, $mandUname));

        return redirect()->route('mandant.kunden.index')
            ->with('status', 'Einladung wurde gesendet.');
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $mandId = (int) $request->session()->get('_mand_id');

        $pcode = CustPcode::where('pcode_id', $id)
            ->where('mand_id', $mandId)
            ->first();

        if (! $pcode) {
            abort(404);
        }

        $validated = $request->validate([
            'sec_level'  => ['required', 'integer', 'min:1', 'max:6'],
            'cust_alias' => ['required', 'string', 'max:255'],
        ]);

        $pcode->update([
            'cust_passcode' => $validated['sec_level'],
            'cust_alias'    => $validated['cust_alias'],
        ]);

        return redirect()->route('mandant.kunden.index')
            ->with('status', 'Sicherheitsstufe aktualisiert.');
    }

    public function destroy(Request $request, int $id): RedirectResponse
    {
        $mandId = (int) $request->session()->get('_mand_id');

        $pcode = CustPcode::where('pcode_id', $id)
            ->where('mand_id', $mandId)
            ->first();

        if (! $pcode) {
            abort(404);
        }

        $custId = $pcode->cust_id;

        $pcode->delete();

        $remaining = CustPcode::where('cust_id', $custId)->count();
        if ($remaining === 0) {
            $cust = CustUser::find($custId);

            if ($cust) {
                $custEmail = $cust->cust_email;
                $custName  = $cust->cust_firstname ?: ($cust->cust_uname ?: 'Hallo');

                Mail::to($custEmail)->send(new CustAccountDeletedMail($custName));

                Passkey::where('user_type', 'cust')->where('user_id', $custId)->delete();
                PasskeyDismissed::where('user_type', 'cust')->where('user_id', $custId)->delete();

                $cust->delete();
            }
        }

        return redirect()->route('mandant.kunden.index')
            ->with('status', 'Mitglied wurde entfernt.');
    }
}
