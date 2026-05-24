<?php
/**
 * FILE:        app/Http/Middleware/SessionIdleTimeout.php
 * VERSION:     1.0.3
 *
 * FUNCTIONS:   handle(Request, Closure) — Enforces configurable idle timeouts for
 *                  all authenticated user types (anon, cust, mand, syst). Reads
 *                  _user_type from the session; a missing key is treated as 'anon'
 *                  because anonymous sessions never write _user_type to the session
 *                  payload (only to sessiondb.session.user_type). Loads the matching
 *                  timeout from config('session_timeout'). Compares time() against
 *                  _last_activity. On timeout: invalidates the session and redirects
 *                  to the login route for the given user type with ?expired=1 appended
 *                  as a query parameter. On valid session: updates _last_activity to time().
 *                  Passes through unchanged only when the session has not been started.
 *
 * CALLS:       Illuminate\Http\Request::hasSession()
 *              Illuminate\Http\Request::session()::isStarted()
 *              Illuminate\Http\Request::session()::get()
 *              Illuminate\Http\Request::session()::put()
 *              Illuminate\Http\Request::session()::invalidate()
 *              Illuminate\Http\Request::session()::regenerateToken()
 *              Illuminate\Support\Facades\config()
 *
 * DB ACCESS:   none
 */

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SessionIdleTimeout
{
    private const REDIRECT_TARGETS = [
        'anon' => '/',
        'cust' => '/customer/login',
        'mand' => '/mandant/login',
        'syst' => '/system/login',
    ];

    public function handle(Request $request, Closure $next): Response
    {
      

        if (! $request->hasSession() || ! $request->session()->isStarted()) {
            return $next($request);
        }

        $userType = $request->session()->get('_user_type') ?? 'anon';

        $timeout      = (int) config('session_timeout.' . $userType, 900);
        $lastActivity = (int) $request->session()->get('_last_activity', 0);

        if ($lastActivity > 0 && (time() - $lastActivity) > $timeout) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect(self::REDIRECT_TARGETS[$userType] . '?expired=1');
        }

        $request->session()->put('_last_activity', time());

        return $next($request);
    }
}
