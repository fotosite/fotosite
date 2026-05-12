<?php
/**
 * FILE:        app/Http/Controllers/UserDb/SystemDashboardController.php
 * VERSION:     1.0.0
 *
 * FUNCTIONS:   index()  — Zeigt das System-Dashboard; liest _syst_id aus der Session,
 *                         lädt den zugehörigen SystUser und übergibt Anzeige-Daten
 *                         an system.dashboard.
 *                         Reads: userdb.syst_user.syst_id, syst_uname, syst_email,
 *                                syst_firstname, syst_lastname
 *
 * CALLS:       App\Models\UserDb\SystUser::find()
 *
 * DB ACCESS:   userdb.syst_user.syst_id, syst_uname, syst_email,
 *              syst_firstname, syst_lastname
 */

namespace App\Http\Controllers\UserDb;

use App\Models\UserDb\SystUser;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class SystemDashboardController extends UserDbController
{
    public function index(Request $request): View
    {
        $systId = $request->session()->get('_syst_id');
        $user   = $systId ? SystUser::find($systId) : null;

        return view('system.dashboard', [
            'userName'  => $user?->syst_uname    ?? 'System',
            'userEmail' => $user?->syst_email     ?? '',
            'firstName' => $user?->syst_firstname ?? '',
            'lastName'  => $user?->syst_lastname  ?? '',
        ]);
    }
}
