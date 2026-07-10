<?php
/**
 * FILE:        app/Http/Controllers/PolicyController.php
 * VERSION:     1.1.0
 *
 * ZWECK:       Blockierende Popup-Seiten für veraltete Policy-Versionen
 *              (Datenschutz / Upload-Bedingungen), für mand und cust getrennt.
 *              Wird von App\Http\Middleware\CheckPolicyVersion angesteuert.
 *
 * FUNCTIONS:   showMand()    — Liest session('_policy_update') ('ds'|'upload');
 *                               kein Pending-Type → Redirect zu mandant.dashboard.
 *                               Reads: (none, nur Session)
 *              showCust()    — Analog für cust → Redirect zu customer.content.
 *              confirmMand() — Schreibt je nach Type ds_version/ds_accepted_at
 *                               oder upload_terms_version/upload_terms_accepted_at
 *                               in mand_user; löscht Session-Flag; Redirect zu
 *                               mandant.dashboard.
 *                               Reads:  userdb.mand_user.mand_id
 *                               Writes: userdb.mand_user.ds_version, ds_accepted_at,
 *                                       upload_terms_version, upload_terms_accepted_at
 *              confirmCust() — ds: schreibt ds_version/ds_accepted_at in cust_user.
 *                               upload: schreibt upload_terms_version/
 *                               upload_terms_accepted_at in cust_user (analog mand,
 *                               seit DDL-Ergänzung von cust_user). Löscht Session-Flag;
 *                               Redirect zu customer.content.
 *                               Reads:  userdb.cust_user.cust_id
 *                               Writes: userdb.cust_user.ds_version, ds_accepted_at,
 *                                       upload_terms_version, upload_terms_accepted_at
 *
 * CALLS:       App\Models\UserDb\PolicyVersion::get()
 *              App\Models\UserDb\MandUser::find()
 *              App\Models\UserDb\CustUser::find()
 *
 * DB ACCESS:   userdb.policy_versions.pv_key, pv_value
 *              userdb.mand_user.mand_id, ds_version, ds_accepted_at,
 *              upload_terms_version, upload_terms_accepted_at
 *              userdb.cust_user.cust_id, ds_version, ds_accepted_at,
 *              upload_terms_version, upload_terms_accepted_at
 *
 * CHANGES:     1.1.0 (2026-06-18) confirmCust() — upload-Zweig schreibt jetzt
 *              upload_terms_version/upload_terms_accepted_at in cust_user statt
 *              nur Session-Flag (DDL um diese Spalten ergänzt); vorher erschien
 *              das Popup bei jedem Login erneut, da die Zustimmung nicht
 *              dauerhaft gespeichert wurde.
 */

namespace App\Http\Controllers;

use App\Models\UserDb\CustUser;
use App\Models\UserDb\MandUser;
use App\Models\UserDb\PolicyVersion;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PolicyController extends Controller
{
    public function showMand(Request $request): View|RedirectResponse
    {
        $type = $request->session()->get('_policy_update');

        if (! in_array($type, ['ds', 'upload'], true)) {
            return redirect()->route('mandant.dashboard');
        }

        return view('policy.mand_update', compact('type'));
    }

    public function showCust(Request $request): View|RedirectResponse
    {
        $type = $request->session()->get('_policy_update');

        if (! in_array($type, ['ds', 'upload'], true)) {
            return redirect()->route('customer.content');
        }

        return view('policy.cust_update', compact('type'));
    }

    public function confirmMand(Request $request): RedirectResponse
    {
        $type = $request->session()->get('_policy_update');
        $mand = MandUser::find($request->session()->get('_mand_id'));

        if ($mand) {
            if ($type === 'ds') {
                $mand->update([
                    'ds_version'     => PolicyVersion::get('ds_version'),
                    'ds_accepted_at' => now(),
                ]);
            }

            if ($type === 'upload') {
                $mand->update([
                    'upload_terms_version'     => PolicyVersion::get('upload_version'),
                    'upload_terms_accepted_at' => now(),
                ]);
            }
        }

        $request->session()->forget('_policy_update');

        return redirect()->route('mandant.dashboard');
    }

    public function confirmCust(Request $request): RedirectResponse
    {
        $type = $request->session()->get('_policy_update');

        if ($type === 'ds') {
            $cust = CustUser::find($request->session()->get('_cust_id'));

            $cust?->update([
                'ds_version'     => PolicyVersion::get('ds_version'),
                'ds_accepted_at' => now(),
            ]);
        }

        $request->session()->forget('_policy_update');

        return redirect()->route('customer.content');
    }
}
