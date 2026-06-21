<?php
/**
 * FILE:        app/Http/Middleware/CheckWelcome.php
 * VERSION:     1.1.0
 *
 * ZWECK:       Prüft nach dem Login, ob mand/cust die Willkommensseite noch
 *              nicht gesehen hat (show_welcome = 1, Default bei Account-
 *              Erstellung). Falls ja: Redirect auf die blockierende
 *              Willkommensseite (customer.welcome / mandant.welcome).
 *              Läuft NACH CheckPolicyVersion (siehe bootstrap/app.php) —
 *              eine veraltete Datenschutz-/Upload-Policy ist eine rechtliche
 *              Pflichtbestätigung und hat Vorrang vor der reinen
 *              Onboarding-Willkommensseite; ist die Policy aktuell, greift
 *              dieser Check als nächstes.
 *
 * FUNCTIONS:   handle(Request, Closure): Response
 *                  — Bricht sofort ab auf den welcome/.confirm-Routen
 *                  selbst (verhindert Redirect-Schleife) und wenn kein
 *                  _user_type in der Session steht (anon/nicht eingeloggt).
 *                  Für mand/cust: lädt den User, prüft show_welcome,
 *                  redirected bei show_welcome === 1 auf die jeweilige
 *                  Willkommensseite.
 *
 * CALLS:       App\Models\UserDb\MandUser::find()
 *              App\Models\UserDb\CustUser::find()
 *
 * DB ACCESS:   userdb.mand_user.mand_id, show_welcome
 *              userdb.cust_user.cust_id, show_welcome
 *
 * CHANGES:     1.1.0 (2026-06-21) Ausschluss um '*.policy.*' erweitert —
 *              verhindert Redirect-Ping-Pong mit CheckPolicyVersion, wenn
 *              ein Account gleichzeitig show_welcome=1 UND eine veraltete
 *              ds_version/upload_terms_version hat (typisch: frisch
 *              eingeladener mand nach Passwort-Reset).
 */

namespace App\Http\Middleware;

use App\Models\UserDb\CustUser;
use App\Models\UserDb\MandUser;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckWelcome
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->routeIs('*.welcome*') || $request->routeIs('*.policy.*')) {
            return $next($request);
        }

        if (! $request->hasSession() || ! $request->session()->isStarted()) {
            return $next($request);
        }

        $userType = $request->session()->get('_user_type');

        if ($userType === 'mand') {
            $mand = MandUser::find($request->session()->get('_mand_id'));

            if ($mand && (int) $mand->show_welcome === 1) {
                return redirect()->route('mandant.welcome');
            }
        }

        if ($userType === 'cust') {
            $cust = CustUser::find($request->session()->get('_cust_id'));

            if ($cust && (int) $cust->show_welcome === 1) {
                return redirect()->route('customer.welcome');
            }
        }

        return $next($request);
    }
}
