<?php
/**
 * FILE:        app/Http/Controllers/UserDb/CustDashboardController.php
 * VERSION:     1.1.0
 * AUTOR:       Martin Wagner
 * DATUM:       2026-06-07
 *
 * ZWECK:       Cust-Dashboard — Einstiegsseite für registrierte Mitglieder (cust)
 *              und anonyme Besucher (anon) nach erfolgreichem Login.
 *
 * FUNCTIONS:   index() — Liest _user_type aus Session; baut View-Daten für anon-
 *                         oder cust-Modus; liest und löscht den einmaligen
 *                         Passkey-Prompt-Flag aus der Session; gibt
 *                         customer.dashboard zurück.
 *                         Bei unbekanntem _user_type: Redirect zu customer.login.
 *                         Reads: userdb.cust_user.cust_id, cust_firstname
 *                                userdb.cust_pcode.pcode_id, cust_id, mand_id,
 *                                cust_passcode, pcode_prefstat
 *                                userdb.mand_user.mand_id, mand_uname
 *
 * CALLS:       App\Models\UserDb\CustUser::find()
 *              App\Models\UserDb\CustPcode::where()->with()->orderByDesc()->get()
 *              App\Models\UserDb\MandUser::find()
 *
 * DB ACCESS:   userdb.cust_user.cust_id, cust_firstname
 *              userdb.cust_pcode.pcode_id, cust_id, mand_id, cust_passcode, pcode_prefstat
 *              userdb.mand_user.mand_id, mand_uname
 *
 * SESSION ACCESS: _user_type, _mand_id, _cust_id, _sec_level (read)
 *                 _prompt_passkey (read + reset), _passkey_os (read)
 */

namespace App\Http\Controllers\UserDb;

use App\Models\UserDb\CustPcode;
use App\Models\UserDb\CustUser;
use App\Models\UserDb\MandUser;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CustDashboardController extends UserDbController
{
    public function index(Request $request): View|RedirectResponse
    {
        $userType = $request->session()->get('_user_type');

        // Passkey-Prompt: einmalig anzeigen, dann sofort zurücksetzen
        $showPasskeyPrompt = session('_prompt_passkey', false);
        if ($showPasskeyPrompt) {
            session(['_prompt_passkey' => false]);
        }
        $passkeyOs = session('_passkey_os', 'unknown');

        if ($userType === 'anon') {
            $mandId   = $request->session()->get('_mand_id');
            $secLevel = $request->session()->get('_sec_level');

            $mand = MandUser::find($mandId);

            return view('customer.dashboard', [
                'userType'          => 'anon',
                'mand'              => $mand,
                'secLevel'          => $secLevel,
                'pcodes'            => null,
                'showPasskeyPrompt' => $showPasskeyPrompt,
                'passkeyOs'         => $passkeyOs,
            ]);
        }

        if ($userType === 'cust') {
            $custId   = $request->session()->get('_cust_id');
            $mandId   = $request->session()->get('_mand_id');
            $secLevel = $request->session()->get('_sec_level');

            $cust = CustUser::find($custId);

            $pcodes = CustPcode::where('cust_id', $custId)
                ->orderByDesc('pcode_prefstat')
                ->orderByDesc('pcode_id')
                ->with('mandUser')
                ->get();

            return view('customer.dashboard', [
                'userType'          => 'cust',
                'cust'              => $cust,
                'mand'              => MandUser::find($mandId),
                'secLevel'          => $secLevel,
                'pcodes'            => $pcodes,
                'showPasskeyPrompt' => $showPasskeyPrompt,
                'passkeyOs'         => $passkeyOs,
            ]);
        }

        return redirect()->route('customer.login');
    }
}
