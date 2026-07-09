# Statusreport: SessionIdleTimeout — individuelle Timeouts je User-Typ

**Datum:** 2026-05-24

---

## Feature
SessionIdleTimeout — ersetzt AnonymousSessionTimeout; erzwingt konfigurierbare Idle-Timeouts für alle vier User-Typen (anon, cust, mand, syst).

## Abgeschlossen (Abschnitte 1–4)
1. `config/session_timeout.php` angelegt — liest `ANON_SESSION_TIMEOUT`, `CUST_SESSION_TIMEOUT`, `MAND_SESSION_TIMEOUT`, `SYST_SESSION_TIMEOUT` aus `.env`
2. `SessionIdleTimeout.php` implementiert — ersetzt `AnonymousSessionTimeout.php`
3. `bootstrap/app.php` angepasst — Middleware-Registrierung auf `SessionIdleTimeout` umgestellt
4. Timeout-Redirect auf `?expired=1`-Query-Parameter umgestellt; Fehlermeldung in `welcome.blade.php` und `login.blade.php` eingebaut

## Getestet
- **anon:** Timeout ausgelöst → Redirect `/` mit `?expired=1` → Fehlermeldung sichtbar ✓

## Offen
- cust/mand/syst-Tests erst möglich, wenn die jeweiligen Login-Views vorhanden sind

## Neue Dateien
- `config/session_timeout.php`
- `app/Http/Middleware/SessionIdleTimeout.php`

## Geänderte Dateien
- `bootstrap/app.php`
- `resources/views/welcome.blade.php`
- `resources/views/auth/login.blade.php`

## Gelöschte Dateien
- `app/Http/Middleware/AnonymousSessionTimeout.php`
