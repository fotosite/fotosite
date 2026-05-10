<?php
/**
 * FILE:        app/Http/Middleware/MandantActiveCheck.php
 * VERSION:     1.1.0
 *
 * FUNCTIONS:   handle(Request, Closure)       — Verifies the authenticated Mandant
 *                  account is active and not expired. Reads _mand_id from the session.
 *                  Invalidates the session and redirects to login when the account is
 *                  missing, deactivated (active = false), or expired (valid_to in the past).
 *                  Passes through immediately for non-mandant sessions.
 *              invalidateAndRedirect(Request, string) — Invalidates the session,
 *                  regenerates the CSRF token, and redirects to login with an error.
 *
 * CALLS:       App\Models\UserDb\MandUser::find()
 *              Illuminate\Http\Request::session()::get()
 *              Illuminate\Http\Request::session()::invalidate()
 *              Illuminate\Http\Request::session()::regenerateToken()
 *
 * DB ACCESS:   userdb.mand_user.mand_id, active, valid_to
 */

namespace App\Http\Middleware;

use App\Models\UserDb\MandUser;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MandantActiveCheck
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->session()->get('_user_type') !== 'mand') {
            return $next($request);
        }

        $mandId = $request->session()->get('_mand_id');

        if (! $mandId) {
            return $this->invalidateAndRedirect($request, 'Ihre Sitzung ist ungültig. Bitte melden Sie sich erneut an.');
        }

        $mandant = MandUser::find($mandId);

        if (! $mandant) {
            return $this->invalidateAndRedirect($request, 'Ihre Sitzung ist ungültig. Bitte melden Sie sich erneut an.');
        }

        if (! $mandant->active) {
            return $this->invalidateAndRedirect($request, 'Ihr Mandanten-Account ist deaktiviert.');
        }

        if ($mandant->valid_to !== null && $mandant->valid_to->isPast()) {
            return $this->invalidateAndRedirect($request, 'Ihr Mandanten-Account ist abgelaufen.');
        }

        return $next($request);
    }

    private function invalidateAndRedirect(Request $request, string $message): Response
    {
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->withErrors(['account' => $message]);
    }
}
