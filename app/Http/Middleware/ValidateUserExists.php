<?php
/**
 * FILE:        app/Http/Middleware/ValidateUserExists.php
 * VERSION:     1.1.0
 * AUTOR:       Martin Wagner
 * DATUM:       2026-05-26
 *
 * ZWECK:       Check 4 — Stellt sicher, dass der in der Session gespeicherte
 *              User-Datensatz noch in der Datenbank existiert. Verhindert, dass
 *              gelöschte Accounts weiterhin aktive Sessions besitzen.
 *              Für mand: zusätzlich active = 1 geprüft — gesperrte Mandanten
 *              werden wie gelöschte behandelt (Session invalidieren).
 *              anon-Sessions werden ohne DB-Prüfung durchgelassen.
 *              Ergebnis wird 60 Sekunden gecacht.
 *
 * FUNCTIONS:   handle(Request, Closure): Response
 *                  — Liest _user_type + passende *_id aus der Session.
 *                  anon: sofort $next($request).
 *                  mand/cust/syst: Existenzprüfung via Cache::remember (TTL 60s).
 *                  Bei nicht gefundenem User: Cache-Eintrag löschen, Session
 *                  invalidieren, regenerateToken(), Redirect zum typgerechten
 *                  Login mit deutscher Fehlermeldung.
 *                  Bei fehlendem *_id-Session-Key: wie nicht gefunden behandeln.
 *
 * CALLS:       App\Models\UserDb\MandUser::find()
 *              App\Models\UserDb\CustUser::find()
 *              App\Models\UserDb\SystUser::find()
 *              Illuminate\Support\Facades\Cache::remember()
 *              Illuminate\Support\Facades\Cache::forget()
 *              Illuminate\Http\Request::session()::get()
 *              Illuminate\Http\Request::session()::invalidate()
 *              Illuminate\Http\Request::session()::regenerateToken()
 *
 * DB ACCESS:   userdb.mand_user.mand_id, active
 *              userdb.cust_user.cust_id
 *              userdb.syst_user.syst_id
 */

namespace App\Http\Middleware;

use App\Models\UserDb\CustUser;
use App\Models\UserDb\MandUser;
use App\Models\UserDb\SystUser;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class ValidateUserExists
{
    private const REDIRECT_TARGETS = [
        'anon' => '/',
        'cust' => '/customer/login',
        'mand' => '/mandant/login',
        'syst' => '/system/login',
    ];

    private const NOT_FOUND_MESSAGES = [
        'cust' => 'Ihre Sitzung ist ungültig. Bitte melden Sie sich erneut an.',
        'mand' => 'Ihre Sitzung ist ungültig. Bitte melden Sie sich erneut an.',
        'syst' => 'Ihre Sitzung ist ungültig. Bitte melden Sie sich erneut an.',
    ];

    private const ID_SESSION_KEYS = [
        'mand' => '_mand_id',
        'cust' => '_cust_id',
        'syst' => '_syst_id',
    ];

    private const MODEL_MAP = [
        'mand' => MandUser::class,
        'cust' => CustUser::class,
        'syst' => SystUser::class,
    ];

    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->hasSession() || ! $request->session()->isStarted()) {
            return $next($request);
        }

        $userType = $request->session()->get('_user_type') ?? 'anon';

        if ($userType === 'anon') {
            return $next($request);
        }

        if (! isset(self::ID_SESSION_KEYS[$userType])) {
            return $next($request);
        }

        $userId = $request->session()->get(self::ID_SESSION_KEYS[$userType]);

        if ($userId === null) {
            return $this->invalidateAndRedirect($request, $userType);
        }

        $cacheKey = 'user_exists_' . $userType . '_' . $userId;
        $model    = self::MODEL_MAP[$userType];

        $exists = Cache::remember($cacheKey, 60, function () use ($model, $userId, $userType) {
            if ($userType === 'mand') {
                return $model::where('mand_id', $userId)->where('active', 1)->exists();
            }
            return $model::find($userId) !== null;
        });

        if (! $exists) {
            Cache::forget($cacheKey);
            return $this->invalidateAndRedirect($request, $userType);
        }

        return $next($request);
    }

    private function invalidateAndRedirect(Request $request, string $userType): Response
    {
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $target  = self::REDIRECT_TARGETS[$userType] ?? '/';
        $message = self::NOT_FOUND_MESSAGES[$userType] ?? 'Bitte melden Sie sich erneut an.';

        return redirect($target)->with('error', $message);
    }
}
