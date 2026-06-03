<?php
/**
 * FILE:        app/Http/Middleware/SessionIdleTimeout.php
 * VERSION:     1.0.5
 * AUTOR:       Martin Wagner
 * DATUM:       2026-05-26
 *
 * ZWECK:       Erzwingt konfigurierbare Idle-Timeouts für alle User-Typen
 *              (anon, cust, mand, syst). Ersetzt AnonymousSessionTimeout.
 *
 * FUNCTIONS:   handle(Request, Closure) — Liest _user_type aus der Session
 *                  (Default 'anon', da anonyme Sessions diesen Key nicht schreiben).
 *                  Lädt den passenden Timeout aus config('session_timeout').
 *                  Vergleicht time() mit _last_activity. Bei Überschreitung:
 *                  Session invalidieren, Token regenerieren, Redirect zum
 *                  typgerechten Login mit deutscher Fehlermeldung.
 *                  Bei gültiger Session: _last_activity aktualisieren.
 *                  Kein Eingriff, wenn die Session noch nicht gestartet wurde.
 *
 * CALLS:       Illuminate\Http\Request::hasSession()
 *              Illuminate\Http\Request::session()::isStarted()
 *              Illuminate\Http\Request::session()::get()
 *              Illuminate\Http\Request::session()::put()
 *              Illuminate\Http\Request::session()::invalidate()
 *              Illuminate\Http\Request::session()::regenerateToken()
 *              Illuminate\Support\Facades\config()
 *              Illuminate\Support\Facades\DB::connection('sessiondb')->table()->delete()
 *
 * DB ACCESS:   sessiondb.session.expires_at (DELETE, probabilistisch 5 %)
 */

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class SessionIdleTimeout
{
    private const REDIRECT_TARGETS = [
        'anon' => '/',
        'cust' => '/customer/login',
        'mand' => '/mandant/login',
        'syst' => '/system/login',
    ];

    private const TIMEOUT_MESSAGES = [
        'anon' => 'Ihre Sitzung ist abgelaufen.',
        'cust' => 'Ihre Sitzung ist abgelaufen. Bitte melden Sie sich erneut an.',
        'mand' => 'Ihre Mandanten-Sitzung ist abgelaufen. Bitte melden Sie sich erneut an.',
        'syst' => 'Ihre System-Sitzung ist abgelaufen. Bitte melden Sie sich erneut an.',
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

            if (rand(1, 100) <= 5) {
                DB::connection('sessiondb')
                    ->table('session')
                    ->where('expires_at', '<', now())
                    ->delete();
            }

            return redirect(self::REDIRECT_TARGETS[$userType])
                ->with('error', self::TIMEOUT_MESSAGES[$userType]);
        }

        $request->session()->put('_last_activity', time());

        return $next($request);
    }
}
