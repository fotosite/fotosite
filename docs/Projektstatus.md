**Fotosite V08**

**Projektstatus #13**

*Stand: 19. Juli 2026*

**Tag: session_usertype_fix_ok — iOS-Long-Tap-Fix abgeschlossen, Upload-Bedingungen-Popup fuer cust entfernt, Trusted-Device-Feature zu vollstaendigem Auto-Login ausgebaut, "Sitzung abgelaufen"-Meldungen entfernt, zwei sessiondb.session-Bugs behoben (verwaiste Sessions, dauerhaft falscher user_type).**

**🎯 NÄCHSTER SCHRITT (Korrektur 19.07.): Phase 6 (Passkey) wurde in früheren Doku-Ständen fälschlich als „✓ Fertig" geführt. Korrekt: technisch vollständig implementiert, aber noch NICHT gründlich getestet. Ein umfassender Test der gesamten Passkey-Funktionalität (nicht nur iOS-spezifisch) ist der nächste anstehende Schritt und soll im neuen Chat erfolgen — siehe Abschnitt 6.**

# 1. Phasen-Übersicht

| **Phase** | **Inhalt** | **Status** | **Git-Tag** |
| --- | --- | --- | --- |
| Phase 1–4 | Fundament, Mand-Login, Eigenverwaltung, Einladungen, pw_list | **✓ Fertig** | p4_complete_ok |
| Phase 5 | Cust-Login (anon + registriert + 2FA + Passkey) | **✓ Fertig** | phase5_cust_login_ok |
| Phase 6 | Passkey-Infrastruktur (mand + cust) | **⚙ Implementiert, gründlicher Test steht noch aus*** | p6_passkey_ui_ok |
| Admin/Auth 16.–20.06. | Datenschutz, Adressfelder, Modals, Policy-Popup, Lösch-Mail, Welcome, FAQ | **✓ Fertig** | user_management_complete_ok |
| Bugfixes 21.–29.06. | Redirect-Loops, syst is_primary, 2FA-Fix, Dropdown, trim(), Touch, PW-Auge, Button-Animation, syst-Löschlogik, MandAccountDeletedMail | **✓ Fertig** | ios_button_feedback_ok, stable_2026-06-30_logins_ok |
| iOS-Long-Tap-Fix 09.–10.07. | Verbliebene `<a>`→`<button>`-Umbauten (Zurück-Links, Dashboard-Kacheln, Policy-Popup-Links) | **✓ Fertig** | ios_longtap_complete_ok |
| Upload-Bedingungen-Popup cust entfernt 10.07. | Popup deaktiviert (DS-Popup bleibt), statischer Hinweis + FAQ-Eintrag | **✓ Fertig** | cust_ds_hinweis_ok |
| Trusted-Device / Auto-Login 10.–18.07. | Vollständiger Auto-Login ohne Passwort (mand+cust), Logout-Widerruf | **✓ Fertig** | autologin_complete_ok |
| Session-Bugfixes 18.–19.07. | "Sitzung abgelaufen"-Meldungen entfernt, /backstage-Redirect-Fix, verwaiste Sessions, user_type-Fix | **✓ Fertig** | session_messages_removed_ok, session_usertype_fix_ok |
| Passkey-Gesamttest | Gründlicher, systematischer Test über alle Rollen/Geräte/Browser (nicht nur iOS) | 🎯 **Nächster Schritt** | — |
| Phase 7 | Foto-Content (Upload, Anzeige, Filter) | ⏳ Danach | — |

** Phase 6: Technisch implementiert, aber bisher nur punktuell getestet (Windows Hello, Android Chrome/Firefox, cust-Banner, ein Grenzfall). Ein gründlicher Gesamttest fehlt — siehe Abschnitt 6 und „Nächster Schritt" oben. Frühere Formulierung „✓ Fertig*" mit reiner iOS-Test-Fußnote war zu stark und ist hiermit korrigiert.*

# 2. Implementierungen 09.–10.07.2026

## 2.1 iOS-Long-Tap-Fix (ios_longtap_complete_ok)

Fortsetzung der globalen Button-Animation (Projektstatus #12, Abschnitt 2.2): Alle verbliebenen button-artigen `<a href>`-Elemente, die den langen Tap auf iOS noch nicht abgefangen hatten, wurden nachgezogen:

- **21 Zurück-Links** auf `<button @click="window.location='...'">` umgebaut (Tag `ios_longtap_fix_ok`)
- **13 Dashboard-Kacheln/Fließtext-Links** ebenso umgebaut (Tag `ios_longtap_dashboard_ok`)
- Regressions-Fix in `customer/galerien.blade.php` (bei den vorangegangenen Umbauten entstanden)
- Bereinigung inkonsistent gesetzter Alt-Tags
- **Policy-Update-Popup-Links** (Datenschutz + Upload-Bedingungen, cust+mand) auf `button`/`window.open` umgestellt (Tag `ios_longtap_policy_ok`)

Gesamtabschluss: Tag `ios_longtap_complete_ok`.

## 2.2 Upload-Bedingungen-Popup für cust entfernt (cust_ds_hinweis_ok)

Der Inhalt der Upload-Bedingungen betrifft nur mand (Content-Upload), ist für cust nicht relevant. Entfernt:

- `PolicyController::confirmCust()` — `upload`-Zweig gelöscht
- `CheckPolicyVersion` — `upload_version`-Vergleich im cust-Zweig entfernt

**DS-Popup bleibt für cust aktiv.** mand unverändert (beide Popups, DS + Upload). Ersatz: statischer Hinweis in `customer/dashboard.blade.php` + neuer FAQ-Eintrag `storage/app/private/faq/cust/Upload-Bedingungen.md`.

> **Korrektur:** Ein zuvor genanntes Tag `cust_upload_popup_removed_ok` existiert nicht in der Git-Historie. Die zutreffenden Tags sind `cust_ds_hinweis_ok` sowie die vorangehenden `ios_longtap_*`-Tags desselben Arbeitsblocks.

# 3. Implementierungen 10.–18.07.2026 — Trusted-Device / Auto-Login

Mehrstufig entwickelt (10 Commits/Schritte), ursprünglich als reiner 2FA-Skip (30 Tage) geplant, nach Anforderungsänderung zu **vollständigem Auto-Login ohne Passwort** erweitert (aktuell 1 Tag Gültigkeit für Testbetrieb, 7 Tage geplant).

## 3.1 Datenmodell

Neue Tabelle `sessiondb.trusted_device` (`td_id`, `user_type` enum(mand,cust), `user_id`, `token_hash` UNIQUE, `ua_hash`, `device_label`, `last_used_at`, `expires_at`, `created_at`) — bewusst in `sessiondb`, nicht `userdb` (Sicherheitstrennung). Neues Model `App\Models\SessionDb\TrustedDevice`.

## 3.2 Konfiguration

`config/trusted_device.php` (`'days' => env('TRUSTED_DEVICE_DAYS', 7)`).

**⚠ Gefunden bei dieser Doku-Aktualisierung:** `TRUSTED_DEVICE_DAYS` ist in `.env` **doppelt** gesetzt (Zeile 17: `=1`, Zeile 97: `=7`). Laravels `phpdotenv` überschreibt bereits gesetzte Variablen nicht — die erste Definition gewinnt, effektiv gilt aktuell `1` Tag. Vor Umstellung auf 7 Tage produktiv muss die doppelte Zeile bereinigt werden.

## 3.3 Helper & Services

- `app/helpers.php`: `trustedDeviceCookieName()`, `checkTrustedDevice()`, `issueTrustedDeviceCookie()`, `guessDeviceLabel()`, `revokeTrustedDevices()`
- `App\Services\UserDb\LoginSessionBuilder` (`buildForCust()`/`buildForMand()`) — zentralisiert den Session-Aufbau, gemeinsam genutzt von `CustLoginController`, `MandantLoginController` und `AutoLoginTrustedDevice`

## 3.4 Middleware `AutoLoginTrustedDevice`

Greift nur auf der `home`-Route ohne bestehende Session. Prüft Trusted-Device-Cookies für cust UND mand; sind beide gültig, wird **immer cust bevorzugt** (mand nie automatisch eingeloggt in diesem Fall). Bei bereits bestehender Session: sofortiger Redirect zum passenden Dashboard statt Login-Modal. Eingehängt in `bootstrap/app.php` zwischen `ValidateUserExists` und `CheckPolicyVersion`.

## 3.5 UI & Logout-Widerruf

- Checkbox „Dieses Gerät als sicher merken" im Login-Modal (cust+mand), Labeltext ohne sichtbare Tagesangabe
- Neue Komponente `resources/views/components/logout-button.blade.php` ersetzt 11 duplizierte Logout-Blöcke. Zeigt einen Bestätigungsdialog nur bei vorhandenem Trusted-Device-Eintrag: „Dieses Gerät ist als sicher gespeichert. Möchtest du dies löschen?" mit „Abmelden mit Löschen" / „Zurück"
- Bei jedem Logout: globales Cleanup abgelaufener `trusted_device`-Einträge (alle User, nicht nur der abmeldende)
- iOS-Caching-Fix: `Cache-Control: no-store` auf der `home`-Route + `pageshow`-Listener mit `persisted`-Check gegen bfcache-Restaurierung
- Neue Mail `App\Mail\TrustedDeviceAddedMail` + `resources/views/emails/trusted-device-added.blade.php`

Getestet auf iOS/Windows/Android.

**Tags:** `trusted_device_cust_ok`, `trusted_device_2fa_skip_complete_ok`, `trusted_device_config_ok`, `trusted_device_config_2FA_ok`, `trusted_device_logout_revoke_ok`, `autologin_pre_live_test`, `autologin_complete_ok`.

**Offener Punkt:** Umbenennung des „Zurück"-Buttons im Löschdialog zu „Verlassen ohne Löschen" wurde besprochen, aber nicht umgesetzt (Nutzer hat abgebrochen).

# 4. Implementierungen 18.–19.07.2026 — Session-Bugfixes

## 4.1 „Sitzung abgelaufen"-Meldungen entfernt (session_messages_removed_ok)

Entfernt für cust/mand/syst/anon. Dabei entdeckt und behoben: `REDIRECT_TARGETS['syst']` verwies in drei Middlewares (`SessionIdleTimeout`, `ValidateUserExists`, `RequireRole`) auf die nicht existierende Route `/system/login` (404) — korrigiert auf `/backstage`. Die jeweils eigenen, andersartigen Fehlermeldungen dieser Middlewares blieben bestehen.

## 4.2 sessiondb.session — zwei unabhängige Bugs (session_usertype_fix_ok)

1. **Verwaiste Session-Zeilen:** `regenerate()` lief ohne `$destroy=true` an mehreren Stellen, alte Zeilen blieben in der DB. Fix: `regenerate(true)` jetzt an **allen 7** Session-Übergängen — cust Passwort/2FA/Passkey/**anon**, mand Passwort/Passkey, syst 2FA.

   > **Korrektur gegenüber einer früheren Zusammenfassung:** Es gibt **keine** Ausnahme für den anon-Login. `CustLoginController::handleAnonLogin()` wurde im selben Commit ebenfalls von `regenerate()` auf `regenerate(true)` umgestellt (siehe Commit `8b4a875`) — identisch zu allen anderen Stellen.

2. **`user_type` war dauerhaft `'anon'`** in `sessiondb.session`, weil `SessionDbSessionHandler::write()` den Wert beim INSERT hartcodiert (der Handler kennt die Rolle beim Schreiben noch nicht) und nie per UPDATE korrigierte. Fix: `App::terminating()`-Callback läuft nach dem finalen `write()` und aktualisiert `user_type`/`cust_id`/`mand_id`/`syst_id` per gezieltem UPDATE.
3. `SessionDbSessionHandler::destroy()` nutzte nicht dieselbe ID-Kürzung (`substr($id, 0, 128)`) wie `write()` — korrigiert.

Betroffene Dateien: `SessionDbSessionHandler.php`, `LoginSessionBuilder.php`, `SystemLoginController.php`, `CustLoginController.php`, `MandantLoginController.php`.

# 5. Datenbankstand (19.07.2026)

| **DB** | **Änderungen seit #12** |
| --- | --- |
| userdb | Keine Schema-Änderungen |
| sessiondb | **NEU:** Tabelle `trusted_device` (siehe Abschnitt 3.1) |
| fotodb | Unverändert |
| fotoblobdb | Unverändert |

# 6. Offene Punkte

| **Priorität** | **Punkt** | **Detail** |
| --- | --- | --- |
| **Höchste (nächster Schritt)** | Gründlicher Passkey-Gesamttest | Phase 6 ist technisch implementiert, aber noch nicht systematisch getestet. Umfasst Registrierung, Login, Umbenennen, Löschen, Prompt-/Dismiss-Logik für mand UND cust über Windows/Android/iOS und die relevanten Browser — **kein reiner iOS-Test**. Für iOS zusätzlich zu beachten: kein Commit mit abgeschlossenem WebAuthn-Passkey-Test gefunden (bisherige iOS-Tests betrafen Button-Feedback/Auto-Login, nicht Passkeys) |
| **Hoch** | dirty-Ausblendung nachziehen | `system/mandanten/index.blade.php` + `customer/auth/register.blade.php` |
| **Hoch** | Regressionstest Android/Windows | Globale Button-Animation auf Desktop/Android prüfen |
| **Hoch** | Abnahmetest cust-Bereich | Blöcke 1–6; Tag: `cust_complete_ok`. Testplan liegt vor (docx + xlsx), pausiert |
| **Hoch** | `.env`-Duplikat `TRUSTED_DEVICE_DAYS` bereinigen | Zeile 17 (`=1`) vs. Zeile 97 (`=7`) — erste gewinnt, zweite ist wirkungslos |
| **Hoch** | Phase 7: mand-Content | ActivityGroup/Subgroup-Controller, Upload-Flow (`/mandant/upload/*`), AG/ASG-CRUD |
| **Mittel** | Phase 7: Cust-UI | Mandanten-Content-Seite, sec_level-Filter, mand_profile-Anzeige — NACH mand-Content |
| **Mittel** | SPF/DKIM Mailserver | E-Mail-Änderungsmails landen im Spam; aktuell nur UI-Hinweis |
| **Mittel** | Passkey-Link in Willkommensseite | Noch nicht umgesetzt |
| **Mittel** | Logout-Button „Zurück"-Text | Umbenennung zu „Verlassen ohne Löschen" besprochen, nicht umgesetzt |
| **Mittel** | Trusted-Device-Gültigkeit 1→7 Tage | Aktuell bewusst auf 1 Tag für Testbetrieb |
| **Niedrig** | E-Mail-Footer Sie-Form | `two-factor-code.blade.php`, `trusted-device-added.blade.php`, `cust-invite.blade.php` — Footer „Bitte antworten Sie..." trotz Du-Form im Mailtext |
| **Niedrig** | iOS Apple Mail Button-Text markierbar | Akzeptierte Einschränkung, nicht per CSS lösbar |
| **Niedrig** | ModerationMail | Erst nach Content-Upload relevant |
| **Niedrig** | Tote Datei löschen | nur `welcome.blade.php` (Breeze-Default) |
| **Plan** | Newsletter | Eigene DB-Tabelle, kein Code derzeit |

# 7. Git-Tags (neu seit #12)

| **Tag** | **Inhalt** |
| --- | --- |
| `stable_2026-06-30_logins_ok` | Abschluss Bugfixes 29.06. (syst-Löschlogik, MandAccountDeletedMail, deutsche PW-Meldungen) |
| `ios_longtap_fix_ok` / `ios_longtap_dashboard_ok` / `ios_longtap_policy_ok` / **`ios_longtap_complete_ok`** | iOS-Long-Tap-Fix (21 Zurück-Links, 13 Dashboard-Kacheln, Policy-Popup-Links) |
| `cust_ds_hinweis_ok` | Upload-Bedingungen-Popup für cust entfernt |
| `trusted_device_cust_ok`, `trusted_device_2fa_skip_complete_ok`, `trusted_device_config_ok`, `trusted_device_config_2FA_ok`, `trusted_device_logout_revoke_ok` | Trusted-Device-Feature (2FA-Skip-Stufe) |
| `autologin_pre_live_test`, **`autologin_complete_ok`** | Ausbau zu vollständigem Auto-Login ohne Passwort |
| `session_messages_removed_ok` | „Sitzung abgelaufen"-Meldungen entfernt, `/backstage`-Redirect-Fix |
| **`session_usertype_fix_ok`** | sessiondb.session: verwaiste Sessions + user_type-Bug behoben (aktueller Stand) |

Alle früheren Tags siehe Projektstatus #12 / PROJECT_CONTEXT Abschnitt 13.

Fotosite V08 — Projektstatus #13  |  Stand 19.07.2026
