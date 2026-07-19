**Fotosite V08**

**Notfall-Startdokument**

*Stand: 19. Juli 2026  |  Letzter Git-Tag: session_usertype_fix_ok*

**🏁 MEILENSTEIN: Benutzer-/Sicherheitsverwaltung implementiert. Tag user_management_complete_ok ist sicherer Rückfallpunkt und als Startimplementierung für künftige Projekte geeignet (siehe Abschnitt 9). EINSCHRAENKUNG: Die Passkey-Funktionalität (Phase 6) ist technisch implementiert, aber noch NICHT gruendlich getestet — siehe naechster Schritt unten und Abschnitt 7.**

**⚠  Wenn ein Claude-Chat verloren geht, gibt dieses Dokument dem naechsten Chat alle nötigen Informationen für einen sofortigen Neustart.**

**⚠  KORREKTUR 29.06. (weiterhin gültig): system/login.blade.php ist NICHT tot, sondern die AKTIVE syst-Login-View (SystemLoginController@login rendert view('system.login'), Login + 2FA via show_2fa-Flag). Frühere Einstufung als tote Datei aufgehoben. Einzige echte tote Datei: resources/views/welcome.blade.php (Breeze-Default).**

**⚠  NEU 09.–19.07.: iOS-Long-Tap-Fix abgeschlossen (ios_longtap_complete_ok), Upload-Bedingungen-Popup fuer cust entfernt (cust_ds_hinweis_ok), Trusted-Device-Feature zu vollstaendigem Auto-Login ohne Passwort ausgebaut (autologin_complete_ok), "Sitzung abgelaufen"-Meldungen entfernt + /backstage-Redirect-Bug behoben (session_messages_removed_ok), zwei sessiondb.session-Bugs behoben: verwaiste Sessions + dauerhaft falscher user_type (session_usertype_fix_ok). Details Abschnitt 5.2e.**

**⚠  KORREKTUR 19.07.: Der anon-Kurzzeit-Kennwort-Login nutzt KEINE Ausnahme mehr bei regenerate() — CustLoginController::handleAnonLogin() wurde ebenfalls auf regenerate(true) umgestellt. Alle 7 Session-Uebergaenge (cust: Passwort/2FA/Passkey/anon; mand: Passwort/Passkey; syst: 2FA) verhalten sich identisch.**

**⚠  KORREKTUR 19.07. (wichtig): Phase 6 (Passkey) ist NICHT "fertig"/"abgeschlossen", auch wenn fruehere Doku-Stellen das so darstellten. Korrekter Status: Passkey-Funktionalität ist technisch VOLLSTAENDIG IMPLEMENTIERT, aber noch NICHT gruendlich getestet. Der gruendliche Test der GESAMTEN Passkey-Funktionalität (nicht nur iOS!) ist der NAECHSTE SCHRITT fuer den neuen Chat. Siehe Abschnitt 5.2 und Abschnitt 7.**

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
| Datenbank | MariaDB - 4 separate DBs (userdb, sessiondb, fotodb, fotoblobdb) |
| Passkeys | web-auth/webauthn-lib 5.3.5 (direkt - NICHT laragear, NICHT laravel/passkeys) |
| Deployment | FTP (WinSCP) auf Alfahosting - fotos.martinwagner.de |
| Git-Repo | github.com/fotosite/fotosite (privat) |
| Aktiver Branch | feature/passkey-infra |
| Lokaler Pfad | D:\mwa\Projekte\fotosite\Fotosite_V08\claudescode\fotosite |
| Letzter Git-Tag | session_usertype_fix_ok (19.07.2026). Meilenstein: user_management_complete_ok (20.06.) |
| Server-Pfad | /var/www/vhosts/u14bc1w8.host159.alfahosting-server.de/fotos.martinwagner.de/ |
| SSH | PuTTY, User u14bc1w8 |
| Mail | host159.alfahosting-server.de:587, MAIL_ENCRYPTION=tls |

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
| invite | inv_id | inv_type: register│pw_reset│email_change; inv_user_type: syst│mand│cust; inv_email bei email_change = neue Adresse; is_primary TINYINT(1) Default 0 (nur für syst-Einladungen relevant) |
| passkey | pk_id | user_type, user_id, credential_id, public_key, sign_count, device_name |
| passkey_dismissed | pd_id | user_type, user_id, os (win│andr│ios), ua_hash |
| policy_versions | pv_key | pv_key: ds_version│upload_version, pv_value, updated_at - syst erhoelt per UI, triggert Popup bei mand/cust |
| cust_invite (RELIKT) | invite_id | NICHT verwenden - sessiondb.cust_invite ist fuehrend |

**⚠  HINWEIS Feldnamen-Inkonsistenz (unkritisch, getrennte Tabellen): cust_user nutzt ****'****cust_postcode_city****'**** (Unterstrich), mand_user nutzt ****'****mand_postcode+city****'**** (Pluszeichen). Bewusst nicht vereinheitlicht.**

## sessiondb (u14bc1w8_v08_sessiondb)

| **Tabelle** | **PK** | **Zweck** |
| --- | --- | --- |
| session | sess_id | Custom Session-Driver. sess_token fuer Lookups, payload (JSON). Jeder Besucher (auch anon). Spalten user_type/cust_id/mand_id/syst_id werden erst NACH dem finalen write() per App::terminating()-Callback nachtraeglich befuellt (behoben 19.07., session_usertype_fix_ok). |
| pw_list | pwlist_id | pw1-pw6 (AES-verschluesselt), valid_from, valid_until - Kurzzeit-Kennwoerter je Mandant |
| twofa_code | tfa_id | 6-stelliger Code, tfa_purpose (login│pw_change│critical), tfa_expires_at, tfa_used |
| cust_invite | invite_id | FUEHREND: mand_id, cust_email, cust_alias, sec_level, token, expires_at, used |
| trusted_device | td_id | NEU 10.-17.07.: "Geraet als sicher merken" fuer vollstaendigen Auto-Login (mand+cust). user_type, user_id, token_hash (SHA-256, UNIQUE), ua_hash, device_label, last_used_at, expires_at, created_at. Bewusst in sessiondb statt userdb. |

## fotodb + fotoblobdb (unveraendert seit 16.06.)

| **Tabelle** | **PK** | **Felder** |
| --- | --- | --- |
| foto_obj (fotodb) | fo_id | fo_filename, mand_id, fo_sec_level TINYINT, fo_is_video bool, fo_datetime, db_saved, fo_filepath |
| activity_group | ag_id | ag_title, mand_id, ag_sec_level TINYINT, ag_prefstat, ag_sort_date |
| activity_subgroup | asg_id | FK -> activity_group.ag_id (einzige echte FK!), asg_sec_level TINYINT, asg_public |
| mand_profile | mp_id | mp_name, mp_title varchar(255), mp_text text, mp_title_start, mp_subtitle_start |
| ag_fo_context / asg_fo_context / mp_fo_context | - | Pivot-Tabellen AG/ASG/Profil <-> Foto (ag_is_banner, ags_is_banner) |
| foto_obj (fotoblobdb) | fod_id | fod_obj BLOB fuer Sicherheitsstufe 6 - vorlaeufig Dummy |

# 5. Aktueller Projektstand (19.07.2026)

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
| Phase 7: Foto-Content | Naechster Schritt | - |

** Gruendlicher Test der gesamten Passkey-Funktionalität steht noch aus (nicht nur iOS) - siehe naechster Schritt unten und Abschnitt 7.*

## 5.2 Unmittelbar naechster Schritt (Anschluss naechster Chat)

**🎯  NAECHSTER SCHRITT (oberste Prioritaet): Gruendlicher Gesamttest der Passkey-Funktionalität. Phase 6 ist technisch implementiert, aber noch nicht systematisch getestet - getestet wurde bisher nur punktuell (Windows Hello, Android Chrome/Firefox, cust-Banner, ein Grenzfall). Der ausstehende Test umfasst Registrierung, Login, Umbenennen, Loeschen, Prompt-/Dismiss-Logik, jeweils fuer mand UND cust, ueber Windows/Android/iOS und die relevanten Browser hinweg - AUSDRUECKLICH KEIN reiner iOS-Test. Details Abschnitt 7.**

**⚠  Danach weiter OFFEN: (a) dirty-Ausblendung bei system/mandanten/index.blade.php + customer/auth/register.blade.php nachziehen. (b) Regressionstest Android/Windows der globalen Button-Animation. (c) Abnahmetest cust-Bereich (Bloecke 1-6), danach Tag cust_complete_ok. (d) TRUSTED_DEVICE_DAYS-Duplikat in .env bereinigen (Zeile 17 vs. 97). (e) Logout-Button "Zurueck"-Text ggf. ueberarbeiten. DANACH Phase 7: mand-Content VOR Cust-UI.**

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
| MAIL_ENCRYPTION=starttls nicht unterstuetzt | MAIL_ENCRYPTION=tls, Port 587 |
| Alfahosting: mod_security-Block-Annahme war falsch | Echte Ursache war abort(403) im Controller; /ds/ bleibt |
| TrimStrings trimmt 'password' NICHT (Vendor-$except: password, current_password, password_confirmation) | pw1-pw6 werden getrimmt (Feldname nicht exempt) -> Anon-Login muss eingegebenes password explizit trimmen, sonst Leerzeichen-Mismatch. Standard-Logins (Hash::check) brauchen kein trim |
| overflow-hidden schneidet absolut positionierte Alpine-Dropdowns ab | Kein overflow-hidden-Vorfahr bei Custom-Dropdowns/Poppern; overflow-visible. rounded-xl funktioniert ohne overflow-hidden. Diagnose: Konsole _x_dataStack[0].open + getComputedStyle-Walk |
| iOS/Safari unterdrueckt :active-State auf Buttons + macht Button-/Linktext markierbar | GLOBALE Loesung (29.06., ios_button_feedback_ok) in app.css: button:active{opacity:.75;transform:scale(.95)}; * {user-select:none} ausser input,textarea; touchstart-Listener auf document in app.js. Button-artige <a> auf <button @click=window.location> umbauen (iOS-Kontextmenue bei langem Tap). Guard-Seiten: @click=$store.unsavedGuard.requestNav(). Login-Buttons: Doppel-Submit-Schutz (type=button + @click submit();submitted=true + :disabled). Erfordert npm run build |
| iOS Apple Mail ignoriert user-select:none komplett | Button-Text in Einladungsmails bleibt auf iOS markierbar. Akzeptierte Einschraenkung, nicht per CSS loesbar (29.06.) |
| Alfahosting-Mail ohne DKIM/SPF -> Spam | E-Mail-Aenderungsmails landen oft im Spam. UI-Hinweis (amber) im Modal; mittelfristig SPF/DKIM einrichten |
| sessiondb.session.user_type dauerhaft 'anon' | SessionDbSessionHandler::write() setzt user_type beim INSERT hartcodiert, kennt die Rolle noch nicht. Fix (19.07.): App::terminating()-Callback aktualisiert user_type+cust_id/mand_id/syst_id NACH dem Schreiben |
| regenerate() ohne $destroy=true erzeugt verwaiste Session-Zeilen | Alte Zeile bleibt in DB stehen. Fix (19.07.): regenerate(true) an ALLEN Login-/Auto-Login-Stellen, keine Ausnahme (auch nicht bei anon) |
| Doppelter .env-Schluessel wird nicht wie erwartet ueberschrieben | phpdotenv (Laravel) ueberschreibt bereits gesetzte Variablen NICHT - die erste Definition gewinnt. TRUSTED_DEVICE_DAYS ist aktuell doppelt gesetzt (=1 und =7), effektiv gilt 1. Vor Bereinigung immer beide Vorkommen pruefen |

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

Fotosite V08 — Notfall-Startdokument  |  Stand 19.07.2026