<?php
/**
 * FILE:        app/Http/Controllers/UserDb/CustDashboardController.php
 * VERSION:     1.2.0
 * AUTOR:       Martin Wagner
 * DATUM:       2026-06-13
 *
 * ZWECK:       Cust-Dashboard und Inhaltsseite — Einstiegsseiten nach Login.
 *              Anonyme Besucher (anon) werden nach Login zu customer.content
 *              geleitet; das Dashboard ist für registrierte Mitglieder (cust).
 *
 * FUNCTIONS:   index()   — Liest _user_type aus Session; baut View-Daten für
 *                           anon- oder cust-Modus; liest und löscht den einmaligen
 *                           Passkey-Prompt-Flag; gibt customer.dashboard zurück.
 *                           Bei unbekanntem _user_type: Redirect zu customer.login.
 *                           Reads: userdb.cust_user.cust_id, cust_firstname
 *              content() — Guard: _user_type muss 'anon' oder 'cust' sein, sonst
 *                           Redirect zu customer.login. Lädt aktiven Mandanten
 *                           via _mand_id; gibt customer.content zurück.
 *                           Reads: userdb.mand_user.mand_id, mand_uname
 *
 * CALLS:       App\Models\UserDb\CustUser::find()
 *              App\Models\UserDb\MandUser::find()
 *
 * DB ACCESS:   userdb.cust_user.cust_id, cust_firstname
 *              userdb.mand_user.mand_id, mand_uname
 *
 * SESSION ACCESS: _user_type, _cust_id, _mand_id (read)
 *                 _prompt_passkey (read + reset), _passkey_os (read)
 */

namespace App\Http\Controllers\UserDb;

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
            return view('customer.dashboard', [
                'cust'              => null,
                'showPasskeyPrompt' => $showPasskeyPrompt,
                'passkeyOs'         => $passkeyOs,
            ]);
        }

        if ($userType === 'cust') {
            $custId = $request->session()->get('_cust_id');
            $cust   = CustUser::find($custId);

            return view('customer.dashboard', [
                'cust'              => $cust,
                'showPasskeyPrompt' => $showPasskeyPrompt,
                'passkeyOs'         => $passkeyOs,
            ]);
        }

        return redirect()->route('customer.login');
    }

    public function content(Request $request): View|RedirectResponse
    {
        $userType = $request->session()->get('_user_type');

        if (! in_array($userType, ['anon', 'cust'])) {
            return redirect()->route('customer.login');
        }

        $mandId = $request->session()->get('_mand_id');
        $mand   = MandUser::find($mandId);

        return view('customer.content', compact('userType', 'mand'));
    }
}
