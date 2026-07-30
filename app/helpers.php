<?php
/**
 * FILE:        app/helpers.php
 * VERSION:     1.5.0
 * AUTHOR:      Martin Wagner
 * DATE:        2026-07-30
 * PURPOSE:     Globale Helper-Funktionen
 *
 * FUNCTIONS:   genitivName()             — Bildet den Genitiv eines Eigennamens (deutsch)
 *              detectOsPlatform()        — Erkennt die Client-Plattform anhand des User-Agent-Strings
 *              detectBrowser()           — Erkennt den Client-Browser anhand des User-Agent-Strings
 *              trustedDeviceCookieName() — Cookie-Name für Trusted-Device-Feature (cust/mand-Login)
 *              checkTrustedDevice()      — Prüft Trusted-Device-Cookie gegen sessiondb.trusted_device
 *              issueTrustedDeviceCookie()— Legt trusted_device-Eintrag an, gibt Cookie zurück
 *              guessDeviceLabel()        — Kosmetische Geräte-Bezeichnung aus User-Agent
 *              revokeTrustedDevices()    — Löscht alle trusted_device-Einträge eines Nutzers
 *              renderMarkdownVariant()   — Extrahiert genau EINEN Tag-Block aus einer
 *                                          Markdown-Datei (<!--TAG-->...<!--/TAG-->) und
 *                                          rendert ihn per CommonMarkConverter zu HTML
 *              loginThrottleKey()        — Gemeinsamer IP-Rate-Limit-Schlüssel für alle
 *                                          Login-Ebenen (cust, mand, syst)
 *              checkLoginThrottle()      — Prüft aktive Login-Sperre für die IP, verlängert
 *                                          sie bei fortgesetztem Angriff
 *              recordFailedLoginAttempt()— Zählt einen fehlgeschlagenen Login-Versuch,
 *                                          meldet, falls dieser Versuch die Sperre auslöst
 *              clearLoginThrottle()      — Setzt den Fehlversuchs-Zähler einer IP zurück
 *                                          (bei erfolgreichem Login)
 *
 * CALLS:       App\Models\SessionDb\TrustedDevice::where()/create()
 *              League\CommonMark\CommonMarkConverter::convert()
 *              Illuminate\Support\Facades\RateLimiter::tooManyAttempts()/hit()/clear()
 *
 * DB ACCESS:   sessiondb.trusted_device (td_id, user_type, user_id, token_hash,
 *              ua_hash, device_label, last_used_at, expires_at, created_at)
 *
 * CHANGES:     1.5.0 (2026-07-30) loginThrottleKey()/checkLoginThrottle()/
 *              recordFailedLoginAttempt()/clearLoginThrottle() ergänzt — einheitliche,
 *              IP-basierte, rollenübergreifende Login-Sperre nach
 *              config('app.login_lockout_max_attempts') Fehlversuchen für
 *              config('app.login_lockout_minutes') Minuten; ersetzt die bisherigen
 *              getrennten RateLimiter cust-login/login-2fa/cust-anon-login. Deaktiviert
 *              bei DEBUGMODE=true, analog zu den bisherigen RateLimitern.
 *              1.4.0 (2026-07-30) renderMarkdownVariant() ergänzt: Ein-Block-Auswahl
 *              aus Tag-markierten Markdown-Dateien (analog zum <!--MAND-->-Mechanismus
 *              in DatenschutzController::erlaeuterung(), aber Auswahl statt Filterung),
 *              für Passkey-Hinweistexte (customer/mandant passkey index).
 *              1.3.0 (2026-07-10) Trusted-Device-Helper ergänzt (cust/mand-Login,
 *              betrifft NICHT anon/pw_list) — siehe FUNCTIONS oben.
 */

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use League\CommonMark\CommonMarkConverter;

if (! function_exists('genitivName')) {
    /**
     * Bildet den deutschen Genitiv eines Eigennamens.
     * Endet der Name auf s, x, z oder ß → Apostroph; sonst → 's'.
     */
    function genitivName(string $name): string
    {
        if (preg_match('/[sxzß]$/u', $name)) {
            return $name . "'";
        }

        return $name . 's';
    }
}

if (! function_exists('detectOsPlatform')) {
    /**
     * Erkennt die Client-Plattform anhand des User-Agent-Strings.
     * Rückgabewerte: 'win', 'andr', 'ios', 'unknown'
     */
    function detectOsPlatform(string $userAgent): string
    {
        if (stripos($userAgent, 'android') !== false) return 'andr';
        if (stripos($userAgent, 'iphone')  !== false) return 'ios';
        if (stripos($userAgent, 'ipad')    !== false) return 'ios';
        if (stripos($userAgent, 'windows') !== false) return 'win';
        return 'unknown';
    }
}

if (! function_exists('detectBrowser')) {
    /**
     * Erkennt den Client-Browser anhand des User-Agent-Strings.
     * Rückgabewerte: 'edge', 'samsung', 'chrome', 'firefox', 'safari', 'unknown'
     * Reihenfolge wichtig: Edge und Samsung vor Chrome prüfen,
     * da deren UA-String ebenfalls 'Chrome' enthält.
     */
    function detectBrowser(string $userAgent): string
    {
        if (stripos($userAgent, 'Edg/')         !== false) return 'edge';
        if (stripos($userAgent, 'SamsungBrowser') !== false) return 'samsung';
        if (stripos($userAgent, 'Chrome')        !== false) return 'chrome';
        if (stripos($userAgent, 'Firefox')       !== false) return 'firefox';
        if (stripos($userAgent, 'Safari')        !== false) return 'safari';
        return 'unknown';
    }
}

if (! function_exists('trustedDeviceCookieName')) {
    function trustedDeviceCookieName(string $userType): string
    {
        return $userType === 'mand' ? 'trusted_device_mand' : 'trusted_device_cust';
    }
}

if (! function_exists('checkTrustedDevice')) {
    /**
     * Prüft, ob für den aktuellen Request ein gültiges Trusted-Device-Cookie
     * vorliegt (cust/mand-Login, NICHT anon). Bei Erfolg wird last_used_at
     * aktualisiert.
     */
    function checkTrustedDevice(string $userType, int $userId, \Illuminate\Http\Request $request): bool
    {
        $cookieValue = $request->cookie(trustedDeviceCookieName($userType));
        if (! $cookieValue || ! str_contains($cookieValue, '.')) {
            return false;
        }

        [$tdId, $plainToken] = explode('.', $cookieValue, 2);

        $device = \App\Models\SessionDb\TrustedDevice::where('td_id', $tdId)
            ->where('user_type', $userType)
            ->where('user_id', $userId)
            ->where('expires_at', '>', now())
            ->first();

        if (! $device) {
            return false;
        }

        if (! hash_equals($device->token_hash, hash('sha256', $plainToken))) {
            return false;
        }

        $currentUaHash = hash('sha256', $request->userAgent() ?? '');
        if (! hash_equals($device->ua_hash, $currentUaHash)) {
            return false;
        }

        $device->update(['last_used_at' => now()]);

        return true;
    }
}

if (! function_exists('issueTrustedDeviceCookie')) {
    /**
     * Legt einen neuen TrustedDevice-DB-Eintrag an (cust/mand-Login) und
     * gibt das zugehörige Cookie zurück (Gültigkeit config('trusted_device.days'), httpOnly, secure).
     */
    function issueTrustedDeviceCookie(string $userType, int $userId, \Illuminate\Http\Request $request): \Symfony\Component\HttpFoundation\Cookie
    {
        $plainToken = bin2hex(random_bytes(32));
        $tokenHash  = hash('sha256', $plainToken);
        $uaHash     = hash('sha256', $request->userAgent() ?? '');

        $device = \App\Models\SessionDb\TrustedDevice::create([
            'user_type'    => $userType,
            'user_id'      => $userId,
            'token_hash'   => $tokenHash,
            'ua_hash'      => $uaHash,
            'device_label' => guessDeviceLabel($request->userAgent() ?? ''),
            'expires_at'   => now()->addDays(config('trusted_device.days')),
            'created_at'   => now(),
        ]);

        $cookieValue = $device->td_id . '.' . $plainToken;

        return cookie(
            trustedDeviceCookieName($userType),
            $cookieValue,
            60 * 24 * config('trusted_device.days'),
            null, null,
            true,
            true,
            false,
            'lax'
        );
    }
}

if (! function_exists('guessDeviceLabel')) {
    /**
     * Grobe, rein kosmetische Geräte-Bezeichnung aus dem User-Agent für die
     * Anzeige in der "Vertrauenswürdige Geräte"-Liste. Keine sicherheits-
     * relevante Funktion.
     */
    function guessDeviceLabel(string $userAgent): string
    {
        $browser = match (true) {
            str_contains($userAgent, 'Edg/')     => 'Edge',
            str_contains($userAgent, 'Chrome/')  => 'Chrome',
            str_contains($userAgent, 'Firefox/') => 'Firefox',
            str_contains($userAgent, 'Safari/') && ! str_contains($userAgent, 'Chrome/') => 'Safari',
            default => 'Unbekannter Browser',
        };

        $os = match (true) {
            str_contains($userAgent, 'iPhone'), str_contains($userAgent, 'iPad') => 'iOS',
            str_contains($userAgent, 'Android') => 'Android',
            str_contains($userAgent, 'Windows')  => 'Windows',
            str_contains($userAgent, 'Macintosh') => 'macOS',
            default => '',
        };

        return trim($browser . ($os ? " auf $os" : ''));
    }
}

if (! function_exists('revokeTrustedDevices')) {
    /**
     * Widerruft alle Trusted-Device-Einträge eines Nutzers (cust/mand),
     * z.B. bei Passwort-Änderung. Betrifft NICHT anon/pw_list.
     */
    function revokeTrustedDevices(string $userType, int $userId): int
    {
        return \App\Models\SessionDb\TrustedDevice::where('user_type', $userType)
            ->where('user_id', $userId)
            ->delete();
    }
}

if (! function_exists('renderMarkdownVariant')) {
    /**
     * Extrahiert GENAU EINEN Tag-Block (<!--TAG-->...<!--/TAG-->) aus einer
     * Markdown-Datei und rendert dessen Inhalt per CommonMarkConverter zu HTML.
     * Im Unterschied zum <!--MAND-->-Mechanismus in
     * DatenschutzController::erlaeuterung() (dort wird bei Nichtübereinstimmung
     * herausgeschnitten) wird hier aus mehreren Varianten EINE ausgewählt.
     *
     * Fallback, falls $tag in der Datei nicht gefunden wird: 'STANDARD' bei
     * Dateien mit 'allgemein' im Pfad, sonst 'UNKNOWN' — zusätzlich
     * Log::warning() mit Dateiname und gesuchtem Tag (kein harter Fehler,
     * da reiner Anzeige-Text).
     */
    function renderMarkdownVariant(string $filePath, string $tag): string
    {
        if (! file_exists($filePath)) {
            Log::warning('renderMarkdownVariant: Datei nicht gefunden.', [
                'file' => $filePath,
                'tag'  => $tag,
            ]);

            return '';
        }

        $content = file_get_contents($filePath);

        $extractTag = static function (string $tag) use ($content): ?string {
            $pattern = '/<!--\s*' . preg_quote($tag, '/') . '\s*-->(.*?)<!--\s*\/' . preg_quote($tag, '/') . '\s*-->/s';

            return preg_match($pattern, $content, $matches) ? trim($matches[1]) : null;
        };

        $block = $extractTag($tag);

        if ($block === null) {
            $fallbackTag = str_contains($filePath, 'allgemein') ? 'STANDARD' : 'UNKNOWN';

            Log::warning('renderMarkdownVariant: Tag nicht gefunden, Fallback verwendet.', [
                'file'     => $filePath,
                'tag'      => $tag,
                'fallback' => $fallbackTag,
            ]);

            $block = $extractTag($fallbackTag) ?? '';
        }

        $converter = new CommonMarkConverter();

        return $converter->convert($block)->getContent();
    }
}

if (! function_exists('loginThrottleKey')) {
    /**
     * Gemeinsamer Rate-Limit-Schlüssel pro IP für alle Login-Ebenen
     * (cust, mand, syst) — zählt rollenübergreifend, EIN Schlüssel pro IP.
     */
    function loginThrottleKey(Request $request): string
    {
        return 'login-throttle:' . $request->ip();
    }
}

if (! function_exists('checkLoginThrottle')) {
    /**
     * Prüft, ob für die aktuelle IP bereits eine Login-Sperre aktiv ist.
     * Verlängert bei aktiver Sperre zusätzlich deren Laufzeit (fortgesetzter
     * Angriff). Im Debug-Modus (DEBUGMODE=true) immer null.
     */
    function checkLoginThrottle(Request $request): ?string
    {
        if (config('app.debugmode') === true) {
            return null;
        }

        $key = loginThrottleKey($request);

        if (RateLimiter::tooManyAttempts($key, config('app.login_lockout_max_attempts'))) {
            RateLimiter::hit($key, config('app.login_lockout_minutes') * 60);

            return 'Zu viele Fehlversuche. Versuche es später noch einmal.';
        }

        return null;
    }
}

if (! function_exists('recordFailedLoginAttempt')) {
    /**
     * Zählt einen fehlgeschlagenen Login-Versuch für die aktuelle IP. Gibt
     * die Sperr-Meldung zurück, wenn DIESER Versuch die Sperre gerade
     * ausgelöst hat — sonst null (bisherige Fehlermeldung soll weiter
     * greifen). Im Debug-Modus (DEBUGMODE=true) immer null (kein Zählen).
     */
    function recordFailedLoginAttempt(Request $request): ?string
    {
        if (config('app.debugmode') === true) {
            return null;
        }

        $key = loginThrottleKey($request);

        RateLimiter::hit($key, config('app.login_lockout_minutes') * 60);

        if (RateLimiter::tooManyAttempts($key, config('app.login_lockout_max_attempts'))) {
            return 'Zu viele Fehlversuche. Versuche es später noch einmal.';
        }

        return null;
    }
}

if (! function_exists('clearLoginThrottle')) {
    /**
     * Setzt den Login-Fehlversuchs-Zähler für die aktuelle IP zurück —
     * bei jedem erfolgreichen Login aufrufen.
     */
    function clearLoginThrottle(Request $request): void
    {
        RateLimiter::clear(loginThrottleKey($request));
    }
}
