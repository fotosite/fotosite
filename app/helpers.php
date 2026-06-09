<?php
/**
 * FILE:        app/helpers.php
 * VERSION:     1.2.0
 * AUTHOR:      Martin Wagner
 * DATE:        2026-06-08
 * PURPOSE:     Globale Helper-Funktionen
 *
 * FUNCTIONS:   genitivName()      — Bildet den Genitiv eines Eigennamens (deutsch)
 *              detectOsPlatform() — Erkennt die Client-Plattform anhand des User-Agent-Strings
 *              detectBrowser()    — Erkennt den Client-Browser anhand des User-Agent-Strings
 *
 * CALLS:       (none)
 *
 * DB ACCESS:   (none)
 */

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
