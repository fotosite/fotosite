<?php
/**
 * FILE:        app/Http/Middleware/BlockSamsungBrowser.php
 * VERSION:     1.0.0
 *
 * FUNCTIONS:   handle(Request, Closure) — Erkennt Samsung Internet per
 *                  detectBrowser($request->userAgent()) und zeigt statt der
 *                  angeforderten Route eine statische Hinweisseite
 *                  (errors.samsung-not-supported, HTTP 200), da Samsung
 *                  Internet nicht unterstützt wird. Andernfalls wird der
 *                  Request unverändert durchgereicht.
 *
 * CALLS:       detectBrowser() (app/helpers.php)
 *
 * DB ACCESS:   none
 */

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BlockSamsungBrowser
{
    public function handle(Request $request, Closure $next): Response
    {
        if (detectBrowser($request->userAgent() ?? '') === 'samsung') {
            return response()->view('errors.samsung-not-supported');
        }

        return $next($request);
    }
}
