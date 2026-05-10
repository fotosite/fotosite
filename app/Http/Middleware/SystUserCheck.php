<?php
/**
 * FILE:        app/Http/Middleware/SystUserCheck.php
 * VERSION:     1.0.0
 *
 * FUNCTIONS:   handle(Request, Closure) — Prüft ob die aktuelle Session einer
 *                  authentifizierten System-User-Sitzung gehört:
 *                  _user_type muss 'syst' sein, _syst_id muss gesetzt und
 *                  nicht null sein. Bei Fehler: Session invalidieren und
 *                  Redirect auf /backstage mit Fehlermeldung.
 *                  Bei Erfolg: Request durchlassen.
 *
 * CALLS:       Illuminate\Http\Request::session()::get()
 *              Illuminate\Http\Request::session()::invalidate()
 *              Illuminate\Http\Request::session()::regenerateToken()
 *
 * DB ACCESS:   none
 */

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SystUserCheck
{
    public function handle(Request $request, Closure $next): Response
    {
        if (
            $request->session()->get('_user_type') !== 'syst' ||
            $request->session()->get('_syst_id') === null
        ) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect('/backstage')
                ->withErrors(['session' => 'Bitte melden Sie sich an.']);
        }

        return $next($request);
    }
}
