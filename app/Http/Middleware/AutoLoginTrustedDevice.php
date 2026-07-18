<?php
/**
 * FILE:        app/Http/Middleware/AutoLoginTrustedDevice.php
 * VERSION:     1.0.0
 * AUTOR:       Martin Wagner
 * DATUM:       2026-07-17
 *
 * ZWECK:       Prüft beim Aufruf der home-Route (ohne bestehende Session),
 *              ob ein gültiges Trusted-Device-Cookie für cust und/oder
 *              mand vorliegt, und loggt bei genau einem Treffer automatisch
 *              ein (ohne Passwort/2FA). Sind beide Cookies gleichzeitig
 *              gültig (z.B. Galerist mit eigenem Testaccount, gleiche
 *              E-Mail für cust und mand), wird IMMER cust bevorzugt — mand
 *              wird in diesem Fall nie automatisch eingeloggt. Bei keinem
 *              gültigen Cookie: kein Eingriff, normales Login-Modal.
 *
 * FUNCTIONS:   handle(Request, Closure): Response
 *                  — Bricht sofort ab außerhalb der home-Route oder bei
 *                  bereits bestehender Session (_user_type gesetzt).
 *                  Prüft Trusted-Device-Cookie für cust und mand; bei
 *                  beiden gültig wird mand verworfen (cust-Vorrang). Baut
 *                  bei Treffer die Session über LoginSessionBuilder auf.
 *
 *              resolveTrustedUserId(string, Request): ?int
 *                  — Prüft das Trusted-Device-Cookie für den gegebenen
 *                  Nutzertyp OHNE bereits bekannte user_id (die wird erst
 *                  aus dem DB-Datensatz ermittelt) und gibt sie bei Erfolg
 *                  zurück, sonst null.
 *
 * CALLS:       App\Models\SessionDb\TrustedDevice::where()
 *              App\Services\UserDb\LoginSessionBuilder::buildForCust()/buildForMand()
 *              App\Models\UserDb\CustUser::find(), CustPcode::where(), MandUser::find()
 *
 * DB ACCESS:   sessiondb.trusted_device (td_id, user_type, user_id, token_hash,
 *              ua_hash, expires_at, last_used_at)
 *              userdb.cust_user, userdb.cust_pcode, userdb.mand_user
 */

namespace App\Http\Middleware;

use App\Models\UserDb\CustUser;
use App\Models\UserDb\CustPcode;
use App\Models\UserDb\MandUser;
use App\Services\UserDb\LoginSessionBuilder;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AutoLoginTrustedDevice
{
    public function __construct(
        private readonly LoginSessionBuilder $loginSessionBuilder,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->routeIs('home')) {
            return $next($request);
        }

        if ($request->session()->has('_user_type')) {
            $userType = $request->session()->get('_user_type');

            return match ($userType) {
                'cust' => redirect()->route('customer.content'),
                'mand' => redirect()->route('mandant.dashboard'),
                'syst' => redirect()->route('system.dashboard'),
                default => $next($request),
            };
        }

        $custId = $this->resolveTrustedUserId('cust', $request);
        $mandId = $this->resolveTrustedUserId('mand', $request);

        // Bei beiden gleichzeitig gültig: immer cust bevorzugen, mand
        // nie automatisch einloggen in diesem Fall.
        if ($custId && $mandId) {
            $mandId = null;
        }

        if ($custId) {
            $cust = CustUser::find($custId);

            if ($cust) {
                $pcode = CustPcode::where('cust_id', $cust->cust_id)
                    ->orderByDesc('pcode_prefstat')
                    ->orderByDesc('pcode_id')
                    ->first();

                if ($pcode) {
                    return $this->loginSessionBuilder->buildForCust($request, $cust, $pcode);
                }
            }
        }

        if ($mandId) {
            $mand = MandUser::find($mandId);

            if ($mand) {
                return $this->loginSessionBuilder->buildForMand($request, $mand);
            }
        }

        return $next($request);
    }

    /**
     * Prüft das Trusted-Device-Cookie für den gegebenen Nutzertyp und
     * gibt die zugehörige user_id zurück, falls gültig — sonst null.
     * Nutzt dieselbe Cookie-Struktur wie checkTrustedDevice(), aber ohne
     * bereits bekannte user_id (die kennen wir hier ja noch nicht).
     */
    private function resolveTrustedUserId(string $userType, Request $request): ?int
    {
        $cookieValue = $request->cookie(trustedDeviceCookieName($userType));

        if (! $cookieValue || ! str_contains($cookieValue, '.')) {
            return null;
        }

        [$tdId, $plainToken] = explode('.', $cookieValue, 2);

        $device = \App\Models\SessionDb\TrustedDevice::where('td_id', $tdId)
            ->where('user_type', $userType)
            ->where('expires_at', '>', now())
            ->first();

        if (! $device) {
            return null;
        }

        if (! hash_equals($device->token_hash, hash('sha256', $plainToken))) {
            return null;
        }

        $currentUaHash = hash('sha256', $request->userAgent() ?? '');
        if (! hash_equals($device->ua_hash, $currentUaHash)) {
            return null;
        }

        $device->update(['last_used_at' => now()]);

        return (int) $device->user_id;
    }
}
