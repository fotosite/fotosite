<?php

/**
 * Fotosite V08
 * Datei: config/session_timeout.php
 * Version: 1.0
 * Autor: Martin Wagner
 * Datum: 2026-05-14
 *
 * Beschreibung:
 *   Konfiguration der Session-Idle-Timeouts je User-Typ.
 *   Werte werden aus .env gelesen; die angegebenen Defaults
 *   greifen, wenn kein .env-Eintrag vorhanden ist.
 *
 * Verwendete Daten:
 *   Keine Datenbank — reine Konfigurationsdatei.
 *
 * Verwendet von:
 *   app/Http/Middleware/SessionIdleTimeout.php
 */

return [

    /*
    |--------------------------------------------------------------------------
    | Session-Idle-Timeouts (Sekunden)
    |--------------------------------------------------------------------------
    |
    | Gibt an, nach wie vielen Sekunden Inaktivität eine Session je User-Typ
    | automatisch invalidiert wird.
    |
    | anon  — Anonyme Besucher          (Standard: 900 s = 15 min)
    | cust  — Registrierte Customer     (Standard: 900 s = 15 min)
    | mand  — Mandanten                 (Standard: 1800 s = 30 min, wegen Upload-Workflows)
    | syst  — System-Administratoren    (Standard: 600 s = 10 min, höchste Sicherheit)
    |
    */

    'anon' => (int) env('ANON_SESSION_TIMEOUT', 900),
    'cust' => (int) env('CUST_SESSION_TIMEOUT', 900),
    'mand' => (int) env('MAND_SESSION_TIMEOUT', 1800),
    'syst' => (int) env('SYST_SESSION_TIMEOUT', 600),

];