<?php
/**
 * FILE:        app/Http/Middleware/AnonymousSessionTimeout.php
 * VERSION:     1.0.0
 *
 * FUNCTIONS:   handle(Request, Closure) — Enforces a configurable idle timeout
 *                  for anonymous customer sessions. Reads _user_type from the
 *                  session; if "anon", compares current time against
 *                  _anon_last_activity. Invalidates the session and redirects
 *                  to home on timeout. Updates _anon_last_activity on every
 *                  valid request. Timeout duration is read from
 *                  ANON_SESSION_TIMEOUT in .env (default: 1800 seconds).
 *
 * CALLS:       Illuminate\Http\Request::session()::has()
 *              Illuminate\Http\Request::session()::get()
 *              Illuminate\Http\Request::session()::put()
 *              Illuminate\Http\Request::session()::invalidate()
 *              Illuminate\Http\Request::session()::regenerateToken()
 *
 * DB ACCESS:   none
 */

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AnonymousSessionTimeout
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->hasSession() || ! $request->session()->isStarted()) {
            return $next($request);
        }

        if ($request->session()->get('_user_type') !== 'anon') {
            return $next($request);
        }

        $timeout     = (int) env('ANON_SESSION_TIMEOUT', 1800);
        $lastActivity = (int) $request->session()->get('_anon_last_activity', 0);

        if ($lastActivity > 0 && (time() - $lastActivity) > $timeout) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect('/')->withErrors(['session' => 'Your session has expired. Please log in again.']);
        }

        $request->session()->put('_anon_last_activity', time());

        return $next($request);
    }
}
