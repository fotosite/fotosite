<?php
/**
 * FILE:        app/Http/Controllers/UserDb/CustDashboardController.php
 * VERSION:     1.0.0
 * AUTOR:       Martin Wagner
 * DATUM:       2026-06-03
 *
 * ZWECK:       Cust-Dashboard — Einstiegsseite für registrierte Mitglieder (cust)
 *              und anonyme Besucher (anon) nach erfolgreichem Login.
 *
 * FUNCTIONS:   index() — Liest _user_type aus Session; baut View-Daten für anon-
 *                         oder cust-Modus; gibt customer.dashboard zurück.
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

        if ($userType === 'anon') {
            $mandId   = $request->session()->get('_mand_id');
            $secLevel = $request->session()->get('_sec_level');

            $mand = MandUser::find($mandId);

            return view('customer.dashboard', [
                'userType' => 'anon',
                'mand'     => $mand,
                'secLevel' => $secLevel,
                'pcodes'   => null,
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
                'userType' => 'cust',
                'cust'     => $cust,
                'mand'     => MandUser::find($mandId),
                'secLevel' => $secLevel,
                'pcodes'   => $pcodes,
            ]);
        }

        return redirect()->route('customer.login');
    }
}
