<?php
/**
 * FILE:        app/Http/Middleware/NoIndexHeader.php
 * VERSION:     1.0.0
 *
 * FUNCTIONS:   handle(Request, Closure) — Appends the X-Robots-Tag header
 *                  with value "noindex, nofollow" to every HTTP response,
 *                  preventing search engine indexing of the entire site.
 *
 * CALLS:       Symfony\Component\HttpFoundation\Response::headers::set()
 *
 * DB ACCESS:   none
 */

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class NoIndexHeader
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        $response->headers->set('X-Robots-Tag', 'noindex, nofollow');

        return $response;
    }
}
