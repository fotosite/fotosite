<?php
/**
 * FILE:        app/Http/Controllers/UserDb/MandantDashboardController.php
 * VERSION:     1.0.0
 * AUTOR:       Martin Wagner
 * DATUM:       2026-06-07
 *
 * ZWECK:       Mand-Dashboard — Einstiegsseite für Mandanten nach erfolgreichem
 *              Login + 2FA. Liest und löscht den einmaligen Passkey-Prompt-Flag
 *              aus der Session und übergibt ihn an die View.
 *
 * FUNCTIONS:   index() — Liest _prompt_passkey + _passkey_os aus Session,
 *                         setzt _prompt_passkey sofort zurück, gibt
 *                         mandant.dashboard zurück.
 *                         Reads:  session._prompt_passkey, session._passkey_os
 *                         Writes: session._prompt_passkey (Reset auf false)
 *
 * CALLS:       —
 *
 * DB ACCESS:   — (keine; nur Session)
 *
 * SESSION ACCESS: _prompt_passkey (read + reset), _passkey_os (read)
 */

namespace App\Http\Controllers\UserDb;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class MandantDashboardController extends UserDbController
{
    public function index(Request $request): View
    {
        // Passkey-Prompt: einmalig anzeigen, dann sofort zurücksetzen
        $showPasskeyPrompt = session('_prompt_passkey', false);
        if ($showPasskeyPrompt) {
            session(['_prompt_passkey' => false]);
        }
        $passkeyOs = session('_passkey_os', 'unknown');

        return view('mandant.dashboard', [
            'showPasskeyPrompt' => $showPasskeyPrompt,
            'passkeyOs'         => $passkeyOs,
        ]);
    }
}
