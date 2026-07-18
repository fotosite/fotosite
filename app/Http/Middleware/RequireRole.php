<?php
/**
 * FILE:        app/Http/Middleware/RequireRole.php
 * VERSION:     1.1
 * AUTOR:       Martin Wagner
 * DATUM:       2026-05-26
 *
 * ZWECK:       Check 2 — Stellt sicher, dass die aktive Session dem geforderten
 *              user_type entspricht. Schützt Routen-Gruppen vor unberechtigtem
 *              Zugriff durch falschen oder fehlenden Session-Typ.
 *
 * FUNCTIONS:   handle(Request, Closure, string $role): Response
 *                  — Liest _user_type ausschließlich aus der Session (nie aus
 *                  Request oder Cookie). Vergleicht mit dem $role-Parameter.
 *                  Bei Mismatch oder fehlender Session: Session invalidieren,
 *                  Token regenerieren, Redirect zum typgerechten Login mit
 *                  deutscher Fehlermeldung. Bei Match: Request durchlassen.
 *
 * CALLS:       Illuminate\Http\Request::hasSession()
 *              Illuminate\Http\Request::session()::isStarted()
 *              Illuminate\Http\Request::session()::get()
 *              Illuminate\Http\Request::session()::invalidate()
 *              Illuminate\Http\Request::session()::regenerateToken()
 *
 * DB ACCESS:   none
 *
 * CHANGES:     1.1 (2026-07-18) REDIRECT_TARGETS['syst'] von
 *              '/system/login' (existierte nicht, führte zu 404) auf
 *              '/backstage' korrigiert (tatsächliche Route:
 *              system.backstage.login).
 */

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireRole
{
    private const REDIRECT_TARGETS = [
        'anon' => '/',
        'cust' => '/customer/login',
        'mand' => '/mandant/login',
        'syst' => '/backstage',
    ];

    private const ACCESS_MESSAGES = [
        'anon' => 'Bitte melden Sie sich an.',
        'cust' => 'Sie sind nicht berechtigt, diesen Bereich zu betreten.',
        'mand' => 'Sie sind nicht berechtigt, diesen Bereich zu betreten.',
        'syst' => 'Sie sind nicht berechtigt, diesen Bereich zu betreten.',
    ];

    public function handle(Request $request, Closure $next, string $role): Response
    {
        $hasSession = $request->hasSession() && $request->session()->isStarted();
        $userType   = $hasSession ? ($request->session()->get('_user_type') ?? 'anon') : 'anon';

        if ($userType !== $role) {
            if ($hasSession) {
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }

            $target  = self::REDIRECT_TARGETS[$userType] ?? '/';
            $message = self::ACCESS_MESSAGES[$userType] ?? 'Bitte melden Sie sich an.';

            return redirect($target)->with('error', $message);
        }

        return $next($request);
    }
}
