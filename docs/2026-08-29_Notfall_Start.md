**Fotosite V08**

**Notfall-Startdokument**

*Stand: 29. August 2026  |  Letzter Git-Tag: ui_texte_ausgelagert_ok (29.08.-Ergänzung unten uncommitted)*

**🏁 MEILENSTEIN: Benutzer-/Sicherheitsverwaltung implementiert. Tag user_management_complete_ok ist sicherer Rückfallpunkt und als Startimplementierung für künftige Projekte geeignet (siehe Abschnitt 9). EINSCHRAENKUNG: Die Passkey-Funktionalität (Phase 6) ist technisch implementiert, aber noch NICHT gruendlich getestet — siehe naechster Schritt unten und Abschnitt 7.**

**⚠  Wenn ein Claude-Chat verloren geht, gibt dieses Dokument dem naechsten Chat alle nötigen Informationen für einen sofortigen Neustart.**

**⚠  KORREKTUR 29.06. (weiterhin gültig): system/login.blade.php ist NICHT tot, sondern die AKTIVE syst-Login-View (SystemLoginController@login rendert view('system.login'), Login + 2FA via show_2fa-Flag). Frühere Einstufung als tote Datei aufgehoben. Einzige echte tote Datei: resources/views/welcome.blade.php (Breeze-Default).**

**⚠  NEU 09.–19.07.: iOS-Long-Tap-Fix abgeschlossen (ios_longtap_complete_ok), Upload-Bedingungen-Popup fuer cust entfernt (cust_ds_hinweis_ok), Trusted-Device-Feature zu vollstaendigem Auto-Login ohne Passwort ausgebaut (autologin_complete_ok), "Sitzung abgelaufen"-Meldungen entfernt + /backstage-Redirect-Bug behoben (session_messages_removed_ok), zwei sessiondb.session-Bugs behoben: verwaiste Sessions + dauerhaft falscher user_type (session_usertype_fix_ok). Details Abschnitt 5.2e.**

**⚠  NEU 29.–31.07.: Umfangreiche Sicherheits-Haertung des Logins. Einheitliche IP-basierte Login-Sperre fuer cust/mand/syst (login_lockout_ip_based_ok, mand+syst hatten vorher GAR KEINE Drosselung), dynamische Honeypot-Routen + Log-Kanal login_attacks (honeypot_login_attacks_log_ok), syst-Login-Pfad ueber .env konfigurierbar (backstage_path_configurable_ok), mand-2FA jetzt tatsaechlich deaktivierbar (mand_2fa_optin_login_fix_ok), Passkey-Hinweistexte in editierbare md-Dateien ausgelagert (passkey_hints_markdown_ok). ZUSAETZLICH, noch UNCOMMITTED: syst-Passwort-Policy auf min. 20 Zeichen + Komplexitaet verschaerft, inkl. Hard-Block beim Login bei altem Passwort (KEIN Self-Service-Reset fuer syst — Notfall-Prozedere siehe Abschnitt 6a). Details Abschnitt 5.2f.**

**⚠  KORREKTUR 19.07.: Der anon-Kurzzeit-Kennwort-Login nutzt KEINE Ausnahme mehr bei regenerate() — CustLoginController::handleAnonLogin() wurde ebenfalls auf regenerate(true) umgestellt. Alle 7 Session-Uebergaenge (cust: Passwort/2FA/Passkey/anon; mand: Passwort/Passkey; syst: 2FA) verhalten sich identisch.**

**⚠  KORREKTUR 19.07. (wichtig): Phase 6 (Passkey) ist NICHT "fertig"/"abgeschlossen", auch wenn fruehere Doku-Stellen das so darstellten. Korrekter Status: Passkey-Funktionalität ist technisch VOLLSTAENDIG IMPLEMENTIERT, aber noch NICHT gruendlich getestet. Der gruendliche Test der GESAMTEN Passkey-Funktionalität (nicht nur iOS!) ist der NAECHSTE SCHRITT fuer den neuen Chat. Siehe Abschnitt 5.2 und Abschnitt 7.**

**⚠  NEU 01.08.: anon-Login jetzt zusaetzlich per teilbarem 7-stelligem Kurzcode-Link moeglich (anon_share_link_shortcode_ok, Commit 15e21bd) — neue Tabelle sessiondb.share_link (code, mand_id, sec_level), Route GET /s/{code} (routes/web.php), praezise Pro-Stufe-Invalidierung in MandantPwListController::update() (Alt/Neu-Vergleich vor dem Speichern). Bisheriger langer, verschluesselter Token-Weg (loginViaShareLink()) vollstaendig ersetzt. Im selben Zug: Datenschutz-Hinweis-Popup fuer anon (beide Zugangswege) aus customer/content.blade.php entfernt — Hinweis kuenftig ueber Content-Seiten selbst geplant (Phase 7, noch offen). Details Abschnitt 5.2g.**

**⚠  NEU 03.08. (Bugfix): cust-Trusted-Device-Cookie im Nicht-2FA-Login-Pfad wurde bisher NIE ausgestellt — remember_device-Checkbox in CustLoginController::handleLogin() wurde ausgelesen, aber nie ausgewertet (trusted_device_cust_nofactor_fix_ok, Commit ddf5e55). Bei mand war der aequivalente Pfad bereits korrekt (siehe 29.07.-Eintrag oben, mand_2fa_optin_login_fix_ok) — nur cust war betroffen. Fix: neue Methode CustLoginController::issueTrustedDeviceIfRequested(), analog MandantLoginController. Details Abschnitt 5.2h.**

**⚠  NEU 04.08. (zwei kleine Fixes): (1) Trusted-Device-Cookie jetzt auch im Passkey-Login-Pfad wirksam (cust+mand) — der Passkey-Button liegt ausserhalb des Passwort-Forms, JS sendete die remember_device-Checkbox bisher nie mit; jetzt per DOM-Read ergaenzt, issueTrustedDeviceIfRequested() in beiden passkeyLogin()-Methoden aufgerufen (Typehint auf RedirectResponse|JsonResponse gelockert). Tag trusted_device_passkey_ok, Commit 268c2b7. (2) Logout-Dialog-Button "Zurueck" zu "Fenster schliessen" umbenannt, zusaetzlicher window.close()-Versuch, Dialog-Fallback bleibt erhalten (loest den seit 09.-19.07. offenen Punkt). Tag logout_dialog_close_window_ok, Commit 859516d. Details Abschnitt 5.2i.**

**⚠  NEU 26.08.: Konfigurierbare Pflichtfelder fuer mand/cust eingefuehrt (Telefon/Strasse+Hausnr./PLZ+Ort/Firma) — neue Datei storage/app/private/pflichtfelder.txt + Helper istPflichtfeld() (pflichtfelder_konfiguration_ok, Commit eef2eed). Registrierung fragt nur Pflichtfelder ab, Konto-Bearbeiten zeigt immer alle Felder dynamisch. Begleitende DB-Migration: cust_tel/cust_street+nr/cust_postcode_city/cust_company + vier mand-Pendants in userdb auf NULL DEFAULT NULL umgestellt (vorher NOT NULL DEFAULT 'nicht vorhanden'), Anzeige-Fallback unveraendert. Ausserdem: mandant/konto.blade.php Pflicht-Sternchen-Bugfix, syst sieht jetzt zusaetzlich mand-Strasse/PLZ+Ort, neue mand-Detailseite je Mitglied (mandant.kunden.show, zeigt erstmals Telefon/Firma/Adresse eigener Mitglieder) + umgebaute Mitgliederliste. Details Abschnitt 5.2j.**

**⚠  NEU 26.08. (2 weitere Aenderungen am selben Tag): (1) Pflichtfelder-Erzwingung nach Login — neue Middleware CheckPflichtfelder erzwingt jetzt aktiv nachtraeglich zur Pflicht gewordene, aber noch leere Felder (Redirect zur Konto-Seite mit Amber-Hinweisbox, 60s-Cache), Tag pflichtfelder_erzwingung_ok, Commit 16e81f3. (2) Bugfix Policy-Version bei Registrierung — ds_version/upload_terms_version lasen bisher einen statischen Config-Wert statt der tatsaechlich aktuellen policy_versions-Version, wodurch das "Policy geaendert"-Popup faelschlich direkt nach der Registrierung erschien, Tag registration_policy_version_fix_ok, Commit 83c738e. Details Abschnitt 5.2k/5.2l.**

**⚠  NEU 27.08.: UI-/E-Mail-Texte nach editierbaren Markdown-Dateien ausgelagert (28 Textstellen, storage/app/private/ui-texte/{all,cust,mand,syst}/*.md, nicht versioniert) — neuer Helper uiText(), renderMarkdownVariant() um Platzhalter-Mechanismus erweitert. 2FA-Code-Gueltigkeitsdauer war an zwei Stellen hartcodiert, jetzt zentral in config/twofa.php (TWOFA_CODE_VALID_MINUTES). Tag ui_texte_ausgelagert_ok, Commit 2dbeb37. ZUSAETZLICH, .env-only (nicht versioniert): Mail-Konfiguration auf echtes Postfach umgestellt (vorheriges MAIL_SCHEME=starttls von Symfony Mailer 8.x NICHT unterstuetzt, verursachte 500er bei jedem Mailversand) — siehe aktualisierten Steckbrief Abschnitt 1. BACKSTAGE_PATH jetzt erstmals explizit in der Server-.env gesetzt (individueller Pfad statt Code-Default 'backstage') — WICHTIG: fruehere Referenzen auf "/backstage" in diesem Dokument sind ab sofort veraltet, siehe Steckbrief Abschnitt 1 fuer den Hinweis zum aktuellen Wert. Details Abschnitt 5.2m.**

**⚠  NEU 28.–29.08.: Fünfte Datenbank subscriptiondb vorbereitet (Grundlage für ein späteres Abrechnungssystem für mand+cust, noch keine Anwendungslogik) — DDL für 5 Tabellen ausgeführt (subscriber, plan, subscription, ledger_entry, invoice), Connection subscriptiondb + Base-Model SubscriptionDbModel angelegt, Demo-Datensatz eingespielt (Subscriber 30118, 3 Verträge). ACHTUNG: DB-Passwort aktuell schwach (Testphase) — vor Produktivnutzung ersetzen. Trigger für Journal-Unveränderbarkeit noch NICHT gesetzt. Noch UNCOMMITTED, kein Tag. Details Abschnitt 5.2n, vollständiges Konzept: docs/Konzept_Abrechnungssystem.md.**

**Neuen Chat starten:**

- Neuen Chat im Projekt "Fotosite V08" in Claude.ai öffnen

- Dieses Dokument hochladen

- Einleitungssatz: "Lies PROJECT_CONTEXT.md und dieses Notfall-Startdokument. Wir machen weiter mit [Aufgabe]."

# 1. Projektsteckbrief

| **Eigenschaft** | **Wert** |
| --- | --- |
| Projektname | Fotosite V08 |
| Art | Multi-Tenant-Fotogalerie (Hobbyprojekt, Einzelentwickler Martin Wagner / Willy) |
| Framework | Laravel 13 / PHP 8.5 / Blade / Alpine.js / Tailwind CSS |
| Datenbank | MariaDB - 5 separate DBs (userdb, sessiondb, fotodb, fotoblobdb, subscriptiondb). subscriptiondb NEU 28.–29.08. — Abrechnungssystem-Vorbereitung, noch keine Anwendungslogik. **⚠ Aktuell schwaches Passwort (Testphase) — vor produktiver Nutzung ersetzen** (siehe Abschnitt 5.2n) |
| Passkeys | web-auth/webauthn-lib 5.3.5 (direkt - NICHT laragear, NICHT laravel/passkeys) |
| Deployment | FTP (WinSCP) auf Alfahosting - fotos.martinwagner.de |
| Git-Repo | github.com/fotosite/fotosite (privat) |
| Aktiver Branch | feature/passkey-infra |
| Lokaler Pfad | D:\mwa\Projekte\fotosite\Fotosite_V08\claudescode\fotosite |
| Letzter Git-Tag | ui_texte_ausgelagert_ok (27.08.2026). Meilenstein: user_management_complete_ok (20.06.) |
| Server-Pfad | /var/www/vhosts/u14bc1w8.host159.alfahosting-server.de/fotos.martinwagner.de/ |
| SSH | PuTTY, User u14bc1w8 |
| Mail | host159.alfahosting-server.de:465, MAIL_SCHEME=smtps (SSL/TLS), Postfach noreply@martinwagner.de bei Alfahosting. Passwort NICHT in diesem Dokument — liegt in der Server-.env (MAIL_PASSWORD) und/oder im Alfahosting-Postfach-Admin. **Korrektur 27.08.:** vorherige Angabe (Port 587, tls) war veraltet/falsch — tatsaechlich stand in .env bis dahin ein lokaler Mailcatcher (127.0.0.1:1025, MAIL_SCHEME=starttls), der zudem von der installierten Symfony-Mailer-Version nicht unterstuetzt wurde (500er bei jedem Mailversand) |
| syst-Login-Pfad (BACKSTAGE_PATH) | `eltorcal/camshaft` — seit 27.08. explizit in der Server-.env gesetzt (individueller Pfad statt Code-Default `backstage`). Aufruf: https://fotos.martinwagner.de/eltorcal/camshaft |

| **Rolle** | **UI-Begriff** | **Bereich** | **Login** |
| --- | --- | --- | --- |
| syst | System-Admin | /system | Passwort + 2FA per E-Mail |
| mand | Galerist:in | /mandant | Passwort + 2FA + Passkey |
| cust | Mitglied | /customer | Passwort + opt. 2FA + Passkey |
| anon | Gast | /customer | Kurzzeit-Passwort aus pw_list |

# 2. Kommunikations- und Arbeitsstandards

**⚠  Code, Befehle, Prompts IMMER in Kasten mit Label (Claude Code / PowerShell / bash / SQL). PowerShell: ; statt ****&****&****.**

| **Tool** | **Verwendung** |
| --- | --- |
| Claude.ai Chat | Planung, Specs, Prompts, Diagnose |
| Claude Code (lokales CLI) | ALLE Datei-Erstellungen und -Aenderungen |

**⚠  Abkuerzung "c-code":** In Chat-Nachrichten mit Claude wird "c-code" als Kurzform fuer "Claude Code" (das CLI-Tool, siehe Tabelle oben) verwendet. Diese Abkuerzung ist so in Chat-Nachrichten gebraeuchlich und braucht keine Rueckfrage.

**Claude Code Prompt einlesen:**

Read the file C:\Users\Street Hunter\Downloads\2026-06-20_mein_prompt.md and execute all instructions in it exactly as written.

**⚠  Nach JEDER Aenderung an Controllern, Routen, Config IMMER alle 4 Cache-Befehle ausfuehren (Hash-Korruption bestätigt 18.06.)**

php artisan route:clear ; php artisan config:clear ; php artisan view:clear ; php artisan cache:clear

**⚠  NEU 20.06.: Nach JEDER Blade-Aenderung mit neuen Tailwind-Klassen IMMER npm run build lokal ausfuehren, dann public/build/ komplett per FTP hochladen. Sonst sind neue Klassen wirkungslos — mehrfach (20.06.) faelschlich als Code-Bug diagnostiziert.**

npm run build

**⚠  Nach Claude-Code-Fix: Server-Datei per grep gegen lokale pruefen, bevor weiter diagnostiziert wird (Datei war mehrfach nie per FTP hochgeladen).**

**⚠  Nie von ****'****Datei liegt schon auf Server****'**** ausgehen ohne Verifikation — willkommen_*.md fehlte trotz Annahme (20.06.).**

# 3. Architekturregeln (NIEMALS brechen)

| **Regel** | **Detail** |
| --- | --- |
| Keine Standard-id-PKs | Alle PKs custom: mand_id, cust_id, fo_id, sess_id etc. Immer $primaryKey explizit setzen. |
| Keine Migrations | DDL direkt per SSH/phpMyAdmin. database/migrations/ nur Breeze-Reste. |
| public $timestamps = false | Auf ALLEN Domain-Models zwingend. |
| Base-Model-Pattern | UserDbModel, SessionDbModel, FotoDbModel, FotoBlobDbModel - alle Domain-Models erben davon. |
| Keine Cross-DB-Joins | 4 DB-Credentials erzwingen Trennung. Verknuepfung in PHP ueber mand_id/cust_id. |
| Echte FKs | activity_subgroup.ag_id -> activity_group.ag_id (fotodb) sowie cust_pcode.mand_id -> mand_user.mand_id (userdb). Alle anderen: logisch, kein FK-Constraint. |
| Docblock-Header | Jede PHP-Datei: Dateiname, Version, Autor, Datum, Zweck, DB-Zugriffe. Version bei Aenderung hochzaehlen. |
| Implementierter Code ist massgeblich | Bei Widerspruch Code gegen Doku: Code gewinnt. |
| BIGINT UNSIGNED NOT NULL | Alle FK-Spalten - Typ-Mismatch bricht FK-Constraints lautlos. |
| AES pw_list | pw1-pw6: Laravel encrypt/decrypt (NICHT Hash) - mand muss Klartext sehen koennen. |
| abort(403) nicht im Log | Bei 403 ohne Logeintrag: zuerst Controller auf abort(403) pruefen. |
| sec_code != sec_level | sec_code = konzeptioneller Anon-Zugangscode (mand-spezifisch), real abgebildet über userdb.cust_pcode.cust_passcode. sec_level = TINYINT UNSIGNED 0-6 in fotodb (*_sec_level). cust-sec_level pro mand = userdb.cust_pcode.cust_passcode; Session = sessiondb.session.cust_passcode; Einladung = sessiondb.cust_invite.sec_level. |
| x-data noetig fuer Alpine-Direktiven | Jedes Element mit @input/x-show/etc. braucht x-data-Vorfahre im DOM-Baum, sonst binden Direktiven nie. |
| @json() nur in einfachen Anfuehrungszeichen | x-data='...@json()...' — niemals doppelte Anfuehrungszeichen um das Attribut. |

# 4. Datenbankuebersicht (Stand 19.07.2026)

## userdb (u14bc1w8_v08_userdb)

| **Tabelle** | **PK** | **Wichtige Felder** |
| --- | --- | --- |
| syst_user | syst_id | System-Admin-Accounts. is_primary TINYINT(1) Default 0: Primäre Admins können nicht gelöscht werden (weder von primary noch non-primary). Ein primary löscht non-primaries (nicht andere primaries, nicht sich selbst). Non-primary löscht NIEMANDEN. Session-Key _is_primary (bool). View-Bedingung Löschen-Button: @if(session('_is_primary') && ! $user->is_primary && $user->syst_id !== session('_syst_id')) — korrigiert 29.06. |
| mand_user | mand_id | mand_pw_hash, mand_cust_2fa, ds_accepted_at, ds_version, upload_terms_accepted_at, upload_terms_version, mand_street+nr, mand_postcode+city, mand_uname, mand_tel, mand_company, show_welcome (NEU 20.06.) |
| cust_user | cust_id | cust_pw_hash, ds_accepted_at, ds_version, upload_terms_accepted_at, upload_terms_version, cust_street+nr, cust_postcode_city, cust_uname, cust_tel, cust_company, show_welcome (NEU 20.06.) |
| cust_pcode | pcode_id | cust_passcode varchar(255) = sec_level des cust bei diesem mand ('enthält die Ziffer des Securitylevel'), cust_alias, pcode_prefstat, mand_id, cust_id - je Mitglied+Mandant. cust_alias = mand's privater Merkname, NICHT cust-sichtbar |
| invite | inv_id | inv_type: register│pw_reset│email_change; inv_user_type: syst│mand│cust; inv_email bei email_change = neue Adresse; is_primary TINYINT(1) Default 0 (nur für syst-Einladungen relevant). Login-Cleanup NEU 29.07.: abgelaufene Einträge werden bei jedem cust/mand-Login mitbereinigt |
| passkey | pk_id | user_type, user_id, credential_id, public_key, sign_count, device_name. HINWEIS: sign_count faktisch wirkungslos (nie ausgelesen/geprüft), siehe Inkonsistenzen.md #12 |
| passkey_dismissed | pd_id | user_type, user_id, os (win│andr│ios), ua_hash |
| policy_versions | pv_key | pv_key: ds_version│upload_version, pv_value, updated_at - syst erhoelt per UI, triggert Popup bei mand/cust |
| cust_invite (RELIKT) | invite_id | NICHT verwenden - sessiondb.cust_invite ist fuehrend |

**⚠  HINWEIS Feldnamen-Inkonsistenz (unkritisch, getrennte Tabellen): cust_user nutzt ****'****cust_postcode_city****'**** (Unterstrich), mand_user nutzt ****'****mand_postcode+city****'**** (Pluszeichen). Bewusst nicht vereinheitlicht.**

## sessiondb (u14bc1w8_v08_sessiondb)

| **Tabelle** | **PK** | **Zweck** |
| --- | --- | --- |
| session | sess_id | Custom Session-Driver. sess_token fuer Lookups, payload (JSON). Jeder Besucher (auch anon). Spalten user_type/cust_id/mand_id/syst_id werden erst NACH dem finalen write() per App::terminating()-Callback nachtraeglich befuellt (behoben 19.07., session_usertype_fix_ok). |
| pw_list | pwlist_id | pw1-pw6 (AES-verschluesselt), valid_from, valid_until - Kurzzeit-Kennwoerter je Mandant |
| twofa_code | tfa_id | 6-stelliger Code, tfa_purpose (login│pw_change│critical), tfa_expires_at, tfa_used. Login-Cleanup NEU 29.07.: abgelaufene Codes werden bei jedem cust/mand-Login mitbereinigt |
| cust_invite | invite_id | FUEHREND: mand_id, cust_email, cust_alias, sec_level, token, expires_at, used. Login-Cleanup NEU 29.07.: abgelaufene Einladungen werden bei jedem cust/mand-Login mitbereinigt |
| trusted_device | td_id | NEU 10.-17.07.: "Geraet als sicher merken" fuer vollstaendigen Auto-Login (mand+cust). user_type, user_id, token_hash (SHA-256, UNIQUE), ua_hash, device_label, last_used_at, expires_at, created_at. Bewusst in sessiondb statt userdb. Login-Cleanup NEU 29.07.: abgelaufene Eintraege werden jetzt zusaetzlich bei jedem cust/mand-Login mitbereinigt (bisher nur bei Logout) |
| share_link | sl_id | NEU 01.08.: Kurzcode-basiertes anon-Login-Link-System. code (varchar(10), UNIQUE, 7-stellig alphanumerisch), mand_id, sec_level, created_at. UNIQUE-Index auf mand_id+sec_level - pro mand+Stufe ein stabiler Code (firstOrCreate() in MandantPwListController::edit()). Invalidierung nur bei tatsaechlicher Passwort-Aenderung dieser Stufe |

## fotodb + fotoblobdb (unveraendert seit 16.06.)

| **Tabelle** | **PK** | **Felder** |
| --- | --- | --- |
| foto_obj (fotodb) | fo_id | fo_filename, mand_id, fo_sec_level TINYINT, fo_is_video bool, fo_datetime, db_saved, fo_filepath |
| activity_group | ag_id | ag_title, mand_id, ag_sec_level TINYINT, ag_prefstat, ag_sort_date |
| activity_subgroup | asg_id | FK -> activity_group.ag_id (einzige echte FK!), asg_sec_level TINYINT, asg_public |
| mand_profile | mp_id | mp_name, mp_title varchar(255), mp_text text, mp_title_start, mp_subtitle_start |
| ag_fo_context / asg_fo_context / mp_fo_context | - | Pivot-Tabellen AG/ASG/Profil <-> Foto (ag_is_banner, ags_is_banner) |
| foto_obj (fotoblobdb) | fod_id | fod_obj BLOB fuer Sicherheitsstufe 6 - vorlaeufig Dummy |

# 5. Aktueller Projektstand (31.07.2026)

## 5.1 Phasen

| **Phase/Bereich** | **Status** | **Letzter Tag** |
| --- | --- | --- |
| Phase 1-4: Fundament, Login, Einladungen | **Fertig** | p4_complete_ok |
| Phase 5: cust-Login | **Fertig** | phase5_cust_login_ok |
| Phase 6: Passkey-Infrastruktur | **Implementiert, gruendlicher Test steht noch aus*** | p6_passkey_ui_ok |
| Admin/Auth 16.-18.06. | **Fertig** | policy_popup_ok |
| Admin/Auth 19.-20.06. | **Fertig** | user_management_complete_ok |
| Bugfixes + Features 21.-22.06. | **Fertig** | syst_primary_ok |
| Bugfixes + Mobile 23.06. | **Fertig** | fixes_23jun_ok, touch_and_trim_ok, pw_eye_ok |
| iOS/Android-Button-Animation 26.-29.06. | **Fertig** | ios_button_feedback_ok, stable_2026-06-30_logins_ok |
| iOS-Long-Tap-Fix 09.-10.07. | **Fertig** | ios_longtap_complete_ok |
| Upload-Bedingungen-Popup cust entfernt 10.07. | **Fertig** | cust_ds_hinweis_ok |
| Trusted-Device / vollstaendiger Auto-Login 10.-18.07. | **Fertig** | autologin_complete_ok |
| Session-Bugfixes (Meldungen entfernt, verwaiste Sessions, user_type) 18.-19.07. | **Fertig** | session_messages_removed_ok, session_usertype_fix_ok |
| Sicherheits-Haertung Login (IP-Sperre, Honeypot, Log-Kanaele, Cleanup, mand-2FA-Opt-out, BACKSTAGE_PATH) 29.-31.07. | **Fertig** (syst-PW-Policy+Login-Hardblock: uncommitted) | honeypot_login_attacks_log_ok |
| Phase 7: Foto-Content | Naechster Schritt | - |

** Gruendlicher Test der gesamten Passkey-Funktionalität steht noch aus (nicht nur iOS) - siehe naechster Schritt unten und Abschnitt 7.*

## 5.2 Unmittelbar naechster Schritt (Anschluss naechster Chat)

**🎯  NAECHSTER SCHRITT (oberste Prioritaet): Gruendlicher Gesamttest der Passkey-Funktionalität. Phase 6 ist technisch implementiert, aber noch nicht systematisch getestet - getestet wurde bisher nur punktuell (Windows Hello, Android Chrome/Firefox, cust-Banner, ein Grenzfall). Der ausstehende Test umfasst Registrierung, Login, Umbenennen, Loeschen, Prompt-/Dismiss-Logik, jeweils fuer mand UND cust, ueber Windows/Android/iOS und die relevanten Browser hinweg - AUSDRUECKLICH KEIN reiner iOS-Test. Details Abschnitt 7. Zwischen dem 19.07.-Stand und heute kam ausschliesslich Sicherheits-Haertung dazwischen (Abschnitt 5.2f) - am Passkey-Testbedarf hat sich nichts geaendert.**

**⚠  Danach weiter OFFEN: (a) dirty-Ausblendung bei system/mandanten/index.blade.php + customer/auth/register.blade.php nachziehen. (b) Regressionstest Android/Windows der globalen Button-Animation. (c) Abnahmetest cust-Bereich (Bloecke 1-6), danach Tag cust_complete_ok. (d) TRUSTED_DEVICE_DAYS-Duplikat in .env bereinigen (Zeile 17 vs. 97). (e) [ERLEDIGT 04.08. — Logout-Button "Zurueck" heisst jetzt "Fenster schliessen", siehe 5.2i]. (f) syst-Passwort-Policy + Honeypot-Infrastruktur committen (aktuell uncommitted), HONEYPOT_LOCKOUT_MINUTES + LOG_STACK=daily auf Server-.env nachtragen. DANACH Phase 7: mand-Content VOR Cust-UI.**

## 5.2n Vorbereitung 28.–29.08.2026 (Fünfte Datenbank subscriptiondb — Abrechnungssystem-Grundlage)

✓  Neue Datenbank u14bc1w8_v08_subscriptiondb angelegt, DB-User eingerichtet, Connection subscriptiondb in config/database.php (.env + .env.example ergaenzt), Base-Model App\Models\SubscriptionDb\SubscriptionDbModel (minimal, analog den vier bestehenden Base-Models: nur $connection). Verbindungstest erfolgreich

✓  Fuenf Tabellen per DDL angelegt (inkl. FK-Constraints — bewusste Ausnahme vom Projektstandard, siehe unten): subscriber (Vertragspartner, ueberdauert geloeschte Accounts), plan (versionierte Tarife), subscription (Vertrag je Nutzer, ein Subscriber kann mehrere Vertraege zahlen), ledger_entry (unveraenderliches Buchungsjournal, fuenf Buchungsarten FO/GG/ZE/ZG/ZA, getrennte Spalten fuer Geldkonto und Vertragssaldo), invoice (Rechnung als Momentaufnahme je Vertrag). Kein Eingriff in bestehende Tabellen — Verknuepfung zu mand_user/cust_user ausschliesslich logisch ueber user_type+user_id, analog passkey/trusted_device

✓  Illustrierender Demo-Datensatz eingespielt: Subscriber 30118 mit drei Vertraegen (115, 170, 205), entspricht exakt dem Buchungsbeispiel aus Konzept-Abschnitt 4.2

⚠  Trigger fuer die Unveraenderbarkeit des Journals (BEFORE UPDATE/DELETE auf ledger_entry) NOCH NICHT gesetzt

⚠  WICHTIG: subscriptiondb wurde mit einem SCHWACHEN Passwort angelegt (Testphase, urspruenglich leere Datenbank) — vor jeder produktiven Nutzung durch ein starkes, zufaelliges Passwort ersetzen. Gehoert auf die Liste der Punkte fuer die Produktivsetzung

✓  Anwendungslogik bewusst NICHT implementiert — Konzept-Abschnitt 4 beschreibt Buchungsablaeufe (Umlage, FIFO-Ausgleich, Erstattung) als Erweiterungsoption fuer eine moegliche spaetere kommerzielle Nutzung, keine Tabellen-Models/Controller/Views vorhanden

⚠  Nebenbei behoben: die bereits ausgefuehrte DDL hatte entry_type faelschlich mit 'LS' statt 'ZA' (Zahlungsausgang) angelegt (Bug, kein bewusster Namenswechsel) — korrigiert per ALTER TABLE+UPDATE, siehe Inkonsistenzen.md

Vollstaendiges Konzept: docs/Konzept_Abrechnungssystem.md. Noch UNCOMMITTED, kein Tag. Details PROJECT_CONTEXT.md Abschnitt 10n.

## 5.2m Feature 27.08.2026 (UI-/E-Mail-Texte ausgelagert + 2FA-Gueltigkeitsdauer konfigurierbar)

✓  Neuer Helper uiText(bereich, dateiname, vars) in app/helpers.php: liest storage/app/private/ui-texte/{all,cust,mand,syst}/*.md (nicht versioniert, analog honeypot_paths.txt/pflichtfelder.txt), rendert per CommonMarkConverter zu HTML. Fehlt eine Datei: kein stiller Fail, sondern roter Platzhalter [FEHLENDER UI-TEXT: ...] im Frontend. 28 Textstellen ausgelagert: Login-/2FA-Hinweise, Konto-Loeschen-/Galerie-entfernen-Warnungen, Upload-Bedingungen-Hinweis, E-Mail-aendern-Erklaerung, Spam-Hinweis, Passkey-Prompts, Alias-Erklaerung, Pflichtfeld-Nachtrag-Hinweis, Policy-Update-Hinweise, 7 E-Mail-Bodies. Wortgleiche Duplikate (auch ueber cust/mand/syst hinweg) auf je eine gemeinsame Datei konsolidiert

✓  renderMarkdownVariant() (bestehender Tag-Varianten-Mechanismus, z.B. Passkey-OS-Hinweise) um denselben {{key}}-Platzhalter-Mechanismus erweitert wie uiText() (str_replace vor Markdown-Konvertierung) — fuer Variablen mitten im Satz (E-Mail-Bodies mit Code/Name/Frist). Alle 28 Dateien vorruebergehend mit rotem [DEV]-Marker als Test-Fortschrittsanzeige versehen, wird nach Verifikation je Anzeigestelle wieder entfernt

✓  2FA-Code-Gueltigkeitsdauer zentralisiert: war bisher an ZWEI Stellen hartcodiert (TwofaService::VALID_MINUTES=2 UND separat TwoFactorCodeMail 'validMinutes'=>2, DRY-Verstoss) und in den drei Rollen-Hinweistexten uneinheitlich (nur cust nannte die Dauer, syst-Text war noch gar nicht ausgelagert). Neue config/twofa.php ('valid_minutes' => env('TWOFA_CODE_VALID_MINUTES', 2)), TwofaService::VALID_MINUTES entfernt, TwoFactorCodeMail liest jetzt ebenfalls config('twofa.valid_minutes'). Alle drei Rollen nutzen jetzt denselben Text (all/a_log_2fa_hinweis.md) mit dynamischem {{validMinutes}}

⚠  ZUSAETZLICH, .env-only (nicht versioniert, kein Tag, aber operational relevant): (1) Mail-Konfiguration auf echtes Postfach umgestellt — MAIL_SCHEME=starttls wurde von der installierten Symfony-Mailer-Version (8.0.8) nicht unterstuetzt und verursachte 500er-Fehler bei jedem Mailversand (2FA-Login etc.), zudem zeigte .env auf einen lokalen 127.0.0.1:1025-Mailcatcher statt echtem SMTP. Neues Postfach noreply@martinwagner.de bei Alfahosting, jetzt MAIL_SCHEME=smtps, MAIL_HOST=host159.alfahosting-server.de, MAIL_PORT=465. Getestet cust/mand/syst — siehe aktualisierten Steckbrief Abschnitt 1. (2) BACKSTAGE_PATH erstmals explizit gesetzt (individueller Pfad statt Code-Default backstage) — Sicherheitsgewinn, da der syst-Login-Pfad nicht mehr aus dem Code ableitbar ist. **WICHTIG:** der tatsaechliche neue Pfad ist in diesem Chat nicht bekannt (nur lokale .env geprueft) — muss im Steckbrief Abschnitt 1 nachgetragen werden, sonst ist der syst-Login im Notfall nicht auffindbar

Tag ui_texte_ausgelagert_ok, Commit 2dbeb37. Details PROJECT_CONTEXT.md Abschnitt 10m.

## 5.2l Bugfix 26.08.2026 (Policy-Version bei Registrierung)

✓  registration_policy_version_fix_ok (Commit 83c738e): CustRegisterController::store() und SystemMandantController::handleRegister() setzten ds_version/upload_terms_version bisher auf den statischen Config-Wert config('datenschutz.version') ('1.0') statt auf die tatsaechlich aktuelle, von CheckPolicyVersion geprueften Version aus userdb.policy_versions — dadurch erschien bei jeder Neuregistrierung sofort beim ersten Login das "Policy geaendert"-Popup, obwohl der Nutzer gerade erst zugestimmt hatte. Zusaetzlich bekam mand upload_terms_version bisher faelschlich den ds_version-Wert. Fix: beide Felder lesen jetzt PolicyVersion::get('ds_version') bzw. PolicyVersion::get('upload_version')

Details PROJECT_CONTEXT.md Abschnitt 10l.

## 5.2k Feature 26.08.2026 (Pflichtfelder-Erzwingung nach Login)

✓  pflichtfelder_erzwingung_ok (Commit 16e81f3): neue Middleware CheckPflichtfelder (bootstrap/app.php, zwischen CheckPolicyVersion und CheckWelcome) prueft bei jedem cust/mand-Request per istPflichtfeld(), ob ein aktuell als Pflicht konfiguriertes Feld (Telefon/Strasse/PLZ+Ort/Firma) NULL ist. Trifft das zu: Redirect zur Konto-Seite mit withErrors() (Feldmarkierung wie bei fehlgeschlagener Validierung) + Amber-Hinweisbox. Ausnahmen: Konto-Seite/Speichern-Route, Logout, Datenschutz-Routen. Ergebnis 60s pro User gecacht (Cache::forget() beim Speichern). mand bleibt nach dem Speichern auf der Konto-Seite; cust wird zur urspruenglich angeforderten Seite zurueckgeleitet (Session-Key pflichtfeld_redirect_target, nur fuer cust gesetzt)

✓  Zusaetzlich: grauer "(optional)"-Hinweis bei Strasse/PLZ+Ort ergaenzt (hatten bisher nur das bedingte Sternchen, siehe 5.2j)

Details PROJECT_CONTEXT.md Abschnitt 10k.

## 5.2j Feature 26.08.2026 (Konfigurierbare Pflichtfelder mand/cust)

✓  Pflichtfelder-Konfiguration (pflichtfelder_konfiguration_ok, Commit eef2eed): neue Datei storage/app/private/pflichtfelder.txt (nicht versioniert, analog honeypot_paths.txt) steuert je Feld mand (Pflicht) oder opt (optional) fuer Telefon/Strasse+Hausnr./PLZOrt/Firma, bei cust UND mand. Neuer Helper istPflichtfeld(userType, feldKey) in app/helpers.php, fehlender Eintrag defaultet zu opt (fail-safe). Registrierung (CustRegisterController, SystemMandantController::handleRegister()) fragt nur Pflichtfelder ab - optionale Felder werden im Formular komplett ausgeblendet. Konto-Bearbeiten (CustSelfController, MandantSelfController::update()) zeigt immer alle vier Felder, Pflicht-Status (required/Sternchen) dynamisch. Startkonfiguration (Telefon+Firma optional, Strasse+PLZOrt Pflicht) bildet das bisherige, vorher fest im Code verdrahtete Verhalten unveraendert ab

✓  DB-Migration: cust_tel/cust_street+nr/cust_postcode_city/cust_company sowie die vier mand-Pendants in userdb per direktem SQL (phpMyAdmin, keine Laravel-Migration) von NOT NULL DEFAULT 'nicht vorhanden' auf NULL DEFAULT NULL umgestellt, bestehende Platzhalterwerte per UPDATE auf echtes NULL migriert. Anzeige-Fallback bleibt ueberall 'nicht vorhanden' bei NULL - fuer den Nutzer unveraendert. mand_uname/cust_uname unveraendert immer Pflicht

✓  Bugfix mandant/konto.blade.php: fehlende rote Pflicht-Sternchen bei mand_uname/mand_firstname/mand_lastname/mand_street+nr/mand_postcode+city ergaenzt (im Gegensatz zu allen anderen Konto-Formularen im Projekt fehlten sie hier)

✓  syst sieht jetzt zusaetzlich mand-Adresse: system/mandanten/show.blade.php + edit.blade.php - Strasse+Hausnr. und PLZ+Ort als neue dt/dd-Paare ergaenzt (vorher nur Telefon/Firma sichtbar), Feldreihenfolge umgestellt auf Benutzername/E-Mail/Vorname/Nachname/Strasse/PLZ+Ort/Telefon/Firma

✓  Neu fuer mand - Detailseite je Mitglied: neue Route GET /mandant/kunden/{id} (mandant.kunden.show, {id} ist pcode_id, konsistent mit kunden.passcode/kunden.destroy), neue Methode MandantCustController::show() (Sicherheitscheck pcode_id+mand_id, sonst abort(404), NIE 403), neue View mandant/cust/show.blade.php (read-only, Stil analog system/mandanten/show.blade.php) - zeigt erstmals Telefon/Firma/Adresse eigener Mitglieder (vorher nirgends in der UI sichtbar). Mitgliederliste (mandant/cust/index.blade.php) umgebaut: E-Mail (Link zur Detailseite, text-indigo-600 underline - auch ohne Hover erkennbar) + cust_uname in erster Spalte/Zeile statt Alias+E-Mail, Alias-Bearbeitung von festem Input auf Anzeige-Text mit Bearbeiten/Speichern-Toggle-Button umgestellt (lokaler Alpine-Scope x-data="{ editing: false }" pro Zeile)

✓  Getestet: cust Registrieren+Bearbeiten, mand Registrieren (per Einladung)+Bearbeiten, syst-Anzeige (show+edit), mand-Mitgliederliste (Alias-Toggle, Detail-Links, Sortierung/Suche weiterhin funktionsfaehig) - alles bestaetigt erfolgreich. Details PROJECT_CONTEXT.md Abschnitt 10j

## 5.2i Fixes 04.08.2026 (Passkey-Trusted-Device + Logout-Dialog-Button)

✓  Trusted-Device-Cookie im Passkey-Login-Pfad (trusted_device_passkey_ok, Commit 268c2b7): Checkbox "Geraet merken" wirkte bisher nur im Passwort-Login-Formular - der Passkey-Button liegt ausserhalb dieses <form>, sein fetch()-Request sendete den Checkbox-Zustand nie mit. Fix: JS liest den Checkbox-Zustand jetzt direkt aus dem DOM (document.getElementById('remember_device_cust'/'_mand')?.checked) und sendet ihn als remember_device im Passkey-Fetch-Body mit. CustLoginController::passkeyLogin() und MandantLoginController::passkeyLogin() rufen jetzt issueTrustedDeviceIfRequested() auf; deren Typehint wurde von RedirectResponse auf RedirectResponse|JsonResponse gelockert (beide nutzen Illuminate\Http\ResponseTrait, ->cookie() funktioniert identisch). Getestet cust + mand, Trusted-Device-Datensatz + Mail-Versand (TrustedDeviceAddedMail) bestaetigt

✓  Logout-Dialog-Button "Zurueck" -> "Fenster schliessen" (logout_dialog_close_window_ok, Commit 859516d): Button in logout-button.blade.php ruft jetzt zusaetzlich window.close() auf (@click="window.close(); showConfirm = false") - schlaegt bei nicht per Skript geoeffneten Tabs browserseitig lautlos fehl und dient dann als Aufforderung an den Nutzer. Bisheriges Fallback-Verhalten (Dialog schliessen via showConfirm = false) bleibt erhalten, Alpine-Mechanismus (x-data/x-show/x-cloak/@click.outside) unveraendert. Loest den seit 09.-19.07. offenen Punkt (siehe 5.2e-Notiz unten)

## 5.2g Aenderungen 01.08.2026 (anon-Login per Kurzcode-Link)

✓  anon-Login per teilbarem Kurzcode-Link (anon_share_link_shortcode_ok, Commit 15e21bd): neue Tabelle sessiondb.share_link (code 7-stellig UNIQUE, mand_id, sec_level, created_at, UNIQUE-Index mand_id+sec_level). MandantPwListController::edit() erzeugt Codes per firstOrCreate() (stabil ueber mehrere Seitenaufrufe, kein neuer Link bei jedem Reload; bei Code-Kollision bis zu 5 Retries, danach Log::error() + Stufe ausgelassen). Neue Route GET /s/{code} (routes/web.php, Name login.shortcode) - geprueft gegen Honeypot-Pfade + BACKSTAGE_PATH, keine Kollision

✓  Praezise Pro-Stufe-Invalidierung: MandantPwListController::update() laedt vor dem Speichern die alte pw_list-Zeile, vergleicht pro Stufe alten Klartext gegen neu eingereichten und loescht share_link NUR fuer tatsaechlich geaenderte Stufen (Erstanlage zaehlt nicht als Aenderung) - Link verhaelt sich damit funktional identisch zum Kurzzeit-Passwort selbst

✓  CustLoginController: alter langer, verschluesselter Token-Weg (loginViaShareLink(), Route customer.login.share) vollstaendig entfernt und durch loginViaShortCode() ersetzt. Gemeinsame Session-Aufbau-Methode buildAnonSession() extrahiert (vorher 2x identischer Code in handleAnonLogin() und loginViaShareLink())

✓  UI (mandant/pwlist.blade.php): Ein-Klick-Icon je Stufe (navigator.share, sonst Clipboard-Fallback mit "✓ Link kopiert"-Bestaetigung), loest das vorherige zweistufige "Login-URL erzeugen"-Verfahren ab

✓  Datenschutz-Hinweis-Popup fuer anon (customer/content.blade.php, beide Zugangswege) entfernt - Hinweis soll kuenftig ueber Content-Seiten selbst erreichbar sein (Phase 7, noch offen). DatenschutzController::hinweisOk(), Route customer.datenschutz.hinweis-ok und Session-Flag _ds_hinweis_gezeigt bleiben unangetastet, aber unerreicht

## 5.2h Bugfix 03.08.2026 (cust: Trusted-Device-Cookie im Nicht-2FA-Pfad)

✓  trusted_device_cust_nofactor_fix_ok (Commit ddf5e55): In CustLoginController::handleLogin() wurde die remember_device-Checkbox im Nicht-2FA-Login-Pfad ausgelesen, aber nie ausgewertet - issueTrustedDeviceCookie() wurde dort nie aufgerufen. Der Trusted-Device-Datensatz entstand bisher nur ueber den 2FA-Pfad (verifyTwoFactor()). Bei mand war der aequivalente Pfad bereits korrekt verdrahtet (issueTrustedDeviceIfRequested(), siehe 5.2f, mand_2fa_optin_login_fix_ok) - nur cust war betroffen. Fix: neue private Methode CustLoginController::issueTrustedDeviceIfRequested() ergaenzt (analog MandantLoginController), im Nicht-2FA-Rueckgabepfad von handleLogin() aufgerufen. verifyTwoFactor() unveraendert, keine doppelte Cookie-Ausstellung moeglich (beide Pfade exklusiv). Getestet mit und ohne 2FA.

## 5.2f Aenderungen 29.-31.07.2026 (Sicherheits-Haertung Login)

✓  Fehlermeldungen dirty-ausgeblendet + Error-Bag-isoliert (error_messages_dirty_fix_ok, 10 Stellen): cust/anon/mand/syst-Login + Dashboards + syst-Profil. Bags noetig, da login-modal.blade.php cust/anon/mand auf DERSELBEN Seite rendert (Tab-Kollisionsgefahr); syst laeuft auf eigener Seite, bekam Bag trotzdem (Konsistenz). Geprueft: KEINE latente mand/syst-Bag-Kollision moeglich (nie gleichzeitig gerendert)

✓  Login-Cleanup erweitert (login_cleanup_expired_records_ok): LoginSessionBuilder::cleanupExpiredRecords() bereinigt jetzt zusaetzlich zu sessiondb.session auch userdb.invite, sessiondb.cust_invite, sessiondb.trusted_device, sessiondb.twofa_code bei jedem cust/mand-Login. userdb.cust_invite (Relikt) bewusst ausgenommen

✓  Passkey-Hinweistexte ausgelagert (passkey_hints_markdown_ok): 4 editierbare md-Dateien storage/app/private/passkey_{allgemein,spezifisch}_{cust,mand}.md (unversioniert, WinSCP-editierbar), neue Helper renderMarkdownVariant() in app/helpers.php

✓  Platzhaltertexte Einladungsformulare praezisiert (invite_placeholder_texts_ok): "ihre@email.de" -> "E-Mail Mitglied"/"E-Mail Galerist:in"/"E-Mail Systuser"

✓  mand-2FA tatsaechlich deaktivierbar (mand_2fa_optin_login_fix_ok): mand_2fa_opt_in war bisher totes Feld (nie beim Login geprueft) - jetzt ausgewertet in MandantLoginController::handleLogin(). Checkbox in mandant/konto.blade.php invertiert+umbenannt (mand_2fa_disable, Label "Anmeldung ohne Sicherheitscode per E-Mail" + Warnhinweis). Trusted-Device-Cookie jetzt auch im 2FA-Bypass-Pfad ausgestellt

✓  Seitenkopf-Label MITGLIED ergaenzt + Inkonsistenz #12 dokumentiert (mitglied_label_und_inkonsistenz12_ok): cust dashboard + passkey-Seite zeigen jetzt festes "MITGLIED"-Label. passkey.sign_count als faktisch wirkungslos dokumentiert (Inkonsistenzen.md #12) - kein akutes Risiko, bewusst nicht behoben

✓  Einheitliche IP-basierte Login-Sperre (login_lockout_ip_based_ok): loginThrottleKey()/checkLoginThrottle()/recordFailedLoginAttempt()/clearLoginThrottle() in app/helpers.php, EIN Schluessel pro IP ueber cust/mand/syst. mand+syst hatten VORHER GAR KEINE Drosselung. 5 Fehlversuche/5 Minuten (LOGIN_LOCKOUT_MAX_ATTEMPTS/LOGIN_LOCKOUT_MINUTES), deaktiviert bei DEBUGMODE=true

✓  syst-Login-Pfad ueber .env konfigurierbar (backstage_path_configurable_ok): config('app.backstage_path') (BACKSTAGE_PATH, Default weiterhin 'backstage') loest alle hartkodierten /backstage-Vorkommen ab (3x redirect() + 6x REDIRECT_TARGETS in 3 Middlewares). Behebt strukturell die Bug-Klasse vom 18.07.

✓  Log-Kanal login_attacks + dynamische Honeypot-Routen (honeypot_login_attacks_log_ok): neuer daily-Kanal (14 Tage, storage/logs/login-attacks.log), checkLoginThrottle() loggt bei aktiver Sperre hinein. Koeder-Pfade (wp-login.php, wp-admin/*, xmlrpc.php, admin, phpmyadmin, .env) aus storage/app/private/honeypot_paths.txt (unversioniert) dynamisch als Routen registriert (registerHoneypotRoutes()), jeder Treffer loest volle Sperre (HONEYPOT_LOCKOUT_MINUTES, Default 60) ueber DENSELBEN IP-Schluessel wie die reguläre Sperre aus - rollenuebergreifend. Standard-Log-Stack ebenfalls auf daily/14 Tage umgestellt (LOG_STACK=daily, nur Server-.env)

⚠  NOCH UNCOMMITTED (zum Zeitpunkt dieses Doku-Standes): syst-Passwort-Policy auf Password::min(20)->mixedCase()->numbers()->symbols() verschaerft (SystemUserController, SystemProfileController) + Hard-Block beim Login bei nicht mehr konformem Bestandspasswort (SystemLoginController::handleLogin(), zaehlt NICHT als Fehlversuch fuer die IP-Sperre). Kein Self-Service-Reset fuer syst - Notfallverfahren bei Selbstaussperrung: Abschnitt 6a

## 5.2e Aenderungen 09.-19.07.2026

✓  iOS-Long-Tap-Fix (ios_longtap_complete_ok): 21 Zurueck-Links + 13 Dashboard-Kacheln auf button umgestellt, Regression-Fix galerien.blade.php, Tag-Mismatch-Bereinigung, Policy-Update-Popup-Links (DS+Upload, cust+mand) auf button/window.open

✓  Upload-Bedingungen-Popup fuer cust entfernt (cust_ds_hinweis_ok): PolicyController::confirmCust() upload-Zweig geloescht, CheckPolicyVersion upload_version-Check fuer cust entfernt. DS-Popup bleibt fuer cust aktiv, mand unveraendert (beide Popups). Statischer Hinweis in customer/dashboard.blade.php + neuer FAQ-Eintrag Upload-Bedingungen.md. HINWEIS: Tag cust_upload_popup_removed_ok existiert NICHT in der Historie - tatsaechliches Tag ist cust_ds_hinweis_ok

✓  Trusted-Device-Feature -> vollstaendiger Auto-Login (autologin_complete_ok): neue Tabelle sessiondb.trusted_device, Model TrustedDevice, Helper trustedDeviceCookieName()/checkTrustedDevice()/issueTrustedDeviceCookie()/guessDeviceLabel()/revokeTrustedDevices(), config/trusted_device.php (TRUSTED_DEVICE_DAYS, Default 7 - ACHTUNG: in .env doppelt gesetzt, siehe unten), Checkbox im Login-Modal (cust+mand), Service LoginSessionBuilder (buildForCust/buildForMand), Middleware AutoLoginTrustedDevice (bei beiden Cookies gueltig: IMMER cust bevorzugen), gemeinsame Komponente logout-button.blade.php (ersetzt 11 Views) mit bedingtem Loesch-Dialog, globales Cleanup abgelaufener Eintraege bei jedem Logout, iOS-Cache-Fix (no-store-Header + pageshow-Listener), neue Mail TrustedDeviceAddedMail. Getestet iOS/Windows/Android

✓  sessiondb.session-Bugfixes (session_usertype_fix_ok): (1) verwaiste Session-Zeilen durch regenerate() ohne destroy=true behoben - jetzt regenerate(true) an ALLEN 7 Session-Uebergaengen inkl. anon-Login (KEINE Ausnahme mehr, siehe Korrektur oben); (2) user_type war dauerhaft 'anon' (hartcodiert in SessionDbSessionHandler::write() INSERT) - jetzt per App::terminating()-Callback nach dem Schreiben korrigiert; (3) destroy() ID-Kuerzung substr($id,0,128) an write() angeglichen

✓  "Sitzung abgelaufen"-Meldungen entfernt (session_messages_removed_ok): cust/mand/syst/anon. Dabei gefunden+behoben: REDIRECT_TARGETS['syst'] zeigte auf nicht existierende Route /system/login (404) - korrigiert auf /backstage in SessionIdleTimeout.php, ValidateUserExists.php, RequireRole.php. Eigene andersartige Fehlermeldungen dieser Middlewares blieben bestehen

**Gefundene, noch offene Punkte (nicht behoben):** Logout-Button-Dialog "Zurueck"-Button (Umbenennung zu "Verlassen ohne Loeschen" besprochen, Nutzer hat abgebrochen); E-Mail-Footer "Bitte antworten Sie..." (Sie-Form) in two-factor-code.blade.php + trusted-device-added.blade.php + cust-invite.blade.php trotz Du-Form im uebrigen Mailtext; TRUSTED_DEVICE_DAYS doppelt in .env (Zeile 17 =1, Zeile 97 =7 - erste Definition gewinnt, effektiv gilt 1 Tag).

## 5.2d Aenderungen 29.06.2026

✓  Globale iOS/Android-Button-Animation (ios_button_feedback_ok): app.css button:active{opacity:.75;transform:scale(.95)}; * {user-select:none} ausser input,textarea; touchstart-Listener in app.js. Pro-Button active:-Klassen + submitted-Pattern zugunsten globaler Regeln entfernt

✓  <a>->Button-Umbauten (~30 Views): button-artige Zurueck-/Aktions-Links auf <button @click=window.location>; Guard-Seiten auf $store.unsavedGuard.requestNav(); Navigations-Links bleiben <a>

✓  Doppel-Submit-Schutz Login-Buttons (cust/mand/syst): type=button + @click submit();submitted=true + :disabled=submitted — verhinderte mehrfache Sicherheitscode-Mails

✓  E-Mail-Button-Texte: inline user-select:none in invite/cust-invite/email_change (wirkt nicht in iOS Apple Mail — akzeptiert)

✓  syst-Loeschlogik korrigiert: primary loescht non-primaries (View-Bedingung war fehlerhaft, zeigte Loeschen-Button auch non-primaries)

✓  MandAccountDeletedMail (NEU): syst-seitige mand-Loeschung schickt jetzt Mail an den mand selbst (Sie-Form). SystemMandantController@destroy ergaenzt. cust erhalten weiterhin CustAccountDeletedMail

✓  Deutsche PW-Fehlermeldungen in 7 Controllern: password.confirmed='Die eingegebenen Passwörter stimmen nicht überein.'; password.min/mixed_case/numbers/symbols/uncompromised='Das Passwort erfüllt nicht die Mindestanforderungen.'; current_password='Das eingegebene Passwort ist nicht korrekt.' (vorher Laravel-Englisch, kein lang/-Verzeichnis)

✓  PW-Hinweistexte syst auf min:12 korrigiert (4 Views): Doku behauptete 14 Zeichen+Regeln, Controller erzwingt nur min:12. PROJECT_CONTEXT Abschnitt 8 entsprechend korrigiert

## 5.2c Aenderungen 23.-26.06.2026

✓  sec_level-Dropdown-Fix (fixes_23jun_ok): Card-Container overflow-hidden -> overflow-visible (schnitt absolut positioniertes Alpine-Dropdown ab); Chevron-Icon + Rahmen am Trigger; Desktop-Button w-9 -> w-14

✓  Verwaister cust beim Login (fixes_23jun_ok): Account ohne cust_pcode-Eintrag wird beim Login still geloescht (cust_user + passkeys), generische Credentials-Fehlermeldung statt "Kein Mandant zugeordnet" (Account-Enumeration vermieden), KEINE Mail

✓  Spam-Hinweis (fixes_23jun_ok): amber Warnhinweis in E-Mail-Aendern-Modals (cust/mand) — Alfahosting ohne DKIM/SPF, Bestaetigungsmails landen oft im Spam

✓  Anon-Login trim() (touch_and_trim_ok): pw1-pw6 werden beim Speichern getrimmt (Middleware), Feld 'password' beim Vergleich nicht -> explizites trim() beim Anon-Login

✓  Touch-Targets 44px (touch_and_trim_ok): min-h-11 ueber alle Verwaltungs-Views (Android: Buttons reagierten kaum, Text ueberlagert)

✓  Passwort-Auge-Toggle (pw_eye_ok): Alle PW-Felder mit show/hide-Auge (Alpine x-data show:false, inline SVG). pw1-pw6 hatten bereits Toggle. (system/login.blade.php ist aktive Datei — Auge dort sachlich nicht ergaenzt, war NICHT wegen 'tote Datei')

✓  syst-Einladungsformular zweizeilig (pw_eye_ok): Android-Platzmangel -> Zeile 1 E-Mail, Zeile 2 is_primary-Checkbox + Senden

✓  iOS-Button-Feedback (UNGETAGGT, 26.06.): active:opacity-75 active:scale-95 transition-transform an allen Buttons; app.css -webkit-tap-highlight-color:transparent + cursor:pointer. ERFORDERTE npm run build (app-Jw6eDKDd.css). Safari unterdrueckt :active sonst

## 5.2b Aenderungen 22.06.2026 (Tag: syst_primary_ok)

✓  syst is_primary: DB-Feld, Invite-Feld, Einladungsformular (nur primary sieht Checkbox), Registrierung, Löschschutz (abort 403 für alle bei primary-Ziel; non-primary kann niemanden löschen), Profil read-only, Session-Key _is_primary

✓  cust 2FA-Logik: ALLE mand-Kontexte geprüft (nicht nur bevorzugter). Schwellwert 0 = nie 2FA (war Bug: 0 löste 2FA aus)

✓  Logout-Redirect: syst + mand jetzt auf route('home') (cust/mand-Loginseite), nicht mehr auf eigene Loginseite

✓  cust-Einladungsmail: $mandFirstnameNom (Nominativ) für Zeilen 102+114, $mandFirstname (Genitiv) nur noch in Zeile 103

✓  E-Mail-Begleittexte: read-only-Hinweis auf 3 Registrierungsseiten; langer 2FA/PW-Reset-Text in Dashboard-Modals (cust du-Form, mand Sie-Form) + system/profile

✓  syst-Mandanten-Löschung: SystemMandantController@destroy führt jetzt dieselbe cust-Kaskade aus wie MandantCustController@destroy (Löschmail + cust_user + passkeys bereinigen + sessiondb.cust_invite)

## 5.3 Was 19.-20.06. neu hinzukam

✓  cust-Account-Loeschung mit E-Mail-Benachrichtigung (cust_delete_mail_ok)

✓  Login-Beschriftungen cust/anon: Anonym->Kurzzeit-Passwort, Registriert->Mitglied (login_labels_ok)

✓  Unsaved-Changes-Guard in 7 Einstellungs-Fenstern, eigenes Verwerfen-Modal (unsaved_changes_guard_ok)

✓  Galerien-Einstellungen: Sofortspeicherung per AJAX statt Button (galerien_ajax_ok)

✓  Mitgliederliste: Custom-Dropdown, Layout, Sortierung, Live-Suche (mand_mitgliederliste_ok)

✓  Willkommensseite beim ersten Login, show_welcome-Gate (welcome_screen_ok)

✓  FAQ & Infos: dynamisches, dateibasiertes Hilfesystem (faq_feature_ok)

## 5.4 Phase-7-Aufgaben (Prioritaet: mand-Content zuerst)

| **Aufgabe** | **Detail** |
| --- | --- |
| ActivityGroup/Subgroup Controller | CRUD fuer AG/ASG, Routen /mandant/activity/* |
| Upload-Controller | /mandant/upload/* - Batch-Upload vom Smartphone, EXIF-Datumserkennung |
| Content-Anzeige cust+anon | Mandanten-Content-Seite, _sec_level-Filter (NACH mand-Content) |
| mand_profile-Anzeige | Profilseite Galerist fuer cust/anon |
| Passkey-Link in Willkommensseite | Urspruenglich geplant, noch offen - aktuelle Welcome-Seite ist generisches Onboarding ohne Passkey-Bezug |
| ModerationMail | syst setzt fo_sec_level=6, mailt mand - erst nach Content-Upload |

# 6. Bekannte Probleme & Lerneffekte

| **Problem** | **Loesung** |
| --- | --- |
| Redirect-Loop zwischen 2 Gate-Middlewares (CheckPolicyVersion <-> CheckWelcome) | Jede Middleware muss ALLE Bestaetigungsrouten der anderen ausschliessen. Endstand: routeIs('*.policy.*') ││ routeIs('*.welcome*') ││ routeIs('*.datenschutz.*') in BEIDEN (21.06.) |
| 'ansehen'-Link oeffnet sich selbst statt DS-/Upload-Text | View-Link war korrekt - Middleware fing datenschutz.*-Route ab solange Policy unbestaetigt. Fix: *.datenschutz.* in beiden Gate-Middlewares ausschliessen (21.06.) |
| Annahme über Dateirolle ohne Routenpruefung (system/login.blade.php am 21.06. versehentlich geaendert -> /backstage-Ausfall) | KORREKTUR 29.06.: system/login.blade.php ist NICHT tot, sondern aktive syst-Login-View. Lerneffekt: Routenzuordnung (welcher Controller rendert welche View) VOR Aenderung pruefen, nicht Dateirolle raten. Einzige echte tote Datei: welcome.blade.php (Breeze). Wiederherstellung bei Bedarf: git checkout <tag> -- <datei> |
| abort(403) erscheint NICHT im Laravel-Log | Bei 403 zuerst Controller auf abort(403) pruefen |
| Korrigierte Datei lokal sauber, Fehler bleibt | Server-Datei per grep gegen lokale pruefen vor weiterer Diagnose |
| Veralteter Cache korrumpiert Daten | Immer alle 4 artisan-Clear-Befehle (bestaetigt: Hash-Korruption 18.06.) |
| npm run build vergessen nach neuen Tailwind-Klassen | Klassen im Blade wirkungslos, mehrfach (20.06.) faelschlich als Code-Bug diagnostiziert. IMMER npm run build + public/build/ hochladen nach Tailwind-Aenderungen |
| x-cloak ohne CSS-Regel [x-cloak]{display:none!important} | Element bleibt bis Alpine-Init sichtbar. Regel direkt im Partial definieren |
| Alpine-Direktiven binden nicht ohne x-data-Vorfahre | @input/x-show etc. brauchen x-data-Scope - x-data='{}' reicht als leerer Scope |
| @json() in doppelt-quoted HTML-Attribut zerstoert es | x-data='...@json()...' mit EINFACHEN Anfuehrungszeichen um das Attribut |
| Datei als 'bereits vorhanden' angenommen ohne Verifikation | Vor Verlassen auf vorausgesetzte Datei: per ls/grep auf Server pruefen |
| withoutMiddleware() in Laravel 13 mit web(append:[]) | Verursacht 403 - stattdessen routeIs()-Check in Middleware selbst |
| mand_tel/cust_tel: UNIQUE + DEFAULT = Konflikt | UNIQUE-Index entfernt (16.06.) |
| inv_type ist ENUM, nicht varchar | email_change musste per ALTER TABLE erganzt werden |
| PowerShell: && nicht unterstuetzt | Semikolon ; zwischen Befehlen |
| BIGINT ohne UNSIGNED bricht FK-Constraints | Alle FK-Spalten: BIGINT UNSIGNED NOT NULL |
| _last_activity im JSON-Payload, nicht DB-Spalte | Nur session()->put(), kein SQL-UPDATE |
| laragear/webauthn: nur Laravel 10/11 | web-auth/webauthn-lib 5.3.5 direkt verwenden |
| MAIL_SCHEME=starttls von Symfony Mailer 8.x nicht unterstuetzt (500er bei jedem Mailversand) | **Korrektur 27.08.:** MAIL_SCHEME=smtps, Port 465 (SSL/TLS) — die fruehere Zeile in diesem Dokument (tls, Port 587) war ebenfalls veraltet/nie tatsaechlich aktiv; .env stand bis 27.08. faelschlich auf einem lokalen 127.0.0.1:1025-Mailcatcher |
| Alfahosting: mod_security-Block-Annahme war falsch | Echte Ursache war abort(403) im Controller; /ds/ bleibt |
| TrimStrings trimmt 'password' NICHT (Vendor-$except: password, current_password, password_confirmation) | pw1-pw6 werden getrimmt (Feldname nicht exempt) -> Anon-Login muss eingegebenes password explizit trimmen, sonst Leerzeichen-Mismatch. Standard-Logins (Hash::check) brauchen kein trim |
| overflow-hidden schneidet absolut positionierte Alpine-Dropdowns ab | Kein overflow-hidden-Vorfahr bei Custom-Dropdowns/Poppern; overflow-visible. rounded-xl funktioniert ohne overflow-hidden. Diagnose: Konsole _x_dataStack[0].open + getComputedStyle-Walk |
| iOS/Safari unterdrueckt :active-State auf Buttons + macht Button-/Linktext markierbar | GLOBALE Loesung (29.06., ios_button_feedback_ok) in app.css: button:active{opacity:.75;transform:scale(.95)}; * {user-select:none} ausser input,textarea; touchstart-Listener auf document in app.js. Button-artige <a> auf <button @click=window.location> umbauen (iOS-Kontextmenue bei langem Tap). Guard-Seiten: @click=$store.unsavedGuard.requestNav(). Login-Buttons: Doppel-Submit-Schutz (type=button + @click submit();submitted=true + :disabled). Erfordert npm run build |
| iOS Apple Mail ignoriert user-select:none komplett | Button-Text in Einladungsmails bleibt auf iOS markierbar. Akzeptierte Einschraenkung, nicht per CSS loesbar (29.06.) |
| Alfahosting-Mail ohne DKIM/SPF -> Spam | E-Mail-Aenderungsmails landen oft im Spam. UI-Hinweis (amber) im Modal; mittelfristig SPF/DKIM einrichten |
| sessiondb.session.user_type dauerhaft 'anon' | SessionDbSessionHandler::write() setzt user_type beim INSERT hartcodiert, kennt die Rolle noch nicht. Fix (19.07.): App::terminating()-Callback aktualisiert user_type+cust_id/mand_id/syst_id NACH dem Schreiben |
| regenerate() ohne $destroy=true erzeugt verwaiste Session-Zeilen | Alte Zeile bleibt in DB stehen. Fix (19.07.): regenerate(true) an ALLEN Login-/Auto-Login-Stellen, keine Ausnahme (auch nicht bei anon) |
| Doppelter .env-Schluessel wird nicht wie erwartet ueberschrieben | phpdotenv (Laravel) ueberschreibt bereits gesetzte Variablen NICHT - die erste Definition gewinnt. TRUSTED_DEVICE_DAYS ist aktuell doppelt gesetzt (=1 und =7), effektiv gilt 1. Vor Bereinigung immer beide Vorkommen pruefen |
| WinSCP-Skript ohne "cd fotos.martinwagner.de" nach "lcd" laedt am falschen Server-Pfad hoch | FTP-Login-Root liegt EINE EBENE UEBER dem Projektverzeichnis (.../u14bc1w8.host159.alfahosting-server.de/ statt .../fotos.martinwagner.de/). "Erfolgreich hochgeladen" trotzdem falscher Pfad, Server zeigt alten Stand. Fuehrte zu stundenlanger Fehlsuche. IMMER cd fotos.martinwagner.de nach lcd, vor put |
| WinSCP-Skripte werden ohne "option transfer binary"/"open <Verbindung>" ausgegeben | Bewusst - Nutzer ergaenzt diese Verbindungszeilen selbst. Ausgegebene Skripte beginnen direkt mit lcd, dann cd, dann put |
| Mehrzeilige Commit-Message in .bat (cmd.exe) mit PowerShell-Anfuehrungszeichen-Bloecken geht nicht | .bat/cmd.exe kennt keine mehrzeiligen Anfuehrungszeichen-Bloecke. Stattdessen mehrere -m "..." -m "..."-Flags verwenden |
| config:clear allein genuegt nicht bei env()-gesteuerten Routen (BACKSTAGE_PATH, LOGIN_LOCKOUT_*, HONEYPOT_LOCKOUT_MINUTES) | route:cache wuerde sonst den alten Pfad einfrieren. Nach Aenderungen an env-gesteuerten Config-/Routen-Werten zusaetzlich route:clear (deckt sich mit "immer alle 4 Cache-Befehle", oben) |
| "Fix wirkt nicht" mehrfach faelschlich als Code-Bug diagnostiziert, obwohl nur der Upload fehlte | Vor jeder weiteren Fehlersuche: per SSH direkt am Server-Pfad verifizieren (grep auf erwarteten Code-Inhalt, ls -la auf Zeitstempel), bevor am Code selbst weitergesucht wird |

## 6a. Notfall: syst-Konto durch Passwort-Policy ausgesperrt

Seit 31.07.2026 wird die syst-Passwort-Policy (min. 20 Zeichen, Groß-/Kleinbuchstaben, Ziffer, Sonderzeichen) auch beim LOGIN geprüft, nicht nur bei Passwort-Vergabe. Ein syst mit einem alten, nicht mehr konformen Passwort wird beim Login mit korrektem Passwort dennoch abgewiesen ("Ihr Passwort erfüllt nicht mehr die aktuellen Sicherheitsanforderungen...").

**Regulärer Weg:** Ein anderer (primärer oder sekundärer) syst-Admin löst über die Benutzerverwaltung einen Passwort-Reset für den betroffenen Account aus (Mail-Versand).

**Notweg, falls kein anderer syst-Admin erreichbar ist** (z.B. Selbstaussperrung):

1. Neuen bcrypt-Hash generieren — per SSH/PuTTY:

   ```bash
   php artisan tinker
   ```

   In der Tinker-Shell:

   ```php
   echo Hash::make('NeuesSicheresPasswort20Zeichen+#');
   ```

   Den ausgegebenen Hash-String (beginnt mit $2y$) kopieren.

2. Hash direkt per phpMyAdmin eintragen:

   ```sql
   UPDATE u14bc1w8_v08_userdb.syst_user
   SET syst_pw_hash = '<hier den kopierten $2y$-Hash einfügen>'
   WHERE syst_email = 'betroffene@email.de';
   ```

3. Login mit dem neuen Klartext-Passwort testen — danach das Notfall-Passwort schnellstmöglich über die normale Kontoverwaltung durch ein regulär gewähltes ersetzen.

**Wichtig:** Das neue Passwort MUSS die aktuelle Policy erfüllen (min. 20 Zeichen, Groß-/Kleinbuchstaben, Ziffer, Sonderzeichen), sonst greift beim nächsten Login erneut dieselbe Sperre.

# 7. Passkeys (Phase 6 - implementiert, gruendlicher Test steht noch aus)

**KORREKTUR 19.07.: Phase 6 wurde in frueheren Doku-Stellen faelschlich als "abgeschlossen" bezeichnet. Korrekt: technisch implementiert, aber noch nicht gruendlich getestet. Siehe naechster Schritt in Abschnitt 5.2.**

| **Aspekt** | **Detail** |
| --- | --- |
| Library | web-auth/webauthn-lib 5.3.5 - NICHT laragear, NICHT laravel/passkeys |
| Geltungsbereich | mand UND cust - Passkey wird aktiv gefördert (Prompt nach Login), aber nicht technisch erzwungen |
| userHandle | base64url('user_type:user_id') z.B. base64url('mand:42') |
| Prompt-Logik | Nach Login: Modal (mand) / Banner (cust) wenn kein Passkey fuer dieses OS + kein passkey_dismissed-Eintrag |
| passkey_dismissed | Schluessel: (user_type, user_id, os, ua_hash) - pro Geraet+Browser eigener Status |
| Cust-Passkey-Hinweis | NOCH OFFEN: urspruenglich ueber Welcome-Seite geplant, aktuelle Welcome-Seite (20.06.) ist generisches Onboarding ohne Passkey-Link |
| Bisher getestet (nur punktuell) | Windows (Hello aktiv/inaktiv, Chrome/Firefox/Edge), Android (Handy+Tablet, Chrome mit Google-Sync, Firefox lokal), cust-Banner, ein Grenzfall (mehrere Rollen auf einem Windows-Konto) |
| **NAECHSTER SCHRITT: Gruendlicher Gesamttest** | Kein reiner iOS-Test, sondern systematischer Test der GESAMTEN Passkey-Funktionalität ueber alle Rollen (mand+cust) x Geraete (Windows/Android/iOS) x Browser x Grenzfaelle. Fuer iOS speziell zusaetzlich zu beachten: kein Commit mit abgeschlossenem WebAuthn-Passkey-Test auf iOS gefunden (iOS wurde bereits fuer Button-Feedback/Long-Tap/Auto-Login getestet, das ist aber ein anderer Testumfang als ein Passkey-Test) |
| sign_count faktisch wirkungslos (30.07.) | Wird geschrieben, nie ausgelesen/geprueft - kein WebAuthn-Rollback-Schutz gegen geklonte Credentials. Kein akutes Risiko, bewusst nicht behoben. Details Inkonsistenzen.md #12 |

# 8. Datenschutz, Policy-System & Onboarding

| **Komponente** | **Detail** |
| --- | --- |
| Dateien (storage/app/private/) | datenschutzerklaerung.pdf, upload_bedingungen.pdf, erlaeuterung.md (mit MAND-Markern), willkommen_cust.md, willkommen_mand.md (NEU 20.06.), faq/cust/*.md, faq/mand/*.md (NEU 20.06.) |
| Routen DS (oeffentlich) | GET /customer/ds/erlaeuterung, /ds/erklaerung-pdf, /ds/upload-bedingungen-pdf |
| Policy-Versionen | DB-Tabelle policy_versions (pv_key, pv_value). syst erhoelt per /system/policy-versionen |
| Popup-Trigger Policy | Middleware CheckPolicyVersion: user.*_version != policy_versions.pv_value -> Popup-Seite, blockiert |
| Willkommensseite NEU | Middleware CheckWelcome: show_welcome=1 -> Redirect, blockiert bis 'Gelesen'. Nach CheckPolicyVersion registriert (Policy hat Vorrang) |
| FAQ & Infos NEU | Dynamische Markdown-Liste, kein DB-Bezug, Path-Traversal-sicher (Regex-Whitelist + basename()) |
| Version erhoehen | syst -> /system/policy-versionen -> Button -> Minor-Version automatisch erhoeht -> alle User sehen Popup |

# 9. Wiederverwendbarkeit fuer kuenftige Projekte

Stand user_management_complete_ok ist als Startimplementierung fuer kuenftige Laravel-Projekte geeignet.

Direkt wiederverwendbar: komplettes Auth-System (2FA, Passkey, PW-Reset, E-Mail-Aenderung mit Bestaetigungslink), Datenschutz-/Einwilligungs-Mechanismus (Markdown-Erlaeuterung, PDF-Auslieferung, Policy-Versions-Popup), Unsaved-Changes-Guard-Partial, FAQ-System (dateibasiert, dynamisch), Willkommensseite-Mechanismus, Custom-Dropdown-Komponente, Sortier-/Such-Logik (client-seitig, localeCompare('de')).

Projektspezifisch als Vorlage nuetzlich: 4-Datenbanken-Trennungsprinzip, Rollenmodell syst/mand/cust/anon (Konzept uebertragbar, Bezeichnungen projektspezifisch).

Nicht uebertragbar: Foto-/Content-Domaene (Activity Group/Subgroup, Sicherheitsstufen) - das ist Phase 7.

Vorgehen fuer neues Projekt: Tag user_management_complete_ok auschecken, Domain-Tabellen/Models/Controller (fotodb, FotoDB/*) entfernen, Rollen-/Tabellennamen anpassen, Rest as-is uebernehmen. Details: PROJECT_CONTEXT.md Abschnitt 15.

Fotosite V08 — Notfall-Startdokument  |  Stand 01.08.2026