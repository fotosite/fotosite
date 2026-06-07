<?php
/**
 * FILE:        app/helpers.php
 * VERSION:     1.1.0
 * AUTHOR:      Martin Wagner
 * DATE:        2026-06-07
 * PURPOSE:     Globale Helper-Funktionen
 *
 * FUNCTIONS:   genitivName()      — Bildet den Genitiv eines Eigennamens (deutsch)
 *              detectOsPlatform() — Erkennt die Client-Plattform anhand des User-Agent-Strings
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
