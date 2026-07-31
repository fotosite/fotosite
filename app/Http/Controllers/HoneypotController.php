<?php
/**
 * FILE:        app/Http/Controllers/HoneypotController.php
 * VERSION:     1.0.0
 * AUTHOR:      Martin Wagner
 * DATE:        2026-07-31
 * PURPOSE:     Faengt Aufrufe der in storage/app/private/honeypot_paths.txt
 *              gelisteten Koeder-Pfade ab (typische Scanner-/Angriffsziele wie
 *              wp-login.php, admin, phpmyadmin, .env etc.), verhaengt eine
 *              Login-Sperre gegen die anfragende IP und liefert 404.
 *
 * FUNCTIONS:   handle() — Loest triggerHoneypotLockout() aus, dann abort(404).
 *
 * CALLS:       triggerHoneypotLockout() (app/helpers.php)
 *
 * DB ACCESS:   keine (nur RateLimiter-Cache via triggerHoneypotLockout())
 *
 * CHANGES:     1.0.0 (2026-07-31) Initiale Version.
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HoneypotController extends Controller
{
    public function handle(Request $request): Response
    {
        triggerHoneypotLockout($request);

        abort(404);
    }
}
