<?php
/**
 * FILE:        app/Http/Middleware/SessionHijackProtection.php
 * VERSION:     1.0.0
 *
 * FUNCTIONS:   handle(Request, Closure) — Validates IP hash and user-agent hash
 *                  stored in the session against the current request values.
 *                  Destroys the session and redirects to login on any mismatch.
 *                  Stores hashes on the first request of a new session.
 *
 * CALLS:       Illuminate\Http\Request::ip()
 *              Illuminate\Http\Request::userAgent()
 *              Illuminate\Http\Request::session()
 *              Illuminate\Http\Request::session()::has()
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

class SessionHijackProtection
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->hasSession() || ! $request->session()->isStarted()) {
            return $next($request);
        }

        $ipHash = hash('sha256', $request->ip() ?? '');
        $uaHash = hash('sha256', $request->userAgent() ?? '');

        if ($request->session()->has('_ip_hash')) {
            $storedIp = $request->session()->get('_ip_hash');
            $storedUa = $request->session()->get('_ua_hash');

            if (! hash_equals($storedIp, $ipHash) || ! hash_equals($storedUa, $uaHash)) {
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')
                    ->withErrors(['session' => 'Your session has been terminated for security reasons.']);
            }
        } else {
            $request->session()->put('_ip_hash', $ipHash);
            $request->session()->put('_ua_hash', $uaHash);
        }

        return $next($request);
    }
}
