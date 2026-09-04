**Fotosite V08**

**Projektstatus #17**

*Stand: 4. September 2026*

**04.09. (aktuellster Stand): Vier weitere neue Tags seit `ui_texte_ausgelagert_ok`** — `mand_deaktivierung_loeschschutz_ok` (Commit `929bbf3`): zweistufiger Löschschutz für Galeristen-Konten (Deaktivieren mit E-Mail-Benachrichtigung, Karenzzeit, endgültiges Löschen erst danach über die Bearbeiten-Seite). `samsung_browser_blocked_ok` (Commit `2e1caf5`): Samsung Internet Browser wird komplett blockiert (eigene Hinweisseite statt Login). `passkey_test_mobile_ui_ok`: Mobile Kartenansicht für „Meine Passkeys" (Desktop-Tabelle ab `md:`, Detailbereich + Tab-Liste darunter). `passkey_gpm_doppelregistrierung_fix_ok` (Commit `24afe41`): AAGUID-Prüfung + `excludeCredentials` verhindert Doppelregistrierung desselben Passkeys bei Google Password Manager. Zusätzlich im selben Arbeitsblock: Mobile Kartenansicht für System-User- und Galeristen-Liste (Desktop-Tabelle ab `md:`, Galeristen-Liste 30 % breiter, `max-w-6xl`), eigenes Lösch-Bestätigungsmodal für Galeristen statt nativem `confirm()`, iOS-Dismiss-Logik-Fix (ignoriert jetzt browserspezifischen `ua_hash`, da Safari/Chrome/Firefox sich auf iOS den iCloud-Schlüsselbund teilen) sowie iOS-Passkey-Namensvorschlag ohne Browser-Suffix. Details Abschnitt 4j.**

**29.08. (Commit `47843f2`, Tag `subscriptiondb_vorbereitet_ok`): Vorbereitung fünfte Datenbank `subscriptiondb` für ein späteres Abrechnungssystem (mand+cust)** — DDL für 5 Tabellen (`subscriber`, `plan`, `subscription`, `ledger_entry`, `invoice`) ausgeführt, inkl. bewusster FK-Constraints (Projektabweichung), Connection + Base-Model `SubscriptionDbModel` angelegt, Demo-Datensatz eingespielt (Subscriber 30118, drei Verträge). Trigger für Journal-Unveränderbarkeit noch nicht gesetzt, Anwendungslogik bewusst nicht implementiert. Details Abschnitt 4i, Konzept: `docs/Konzept_Abrechnungssystem.md`.**

**Tag: ui_texte_ausgelagert_ok — 28 UI-/E-Mail-Textstellen (Login-/2FA-Hinweise, Konto-Löschen-/Galerie-entfernen-Warnungen, Upload-Bedingungen, E-Mail-ändern-Erklärung, Spam-Hinweis, Passkey-Prompts, Alias-Erklärung, Pflichtfeld-Nachtrag-Hinweis, Policy-Update-Hinweise, 7 E-Mail-Bodies) nach editierbaren Markdown-Dateien (`storage/app/private/ui-texte/{all,cust,mand,syst}/*.md`, nicht versioniert) ausgelagert — neuer Helper `uiText()`, `renderMarkdownVariant()` um `$vars`-Platzhalter-Mechanismus erweitert. Zusätzlich 2FA-Code-Gültigkeitsdauer zentralisiert (war an zwei Stellen hartcodiert) über neue `config/twofa.php`. Plus zwei `.env`-only-Korrekturen: Mail-Konfiguration auf echtes Postfach (smtps/Alfahosting) umgestellt (vorheriges `starttls` von Symfony Mailer 8.x nicht unterstützt, verursachte 500er bei jedem Mailversand), `BACKSTAGE_PATH` erstmals explizit gesetzt. Commit 2dbeb37 — siehe Abschnitt 4h. Vorheriger Stand (26.08., Tag registration_policy_version_fix_ok): Bugfix — `ds_version`/`upload_terms_version` bei Registrierung lasen bisher einen statischen Config-Wert statt der tatsächlich aktuellen `policy_versions`-Version, wodurch das „Policy geändert"-Popup fälschlich direkt nach der Registrierung erschien. Commit 83c738e — siehe Abschnitt 4g. Davor (26.08., Tag pflichtfelder_erzwingung_ok): neue Middleware `CheckPflichtfelder` erzwingt nachträglich zur Pflicht gewordene, aber noch leere Felder nach dem Login (Redirect zur Konto-Seite mit Amber-Hinweisbox, 60s-Cache); grauer „(optional)"-Hinweis bei Straße/PLZ+Ort ergänzt. Commit 16e81f3 — siehe Abschnitt 4f. Weiter zurück (26.08., Tag pflichtfelder_konfiguration_ok): Konfigurierbare Pflichtfelder für mand/cust (Telefon/Straße+Hausnr./PLZ+Ort/Firma): neue Datei `pflichtfelder.txt` + Helper `istPflichtfeld()`, Registrierung fragt nur Pflichtfelder ab, Konto-Bearbeiten zeigt immer alle Felder dynamisch; begleitende DB-Migration auf echtes NULL statt Platzhalter-String; neue mand-Detailseite je Mitglied + umgebaute Mitgliederliste; syst sieht jetzt zusätzlich mand-Adresse; Pflicht-Sternchen-Bugfix in mandant/konto.blade.php. Commit eef2eed — siehe Abschnitt 4e. Noch weiter zurück (04.08., Tag logout_dialog_close_window_ok): Zwei kleine Fixes: (1) Trusted-Device-Cookie jetzt auch im Passkey-Login-Pfad wirksam (cust+mand), Tag trusted_device_passkey_ok, Commit 268c2b7 — siehe Abschnitt 4d. (2) Logout-Dialog-Button „Zurück" zu „Fenster schließen" umbenannt, window.close()-Versuch mit Dialog-Fallback, Commit 859516d — siehe Abschnitt 4d. Davor (03.08., Tag trusted_device_cust_nofactor_fix_ok): Bugfix cust-Trusted-Device-Cookie im Nicht-2FA-Login-Pfad, siehe Abschnitt 4c. Weiter zurück (31.07., Tag honeypot_login_attacks_log_ok): Sicherheits-Härtung des Logins abgeschlossen: einheitliche IP-basierte Login-Sperre (cust/mand/syst), dynamische Honeypot-Routen + Log-Kanal login_attacks, syst-Login-Pfad über .env konfigurierbar, mand-2FA-Opt-out, Passkey-Hinweistexte ausgelagert. Zusätzlich, weiterhin UNCOMMITTED: syst-Passwort-Policy auf min. 20 Zeichen + Komplexität verschärft, inkl. Login-Hard-Block.**

**🎯 NÄCHSTER SCHRITT (weiterhin gültig): Phase 6 (Passkey) wurde in früheren Doku-Ständen fälschlich als „✓ Fertig" geführt. Korrekt: technisch vollständig implementiert, ein umfassender Test der gesamten Passkey-Funktionalität (nicht nur iOS-spezifisch) bleibt der nächste anstehende Schritt — siehe Abschnitt 6. Seit dem letzten Stand (27.08.) wurde erheblicher Fortschritt erzielt (GPM-Doppelregistrierung behoben, Mobile-UI für die Passkey-Liste, iOS-Dismiss-/Namensvorschlag-Fixes, Samsung-Internet-Blockade — Details Abschnitt 4j), der verbleibende Rest-Umfang des Gesamttests ist jedoch noch nicht abschließend spezifiziert.**

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
| Sicherheits-Härtung Login 29.–31.07. | IP-Sperre, Honeypot+Log-Kanäle, Login-Cleanup, mand-2FA-Opt-out, BACKSTAGE_PATH, Passkey-Hints ausgelagert | **✓ Fertig** (syst-PW-Policy+Hard-Block: uncommitted) | honeypot_login_attacks_log_ok |
| Passkey-Gesamttest | Gründlicher, systematischer Test über alle Rollen/Geräte/Browser (nicht nur iOS) | 🎯 **Nächster Schritt** (erheblicher Fortschritt 31.08.–04.09., Rest-Umfang offen — Abschnitt 4j) | — |
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

# 4a. Implementierungen 29.–31.07.2026 — Sicherheits-Härtung Login

Neun Commits (`error_messages_dirty_fix_ok` bis `honeypot_login_attacks_log_ok`), Details in PROJECT_CONTEXT.md Abschnitt 8a/10e und Notfall_Start.md Abschnitt 5.2f:

- **Fehlermeldungen dirty-ausgeblendet + Error-Bag-isoliert** (10 Stellen, `error_messages_dirty_fix_ok`) — cust/anon/mand/syst-Login teilen sich teilweise dieselbe Seite (`login-modal.blade.php`), benannte Bags verhindern Cross-Tab-Bleed
- **Login-Cleanup erweitert** (`login_cleanup_expired_records_ok`) — `LoginSessionBuilder::cleanupExpiredRecords()` bereinigt jetzt auch `invite`, `cust_invite`, `trusted_device`, `twofa_code` bei jedem cust/mand-Login
- **Passkey-Hinweistexte ausgelagert** (`passkey_hints_markdown_ok`) — 4 editierbare md-Dateien statt hartkodierter Blade-Blöcke, neue Funktion `renderMarkdownVariant()`
- **Platzhaltertexte präzisiert** (`invite_placeholder_texts_ok`) — Einladungsformulare zeigen jetzt „E-Mail Mitglied"/„E-Mail Galerist:in"/„E-Mail Systuser" statt irreführendem `ihre@email.de`
- **mand-2FA optional deaktivierbar** (`mand_2fa_optin_login_fix_ok`) — `mand_2fa_opt_in` war totes Feld, wird jetzt beim Login ausgewertet; Checkbox invertiert/umbenannt
- **Seitenkopf-Label MITGLIED + Inkonsistenz #12** (`mitglied_label_und_inkonsistenz12_ok`) — `passkey.sign_count` als faktisch wirkungslos dokumentiert
- **Einheitliche IP-basierte Login-Sperre** (`login_lockout_ip_based_ok`) — 5 Fehlversuche/5 Minuten, EIN Schlüssel über cust/mand/syst; mand+syst hatten zuvor gar keine Drosselung
- **syst-Login-Pfad über `.env` konfigurierbar** (`backstage_path_configurable_ok`) — löst die hartkodierten `/backstage`-Vorkommen strukturell ab
- **Log-Kanal `login_attacks` + dynamische Honeypot-Routen** (`honeypot_login_attacks_log_ok`) — Köder-Pfade aus `storage/app/private/honeypot_paths.txt`, Treffer lösen volle IP-Sperre über denselben Schlüssel aus

**Zusätzlich, zum Zeitpunkt dieses Doku-Standes noch UNCOMMITTED:** syst-Passwort-Policy auf `Password::min(20)->mixedCase()->numbers()->symbols()` verschärft (bisher `min:12`) + Hard-Block beim Login bei nicht mehr konformem Bestandspasswort (kein Self-Service-Reset für syst, Notfallverfahren dokumentiert in Notfall_Start.md Abschnitt 6a).

# 4b. Implementierung 01.08.2026 — anon-Login per Kurzcode-Link

Tag `anon_share_link_shortcode_ok` (Commit `15e21bd`): neue Tabelle `sessiondb.share_link` (7-stelliger UNIQUE-Code je `mand_id`+`sec_level`), erzeugt per `firstOrCreate()` in `MandantPwListController::edit()` (Route `GET /s/{code}`, `routes/web.php`). Präzise Pro-Stufe-Invalidierung: `update()` vergleicht vor dem Speichern jede Stufe alt/neu und löscht den Share-Link nur für tatsächlich geänderte Stufen. Bisheriger langer, verschlüsselter Token-Weg (`loginViaShareLink()`) vollständig ersetzt durch `loginViaShortCode()` + gemeinsame `buildAnonSession()`-Methode. Zusätzlich: Datenschutz-Hinweis-Popup für anon (beide Zugangswege) aus `customer/content.blade.php` entfernt — künftig über Content-Seiten selbst geplant (Phase 7, noch offen). Details PROJECT_CONTEXT.md Abschnitt 10f.

# 4c. Bugfix 03.08.2026 — cust: Trusted-Device-Cookie im Nicht-2FA-Pfad

Tag `trusted_device_cust_nofactor_fix_ok` (Commit `ddf5e55`): In `CustLoginController::handleLogin()` wurde die `remember_device`-Checkbox im Nicht-2FA-Login-Pfad ausgelesen, aber nie ausgewertet — `issueTrustedDeviceCookie()` wurde dort nie aufgerufen. Der Trusted-Device-Datensatz entstand bisher nur über den 2FA-Pfad (`verifyTwoFactor()`). Bei `mand` war der äquivalente Pfad bereits korrekt verdrahtet (`issueTrustedDeviceIfRequested()`, siehe Abschnitt 4a, `mand_2fa_optin_login_fix_ok`) — nur `cust` war betroffen. Fix: neue private Methode `CustLoginController::issueTrustedDeviceIfRequested()` ergänzt (analog `MandantLoginController`), im Nicht-2FA-Rückgabepfad von `handleLogin()` aufgerufen. `verifyTwoFactor()` unverändert, keine doppelte Cookie-Ausstellung möglich (beide Pfade exklusiv). Getestet mit und ohne 2FA. Details PROJECT_CONTEXT.md Abschnitt 10g.

# 4d. Fixes 04.08.2026 — Passkey-Trusted-Device + Logout-Dialog-Button

**Trusted-Device-Cookie im Passkey-Login-Pfad (Tag `trusted_device_passkey_ok`, Commit `268c2b7`):** Die Checkbox „Gerät merken" wirkte bisher nur im Passwort-Login-Formular — der Passkey-Button liegt außerhalb dieses `<form>`, sein `fetch()`-Request sendete den Checkbox-Zustand nie mit. Fix: JS liest den Checkbox-Zustand jetzt direkt aus dem DOM (`document.getElementById('remember_device_cust'/'_mand')?.checked`) und sendet ihn als `remember_device` im Passkey-Fetch-Body mit. `CustLoginController::passkeyLogin()` und `MandantLoginController::passkeyLogin()` rufen jetzt `issueTrustedDeviceIfRequested()` auf; deren Typehint wurde von `RedirectResponse` auf `RedirectResponse|JsonResponse` gelockert (beide nutzen `Illuminate\Http\ResponseTrait`, `->cookie()` funktioniert identisch). Getestet cust + mand, Trusted-Device-Datensatz + Mail-Versand (`TrustedDeviceAddedMail`) bestätigt. Details PROJECT_CONTEXT.md Abschnitt 10h.

**Logout-Dialog-Button „Zurück" → „Fenster schließen" (Tag `logout_dialog_close_window_ok`, Commit `859516d`):** Im Trusted-Device-Bestätigungsdialog (`logout-button.blade.php`) ruft der Button jetzt zusätzlich `window.close()` auf (`@click="window.close(); showConfirm = false"`) — schlägt bei nicht per Skript geöffneten Tabs browserseitig lautlos fehl und dient dann als Aufforderung an den Nutzer. Bisheriges Fallback-Verhalten (Dialog schließen via `showConfirm = false`) bleibt erhalten, Alpine-Mechanismus unverändert. Löst den offenen Punkt aus Abschnitt 6 / Inkonsistenzen.md #7 (siehe dortige Anmerkung zur Nummerierung).

# 4e. Feature 26.08.2026 — Konfigurierbare Pflichtfelder für mand/cust (Telefon/Straße/PLZ+Ort/Firma)

**Pflichtfelder-Konfiguration (Tag `pflichtfelder_konfiguration_ok`, Commit `eef2eed`):** Neue Datei `storage/app/private/pflichtfelder.txt` (nicht versioniert, analog `honeypot_paths.txt`) steuert je Feld `mand` (Pflicht) oder `opt` (optional) über neuen Helper `istPflichtfeld(userType, feldKey)` in `app/helpers.php` — fehlender Eintrag defaultet zu `opt`. Betroffene Felder: Telefon, Straße+Hausnr., PLZ+Ort, Firma (bei cust UND mand). Registrierung (`CustRegisterController`, `SystemMandantController::handleRegister()`) fragt nur Pflichtfelder ab, optionale Felder werden im Formular komplett ausgeblendet. Konto-Bearbeiten (`CustSelfController`, `MandantSelfController::update()`) zeigt immer alle vier Felder, Pflicht-Status (required/Sternchen) dynamisch. Startkonfiguration entspricht dem bisherigen Verhalten (Telefon+Firma optional, Straße+PLZ/Ort Pflicht).

**DB-Migration:** `cust_tel`/`cust_street+nr`/`cust_postcode_city`/`cust_company` sowie die vier mand-Pendants in `userdb` per direktem SQL von `NOT NULL DEFAULT 'nicht vorhanden'` auf `NULL DEFAULT NULL` umgestellt, bestehende Platzhalterwerte per UPDATE auf echtes NULL migriert. Anzeige-Fallback bleibt überall `'nicht vorhanden'` bei NULL — für den Nutzer unverändert. `mand_uname`/`cust_uname` unverändert immer Pflicht.

**Bugfix `mandant/konto.blade.php`:** fehlende rote Pflicht-Sternchen bei `mand_uname`/`mand_firstname`/`mand_lastname`/`mand_street+nr`/`mand_postcode+city` ergänzt (im Gegensatz zu allen anderen Konto-Formularen im Projekt fehlten sie hier).

**syst sieht jetzt zusätzlich mand-Adresse:** `system/mandanten/show.blade.php` + `edit.blade.php` — Straße+Hausnr. und PLZ+Ort als neue dt/dd-Paare ergänzt (vorher nur Telefon/Firma sichtbar), Feldreihenfolge umgestellt auf Benutzername/E-Mail/Vorname/Nachname/Straße/PLZ+Ort/Telefon/Firma.

**Neu für mand — Detailseite je Mitglied:** Neue Route `GET /mandant/kunden/{id}` (`mandant.kunden.show`), neue Methode `MandantCustController::show()` (Sicherheitscheck pcode_id+mand_id, sonst `abort(404)`), neue View `mandant/cust/show.blade.php` (read-only, Stil analog `system/mandanten/show.blade.php`) — zeigt erstmals Telefon/Firma/Adresse eigener Mitglieder (vorher nirgends sichtbar). Mitgliederliste (`mandant/cust/index.blade.php`) umgebaut: E-Mail (Link zur Detailseite, farblich erkennbar ohne Hover) + `cust_uname` in erster Spalte/Zeile statt Alias+E-Mail; Alias-Bearbeitung von festem Input auf Anzeige-Text mit Bearbeiten/Speichern-Toggle-Button umgestellt.

Getestet: cust/mand Registrieren+Bearbeiten, syst-Anzeige, mand-Mitgliederliste (Toggle, Links, Sortierung/Suche) — alles bestätigt erfolgreich. Details PROJECT_CONTEXT.md Abschnitt 10j.

# 4f. Feature 26.08.2026 — Pflichtfelder-Erzwingung nach Login

**Tag `pflichtfelder_erzwingung_ok`, Commit `16e81f3`:** Neue Middleware `CheckPflichtfelder` (`bootstrap/app.php`, zwischen `CheckPolicyVersion` und `CheckWelcome`) prüft bei jedem cust/mand-Request per `istPflichtfeld()`, ob ein aktuell als Pflicht konfiguriertes Feld (Telefon/Straße/PLZ+Ort/Firma) `NULL` ist. Trifft das zu: Redirect zur Konto-Seite mit `withErrors()` (Feldmarkierung wie bei fehlgeschlagener Validierung) + Amber-Hinweisbox. Ausnahmen: Konto-Seite/Speichern-Route, Logout, Datenschutz-Routen. Ergebnis 60s pro User gecacht (`Cache::forget()` beim Speichern). mand bleibt nach dem Speichern auf der Konto-Seite; cust wird zur ursprünglich angeforderten Seite zurückgeleitet (Session-Key `pflichtfeld_redirect_target`, nur für cust gesetzt).

Zusätzlich: grauer „(optional)"-Hinweis bei Straße/PLZ+Ort ergänzt (hatten bisher nur das bedingte Sternchen, siehe Abschnitt 4e).

Details PROJECT_CONTEXT.md Abschnitt 10k.

# 4g. Bugfix 26.08.2026 — Policy-Version bei Registrierung

**Tag `registration_policy_version_fix_ok`, Commit `83c738e`:** `CustRegisterController::store()` und `SystemMandantController::handleRegister()` setzten `ds_version`/`upload_terms_version` bisher auf den statischen Config-Wert `config('datenschutz.version')` (`'1.0'`) statt auf die tatsächlich aktuelle, von `CheckPolicyVersion` geprüfte Version aus `userdb.policy_versions` — dadurch erschien bei jeder Neuregistrierung sofort beim ersten Login das „Policy geändert"-Popup, obwohl der Nutzer gerade erst zugestimmt hatte. Zusätzlich bekam mand `upload_terms_version` bisher fälschlich den `ds_version`-Wert. Fix: beide Felder lesen jetzt `PolicyVersion::get('ds_version')` bzw. `PolicyVersion::get('upload_version')`.

Details PROJECT_CONTEXT.md Abschnitt 10l.

# 4h. Feature 27.08.2026 — UI-/E-Mail-Texte ausgelagert + 2FA-Gültigkeitsdauer konfigurierbar

**Tag `ui_texte_ausgelagert_ok`, Commit `2dbeb37`:** Neuer Helper `uiText(bereich, dateiname, vars)` (`app/helpers.php`) liest `storage/app/private/ui-texte/{all,cust,mand,syst}/*.md` (nicht versioniert, analog `honeypot_paths.txt`/`pflichtfelder.txt`) und rendert per `CommonMarkConverter` zu HTML — 28 Textstellen ausgelagert (Login-/2FA-Hinweise, Konto-Löschen-/Galerie-entfernen-Warnungen, Upload-Bedingungen-Hinweis, E-Mail-ändern-Erklärung, Spam-Hinweis, Passkey-Prompts, Alias-Erklärung, Pflichtfeld-Nachtrag-Hinweis, Policy-Update-Hinweise, 7 E-Mail-Bodies), wortgleiche Duplikate (auch über cust/mand/syst hinweg) auf je eine gemeinsame Datei konsolidiert. `renderMarkdownVariant()` (bestehender Tag-Varianten-Mechanismus, z.B. Passkey-OS-Hinweise) um denselben `{{key}}`-Platzhalter-Mechanismus erweitert, für Variablen mitten im Satz (E-Mail-Bodies mit Code/Name/Frist). Alle Dateien vorübergehend mit rotem `[DEV]`-Marker als Test-Fortschrittsanzeige versehen.

**2FA-Gültigkeitsdauer zentralisiert:** war an zwei Stellen hartcodiert (`TwofaService::VALID_MINUTES=2`, `TwoFactorCodeMail` `'validMinutes'=>2`, DRY-Verstoß) und in den drei Rollen-Hinweistexten uneinheitlich (nur cust nannte die Dauer, syst-Text war noch gar nicht ausgelagert). Neue `config/twofa.php` + `TWOFA_CODE_VALID_MINUTES` in `.env` zentralisieren das; alle drei Rollen nutzen jetzt denselben Text (`all/a_log_2fa_hinweis.md`) mit dynamischem `{{validMinutes}}`.

**Zusätzlich, `.env`-only (nicht versioniert, kein Tag):**
1. Mail-Konfiguration auf echtes Postfach umgestellt — `MAIL_SCHEME=starttls` wurde von der installierten Symfony-Mailer-Version (8.0.8) nicht unterstützt und verursachte 500er-Fehler bei jedem Mailversand; zeigte zudem auf einen lokalen `127.0.0.1:1025`-Mailcatcher. Neues Postfach `noreply@martinwagner.de` bei Alfahosting, jetzt `MAIL_SCHEME=smtps`, `MAIL_HOST=host159.alfahosting-server.de`, `MAIL_PORT=465`. Getestet cust/mand/syst.
2. `BACKSTAGE_PATH` erstmals explizit gesetzt (individueller Pfad statt Code-Default `backstage`) — Sicherheitsgewinn, da der syst-Login-Pfad nicht mehr aus dem Code ableitbar ist. Frühere Referenzen auf „/backstage" sind ab sofort als Default-Pfad zu lesen.

Details PROJECT_CONTEXT.md Abschnitt 10m.

# 4i. Vorbereitung 28.–29.08.2026 — Fünfte Datenbank subscriptiondb (Abrechnungssystem-Grundlage)

**Vorbereitung einer Schnittstelle/Datenstruktur für ein späteres Abrechnungssystem für `mand` und `cust`.** Neue Datenbank `u14bc1w8_v08_subscriptiondb`, Laravel-Connection `subscriptiondb`, Base-Model `App\Models\SubscriptionDb\SubscriptionDbModel` (minimal, analog den vier bestehenden Base-Models: nur `$connection`).

**Fünf Tabellen per DDL angelegt** (inkl. FK-Constraints — bewusste Ausnahme vom Projektstandard, sonst nur ein einziger echter FK im gesamten Schema): `subscriber` (Vertragspartner, überdauert gelöschte Accounts), `plan` (versionierte Tarife), `subscription` (Vertrag je Nutzer, ein Subscriber kann mehrere Verträge zahlen), `ledger_entry` (unveränderliches Buchungsjournal, fünf Buchungsarten `FO`/`GG`/`ZE`/`ZG`/`ZA`, getrennte Spalten für Geldkonto und Vertragssaldo), `invoice` (Rechnung als Momentaufnahme je Vertrag). Kein Eingriff in bestehende Tabellen — Verknüpfung zu `mand_user`/`cust_user` ausschließlich logisch über `user_type`+`user_id`, analog `passkey`/`trusted_device`.

**Illustrierender Demo-Datensatz eingespielt:** Subscriber 30118 mit drei Verträgen (115, 170, 205), entspricht exakt dem Buchungsbeispiel aus Konzept-Abschnitt 4.2.

**Noch nicht gesetzt:** Trigger für die Unveränderbarkeit des Journals (`BEFORE UPDATE`/`BEFORE DELETE` auf `ledger_entry`).

**Anwendungslogik bewusst NICHT implementiert** — Konzept-Abschnitt 4 beschreibt die Buchungsabläufe (Umlage, FIFO-Ausgleich, Erstattung) als Erweiterungsoption für eine mögliche spätere kommerzielle Nutzung; keine Tabellen-Models/Controller/Views vorhanden.

**Nebenbei behoben:** Die bereits ausgeführte DDL hatte `entry_type` fälschlich mit `'LS'` statt `'ZA'` (Zahlungsausgang) angelegt (Bug, kein bewusster Namenswechsel) — korrigiert per `ALTER TABLE`+`UPDATE`, siehe Inkonsistenzen.md.

Vollständiges Konzept: `docs/Konzept_Abrechnungssystem.md`. Details PROJECT_CONTEXT.md Abschnitt 10n.

**Noch UNCOMMITTED — kein Tag, folgt separat.**

# 4j. Passkey-Gesamttest, iOS-Fixes, Mobile-UI-Überarbeitung, Galeristen-Löschschutz (31.08.–04.09.2026)

Vier Tags (`passkey_gpm_doppelregistrierung_fix_ok` bis `mand_deaktivierung_loeschschutz_ok`) im Rahmen des laufenden, noch nicht abgeschlossenen Passkey-Gesamttests (Abschnitt 6):

- **GPM-Doppelregistrierung verhindert** (`passkey_gpm_doppelregistrierung_fix_ok`) — AAGUID-Prüfung + `excludeCredentials` verhindert, dass derselbe Passkey beim Google Password Manager doppelt registriert wird.
- **Mobile Kartenansicht für „Meine Passkeys"** (`passkey_test_mobile_ui_ok`) — Desktop-Tabelle nur noch ab `md:` sichtbar, darunter Detailbereich + Tab-Liste für schmale Bildschirme.
- **iOS: Dismiss-Logik ignoriert browserspezifischen `ua_hash`** — Safari/Chrome/Firefox teilen sich auf iOS denselben iCloud-Schlüsselbund, daher wird „Nie wieder fragen" jetzt browserübergreifend respektiert statt pro Browser separat abgefragt. Zusätzlich: Passkey-Namensvorschlag zeigt bei iOS nur noch „iOS" ohne Browser-Suffix (z. B. „iOS" statt „iOS – Safari").
- **Samsung Internet Browser wird komplett blockiert** (`samsung_browser_blocked_ok`) — eigene Hinweisseite statt Login-Möglichkeit.
- **Mobile Kartenansicht für System-User- und Galeristen-Liste** — Desktop-Tabelle ab `md:`, darunter Detailbereich + Tab-Liste; Galeristen-Liste zusätzlich 30 % breiter (`max-w-6xl` statt `max-w-4xl`).
- **Eigenes Lösch-Bestätigungsmodal für Galeristen** — ersetzt natives `confirm()`, Name fett hervorgehoben, Warntext aus editierbarer UI-Text-Datei (`storage/app/private/ui-texte/syst/s_mand_delete_warnung.md`).
- **NEU — Zweistufiger Löschschutz für Galeristen-Konten** (`mand_deaktivierung_loeschschutz_ok`, Commit `929bbf3`) — Syst kann ein Galeristen-Konto deaktivieren statt sofort zu löschen: Galerist erhält eine E-Mail-Benachrichtigung, Zeitpunkt wird in `mand_user.mand_deactivated_at` gespeichert. Endgültiges Löschen ist erst nach Ablauf einer editierbaren Karenzzeit möglich (`.env`-Wert `MAND_DELETE_GRACE_DAYS`, Standard 7 Tage) und nur noch über die Bearbeiten-Seite (Löschen-Aktion wurde aus der Liste entfernt). Neues DB-Feld `mand_user.mand_deactivated_at` (datetime, nullable), neue Config `config/mand_deactivation.php`, neue Route+Methode `SystemMandantController::toggleActive()`, neue Mailables `MandDeactivatedMail`/`MandActivatedMail`.

**Passkey-Gesamttest weiterhin nicht abschließend spezifiziert:** Die obigen Punkte stellen erheblichen Fortschritt dar, decken aber nicht nachweislich den vollständigen in Abschnitt 6 beschriebenen Testumfang (alle Rollen × Geräte × Browser × Grenzfälle) ab. Siehe Nächster-Schritt-Banner oben und Abschnitt 6.

# 5. Datenbankstand (04.09.2026)

| **DB** | **Änderungen seit 19.07. (#12/#16)** |
| --- | --- |
| userdb | `mand_user.mand_deactivated_at` (datetime, nullable) — NEU 04.09., Basis für die Karenzzeit-Berechnung vor endgültiger Galeristen-Löschung (siehe Abschnitt 4j) |
| sessiondb | `share_link` — NEU 01.08., siehe Abschnitt 4b (7-stelliger UNIQUE-Code je `mand_id`+`sec_level` für anon-Kurzcode-Login). `trusted_device` (siehe Abschnitt 3.1) unverändert vorhanden |
| subscriptiondb | **NEU 28.–29.08. (Vorbereitung, siehe Abschnitt 4i):** komplett neue, fünfte Datenbank mit 5 Tabellen (`subscriber`, `plan`, `subscription`, `ledger_entry`, `invoice`) — Grundlage für ein späteres Abrechnungssystem, Anwendungslogik noch nicht implementiert |
| fotodb | Unverändert |
| fotoblobdb | Unverändert |

# 6. Offene Punkte

| **Priorität** | **Punkt** | **Detail** |
| --- | --- | --- |
| **Höchste (nächster Schritt)** | Gründlicher Passkey-Gesamttest | Phase 6 ist technisch implementiert, aber noch nicht systematisch getestet. Umfasst Registrierung, Login, Umbenennen, Löschen, Prompt-/Dismiss-Logik für mand UND cust über Windows/Android/iOS und die relevanten Browser — **kein reiner iOS-Test**. Für iOS zusätzlich zu beachten: kein Commit mit abgeschlossenem WebAuthn-Passkey-Test gefunden (bisherige iOS-Tests betrafen Button-Feedback/Auto-Login, nicht Passkeys) (erheblicher Fortschritt 31.08.-04.09., siehe Abschnitt 4j — verbleibender Umfang noch nicht abschließend spezifiziert) |
| **Hoch** | syst-Passwort-Policy + Honeypot-Infrastruktur committen | Aktuell uncommitted (Abschnitt 4a). Zusätzlich `HONEYPOT_LOCKOUT_MINUTES` und `LOG_STACK=daily` auf Server-`.env` nachtragen |
| **Hoch** | dirty-Ausblendung nachziehen | `system/mandanten/index.blade.php` + `customer/auth/register.blade.php` |
| **Hoch** | Regressionstest Android/Windows | Globale Button-Animation auf Desktop/Android prüfen |
| **Hoch** | Abnahmetest cust-Bereich | Blöcke 1–6; Tag: `cust_complete_ok`. Testplan liegt vor (docx + xlsx), pausiert |
| **Hoch** | `.env`-Duplikat `TRUSTED_DEVICE_DAYS` bereinigen | Zeile 17 (`=1`) vs. Zeile 97 (`=7`) — erste gewinnt, zweite ist wirkungslos |
| **Hoch** | Phase 7: mand-Content | ActivityGroup/Subgroup-Controller, Upload-Flow (`/mandant/upload/*`), AG/ASG-CRUD |
| **Mittel** | Phase 7: Cust-UI | Mandanten-Content-Seite, sec_level-Filter, mand_profile-Anzeige — NACH mand-Content |
| **Mittel** | SPF/DKIM Mailserver | E-Mail-Änderungsmails landen im Spam; aktuell nur UI-Hinweis |
| **Mittel** | Passkey-Link in Willkommensseite | Noch nicht umgesetzt |
| **Mittel** | Trusted-Device-Gültigkeit 1→7 Tage | Aktuell bewusst auf 1 Tag für Testbetrieb |
| **Niedrig** | E-Mail-Footer Sie-Form | `two-factor-code.blade.php`, `trusted-device-added.blade.php`, `cust-invite.blade.php` — Footer „Bitte antworten Sie..." trotz Du-Form im Mailtext |
| **Niedrig** | iOS Apple Mail Button-Text markierbar | Akzeptierte Einschränkung, nicht per CSS lösbar |
| **Niedrig** | ModerationMail | Erst nach Content-Upload relevant |
| **Niedrig** | Tote Datei löschen | nur `welcome.blade.php` (Breeze-Default) |
| **Plan** | Newsletter | Eigene DB-Tabelle, kein Code derzeit |

# 7. Git-Tags (neu seit #12, Passkey-Gesamttest weiterhin ausstehend)

| **Tag** | **Inhalt** |
| --- | --- |
| `stable_2026-06-30_logins_ok` | Abschluss Bugfixes 29.06. (syst-Löschlogik, MandAccountDeletedMail, deutsche PW-Meldungen) |
| `ios_longtap_fix_ok` / `ios_longtap_dashboard_ok` / `ios_longtap_policy_ok` / **`ios_longtap_complete_ok`** | iOS-Long-Tap-Fix (21 Zurück-Links, 13 Dashboard-Kacheln, Policy-Popup-Links) |
| `cust_ds_hinweis_ok` | Upload-Bedingungen-Popup für cust entfernt |
| `trusted_device_cust_ok`, `trusted_device_2fa_skip_complete_ok`, `trusted_device_config_ok`, `trusted_device_config_2FA_ok`, `trusted_device_logout_revoke_ok` | Trusted-Device-Feature (2FA-Skip-Stufe) |
| `autologin_pre_live_test`, **`autologin_complete_ok`** | Ausbau zu vollständigem Auto-Login ohne Passwort |
| `session_messages_removed_ok` | „Sitzung abgelaufen"-Meldungen entfernt, `/backstage`-Redirect-Fix |
| `session_usertype_fix_ok` | sessiondb.session: verwaiste Sessions + user_type-Bug behoben |
| `error_messages_dirty_fix_ok` | Fehlermeldungen dirty-ausgeblendet + Error-Bag-Isolation (10 Stellen) |
| `login_cleanup_expired_records_ok` | Login-Cleanup erweitert: invite, cust_invite, trusted_device, twofa_code |
| `passkey_hints_markdown_ok` | Passkey-Hinweistexte in editierbare md-Dateien ausgelagert |
| `invite_placeholder_texts_ok` | Platzhaltertexte Einladungsformulare präzisiert |
| `mand_2fa_optin_login_fix_ok` | mand-2FA-Deaktivierung überarbeitet, Trusted-Device-Cookie auch im 2FA-Bypass |
| `mitglied_label_und_inkonsistenz12_ok` | Seitenkopf-Label MITGLIED, Inkonsistenz #12 (passkey.sign_count) |
| `login_lockout_ip_based_ok` | Einheitliche IP-basierte Login-Sperre (5/5min, cust+mand+syst) |
| `backstage_path_configurable_ok` | syst-Login-Pfad über `.env` konfigurierbar (BACKSTAGE_PATH) |
| `honeypot_login_attacks_log_ok` | Log-Kanal `login_attacks` + dynamische Honeypot-Routen |
| `anon_share_link_shortcode_ok` | anon-Login per teilbarem 7-stelligem Kurzcode-Link (`sessiondb.share_link`), Pro-Stufe-Invalidierung, Datenschutz-Popup für anon entfernt |
| `trusted_device_cust_nofactor_fix_ok` | cust: Trusted-Device-Cookie im Nicht-2FA-Login-Pfad nachgezogen (`issueTrustedDeviceIfRequested()`, analog mand) |
| `trusted_device_passkey_ok` | Trusted-Device-Cookie auch im Passkey-Login-Pfad wirksam (cust+mand), Checkbox-Zustand jetzt per JS aus dem DOM gelesen |
| `logout_dialog_close_window_ok` | Logout-Dialog-Button „Zurück" zu „Fenster schließen", `window.close()`-Versuch mit Dialog-Fallback |
| `pflichtfelder_konfiguration_ok` | Konfigurierbare Pflichtfelder mand/cust (Telefon/Straße/PLZ+Ort/Firma) via `pflichtfelder.txt`+`istPflichtfeld()`, DB-Migration auf NULL, neue mand-Detailseite je Mitglied (`mandant.kunden.show`), umgebaute Mitgliederliste, syst-Adress-Sichtbarkeit, Pflicht-Sternchen-Bugfix |
| `pflichtfelder_erzwingung_ok` | Neue Middleware `CheckPflichtfelder` erzwingt fehlende, neu zur Pflicht gewordene Felder nach Login; „(optional)"-Hinweis bei Straße/PLZ+Ort ergänzt |
| `registration_policy_version_fix_ok` | Bugfix: `ds_version`/`upload_terms_version` bei Registrierung lesen jetzt `PolicyVersion::get()` statt statischem Config-Wert |
| `ui_texte_ausgelagert_ok` | 28 UI-/E-Mail-Textstellen nach editierbaren Markdown-Dateien ausgelagert (`uiText()`, `storage/app/private/ui-texte/`), 2FA-Gültigkeitsdauer über `config/twofa.php` zentralisiert, `.env`-only: Mail-Konfiguration korrigiert (smtps/Alfahosting) + `BACKSTAGE_PATH` gesetzt |
| `subscriptiondb_vorbereitet_ok` | Vorbereitung fünfte Datenbank `subscriptiondb` (5 Tabellen: subscriber/plan/subscription/ledger_entry/invoice), Grundlage für ein späteres Abrechnungssystem, Anwendungslogik noch nicht implementiert |
| `passkey_gpm_doppelregistrierung_fix_ok` | AAGUID-Prüfung + `excludeCredentials` verhindert Doppelregistrierung desselben Passkeys bei Google Password Manager |
| `passkey_test_mobile_ui_ok` | Mobile Kartenansicht für „Meine Passkeys" (Desktop-Tabelle ab `md:`, Detailbereich + Tab-Liste) |
| `samsung_browser_blocked_ok` | Samsung Internet Browser wird komplett blockiert (eigene Hinweisseite statt Login) |
| **`mand_deaktivierung_loeschschutz_ok`** | Zweistufiger Löschschutz für Galeristen-Konten: Deaktivieren mit E-Mail-Benachrichtigung + Karenzzeit (`mand_user.mand_deactivated_at`, `config/mand_deactivation.php`), endgültiges Löschen erst danach über die Bearbeiten-Seite (aktueller Stand) |

Alle früheren Tags siehe Projektstatus #12 / PROJECT_CONTEXT Abschnitt 13. **Noch uncommitted, kein Tag:** syst-Passwort-Policy-Verschärfung (min:20) + Login-Hard-Block, Doku-Dateien dieser Aktualisierung.

Fotosite V08 — Projektstatus #17  |  Stand 04.09.2026
