<?php
/**
 * FILE:        app/helpers.php
 * VERSION:     1.0.0
 * AUTHOR:      Martin Wagner
 * DATE:        2026-05-30
 * PURPOSE:     Globale Helper-Funktionen
 *
 * FUNCTIONS:   genitivName()   — Bildet den Genitiv eines Eigennamens (deutsch)
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
