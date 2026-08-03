# PROJECT_CONTEXT.md — Fotosite V08

> Stand: 03.08.2026 — Tag: `trusted_device_cust_nofactor_fix_ok`. **03.08.: Bugfix** — im cust-Nicht-2FA-Login-Pfad wurde die `remember_device`-Checkbox ausgelesen, aber nie ausgewertet; `issueTrustedDeviceCookie()` wurde dort nie aufgerufen (analoger mand-Pfad war bereits korrekt). Behoben durch neue Methode `CustLoginController::issueTrustedDeviceIfRequested()`, analog `MandantLoginController`. Details Abschnitt 10g. Vorher, 01.08. — Tag: `anon_share_link_shortcode_ok`. Seit 19.07.: umfangreiche Sicherheits-Härtung des Login-Systems — Fehlermeldungen dirty-ausgeblendet + Error-Bag-isoliert (`error_messages_dirty_fix_ok`), Login-Cleanup um `invite`/`cust_invite`/`trusted_device`/`twofa_code` erweitert (`login_cleanup_expired_records_ok`), Passkey-Hinweistexte in editierbare md-Dateien ausgelagert (`passkey_hints_markdown_ok`), Platzhaltertexte in Einladungsformularen präzisiert (`invite_placeholder_texts_ok`), mand-2FA optional deaktivierbar (`mand_2fa_optin_login_fix_ok`), Seitenkopf-Label „MITGLIED" ergänzt + Inkonsistenz #12 (`passkey.sign_count`) dokumentiert (`mitglied_label_und_inkonsistenz12_ok`), einheitliche IP-basierte Login-Sperre für cust/mand/syst (`login_lockout_ip_based_ok`), syst-Login-Pfad über `.env` konfigurierbar (`backstage_path_configurable_ok`), Log-Kanal `login_attacks` + dynamische Honeypot-Routen (`honeypot_login_attacks_log_ok`). **Zusätzlich, zum Zeitpunkt dieses Doku-Standes noch UNCOMMITTED (kein Tag):** syst-Passwort-Policy auf min. 20 Zeichen + Groß-/Kleinbuchstaben + Ziffer + Sonderzeichen verschärft, inkl. Hard-Block beim Login bei nicht mehr konformem Bestandspasswort (kein Self-Service-Reset für syst). Details Abschnitt 8/8a. Phase 7 (Foto-Content) und der gründliche Passkey-Gesamttest sind weiterhin die fachlich nächsten Schritte — siehe unten.
>
> **Offen (Stand 31.07.):** **(0) NÄCHSTER SCHRITT für den neuen Chat: gründlicher Gesamttest der Passkey-Funktionalität (Phase 6) — technisch implementiert, aber noch nicht umfassend getestet, siehe Abschnitt 9.** Zwischen dem letzten Doku-Stand (19.07.) und heute kam ausschließlich Sicherheits-Härtung dazwischen (siehe oben) — am Passkey-Teststand hat sich inhaltlich nichts geändert. (a) `dirty`-Ausblendung bei `system/mandanten/index.blade.php` + `customer/auth/register.blade.php` nachziehen, (b) Abnahmetest cust-Bereich (Tag: `cust_complete_ok`), (c) Regressionstest Android/Windows der globalen Button-Animation, (d) SPF/DKIM-Setup Mailserver, (e) syst-Passwort-Policy-Änderung + Honeypot-Infrastruktur committen (aktuell uncommitted), `HONEYPOT_LOCKOUT_MINUTES`/`LOG_STACK=daily` auf Server-`.env` nachtragen. Details siehe Abschnitt 16.
>
> **KORREKTUR 29.06. (weiterhin gültig):** `resources/views/system/login.blade.php` ist **NICHT** tot — sie ist die **aktive** syst-Login-View. `SystemLoginController@login` rendert `view('system.login')`; Login UND 2FA laufen über diese Datei (2FA-Block via `show_2fa`-Flash-Variable konditionell eingeblendet). Die frühere Einstufung als „tote Datei" (Altlasten 2b, Lerneffekt 17) war falsch und ist hiermit aufgehoben.
>
> **KORREKTUR 19.07. (wichtig):** Der anon-Kurzzeit-Kennwort-Login verwendet entgegen einer früheren Chat-Zusammenfassung **kein** `regenerate()` ohne `destroy` — der Commit `8b4a875`/`7b52b05` hat `handleAnonLogin()` in `CustLoginController.php` explizit auf `regenerate(true)` umgestellt (Zeile 305), identisch zu allen anderen Login-Stellen. Es gibt also **keine** Ausnahme für anon — alle 7 Session-Übergänge (cust: Passwort, 2FA, Passkey, anon; mand: Passwort, Passkey; syst: 2FA) nutzen einheitlich `regenerate(true)`. Details siehe Abschnitt 10d.
>
> **NEU 30.–31.07.:** `/backstage` ist als hartkodierter Pfad **abgelöst** — der syst-Login-Pfad ist jetzt über `config('app.backstage_path')` (`.env`-Variable `BACKSTAGE_PATH`, Default weiterhin `backstage`) konfigurierbar; alle Vorkommen in Middlewares nutzen `route('system.backstage.login')` statt hartkodierter Strings. Erwähnungen von „`/backstage`" in älteren Abschnitten dieses Dokuments sind als Default-Pfad zu lesen, nicht als fixer Wert. Details Abschnitt 10e.
>
> **NEU 01.08.:** anon-Login ist jetzt zusätzlich per teilbarem 7-stelligem Kurzcode-Link möglich (`GET /s/{code}`, `routes/web.php`) — persistenter Code je mand+Stufe in neuer Tabelle `sessiondb.share_link`, erzeugt per `firstOrCreate()` in `MandantPwListController::edit()`. Präzise Pro-Stufe-Invalidierung: `update()` vergleicht vor dem Speichern jede Stufe alt/neu und löscht den Share-Link nur für tatsächlich geänderte Stufen — der Link verhält sich damit funktional identisch zum Kurzzeit-Passwort selbst. Der bisherige lange, verschlüsselte Token-Mechanismus (`loginViaShareLink()`, `customer.login.share`) ist vollständig ersetzt. Im selben Commit wurde das Datenschutz-Hinweis-Popup für anon (beide Zugangswege) aus `customer/content.blade.php` entfernt — der Hinweis soll künftig über die Content-Seiten selbst erreichbar sein (Phase 7, noch nicht umgesetzt); Abschnitt 10a entsprechend korrigiert. Tag: `anon_share_link_shortcode_ok` (Commit `15e21bd`). Details Abschnitt 10f.

## 1. Project Overview

Multi-Tenant-Fotogalerie-Plattform (Hobbyprojekt, Ziel: fertige, nutzbare Website). Jeder Mandant (Galerist:in) verwaltet eigenen Foto-Content (Activity Groups, Subgroups), eigene Mitglieder und eine Profilseite. Vier Rollen mit unterschiedlichen Frontends.

**User-Rollen:**
- `syst` — System-Admin (Plattformbetreiber), UI: „System-Admin"
- `mand` — Mandant/Tenant, UI: „Galerist:in"
- `cust` — Mitglied (registriert oder anonym mit Kurzzeit-Kennwort), UI: „Mitglied/Mitglieder"
- `anon` — Anonymer Besucher (sessionbasiert, kein Login)

**Sicherheitsstufen:** Foto-Objekte, Activity Groups und Subgroups tragen ein `*_sec_level`-Feld (`TINYINT UNSIGNED`, Werte 0–6), das die Sichtbarkeit steuert.

**Terminologie (konzeptionell):**
- `sec_level` — Sicherheitsstufe eines Inhalts (0–6)
- `sec_code` — konzeptioneller Begriff für einen Zugangscode zu einer Sicherheitsstufe (kein eigenes DB-Feld)
- `cust_passcode` (`userdb.cust_pcode.cust_passcode`, varchar) — enthält die Ziffer des sec_levels, den der cust bei diesem mand hat; in der Session gespiegelt als `_sec_level`
- `pw1`–`pw6` (`sessiondb.pw_list`) — Kurzzeit-Kennwörter (AES-verschlüsselt) für anon-Zugriff; Slot-Nummer entspricht dem sec_level (pw1=Stufe 1 … pw6=Stufe 6)

> Hinweis: In der DB sind die Bezeichner historisch gewachsen und teilweise inkonsistent — die obige Terminologie ist die konzeptionell korrekte.

Der sec_level eines cust pro mand steht in `userdb.cust_pcode.cust_passcode`. Bei Einladung festgelegt in `sessiondb.cust_invite.sec_level` (wandert bei Registrierung nach `cust_pcode.cust_passcode`). In der Session als Key `_sec_level` gespiegelt.

| Stufe | Bedeutung | Speicherung |
|---|---|---|
| 0 | Öffentlich — sichtbar für alle, auch anon | Datei |
| 1 | Bekannte | Datei |
| 2 | Großfamilie | Datei |
| 3 | Freunde | Datei |
| 4 | Enge Freunde & Kernfamilie | Datei |
| 5 | Vertraulich | Datei |
| 6 | Streng vertraulich | BLOB in `fotoblobdb` |

> Stufe 6 wird als BLOB in `fotoblobdb.foto_obj` gespeichert (Spalte `fod_obj`, verknüpft über `fo_id`). Alle anderen Stufen (0–5) liegen als Datei im Dateisystem (`foto_obj.fo_filepath`).

**Moderation (syst):** Bei Decency-Verstößen setzt `syst` `fo_sec_level` auf 6 — Foto bleibt für `mand` und für `cust` mit `sec_level=6` sichtbar, ist aber für niedrigere Stufen und `anon` gesperrt. Reversibel. Kein `fo_blocked`-Flag. `mand` erhält automatisch eine `ModerationMail` (reiner Mailversand, keine DB-Tabelle; erst nach Content-Upload relevant).

**Session-Modell:** Jeder Besucher — auch `anon` — erhält einen Eintrag in `sessiondb.session`. Custom Session-Driver `sessiondb` mit `sess_id` als PK (nicht Laravels Standard-`id`), Abfrage über `sess_token`. Session-Timeouts pro Rolle in `.env` konfigurierbar (anon=900s, cust=900s, mand=1800s, syst=600s). Sessions werden beim Login (abgelaufene) bzw. beim Logout (eigene) aus der DB gelöscht — kein Cron/Scheduler.

**Logout-Redirect:** Nach Logout von syst, mand und cust immer `redirect()->route('home')` (cust/mand-Loginseite mit Login-Modal) — nicht auf rollenspezifische Loginseiten.

**syst is_primary:** Primäre System-User (`is_primary = 1`) können nicht gelöscht werden — weder von primären noch von nicht-primären syst-Usern. Ein **primärer** syst-User kann **nicht-primäre** syst-User löschen (nicht aber andere primäre und nicht sich selbst). **Nicht-primäre** syst-User können **niemanden** löschen. `is_primary` wird bei der Einladung festgelegt (Checkbox im Einladungsformular, nur sichtbar für primäre syst-User — serverseitig erzwungen). Nach der Registrierung nur per direkter DB-Änderung modifizierbar. Session-Key: `_is_primary` (bool). Anzeige in `system/profile.blade.php`: read-only. Löschen-Button in syst-Userliste (`system/users/index.blade.php`) nur sichtbar, wenn der eingeloggte User primary ist UND das Ziel nicht-primary ist UND es nicht der eigene Account ist: `@if(session('_is_primary') && ! $user->is_primary && $user->syst_id !== session('_syst_id'))` (korrigiert 29.06. — zuvor war die View-Bedingung fehlerhaft und zeigte den Button auch non-primaries).

---

## 2. Tech Stack

| Layer | Technology |
|---|---|
| Framework | Laravel 13 |
| Language | PHP 8.5 |
| Templating | Blade |
| Frontend JS | Alpine.js |
| CSS | Tailwind CSS |
| Database | MariaDB (4 separate DBs) |
| Passkeys | web-auth/webauthn-lib 5.3.5 (direkt, nicht laragear/webauthn — abandoned; nicht laravel/passkeys — pre-stable) |
| Build tool | Vite |
| Package manager | Composer / npm |

---

## 3. Server Configuration

- **Document root:** `/public`
- **Server-Pfad:** `/var/www/vhosts/u14bc1w8.host159.alfahosting-server.de/fotos.martinwagner.de/`
- **Deployment:** FTP-Upload (WinSCP) der geänderten Dateien
- **SSH:** PuTTY, User `u14bc1w8`
- **Composer auf Server:** `php /lib64/plesk-9.0/composer.phar`
- **Post-Deploy (IMMER nach Änderungen an Controllern, Routen, Config):**
  ```bash
  php artisan route:clear
  php artisan config:clear
  php artisan view:clear
  php artisan cache:clear
  ```
  Veralteter Cache kann Daten korrumpieren — bestätigt durch Hash-Korruption nach E-Mail-Änderung (18.06.). Immer alle vier Befehle ausführen, nicht nur einzelne.
- **Mail:** SMTP `host159.alfahosting-server.de:587`, `MAIL_ENCRYPTION=tls` (Port 465/smtps timeout). `MAIL_FROM_NAME="Fotogalerie"`.
- Kein CI/CD — alles manuell.

---

## 4. Databases

Vier separate MariaDB-Datenbanken, jede mit eigener Laravel-Connection und eigenem DB-User (`DB_<CONNNAME>_*` in `.env`). **Keine Cross-DB-Joins** — Verknüpfung ausschließlich über `mand_id` / `cust_id` / `fo_id` in PHP.

> **Echte Foreign Keys:** `fotodb.activity_subgroup.ag_id → fotodb.activity_group.ag_id` sowie `userdb.cust_pcode.mand_id → userdb.mand_user.mand_id`. Alle anderen Verknüpfungen sind logisch, ohne FK-Constraint.

### 4.1 `userdb` — User Management
Connection: `userdb`

| Table | PK | Purpose |
|---|---|---|
| `syst_user` | `syst_id` | System-Admin-Accounts (inkl. `is_primary` TINYINT(1) Default 0 — primäre Admins können nicht gelöscht werden) |
| `mand_user` | `mand_id` | Galerist:in-Accounts (inkl. `mand_pw_hash`, `mand_cust_2fa`, `mand_2fa_opt_in`, `has_public_content`, `active`, `valid_to` DATE NULL (Option bei Zahlungsausfall — Nutzung noch offen), `ds_accepted_at`, `ds_version`, `upload_terms_accepted_at`, `upload_terms_version`, `show_welcome` TINYINT(1) Default 1) |
| `cust_user` | `cust_id` | Mitglieder-Accounts (inkl. `cust_2fa_opt_in` TINYINT(1) Default 0 — Funktion noch zu klären, `ds_accepted_at`, `ds_version`, `upload_terms_accepted_at`, `upload_terms_version` — Felder strukturell vorhanden, Upload-Bedingungen derzeit für cust nicht relevant, `show_welcome` TINYINT(1) Default 1) |
| `cust_pcode` | `pcode_id` | Sicherheitsstufe je Mitglied+Mandant. Spalten: `cust_passcode` varchar(255) (= sec_level des cust bei diesem mand, „enthält die Ziffer des Securitylevel"), `cust_alias`, `pcode_prefstat`, `mand_id`, `cust_id`, `cust_mailrequest`, `mand_sort_date` DATE (Funktion noch zu klären) |
| `invite` | `inv_id` | Einladungen/Reset-Tokens für syst/mand/cust (`inv_type`: register\|pw_reset\|email_change; `inv_user_type`: syst\|mand\|cust; `inv_email` dient bei email_change als Speicher für neue Adresse; `is_primary` TINYINT(1) Default 0 — nur für syst-Einladungen relevant). **Login-Cleanup (NEU 29.07.):** abgelaufene Einträge (`expires_at < now()`) werden bei jedem cust/mand-Login zusätzlich zu den Sessions bereinigt (`LoginSessionBuilder::cleanupExpiredRecords()`) |
| `passkey` | `pk_id` | WebAuthn-Credentials (`user_type`, `user_id`, `credential_id`, `public_key`, `sign_count`, `device_name`, `last_used_at`). **`sign_count` faktisch wirkungslos** (Inkonsistenz #12) — wird geschrieben, aber nie wieder ausgelesen/geprüft; kein WebAuthn-Rollback-Schutz gegen geklonte Credentials. `last_used_at` ist die verlässliche Größe für Nutzungs-Tracking |
| `passkey_dismissed` | `pd_id` | „Nie wieder fragen"-Einträge je User+OS+Gerät (`user_type`, `user_id`, `os`: win\|andr\|ios, `ua_hash`) |
| `cust_invite` | `invite_id` | **Veraltetes Relikt** — Struktur identisch zu `sessiondb.cust_invite`, wird nicht verwendet, aus Vorsicht nicht gelöscht |
| `policy_versions` | `pv_key` | Aktuelle Policy-Versionsnummern (`pv_key`: ds_version\|upload_version, `pv_value`, `updated_at`) — wird von syst per UI erhöht, triggert Popup bei mand/cust beim nächsten Login |

### 4.2 `sessiondb` — Sessions, Kurzzeit-Kennwörter, 2FA
Connection: `sessiondb`

| Table | PK | Purpose |
|---|---|---|
| `session` | `sess_id` | Eine Zeile pro aktiver Session (anon + authentifiziert), `sess_token` für Lookups, `payload` (JSON). Zusätzlich Spalten `user_type`, `cust_id`, `mand_id`, `syst_id` — werden erst NACH dem finalen `write()` per `App::terminating()`-Callback nachträglich befüllt (siehe Abschnitt 10d) |
| `pw_list` | `pwlist_id` | Bis zu 6 zeitlich begrenzte Kurzzeit-Kennwörter je Mandant (`pw1`–`pw6`, AES-verschlüsselt, `valid_from`/`valid_until`) |
| `twofa_code` | `tfa_id` | 2FA-Codes (6-stellig, `tfa_purpose`: login\|pw_change\|critical, `tfa_expires_at`, `tfa_used`). **Login-Cleanup (NEU 29.07.):** abgelaufene Codes (`tfa_expires_at < now()`) werden bei jedem cust/mand-Login mitbereinigt |
| `cust_invite` | `invite_id` | **Führende Tabelle** für Mitglieder-Einladungen (`mand_id`, `cust_email`, `cust_alias`, `sec_level`, `token`, `expires_at`, `used`). **Login-Cleanup (NEU 29.07.):** abgelaufene Einladungen werden bei jedem cust/mand-Login mitbereinigt — `userdb.cust_invite` (Relikt) bewusst NICHT einbezogen |
| `trusted_device` | `td_id` | **NEU 10.–17.07.** „Gerät als sicher merken" für vollständigen Auto-Login (mand+cust). Spalten: `user_type` (enum mand\|cust), `user_id`, `token_hash` (SHA-256, UNIQUE), `ua_hash`, `device_label`, `last_used_at`, `expires_at`, `created_at`. Bewusst in `sessiondb` statt `userdb` (Sicherheitstrennung). **Login-Cleanup (NEU 29.07.):** abgelaufene Einträge werden bei jedem cust/mand-Login mitbereinigt (bisher nur bei Logout) |
| `share_link` | `sl_id` | **NEU 01.08.** Kurzcode-basiertes anon-Login-Link-System. Spalten: `code` (varchar(10), UNIQUE, 7-stelliger alphanumerischer Zufallscode), `mand_id`, `sec_level`, `created_at`. UNIQUE-Index auf (`mand_id`, `sec_level`) — pro mand+Stufe genau ein stabiler Code, erzeugt per `firstOrCreate()` in `MandantPwListController::edit()`. Invalidierung (DELETE) erfolgt NUR bei tatsächlicher Passwort-Änderung dieser Stufe (`update()`-Alt/Neu-Vergleich) — Erstanlage einer Stufe zählt nicht als Änderung. Details Abschnitt 10f |

### 4.3 `fotodb` — Foto-Content
Connection: `fotodb`

| Table | PK | Purpose |
|---|---|---|
| `foto_obj` | `fo_id` | Foto/Video-Metadaten (`fo_filename`, `fo_title`, `fo_subtitle`, `fo_text`, `mand_id`, `fo_sec_level` TINYINT, `fo_is_video` bool, `fo_datetime`, `db_saved`, `fo_filepath`, `fo_prefstat`) |
| `activity_group` | `ag_id` | Oberste Content-Ebene je Mandant (`ag_title`, `ag_subtitle`, `ag_text`, `mand_id`, `ag_sec_level` TINYINT, `ag_prefstat`, `ag_sort_date`) |
| `activity_subgroup` | `asg_id` | Untergruppe (FK → `activity_group.ag_id`), gleiche Struktur wie AG, zusätzlich `asg_public`, `asg_sec_level` TINYINT |
| `ag_fo_context` | — | Pivot: Activity Group ↔ Foto (`ag_is_banner`) |
| `asg_fo_context` | — | Pivot: Activity Subgroup ↔ Foto |
| `mand_profile` | `mp_id` | **Cust-sichtbare Profilseite** des Mandanten (Selbstvorstellung) — gehört zum Foto-Content, nicht zur Eigenverwaltung. Schema korrekt: `mp_name`/`mp_title` varchar(255), `mp_text` text, `mp_title_start`/`mp_subtitle_start` varchar(255). Tabelle leer. Keine Schema-Korrektur mehr nötig. |
| `mp_fo_context` | — | Pivot: Mandant-Profil ↔ Foto |

### 4.4 `fotoblobdb` — Binärdaten Stufe 6
Connection: `fotoblobdb`

| Table | PK | Purpose |
|---|---|---|
| `foto_obj` | `fod_id` | BLOB-Speicher (`fod_obj`) für Fotos/Videos der Sicherheitsstufe 6, verknüpft über `fo_id` |

**Wichtig:** Schema ist vollständig vordefiniert, außerhalb von Laravel verwaltet. Keine Laravel-Migrationen für Domain-Tabellen — DDL direkt per SSH/phpMyAdmin auf dem Server. `database/migrations/` enthält nur Breeze-Reste.

---

## 5. MVC-Struktur (vollständig, Stand 19.07.2026)

> Diese Übersicht ist gegen die Dateiliste vom 19.07. und den tatsächlichen
> Code-Stand verifiziert. Maßgeblich bleibt der implementierte Code; bei
> Abweichung gilt der Code.

### Architektur-Hinweis
Seit Phase 6 existiert eine **Service-Schicht** (`app/Services/<DB>/`). Controller
delegieren DB-Logik an Services; je Datenbank ein Service-Namespace. Diese Schicht
fehlte in der bisherigen Doku komplett.

```
app/
├── Console/Commands/
│   └── CleanExpiredSessions.php     # sessions:clean — implementiert: handle() löscht expires_at < now()
├── Extensions/
│   └── SessionDbSessionHandler.php  # Custom Session-Driver (sess_id PK)
├── Http/
│   ├── Controllers/
│   │   ├── Auth/                    # Breeze-Reste (8 Controller, größtenteils ungenutzt)
│   │   ├── FotoDB/
│   │   │   └── FotoDbController.php        # Phase-7-Gerüst (abstrakte Basisklasse, keine Methoden)
│   │   ├── FotoBlobDb/
│   │   │   └── FotoBlobDbController.php    # Phase-7-Gerüst (abstrakte Basisklasse, keine Methoden)
│   │   ├── SessionDb/
│   │   │   ├── MandantPwListController.php # Kurzzeit-Kennwörter pw1–pw6
│   │   │   └── SessionDbController.php     # Phase-7-Gerüst (abstrakte Basisklasse, keine Methoden)
│   │   ├── Passkey/
│   │   │   ├── MandPasskeyController.php
│   │   │   └── CustPasskeyController.php
│   │   ├── UserDb/
│   │   │   ├── SystemLoginController.php
│   │   │   ├── SystemDashboardController.php
│   │   │   ├── SystemUserController.php
│   │   │   ├── SystemMandantController.php
│   │   │   ├── SystemProfileController.php
│   │   │   ├── SystemPolicyController.php
│   │   │   ├── MandantLoginController.php
│   │   │   ├── MandantDashboardController.php
│   │   │   ├── MandantSelfController.php
│   │   │   ├── MandantCustController.php
│   │   │   ├── MandPasswordResetController.php
│   │   │   ├── CustLoginController.php
│   │   │   ├── CustRegisterController.php
│   │   │   ├── CustDashboardController.php
│   │   │   ├── CustSelfController.php
│   │   │   ├── CustPasswordResetController.php
│   │   │   └── UserDbController.php        # Phase-7-Gerüst (abstrakte Basisklasse, keine Methoden)
│   │   ├── DatenschutzController.php
│   │   ├── PolicyController.php
│   │   ├── WelcomeScreenController.php
│   │   └── FaqController.php
│   ├── Middleware/
│   │   ├── NoIndexHeader.php
│   │   ├── SessionHijackProtection.php
│   │   ├── SessionIdleTimeout.php
│   │   ├── ValidateUserExists.php
│   │   ├── AutoLoginTrustedDevice.php  # NEU 17.07. — vollständiger Auto-Login ohne Passwort
│   │   ├── RequireRole.php
│   │   ├── SystUserCheck.php
│   │   ├── MandantActiveCheck.php
│   │   ├── CheckPolicyVersion.php
│   │   └── CheckWelcome.php
│   └── Requests/
│       ├── Auth/LoginRequest.php
│       └── ProfileUpdateRequest.php
├── Models/
│   ├── FotoDB/      (ActivityGroup, ActivitySubgroup, FotoObj, MandProfile,
│   │                 AgFoContext, AsgFoContext, MpFoContext, FotoDbModel)
│   ├── FotoBlobDb/  (FotoObjDb, FotoBlobDbModel)
│   ├── SessionDb/   (Session, PwList, CustInvite, TwofaCode, TrustedDevice, SessionDbModel)
│   │                # TrustedDevice NEU 10.07. (Model zu sessiondb.trusted_device)
│   └── UserDb/      (SystUser, MandUser, CustUser, CustPcode, Invite,
│                     Passkey, PasskeyDismissed, PolicyVersion, UserDbModel)
├── Mail/
│   ├── InviteMail.php
│   ├── CustInviteMail.php
│   ├── CustAccountDeletedMail.php
│   ├── MandAccountDeletedMail.php
│   ├── EmailChangeMail.php
│   ├── TwoFactorCodeMail.php
│   └── TrustedDeviceAddedMail.php  # NEU 10.07. — Benachrichtigung bei neuem Trusted Device
│   # ModerationMail.php — geplant Phase 7, noch nicht vorhanden
├── Services/
│   ├── FotoDB/FotoDbService.php           # Phase-7-Gerüst (abstrakte Basisklasse, keine Methoden)
│   ├── FotoBlobDb/FotoBlobDbService.php   # Phase-7-Gerüst (abstrakte Basisklasse, keine Methoden)
│   ├── SessionDb/
│   │   ├── SessionDbService.php           # Basisklasse für TwofaService, keine eigenen Methoden
│   │   ├── SessionIntegrityService.php    # genau ein *_id in Session
│   │   └── TwofaService.php               # generate(), verify(), generateCode(), verifyCode(), purgeExpired()
│   ├── UserDb/
│   │   ├── UserDbService.php              # Phase-7-Gerüst (abstrakte Basisklasse, keine Methoden)
│   │   └── LoginSessionBuilder.php        # NEU 17.07. — buildForCust()/buildForMand(),
│   │                                       # zentralisierter Session-Aufbau (Passwort-Login UND Auto-Login)
│   └── Passkey/
│       ├── PasskeyRepository.php
│       ├── PasskeySessionStorage.php
│       └── PasskeyUserEntityRepository.php
├── Providers/      (AppServiceProvider, PasskeyServiceProvider)
├── View/Components/ (AppLayout, GuestLayout)
└── helpers.php     # genitivName(), detectOsPlatform(), detectBrowser(),
                     # trustedDeviceCookieName(), checkTrustedDevice(), issueTrustedDeviceCookie(),
                     # guessDeviceLabel(), revokeTrustedDevices() — alle NEU 10.–17.07.

resources/views/components/
└── logout-button.blade.php  # NEU 17.07. — ersetzt 11 duplizierte Logout-Blöcke, bedingter
                              # Trusted-Device-Löschdialog (siehe Abschnitt 10d)

config/
└── trusted_device.php  # NEU 10.07. — 'days' => env('TRUSTED_DEVICE_DAYS', 7)

routes/
├── web.php       # Route 'home' (GET /) → auth/login-modal.blade.php
├── auth.php      # Breeze-Auth-Routen
├── system.php    # Prefix /system, Rolle syst
├── mandant.php   # Prefix /mandant, Rolle mand
├── customer.php  # Prefix /customer, Rolle cust/anon
├── mail.php
└── console.php
```

**Korrekturen gegenüber der bisherigen Doku:**
- `SessionIntegrityService` ist ein **Service** (`Services/SessionDb/`), keine Middleware.
- `MandantPwListController` liegt unter `Controllers/SessionDb/`, nicht `UserDb/`.
- Service-Schicht, `SystUserCheck`, `EmailChangeMail`, `TwofaCode`-Model,
  `PolicyVersion`-Model, `CleanExpiredSessions`, `routes/auth.php` + `routes/mail.php`
  waren bisher nicht dokumentiert.
- Phase-7-Gerüst-Controller (FotoDB/FotoBlobDb/SessionDb/UserDb) existieren bereits
  als Dateien — der frühere Doku-Satz "keine Controller für fotodb-Content" bezog
  sich auf fehlende **Funktionalität**, nicht auf fehlende Dateien.

---

## 6. Coding Rules

- **Docblock-Header** auf jeder PHP-Datei: Dateiname, Version (fortlaufend hochzählen bei jeder Änderung), Datum, Funktionsbeschreibung, CALLS, DB ACCESS, SESSION ACCESS.
- **Keine Laravel-Migrationen** für Domain-Tabellen — Schema-Änderungen per DDL direkt auf dem Server.
- **Custom Primary Keys** überall (`mand_id`, `cust_id`, `fo_id`, `sess_id`, `pk_id`, `pd_id` etc.) — nie Laravels Standard-`id`. Explizit `protected $primaryKey` setzen.
- **`public $timestamps = false`** auf allen Domain-Models.
- **Base-Model-Pattern:** `UserDbModel` → `userdb`, `SessionDbModel` → `sessiondb`, `FotoDbModel` → `fotodb`, `FotoBlobDbModel` → `fotoblobdb`. Alle Domain-Models erben davon.
- **Helper-Funktionen** in `app/helpers.php`, registriert in `composer.json` → `autoload.files`, eingebunden per `use function <name>;` in Controllern. Mit `function_exists()`-Guard.
- **PowerShell:** kein `&&`, stattdessen `;` zwischen Befehlen.

---

## 7. Sprache & UI-Terminologie

- Alle UI-Texte und Code-Kommentare auf Deutsch.
- `cust` → UI: **„Mitglied/Mitglieder"** (nie „Kunde/Kunden")
- `mand` → UI: **„Galerist:in"** (Code-Variable bleibt `mand`)
- „Passcode/Passcodes/Passcodeliste" → UI: **„Kurzzeit-Kennwort/Kurzzeit-Kennwörter/Kurzzeit-Kennwortliste"**
- „Fotosite" / „Fotosite V8" → UI: **„Fotogalerie"** (gilt für Browser-Titel, Footer, Mail-Texte; interne Bezeichner wie Git-Repo-Name, `.env`-Variablen bleiben „Fotosite V08")
- Code-Kommentare: `syst`, `mand`, `cust` bleiben als technische Bezeichner.

---

## 8. Passwort-Policy

> **KORREKTUR 29.06. (historisch, seit 31.07. überholt):** Die damals dokumentierten syst-Anforderungen (14 Zeichen, Sonderzeichen, HIBP) entsprachen nicht der Implementierung — die Controller erzwangen tatsächlich nur `min:12`. Die View-Hinweistexte wurden am 29.06. controller-konform auf „Mindestens 12 Zeichen" korrigiert.
>
> **NEU 31.07. (aktueller Stand, zum Zeitpunkt dieses Doku-Standes noch UNCOMMITTED):** Die syst-Passwort-Policy wurde bewusst verschärft — jetzt `Password::min(20)->mixedCase()->numbers()->symbols()` in `SystemUserController` (`handleRegister()`, `handlePasswordReset()`) und `SystemProfileController::updatePassword()`. Die Tabelle unten gibt den neuen Stand wieder. **Wichtig: Die Policy wird zusätzlich beim LOGIN geprüft** (`SystemLoginController::handleLogin()`, nach korrektem `Hash::check()`, vor 2FA-Versand) — ein syst mit einem alten, nicht mehr konformen Passwort wird beim Login mit korrektem Passwort trotzdem hart abgewiesen. Da es **keinen Self-Service-Passwort-Reset für syst** gibt (neues Passwort nur durch einen anderen syst-Admin über die Benutzerverwaltung), verweist die Fehlermeldung explizit auf diesen Weg. Notfall-Prozedere bei Selbstaussperrung: siehe Notfall_Start.md Abschnitt 6a. Details zum Login-Hard-Block: Abschnitt 8a.

| Kriterium | syst | mand | cust |
|---|---|---|---|
| Mindestlänge | **20 Zeichen** | 12 Zeichen | 10 Zeichen |
| Groß-/Kleinbuchstaben | ✅ | ✅ | ✅ |
| Ziffern | ✅ | ✅ | ✅ |
| Sonderzeichen | ✅ | ✅ | optional |
| HIBP (`uncompromised()`) | — | ✅ | — |
| Username im PW verboten | — | — | — |
| Policy auch beim Login geprüft | ✅ (NEU 31.07., Hard-Block) | — | — |

> Hinweis: cust/mand-Policy unverändert. syst ist als einzige Rolle strenger als mand, weil syst-Accounts plattformweiten Zugriff haben und kein HIBP-Check vorgesehen ist (Kompensation über Länge/Komplexität + jetzt zusätzlich Login-Zeit-Durchsetzung).

**Deutsche Validierungs-Fehlermeldungen (29.06., syst-Teil erweitert 31.07.):** Alle sieben Passwort-verarbeitenden Controller (`CustPasswordResetController`, `MandPasswordResetController`, `CustSelfController`, `MandantSelfController`, `SystemUserController` [2 Stellen], `SystemProfileController`, `SystemMandantController`) erhielten `messages`-Arrays mit deutschen Texten:
- `password.confirmed` → „Die eingegebenen Passwörter stimmen nicht überein."
- cust/mand/`SystemMandantController`: `password.min` / `.mixed_case` / `.numbers` / `.symbols` / `.uncompromised` → „Das Passwort erfüllt nicht die Mindestanforderungen."
- syst (`SystemUserController`, `SystemProfileController`, NEU 31.07.): eigene, spezifische Texte je Regel — `password.min` → „Das Passwort muss mindestens 20 Zeichen lang sein.", `password.mixed` → „...Groß- als auch Kleinbuchstaben...", `password.numbers` → „...mindestens eine Ziffer...", `password.symbols` → „...mindestens ein Sonderzeichen..."
- `current_password` → „Das eingegebene Passwort ist nicht korrekt."

Zuvor erschienen Laravels englische Standard-Meldungen (kein `lang/`-Verzeichnis vorhanden). Die Anforderungs-Hinweistexte sind in allen PW-Dialogen vorhanden und controller-konform (syst-Views: „Mindestens 20 Zeichen, mit Groß- und Kleinbuchstaben, einer Ziffer und einem Sonderzeichen.", NEU 31.07.).

`pw_list`-Kurzzeit-Kennwörter (pw1–pw6): min. 8 Zeichen, AES-verschlüsselt (nicht gehasht — mand muss Klartext einsehen können, um sie zu teilen). **Trim-Asymmetrie beachten:** pw1–pw6 werden beim Speichern durch Laravels `TrimStrings`-Middleware getrimmt (Feldname nicht exempt), das eingegebene `password` beim Anon-Login-Vergleich jedoch nicht (`password` ist in `$except`) → Anon-Login trimmt das eingegebene Passwort daher explizit per Code (`CustLoginController`, Tag `touch_and_trim_ok`).

---

## 8a. Login-Sicherheit — NEU, 29.–31.07.2026

### Einheitliche IP-basierte Login-Sperre (`login_lockout_ip_based_ok`)
Ersetzt die bisherigen getrennten RateLimiter (`cust-login`, `cust-anon-login`, `login-2fa` in `AppServiceProvider`) durch einen gemeinsamen, **rollenübergreifenden** Mechanismus in `app/helpers.php`:
- `loginThrottleKey($request)` → EIN Schlüssel pro IP (`login-throttle:<ip>`), gemeinsam für cust/mand/syst
- `checkLoginThrottle($request)` — prüft vor jedem Login-Versuch, ob die Sperre aktiv ist; verlängert bei fortgesetztem Angriff zusätzlich die Sperrzeit und loggt dabei in den Kanal `login_attacks` (NEU 31.07., siehe unten)
- `recordFailedLoginAttempt($request)` — zählt einen echten Fehlversuch (nicht jeden Request), meldet nur, wenn DIESER Versuch die Sperre auslöst
- `clearLoginThrottle($request)` — Reset bei erfolgreichem Login

Konfigurierbar über `LOGIN_LOCKOUT_MAX_ATTEMPTS`/`LOGIN_LOCKOUT_MINUTES` (`.env`, Default 5/5), gespiegelt in `config/app.php`. Deaktiviert bei `DEBUGMODE=true`. **mand- und syst-Login hatten zuvor überhaupt keine Drosselung** — jetzt erstmals abgedeckt. `password-reset`- und `email-verify`-RateLimiter (weiterhin in `AppServiceProvider`) unverändert.

### Log-Kanal `login_attacks` (NEU 31.07.)
Neuer `daily`-Kanal in `config/logging.php` (`storage/logs/login-attacks.log`, 14 Tage Rotation, Level `warning`). `checkLoginThrottle()` loggt hinein bei aktiver/verlängerter Sperre (`ip`, `path`). Der Standard-Stack-Kanal (`LOG_STACK`) wurde ebenfalls auf `daily`/14 Tage umgestellt (bisher `single`, unbegrenztes Wachstum) — Änderung nur in der Server-`.env` (`LOG_STACK=daily`), `config/logging.php`-Default (`'daily'`) hatte bereits `days => 14`.

### Honeypot-Routen (NEU 31.07., `honeypot_login_attacks_log_ok`)
Dynamisch registrierte Köder-Routen für typische Scanner-Ziele (`wp-login.php`, `wp-admin/*`, `xmlrpc.php`, `admin`, `phpmyadmin`, `.env`), definiert in `storage/app/private/honeypot_paths.txt` — **unversioniert, per WinSCP editierbar**, analog zu `erlaeuterung.md`/`willkommen_*.md` (Abschnitt 10a). `registerHoneypotRoutes()` (`app/helpers.php`) liest die Datei bei jedem Request-Bootstrap (`routes/web.php`, ganz am Ende, außerhalb jeder Middleware-Gruppe) und registriert `Route::any(...)` auf `HoneypotController::handle()`. Jeder Treffer ruft `triggerHoneypotLockout($request)` auf: verhängt sofort eine volle Sperre (`config('app.login_lockout_max_attempts')`-fach `RateLimiter::hit()`) über **denselben** IP-Schlüssel wie die reguläre Login-Sperre — wirkt also rollenübergreifend — für `HONEYPOT_LOCKOUT_MINUTES` Minuten (Default 60, `.env`, `config/app.php` → `honeypot_lockout_minutes`), loggt in `login_attacks` (`ip`, `path`, `method`, `user_agent`) und liefert von außen nicht unterscheidbar von echtem Content ein Standard-404. Deaktiviert bei `DEBUGMODE=true`. Fehlt `honeypot_paths.txt`, wird nur ein Standard-`Log::warning()` geschrieben (kein Honeypot-Kanal) und keine Route registriert.

### syst-Login-Hard-Block bei veralteter Policy (NEU 31.07., uncommitted)
Siehe Abschnitt 8 — `SystemLoginController::handleLogin()` prüft nach korrektem `Hash::check()` zusätzlich `Password::min(20)->mixedCase()->numbers()->symbols()` gegen das eingegebene (korrekte) Klartext-Passwort. Bei Nichterfüllung: Hard-Block mit Verweis auf Admin-Reset-Weg, **zählt NICHT** als Fehlversuch für die IP-Sperre (kein Angriffsindiz, das Passwort war korrekt). Notfall-SQL-Weg bei Selbstaussperrung: Notfall_Start.md Abschnitt 6a.

---

## 9. Passkeys (WebAuthn) — Status: Implementiert, gründlicher Test steht noch aus

> **Korrektur 19.07.:** Frühere Doku-Stände bezeichneten Phase 6 als „abgeschlossen". Das ist zu stark formuliert: Die Passkey-Funktionalität (Registrierung, Login, Verwaltung, Prompt-Logik) ist **technisch vollständig implementiert**, aber bislang nur punktuell und nicht systematisch getestet (siehe „Getestet"/„Ausstehend" unten — das deckt nicht alle Kombinationen aus Rolle × Gerät × Browser × Grenzfall ab). Ein **gründlicher Gesamttest der gesamten Passkey-Funktionalität** — nicht nur ein iOS-spezifischer WebAuthn-Test — ist der **nächste anstehende Schritt** und soll im neuen Chat erfolgen. Siehe auch Abschnitt 16 und Inkonsistenzen.md #11.

### Implementiert
- `web-auth/webauthn-lib 5.3.5` direkt integriert (Registrierung + Login für `mand` und `cust`)
- `userVerification: required`, `authenticatorAttachment: platform`, `residentKey: required`
- Passkey-Verwaltung (`/mandant/passkeys`, `/customer/passkeys`): registrieren, umbenennen, löschen, Geräteliste mit `last_used_at`
- **Plattform-/Browser-Erkennung:** `detectOsPlatform()` (win/andr/ios/unknown) und `detectBrowser()` (chrome/firefox/edge/safari/samsung/unknown) in `app/helpers.php`, Session-Keys `_passkey_os`, `_passkey_browser`, `_passkey_uahash`
- **Kontextabhängige Hinweistexte** auf Passkey-Verwaltungsseiten — je OS+Browser-Kombination, nutzerorientiert (wo gilt der Passkey, nicht wo wird er gespeichert). **NEU 30.07. (`passkey_hints_markdown_ok`):** Die Texte sind nicht mehr hartkodiert in den Blade-Views, sondern liegen in vier editierbaren Markdown-Dateien `storage/app/private/passkey_{allgemein,spezifisch}_{cust,mand}.md` (unversioniert, per WinSCP editierbar, analog zu `erlaeuterung.md`). `CustPasskeyController`/`MandPasskeyController::index()` leiten aus OS/Browser einen Tag ab (`STANDARD`/`CHROME_IOS` für „allgemein", `EDGE`/`FIREFOX_WIN`/`FIREFOX_ANDROID`/`CHROME`/`IOS`/`UNKNOWN` für „spezifisch") und rendern den passenden `<!--TAG-->...<!--/TAG-->`-Block per neuer Helper-Funktion `renderMarkdownVariant()` (`app/helpers.php`) zu HTML.
- **Passkey-Prompt-Logik:** Modal (mand) / Banner (cust) erscheint einmalig nach Login, wenn kein Passkey für dieses OS existiert und kein `passkey_dismissed`-Eintrag (User+OS+Gerät) vorliegt. Buttons: „Einrichten" / „Nie wieder fragen" / „Später". **Hinweis 20.06.:** Die neue Willkommensseite (`show_welcome`, Abschnitt 10c) ist generisches Onboarding (Markdown-Text + „Gelesen") — sie enthält noch KEINEN Passkey-Einrichtungslink. Die ursprünglich geplante strukturelle Lösung des Cust-Passkey-Hinweises über die Welcome-Seite ist damit weiterhin offen, falls gewünscht müsste der Link nachträglich in `willkommen_cust.md` ergänzt oder die Logik im `WelcomeScreenController` erweitert werden.
- Getestet (punktuell, nicht systematisch): Windows (Hello aktiv/inaktiv, Chrome/Firefox/Edge), Android (Handy+Tablet, Chrome mit Google-Sync, Firefox lokal), cust-Banner, Grenzfälle (mehrere Rollen auf einem Windows-Konto)
- **Ausstehend — NÄCHSTER SCHRITT (neuer Chat):** Gründlicher, systematischer Gesamttest der Passkey-Funktionalität über alle Rollen (mand+cust), Geräte (Windows/Android/iOS) und Browser hinweg — inkl. iOS, wo bislang kein abgeschlossener WebAuthn-Test dokumentiert ist (iPhone SE 2020 vorhanden, wurde aber bereits für andere Tests wie Button-Feedback/Auto-Login genutzt, siehe Abschnitt 10d). Kein reiner iOS-Test, sondern vollständiger Testdurchlauf der gesamten Passkey-Funktionalität.

### Wichtige Erkenntnisse
- Passkey ist an die **Windows-Anmeldung** gebunden (nicht an Fotosite-Account direkt) — Login funktioniert nur mit demselben Windows-Konto wie bei der Registrierung
- Chrome: Sync über Google-Konto (Windows ↔ Android)
- Firefox: lokal, kein Sync — pro Gerät eigener Passkey empfohlen
- Edge: wie Windows Hello, lokal, kein Sync (zusätzlich schlechte Datenschutz-Reputation, daher nicht aktiv empfohlen)
- iOS: iCloud Keychain, alle Browser nutzen denselben Speicher (Apple erzwingt das)
- `passkey_dismissed` schlüsselt auf `(user_type, user_id, os, ua_hash)` — pro Gerät+Browser+OS-Kombination eigener Dismiss-Status
- **`passkey.sign_count` faktisch wirkungslos** (gefunden 30.07., Inkonsistenzen.md #12) — kein akuter Sicherheitsmangel, aber entgangener Rollback-Schutz gegen geklonte Credentials, bewusst nicht behoben. Details Inkonsistenzen.md #12

---

## 10. Passwort-Reset (cust + mand) — NEU, Phase 6

Analog zum bestehenden `syst`-Reset, über `userdb.invite` (`inv_type='pw_reset'`).

| Schritt | Route (cust/mand) | Aktion |
|---|---|---|
| 1 | `GET .../password-reset` | Formular: Email eingeben |
| 2 | `POST .../password-reset` | Invite-Token (24h gültig) erzeugen, Mail senden. **Immer identische neutrale Antwort** — keine Enumeration |
| 3 | `GET .../password-reset/{token}` | Formular: neues Passwort |
| 4 | `POST .../password-reset/{token}` | Passwort setzen, Invite löschen, **stiller** Redirect (keine Erfolgsmeldung — Sicherheit) |

- Rate-Limiting: `throttle:3,10` auf POST-Routen
- cust: min. 10 Zeichen; mand: min. 12 Zeichen + mixedCase + numbers + symbols + `uncompromised()`
- „Passwort vergessen?"-Link in `auth/login-modal.blade.php` (Tab „Registriert" für cust, mand-Bereich für mand)
- `emails/invite.blade.php` unterscheidet im `pw_reset`-Zweig: „System-Account" (syst) / „Mitglieds-Konto" (cust) / „Galerist:innen-Konto" (mand)

---

## 10a. Datenschutz & Einwilligung — NEU, 16.06.

**Zwei Dokumente** (konstante Dateinamen, Datum nur im Dokumentinhalt) unter `storage/app/private/`:
- `datenschutzerklaerung.pdf` — bestätigungspflichtig für cust + mand
- `upload_bedingungen.pdf` — zusätzlich bestätigungspflichtig für mand
- `erlaeuterung.md` — umgangssprachliche Vorbemerkung (unversioniert, per WinSCP editierbar)

**Erläuterungsseite:** `erlaeuterung.md` wird zur Laufzeit gelesen und via `league/commonmark` (2.8.2) zu HTML gerendert. Rollenabhängige `<!--MAND-->...<!--/MAND-->`-Blöcke: bei mand sichtbar, bei cust/anon herausgeschnitten (Regex, Leerzeichen-tolerant). Styling per `<style>`-Block in der Blade-View (kein `@tailwindcss/typography` installiert).

**Routen** (öffentlich, OHNE Login — für Einladungsempfänger, anon, cust, mand). URL-Segment `/ds/` statt `/datenschutz/`, Route-Namen bleiben `customer.datenschutz.*`:
- `GET /customer/ds/erlaeuterung`
- `GET /customer/ds/erklaerung-pdf` (PDF inline)
- `GET /customer/ds/upload-bedingungen-pdf` (PDF inline)
- `POST /customer/ds/hinweis-ok` (ehemals anon-Popup-Bestätigung, Session-Flag — **Route seit 01.08. unerreicht**, siehe Korrektur unten)

**Einwilligung:**
- cust: Pflicht-Checkbox bei Registrierung → `cust_user.ds_accepted_at` + `ds_version`. Zusätzlich `upload_terms_accepted_at`/`upload_terms_version` in `cust_user` strukturell vorhanden (Policy-Versionierungssystem) — der Inhalt der Upload-Bedingungen ist für cust derzeit nicht relevant, die Felder müssen aber existieren.
- mand: zwei Pflicht-Checkboxen → `mand_user.ds_accepted_at`/`ds_version` + `upload_terms_accepted_at`/`upload_terms_version`
- anon: **Korrektur 01.08. — Mechanismus entfernt.** Bis 31.07. zeigte `customer/content.blade.php` nach korrektem Passcode ein Popup (Hinweis + Link, Kenntnisnahme via Session-Flag `_ds_hinweis_gezeigt`, KEINE DB-Speicherung). Im Zuge des Kurzcode-Share-Link-Features (Abschnitt 10f) wurde dieses Popup für **beide** anon-Zugangswege (Passwort-Eingabe UND Link-Login) ersatzlos entfernt — der Hinweis soll künftig über die Content-Seiten selbst erreichbar sein (Phase 7, noch nicht umgesetzt). `DatenschutzController::hinweisOk()`, Route `customer.datenschutz.hinweis-ok` und das Session-Flag `_ds_hinweis_gezeigt` bleiben im Code unangetastet stehen, sind aber seither unerreicht (bewusst nicht aufgeräumt).
- DS-Versionswert: Config `config/datenschutz.php` → `['version' => '1.0']`

**Hinweis Link in erlaeuterung.md:** Der PDF-Link ist hartkodiert auf `/customer/ds/erklaerung-pdf` (kein `route()`-Aufruf, da Markdown). Bei Routenänderung manuell anpassen.

---

## 10b. Admin/Auth-Features 18.06.2026

### Adressfelder (mand + cust)
Alle Adressfelder in Registrierung und „Mein Konto" ergänzt. Pflicht: `*_uname`, `*_email`, `*_firstname`, `*_lastname`, `*_street+nr`, `*_postcode+city`. Optional: `*_tel`, `*_company`. `mand_email`/`cust_email` in „Mein Konto" nur readonly (Änderung über Modal). UNIQUE-Index auf `mand_tel`/`cust_tel` entfernt (DEFAULT 'nicht vorhanden' + UNIQUE = Konflikt ab zweitem User).

### E-Mail- und Passwort-Änderung per Modal
Einstellungsseite (mand + cust): zwei neue Buttons „Passwort ändern" + „E-Mail ändern". PW-Modal: altes PW + neues PW + Bestätigung, direkt speichern. E-Mail-Modal: neue Adresse → Bestätigungsmail an neue Adresse → erst nach Bestätigungslink wird neue E-Mail eingetragen. Alte Adresse bleibt bis zur Bestätigung aktiv. Token über `invite`-Tabelle (`inv_type='email_change'`, `inv_email` = neue Adresse). Bestätigungsroute öffentlich (kein Role-Check — Link wird ggf. auf anderem Gerät geklickt).

### Policy-Versions-Popup
Nach Login: Middleware `CheckPolicyVersion` vergleicht `user.ds_version`/`upload_terms_version` gegen `policy_versions`-Tabelle. Bei Abweichung → Weiterleitung auf Popup-Seite (blockiert bis OK). mand: DS + Upload. cust: DS + Upload (beide mit DB-Speicherung). anon: unverändert (Session-Flag `_ds_hinweis_gezeigt`). syst verwaltet Versionen über `/system/policy-versionen` (Buttons „DS-Version erhöhen" / „Upload-Version erhöhen" → Minor-Version wird automatisch erhöht, alle User sehen Popup beim nächsten Login). Keine Config-Datei-Änderung nötig.

### UI-Terminologie (16.06.)
- „Mandant" → „Galerist:in" (Plural: „Galeristen") in allen Views + Mails
- „Dashboard" → „Einstellungen" in mand + cust-Bereich (route-Namen unverändert)

### E-Mail-Begleittexte (22.06.)
Unterhalb von E-Mail-Eingabefeldern (class `mt-1 text-sm text-gray-600`):
- **Registrierungsseiten** (`customer/auth/register`, `system/mandanten/register`, `system/users/register`) — E-Mail read-only (vorausgefüllt aus Einladung): „Diese E-Mail-Adresse ist eine Voreinstellung, die später geändert werden kann."
- **E-Mail-Ändern-Modal** (`customer/dashboard`, `mandant/dashboard`) — aktives Eingabefeld: langer Erklärungstext zu 2FA + Passwort-Reset. cust: du-Form. mand: Sie-Form.
- **`system/profile.blade.php`** — aktives E-Mail-Eingabefeld: langer Erklärungstext, du-Form.

### Spam-Hinweis im E-Mail-Ändern-Modal (23.06.)
In `customer/dashboard` + `mandant/dashboard` unterhalb des E-Mail-Begleittexts ein amberfarbener Warnhinweis (`mt-2 text-sm text-amber-600`): „Bitte denk(en Sie) daran, dass E-Mails wie diese oft im Spam-Ordner landen. ... prüfe(n Sie) den Spam-Ordner." Hintergrund: Alfahosting-Mailserver ohne DKIM/SPF → Bestätigungsmails zur E-Mail-Änderung landen häufig im Spam. Betrifft alle mand → daher UI-Hinweis statt Server-Fix.

### Mobile-/Touch-Optimierungen (23.–26.06.)
- **Touch-Targets ≥44px (`min-h-11`)** über alle Verwaltungs-Views (mand/cust/syst): Buttons, Links, Submit. Android meldete kaum reagierende, textüberlagerte Buttons.
- **sec_level-Dropdown-Fix** (`mandant/cust/index`): Der Card-Container hatte `overflow-hidden`, was das absolut positionierte Custom-Dropdown abschnitt → auf `overflow-visible` geändert. Zusätzlich Chevron-Icon (▼) + select-artiger Rahmen am Trigger-Button (vorher kein Hinweis auf Bedienbarkeit); Desktop-Button von `w-9` auf `w-14` verbreitert (Platz für Ziffer + Pfeil).
- **syst-Einladungsformular zweizeilig** (`system/users/index`): Auf Android zu wenig Platz → Zeile 1 E-Mail (volle Breite), Zeile 2 is_primary-Checkbox + Senden-Button.
- **iOS-Button-Feedback** (ungetaggt): Safari unterdrückt `:active` → an allen Buttons `active:opacity-75 active:scale-95 transition-transform`; in `resources/css/app.css` global `-webkit-tap-highlight-color: transparent` + `cursor: pointer`. **Erfordert `npm run build`** (gebaute Datei `public/build/assets/app-Jw6eDKDd.css`).

### Passwort-Auge-Toggle (23.06., Tag `pw_eye_ok`)
Alle Passwort-Felder (Login-Modal, Registrierungen, PW-Reset, PW-Ändern-Modals, pw1–pw6) erhalten ein Auge-Icon (Alpine `x-data="{ show:false }"`, `:type="show ? 'text' : 'password'"`, inline SVG offen/geschlossen, `pr-10` am Input). Jedes Feld eigener Scope; bei Feldern mit bestehendem `x-data` (dirty-State) wird `show` dort ergänzt. `pw1–pw6` hatten bereits einen Toggle. `system/login.blade.php` wurde bewusst ausgenommen (aktive syst-Login-View — Passwort-Auge dort sachlich nicht relevant).

### Erfolgsmeldungen bei Kontoerstellung
Nach cust-Registrierung: „Konto erfolgreich angelegt. Bitte melde dich jetzt als Mitglied an." Nach mand-Registrierung: „... als Galerist:in an." + `login_page=mand` für korrektes Modal-Tab.

### Datenschutz-Buttons in Einstellungen
mand + cust: Links zu Datenschutz-Erläuterung und Upload-Bedingungen direkt in der Einstellungsseite.

---

## 10c. Admin/Auth-Features 19.–20.06.2026

### cust-Account-Löschung mit Benachrichtigung
Löscht ein mand die letzte `userdb.cust_pcode`-Referenz zu einem Mitglied (verwaister Account), wird der `cust_user`-Datensatz gelöscht — **vor** der Löschung erhält der cust eine E-Mail (`CustAccountDeletedMail`): „Dein Galerist:in hat seine Galerie geschlossen...". Inkl. Bereinigung von `passkey`/`passkey_dismissed` für den gelöschten User. Die Verwaisungs-Prüfung selbst existierte bereits in `MandantCustController::destroy()`.

**Dieselbe Kaskade läuft auch bei syst-seitiger Mandanten-Löschung** (`SystemMandantController@destroy`): Vor der `mand_user`-Löschung werden alle `userdb.cust_pcode`-Einträge des Mandanten durchlaufen, verwaiste cust erhalten `CustAccountDeletedMail`, deren `cust_user` + `passkey` + `passkey_dismissed` werden gelöscht. Danach werden alle `sessiondb.cust_invite`-Einträge des mand gelöscht.

**Verwaister cust beim Login (23.06.):** Versucht sich ein cust einzuloggen, der keinen `userdb.cust_pcode`-Eintrag mehr hat (verwaister Account, sollte regulär nicht vorkommen), wird der Account **still gelöscht** (`cust_user` + `passkey` + `passkey_dismissed`) und es erscheint die **generische** Credentials-Fehlermeldung („Diese Zugangsdaten sind uns nicht bekannt.") — identisch zum Fall falscher Zugangsdaten, kein Hinweis auf die Löschung, **keine** `CustAccountDeletedMail`. Grund: Die frühere spezifische Meldung „Kein Mandant zugeordnet" war ein potenzielles Sicherheitsleck (Account-Enumeration). In `CustLoginController::handleLogin()`.

### cust-Login 2FA-Entscheidungslogik
**ALLE** mand-Kontexte des cust werden geprüft (nicht nur der bevorzugte). Fordert irgendein mand 2FA für den sec_level des cust bei diesem Mandanten (`userdb.cust_pcode.cust_passcode` ≥ `userdb.mand_user.mand_cust_2fa`), wird 2FA ausgelöst — ein Treffer genügt. Schwellwert 0 = nie 2FA (explizit ausgeschlossen). Schwellwert 7 = nie 2FA (Sentinel). Session-Daten (`pending_mand_id`, `_mand_id`, `_sec_level` etc.) basieren weiterhin auf dem bevorzugten mand-Kontext (höchster `cust_pcode.pcode_prefstat`).

### Login-Beschriftungen (cust/anon)
„Anonym" → „Kurzzeit-Passwort", „Registriert" → „Mitglied" im Login-Modal (`auth/login-modal.blade.php`). Nur cust/anon-Bereich betroffen, mand/syst unverändert.

### Unsaved-Changes-Guard (7 Einstellungs-Fenster)
Wiederverwendbares Partial `resources/views/partials/unsaved-changes-guard.blade.php` mit Alpine `Alpine.store('unsavedGuard')`: trackt Änderungen (`markDirty()`), fängt wegführende `<a href>`-Klicks global ab (`document.addEventListener('click', ...)`) und zeigt eigenes Modal „Willst du deine Änderungen verwerfen?" mit „Zurück"/„Weiter". `beforeunload` als Fangnetz für Tab-Schließen (Browser-Standardtext, technisch nicht anders möglich). Eingebunden in: cust `konto`/`galerien`/`passkey/index`, mand `konto`/`passkey/index`/`pwlist`/`cust/index`.

**Drei kritische Bugs dabei gefunden und behoben** (siehe Abschnitt 12, Lerneffekte 10–13):
- Fehlende `[x-cloak]{display:none!important}`-CSS-Regel → Modal initial sichtbar, Seite blockiert
- Fehlendes `x-data`-Vorfahre-Element auf `<form>` → `@input`/`@change` binden nie (Alpine durchläuft Element ohne x-data-Vorfahre nicht als Komponenten-Baum)
- Gleiches Problem am Modal-`<div>` selbst → Partial bekam eigenes `x-data="{}"` direkt am Modal, dadurch unabhängig von der einbindenden Seite

`customer/galerien.blade.php`: Guard dort wieder entfernt — Checkbox + Reihenfolge speichern jetzt **sofort per AJAX** (kein „Einstellungen speichern"-Button mehr), Bestätigungs-Popup „Einstellungen gespeichert" (kein OK-Button, blendet nach 2,5s automatisch aus, mit Rahmen).

### Mitgliederliste (mand) — Layout + Sortierung + Suche
`resources/views/mandant/cust/index.blade.php`, mehrere Iterationen:
- Custom-Dropdown für `sec_level` (Alpine, kein natives `<select>`): geschlossen nur Zahl sichtbar, offen ausgeschriebene Bezeichnungen
- Desktop: einzeilig, kein Tabellenkopf-Text pro Zeile, kein Rahmen, schmale Abstände
- Mobile: gestapelt (Alias → E-Mail → sec_level → Buttons nebeneinander), Speichern-Button per `form="..."`-Attribut an externes Formular gebunden
- Sortierung (E-Mail/Alias/Sicherheitsstufe, je auf/ab togglebar) + Live-Suche (startsWith, Auswahl Alias/E-Mail) — vollständig client-seitig (Alpine), `localeCompare('de', {sensitivity:'base'})` für korrekte Umlaut-Sortierung, Zeilen werden per `appendChild` umsortiert (nicht neu gerendert) — bestehende Formulare/CSRF/Custom-Dropdown bleiben unangetastet funktionsfähig

### Willkommensseite (erster Login)
`show_welcome` (TINYINT(1), Default 1) in `cust_user`+`mand_user`. Middleware `CheckWelcome` (nach `CheckPolicyVersion` registriert — Policy hat Vorrang, da rechtlich verbindlich vs. reines Onboarding) leitet bei `show_welcome=1` auf `/customer/willkommen` bzw. `/mandant/willkommen` um (blockiert bis „Gelesen"). Inhalt aus `storage/app/private/willkommen_cust.md`/`willkommen_mand.md` (Markdown→HTML, gleiches Verfahren wie Datenschutz-Erläuterung). Klick auf „Gelesen" setzt `show_welcome=0`.

### FAQ & Infos (dynamische Markdown-Liste)
Neuer Button „FAQ und Infos" in Einstellungsseite (mand+cust). Liest **dynamisch** alle `.md`-Dateien aus `storage/app/private/faq/cust/` bzw. `faq/mand/` — neue Dateien erscheinen automatisch ohne Code-Änderung. Button-Label = Dateiname ohne `.md`, Unterstriche → Leerzeichen. Alphabetisch sortiert, vertikal scrollbare Liste. Klick öffnet Inhalt per AJAX in Modal (`FaqController@showCust`/`showMand`). **Sicherheit:** Slug zweistufig gegen Path-Traversal abgesichert (`preg_match('/^[a-zA-Z0-9_\-]+$/')` + `basename()`-Vergleich); `$role` kommt nie aus Nutzereingabe. Kein DB-Bezug — leerer/fehlender Ordner führt zu Hinweistext, kein Fehler.

### cust_kommentarname — verworfenes Feature (Dokumentation der Entscheidung)
Es wurde testweise ein neues Feld `cust_user.cust_kommentarname` angelegt (für einen vom cust selbst pflegbaren, öffentlichen Kommentar-Anzeigenamen). Nach Klärung: **überflüssig** — Login läuft über E-Mail, nicht `cust_uname`, und `cust_uname` wird bereits von cust selbst gepflegt und reicht für Kommentare aus. Feld wieder per `ALTER TABLE ... DROP COLUMN` entfernt. **Bestehende Struktur bleibt:** `cust_uname` (cust-Login-/Account-Name) für Kommentare, `cust_pcode.cust_alias` (mand's privater Merkname pro Mitglied, dem cust nie angezeigt) bleibt getrennt.

---

## 10d. iOS-Long-Tap-Fix, Trusted-Device/Auto-Login, Session-Bugfixes — NEU, 09.–19.07.2026

### iOS-Long-Tap-Fix (09.–10.07., Tag `ios_longtap_complete_ok`)
Ergänzung zur globalen Button-Animation (Abschnitt 10b): Sämtliche verbliebenen button-artigen `<a href>`-Elemente, die noch nicht auf `<button @click="window.location='...'">` umgebaut waren, wurden nachgezogen — **21 Zurück-Links** (Tag `ios_longtap_fix_ok`) + **13 Dashboard-Kacheln/Fließtext-Links** (Tag `ios_longtap_dashboard_ok`), dazwischen ein Regressions-Fix in `customer/galerien.blade.php` sowie eine Bereinigung nicht mehr zutreffender Alt-Tags. Abschließend wurden die **Policy-Update-Popup-Links** (Datenschutz + Upload-Bedingungen, cust+mand) auf `button`/`window.open` umgestellt (Tag `ios_longtap_policy_ok`). Gesamtabschluss Tag `ios_longtap_complete_ok`.

### Upload-Bedingungen-Popup für cust entfernt (10.07., Tag `cust_ds_hinweis_ok`)
Der Inhalt der Upload-Bedingungen ist für cust nicht relevant (nur mand lädt Content hoch). Entfernt:
- `PolicyController::confirmCust()` — der `upload`-Zweig (DB-Update von `upload_terms_version`/`upload_terms_accepted_at`) wurde gelöscht
- `CheckPolicyVersion`-Middleware — der Vergleichs-/Redirect-Block für `upload_version` im cust-Zweig wurde entfernt (auskommentiert mit Verweis auf diese Doku)

Das **DS-Popup bleibt für cust aktiv** (unverändert). Für **mand bleiben beide Popups** (DS + Upload) unverändert aktiv. Als Ersatz: statischer Hinweis in `customer/dashboard.blade.php` (Abschnitt „Rechtliches") sowie ein neuer FAQ-Eintrag `storage/app/private/faq/cust/Upload-Bedingungen.md`.

> Das in einer früheren Chat-Zusammenfassung genannte Tag `cust_upload_popup_removed_ok` **existiert nicht** in der Git-Historie — die tatsächlichen Tags für diese Änderung sind `cust_ds_hinweis_ok` (10.07.) sowie die vorangegangenen `ios_longtap_*`-Tags im selben Arbeitsblock.

### Trusted-Device-Feature → vollständiger Auto-Login (10.–18.07.)
Mehrstufig entwickelt, ursprünglich als reiner 2FA-Skip geplant, später (Anforderungsänderung) zu vollständigem Auto-Login ohne Passwort erweitert:

- **Neue Tabelle `sessiondb.trusted_device`** (`td_id`, `user_type` enum(mand,cust), `user_id`, `token_hash` UNIQUE, `ua_hash`, `device_label`, `last_used_at`, `expires_at`, `created_at`) — bewusst in `sessiondb`, nicht `userdb` (Sicherheitstrennung, analog zu `session`/`twofa_code`).
- **Model** `App\Models\SessionDb\TrustedDevice`.
- **Helper-Funktionen** in `app/helpers.php`: `trustedDeviceCookieName()`, `checkTrustedDevice()`, `issueTrustedDeviceCookie()`, `guessDeviceLabel()`, `revokeTrustedDevices()`.
- **Konfigurierbare Gültigkeitsdauer:** `config/trusted_device.php` (`'days' => env('TRUSTED_DEVICE_DAYS', 7)`).
  > **Achtung — Duplikat in `.env`:** Der Schlüssel `TRUSTED_DEVICE_DAYS` ist aktuell **zweimal** in `.env` gesetzt (Zeile 17: `=1`, Zeile 97: `=7`). Da Laravels `phpdotenv` bereits gesetzte Variablen nicht überschreibt, gewinnt die **erste** Definition — effektiv gilt also `1` Tag (passt zum aktuellen Testbetrieb), nicht `7`. Vor einer produktiven Umstellung auf 7 Tage muss die doppelte Zeile bereinigt werden, sonst bleibt der spätere Wert wirkungslos. Siehe Inkonsistenzen.md.
- **Checkbox** „Dieses Gerät als sicher merken" im Login-Modal (cust+mand), Labeltext ohne sichtbare Tagesangabe (mehrfach gekürzt, siehe Commits `fb2c49a`/`54375a6`/`609c58f`).
- **Service** `App\Services\UserDb\LoginSessionBuilder` (`buildForCust()`/`buildForMand()`) — zentralisiert den Session-Aufbau (Regeneration, `_user_type`/`_cust_id`/`_mand_id`/`_sec_level`, Passkey-Prompt-Logik, Session-Cleanup), gemeinsam genutzt von `CustLoginController` (Passwort-Login), `MandantLoginController` (Passwort-Login) und `AutoLoginTrustedDevice`.
- **Middleware** `App\Http\Middleware\AutoLoginTrustedDevice`: greift nur auf der `home`-Route ohne bestehende Session (`_user_type` nicht gesetzt). Prüft Trusted-Device-Cookies für cust UND mand; sind **beide** gültig, wird **immer cust bevorzugt** (mand wird in diesem Fall nie automatisch eingeloggt). Bei bereits bestehender Session: sofortiger Redirect zum passenden Dashboard (`customer.content`/`mandant.dashboard`/`system.dashboard`) statt Login-Modal. Eingehängt in `bootstrap/app.php` in der Kette `SessionHijackProtection → SessionIdleTimeout → ValidateUserExists → AutoLoginTrustedDevice → CheckPolicyVersion → CheckWelcome`.
- **Logout widerruft Trusted-Device:** neue gemeinsame Blade-Komponente `resources/views/components/logout-button.blade.php` (ersetzt 11 duplizierte Logout-Blöcke in customer-/mandant-Views). Zeigt einen Bestätigungsdialog **nur**, wenn für den aktuellen User ein gültiger `trusted_device`-Eintrag existiert: „Dieses Gerät ist als sicher gespeichert. Möchtest du dies löschen?" mit den Buttons „Abmelden mit Löschen" und „Zurück" (schließt nur den Dialog, kein separater „ohne Löschen"-Pfad — siehe offener Punkt unten). Bei jedem Logout (unabhängig vom Nutzer) werden zusätzlich global alle abgelaufenen `trusted_device`-Einträge aller User bereinigt.
- **iOS-Caching-Probleme gelöst:** `home`-Route sendet explizit `Cache-Control: no-store`; zusätzlich ein `pageshow`-Event-Listener mit `persisted`-Check in `login-modal.blade.php` erzwingt einen Reload bei bfcache-Restaurierung (verhinderte, dass iOS Safari nach Logout das gecachte eingeloggte Layout aus dem Zurück-Cache zeigt).
- **Neue Mail** `App\Mail\TrustedDeviceAddedMail` + `resources/views/emails/trusted-device-added.blade.php` — Benachrichtigung bei neu hinzugefügtem Trusted Device.
- Getestet auf iOS/Windows/Android (Commit `b64d3ce`).
- **Tags:** `trusted_device_cust_ok`, `trusted_device_2fa_skip_complete_ok`, `trusted_device_config_ok`, `trusted_device_config_2FA_ok`, `trusted_device_logout_revoke_ok`, `autologin_pre_live_test`, `autologin_complete_ok`.

**Offener Punkt (nicht umgesetzt, Nutzer hat abgebrochen):** Eine Umbenennung des „Zurück"-Buttons im Trusted-Device-Bestätigungsdialog zu „Verlassen ohne Löschen" (mit Tab-Schließen statt nur Dialog-Schließen) wurde besprochen, aber nicht implementiert. Siehe Inkonsistenzen.md.

### sessiondb.session — zwei unabhängige Bugs behoben (18.–19.07., Tag `session_usertype_fix_ok`)
1. **Verwaiste Session-Zeilen:** `session()->regenerate()` lief an mehreren Login-Stellen ohne `$destroy=true` — die alte Zeile blieb als Leiche in der DB stehen. Jetzt `regenerate(true)` an **allen 7** Session-Übergängen: cust Passwort (`LoginSessionBuilder::buildForCust()`), cust 2FA-Verifikation, cust Passkey-Login, **cust anon-Kurzzeit-Kennwort-Login** (`CustLoginController::handleAnonLogin()`, Zeile 305), mand Passwort (`LoginSessionBuilder::buildForMand()`), mand Passkey-Login, syst 2FA-Verifikation.
   > **Korrektur:** Es gibt **keine** Ausnahme für den anon-Login. `handleAnonLogin()` nutzte vor diesem Fix `regenerate()` ohne `destroy` und wurde im selben Commit auf `regenerate(true)` umgestellt — identisch zu allen anderen Stellen. Eine frühere Aussage, anon sei bewusst ausgenommen, trifft auf den aktuellen Code nicht zu.
2. **`user_type` in `sessiondb.session` war immer `'anon'`** — hartkodiert in `SessionDbSessionHandler::write()` beim `INSERT`, nie per `UPDATE` korrigiert (der Session-Handler kennt zum Zeitpunkt des Schreibens die Rolle nicht). Fix: `App::terminating()`-Callback (registriert in `LoginSessionBuilder::buildForCust()`/`buildForMand()`, `CustLoginController::verifyTwoFactor()`/`passkeyLogin()`, `MandantLoginController`, `SystemLoginController`) läuft **nach** dem finalen `write()`/INSERT und aktualisiert `user_type` + `cust_id`/`mand_id`/`syst_id` nachträglich per gezieltem `UPDATE ... WHERE sess_token = ...`.
3. **`SessionDbSessionHandler::destroy()`** nutzte nicht dieselbe ID-Kürzung `substr($id, 0, 128)` wie `write()` — potenzielle Diskrepanz (und damit ein nie greifendes `DELETE`) bei Session-IDs über 128 Zeichen. Korrigiert.

Betroffene Dateien: `app/Extensions/SessionDbSessionHandler.php`, `app/Services/UserDb/LoginSessionBuilder.php`, `app/Http/Controllers/UserDb/SystemLoginController.php`, `CustLoginController.php`, `MandantLoginController.php`.

### „Sitzung abgelaufen"-Meldungen entfernt + `/backstage`-Redirect-Bug (18.07., Tag `session_messages_removed_ok`)
Die dedizierten „Sitzung abgelaufen"-Meldungen für cust/mand/syst/anon wurden entfernt. Dabei entdeckt und behoben: `REDIRECT_TARGETS['syst']` verwies in drei Middlewares auf die nicht existierende Route `/system/login` (führte zu 404) — korrigiert auf `/backstage` (tatsächliche Route `system.backstage.login`) in `SessionIdleTimeout.php`, `ValidateUserExists.php`, `RequireRole.php`. Die jeweils eigenen, andersartigen Fehlermeldungen dieser drei Middlewares (z. B. `ValidateUserExists`: „Ihre Sitzung ist ungültig. Bitte melden Sie sich erneut an.") blieben unverändert bestehen — nur die spezifischen „abgelaufen"-Texte wurden entfernt.

---

## 10e. Sicherheits-Härtung & kleinere Anpassungen — NEU, 29.–31.07.2026

### Fehlermeldungen: dirty-Ausblendung + Error-Bag-Isolation (29.07., `error_messages_dirty_fix_ok`)
10 Stellen (cust/anon/mand/syst-Login, Dashboards, syst-Profil) erhielten benannte Error-Bags (`->withErrors([...], 'mand')`/`'syst'`/`'anon'`) statt des Default-Bags, plus `x-data="{dirty:false}"` + `@input="dirty=true"` + `x-show="!dirty"` auf den Fehlertext — sobald der User erneut tippt, verschwindet die alte Fehlermeldung, statt bis zum nächsten Submit stehen zu bleiben. **Grund für die Bag-Trennung:** `auth/login-modal.blade.php` rendert cust-, anon- und mand-Login **auf derselben Seite** (Tabs) — ohne benannte Bags hätte ein Fehler aus einem Tab im falschen Tab aufscheinen können. syst läuft auf einer komplett separaten Seite (`system/login.blade.php`, nutzt ausschließlich `$errors->syst`) und war von dieser Kollisionsgefahr nie betroffen, bekam aber ebenfalls einen benannten Bag (Konsistenz). **Geprüft bei dieser Doku-Aktualisierung:** Eine analoge mand/syst-Bag-Kollision existiert nicht und ist auch nicht latent möglich, da beide Logins nie in derselben Response gerendert werden.

### Login-Cleanup erweitert (29.07., `login_cleanup_expired_records_ok`)
`LoginSessionBuilder` bereinigte bisher nur abgelaufene `sessiondb.session`-Zeilen bei jedem cust/mand-Login. Neue private Methode `cleanupExpiredRecords()` bereinigt zusätzlich abgelaufene `userdb.invite`, `sessiondb.cust_invite`, `sessiondb.trusted_device`, `sessiondb.twofa_code` — `userdb.cust_invite` (Relikt, Abschnitt 12 #1) bewusst ausgenommen. Details Abschnitt 4.1/4.2.

### Passkey-Hinweistexte ausgelagert (30.07., `passkey_hints_markdown_ok`)
Siehe Abschnitt 9.

### Platzhaltertexte in Einladungsformularen präzisiert (30.07., `invite_placeholder_texts_ok`)
`ihre@email.de` (ließ vermuten, die eigene Adresse sei gemeint) → „E-Mail Mitglied" (`mandant/cust/einladen.blade.php`), „E-Mail Galerist:in" (`system/mandanten/index.blade.php`), „E-Mail Systuser" (`system/users/index.blade.php`).

### mand: 2FA optional deaktivierbar (30.07., `mand_2fa_optin_login_fix_ok`)
`mand_user.mand_2fa_opt_in` (Default 1, 2FA per Default aktiv) wurde bisher in der Kontoverwaltung zwar gespeichert, aber **beim Login nie ausgewertet** — totes Feld. Jetzt in `MandantLoginController::handleLogin()`: ist `mand_2fa_opt_in` false, wird der 2FA-Versand übersprungen und direkt die Session aufgebaut (inkl. Trusted-Device-Cookie, falls „Gerät merken" angehakt — vorher nur im 2FA-Pfad ausgestellt, jetzt gemeinsame private Methode `issueTrustedDeviceIfRequested()` für beide Pfade). UI: Checkbox in `mandant/konto.blade.php` invertiert und umbenannt — Feldname im Formular jetzt `mand_2fa_disable` (invertiert zu `mand_2fa_opt_in` in `MandantSelfController::update()`), Label „Anmeldung ohne Sicherheitscode per E-Mail" mit Warnhinweis, dass das Konto dann nur noch durch das Passwort geschützt ist. Dashboard-Kachel-Text „Profil und Passwort verwalten" → „Profil verwalten" (präzisiert, da Passwort-Änderung dort nicht stattfindet). `Konzept_Fotosite_V8.md` entsprechend aktualisiert.

### Seitenkopf-Label „MITGLIED" (30.07., `mitglied_label_und_inkonsistenz12_ok`)
`customer/dashboard.blade.php` + `customer/passkey/index.blade.php`: Seitenkopf zeigte bisher nur den Vornamen (Fallback „Mitglied"), jetzt zusätzlich fest „MITGLIED" als Rollenlabel + Vorname daneben — Konsistenz mit den anderen Rollen (die eine feste UI-Bezeichnung im Kopf zeigen).

### syst-Login-Pfad über `.env` konfigurierbar (30.07., `backstage_path_configurable_ok`)
Bisher war `/backstage` an mehreren Stellen hartkodiert (3× `redirect()` + 6× `REDIRECT_TARGETS` in drei Middlewares) — strukturelle Ursache der Bug-Klasse vom 18.07. (Abschnitt 10d, vergessene Stelle bei Pfadänderung). Jetzt einzige Quelle: `config('app.backstage_path')` (`env('BACKSTAGE_PATH', 'backstage')`) in `routes/web.php`; alle Middleware-Redirects nutzen `route('system.backstage.login')` statt des Strings. `REDIRECT_TARGETS` in `RequireRole`/`SessionIdleTimeout`/`ValidateUserExists` von Klassenkonstante auf private Methode umgestellt (Konstanten erlauben keine Funktionsaufrufe). `BACKSTAGE_PATH` muss bei Bedarf separat in der Server-`.env` gesetzt werden, Default bleibt `backstage`.

---

## 10f. anon-Login per teilbarem Kurzcode-Link — NEU, 01.08.2026 (`anon_share_link_shortcode_ok`)

### Mechanismus
Neue Tabelle `sessiondb.share_link` (`sl_id`, `code` UNIQUE varchar(10), `mand_id`, `sec_level`, `created_at`, UNIQUE-Index auf `mand_id`+`sec_level`) löst den bisherigen langen, verschlüsselten Token-Mechanismus (`encrypt('mand_id|level')`, `CustLoginController::loginViaShareLink()`, Route `customer.login.share`) vollständig ab.

- **Erzeugung:** `MandantPwListController::edit()` legt für jede aktuell gültige Stufe (`valid_from`/`valid_until`) per `ShareLink::firstOrCreate(['mand_id'=>..,'sec_level'=>..], ['code'=>Str::random(7), 'created_at'=>now()])` einen 7-stelligen, alphanumerischen Code an — stabil über mehrere Seitenaufrufe hinweg (kein neuer Link bei jedem Reload). Bei Code-Kollision (UNIQUE-Verletzung auf `code`) bis zu 5 Versuche, danach `Log::error()` und die betroffene Stufe wird ohne Link ausgelassen (kein Absturz der ganzen Seite).
- **Präzise Pro-Stufe-Invalidierung:** `MandantPwListController::update()` lädt VOR dem Überschreiben die alte `pw_list`-Zeile und vergleicht pro Stufe den alten entschlüsselten Klartext gegen den neu eingereichten. Nur für Stufen, deren Klartext sich tatsächlich geändert hat, wird `ShareLink::where('mand_id',..)->where('sec_level',..)->delete()` ausgeführt — der Link verhält sich damit funktional identisch zum Kurzzeit-Passwort selbst (bleibt gültig, solange sich das zugehörige Passwort nicht ändert). Erstanlage einer Stufe zählt NICHT als Änderung (vorher konnte kein Link existieren).
- **Route:** `GET /s/{code}` (Name `login.shortcode`), bewusst in `routes/web.php` (kurze, mandantenunabhängige URL außerhalb des `/customer`-Prefix), NICHT `routes/customer.php`. Vor Anlage gegen Honeypot-Pfade (`storage/app/private/honeypot_paths.txt`) und `config('app.backstage_path')` geprüft — keine Kollision.
- **`CustLoginController::loginViaShortCode()`:** lädt `ShareLink` per `code`, prüft die Live-Gültigkeit gegen `pw_list` (`valid_from`/`valid_until`), baut bei Erfolg die anonyme Session auf. Bei ungültigem/abgelaufenem Code: Redirect zum Login mit Fehlermeldung „Dieser Link ist abgelaufen."
- **Gemeinsame Session-Aufbau-Methode:** neue private `CustLoginController::buildAnonSession(Request $request, int $mandId, int $secLevel)` — vorher 2× identischer Code in `handleAnonLogin()` und dem entfernten `loginViaShareLink()`, jetzt einmal extrahiert; beide anon-Login-Wege (Passwort-Eingabe und Link) nutzen dieselbe Methode.

### UI (`mandant/pwlist.blade.php`)
Ein-Klick-Icon je Stufe (löst das frühere zweistufige „Login-URL erzeugen" → aufklappbares Feld ab): `navigator.share({url: ...})`, falls verfügbar, sonst Clipboard-Fallback mit kurzer „✓ Link kopiert"-Bestätigung (2s, `x-transition`). Die `$shareLinks[level]`-Array-Struktur in der View ist unverändert — nur der Inhalt ist jetzt eine kurze `/s/{code}`-URL statt eines langen Tokens.

### Datenschutz-Hinweis-Popup für anon entfernt
Im selben Commit wurde das Datenschutz-Hinweis-Popup für anon (`customer/content.blade.php`, beide Zugangswege — Passwort UND Link) entfernt. Der Hinweis soll künftig über die Content-Seiten selbst erreichbar sein (Phase 7, noch nicht umgesetzt) — siehe Abschnitt 10a, dort korrigiert. `DatenschutzController::hinweisOk()`, Route `customer.datenschutz.hinweis-ok` und das Session-Flag `_ds_hinweis_gezeigt` bleiben unangetastet stehen (unerreicht, bewusst nicht aufgeräumt).

**Tag:** `anon_share_link_shortcode_ok`. Commit: `15e21bd`.

---

## 10g. cust: Trusted-Device-Cookie im Nicht-2FA-Pfad nachgezogen — NEU, 03.08.2026 (`trusted_device_cust_nofactor_fix_ok`)

**Bug:** In `CustLoginController::handleLogin()` wurde die `remember_device`-Checkbox im Nicht-2FA-Login-Pfad ausgelesen (`$checkboxChecked = $request->boolean('remember_device')`), aber nie ausgewertet — `issueTrustedDeviceCookie()` wurde dort nie aufgerufen. Der Cookie/DB-Eintrag (`sessiondb.trusted_device`) entstand bisher nur über den 2FA-Pfad (`verifyTwoFactor()`). Bei `mand` war der äquivalente Pfad bereits korrekt verdrahtet (`issueTrustedDeviceIfRequested()` im Nicht-2FA-Zweig von `MandantLoginController`, siehe Abschnitt 10e, `mand_2fa_optin_login_fix_ok`) — nur `cust` war betroffen.

**Fix:** Neue private Methode `CustLoginController::issueTrustedDeviceIfRequested()` ergänzt (analog zu `MandantLoginController`), im Nicht-2FA-Rückgabepfad von `handleLogin()` aufgerufen. `verifyTwoFactor()` (2FA-Pfad) bleibt unverändert — keine doppelte Cookie-Ausstellung möglich, da beide Pfade exklusiv sind.

Getestet mit und ohne 2FA — der `cust`-Trusted-Device-Datensatz wird jetzt in beiden Fällen korrekt angelegt.

**Tag:** `trusted_device_cust_nofactor_fix_ok`. Commit: `ddf5e55`.

---

**Geräteklassen:**

| Gerät | Primäre Nutzer | Besonderheit |
|---|---|---|
| Desktop (Win10) | alle Rollen | Referenz-Layout |
| Smartphone | mand (Batch-Foto-Upload), alle Rollen | Hoch-/Querformat, große Tap-Ziele |
| Tablet | cust (Fotoalbum-Hauptgerät) | Querformat, Wischgesten, Layout wie Desktop mit größeren Tap-Zielen |

**Strategie:** Responsive Tailwind-Breakpoints (`md:` = Tablet+Desktop, darunter = Smartphone). Ausnahme: Smartphone-Batch-Upload für mand erhält eigene Komponente.

**Bereits umgesetzt:**
- Dashboard-Passkey-Prompt (mand+cust): Buttons untereinander auf Smartphone, `py-3` Tap-Ziele
- Mitgliederliste (`mandant/cust/index.blade.php`): responsive Card-Layout auf Smartphone (zweizeilig), Tabelle ab `md:`
- Passkey-Seiten: „Neuen Passkey"-Button bildschirmbreit unterhalb Hinweistext auf Smartphone

**Noch offen (Hauptaufgaben Phase 7):**
- Keine Controller/Views für `ActivityGroup`, `ActivitySubgroup`, `FotoObj` (Anzeige + CRUD)
- Smartphone-Batch-Upload-Flow für mand (`/mandant/upload/*`): Galerie-Picker mit Mehrfachauswahl, EXIF/Dateinamen-Datumserkennung (Fallback-Kette: EXIF → Dateiname-Parsing → `filemtime()` → manuell), AG/ASG-Zuordnung, Sicherheitsstufe, Upload mit Fortschritt/Retry
- Mandanten-Content-Seite (horizontale Balken, Thumbnails, Navigation zwischen Mandanten) für cust + anon; anon leer, cust mit Textlink zum Dashboard
- `mand_profile`-Anzeige (Profilseite des Galeristen für cust/anon)
- `_sec_level`-Session-Key (gespiegelt aus `userdb.cust_pcode.cust_passcode` bzw. `sessiondb.session.cust_passcode`) in Content-Filterung einbinden (aktuell gesetzt, aber ungenutzt)
- `ModerationMail` implementieren (erst nach Content-Upload)

---

## 12. Bekannte Inkonsistenzen / Altlasten

| # | Befund | Status |
|---|---|---|
| 1 | `userdb.cust_invite` ist Relikt, `sessiondb.cust_invite` ist führend | Bewusst nicht gelöscht (Vorsicht) |
| 2 | `resources/views/welcome.blade.php` (Breeze-Default) von keiner Route gerendert | Bleibt ungenutzte Altlast — die NEUE Willkommensseite (20.06.) liegt unter `customer/welcome.blade.php`/`mandant/welcome.blade.php` (eigener Namespace, keine Kollision). Breeze-Datei könnte bei Gelegenheit gelöscht werden |
| 2b | ~~`resources/views/system/login.blade.php` ist eine tote Datei~~ **KORRIGIERT 29.06.: Diese Einstufung war FALSCH.** `system/login.blade.php` ist die **aktive** syst-Login-View — `SystemLoginController@login` rendert `view('system.login')`, Login UND 2FA laufen über diese Datei (2FA via `show_2fa`-Flash-Variable). Der `/backstage`-Ausfall am 21.06. entstand durch eine fehlerhafte Änderung an dieser Datei, nicht weil sie tot wäre. | **Aktive Datei — darf und muss bearbeitet werden.** Frühere „tote Datei"-Einstufung aufgehoben |
| 3 | `_last_activity` liegt im JSON-Session-Payload, nicht als DB-Spalte | SQL-Updates auf die DB-Spalte wirken sich nicht aus — nur `session()->put()` verwenden |
| 4 | `ag_fo_context.ag_banner` (obsolet) aus DDL und Code entfernt | Erledigt 14.06. (Tag `ag_banner_removed_ok`); `ag_is_banner` bleibt |
| 5 | `*_sec_code` → `*_sec_level` (TINYINT UNSIGNED) in fotodb-DDL + Models | **Erledigt 16.06.** (Tag `sec_level_sync_ok`); `fo_is_video` ergänzt |
| 6 | 403 ohne Logeintrag → `abort(403)`/403-Responses erscheinen NICHT im Laravel-Log | Bei 403 zuerst Controller auf `abort(403)` prüfen, nicht Server/Middleware |
| 7 | Korrigierte Datei lokal sauber, aber 403/Fehler bleibt → Datei war nie per FTP hochgeladen | Nach Claude-Code-Fix per grep Server-Datei gegen lokale prüfen, bevor weiter diagnostiziert wird |
| 8 | Alfahosting: kein mod_security-Block auf „datenschutz" (Annahme war falsch) | URL-Segment `/ds/` bleibt trotzdem bestehen; echte Ursache war `abort(403)` im Controller |
| 9 | Veralteter Laravel-Cache korrumpiert Daten nach Controller-Änderungen | Nach JEDER Änderung an Controllern, Routen, Config: `php artisan route:clear ; php artisan config:clear ; php artisan view:clear ; php artisan cache:clear` — bestätigt durch Hash-Korruption nach E-Mail-Änderung ohne cache:clear |
| 10 | **`npm run build` vergessen nach neuen Tailwind-Klassen** — Klassen im Blade-Code wirkungslos, da kompiliertes CSS sie nicht enthält. Mehrfach (20.06.) fälschlich als Code-/Alpine-Bug diagnostiziert | Nach JEDER Blade-Änderung mit neuen Tailwind-Klassen: `npm run build` lokal ausführen, `public/build/` (manifest.json + assets) per FTP mit hochladen |
| 11 | `x-cloak` ohne begleitende CSS-Regel `[x-cloak]{display:none!important}` ist wirkungslos | Element bleibt bis zur Alpine-Initialisierung sichtbar (Race Condition). Regel muss explizit vorhanden sein — am sichersten direkt im wiederverwendbaren Partial selbst |
| 12 | Alpine-Direktiven (`@input`, `x-show`, etc.) binden NICHT, wenn das Element keinen `x-data`-Vorfahren im DOM-Baum hat | Jedes Element mit Alpine-Direktiven braucht einen `x-data`-Scope — entweder geerbt von einem Vorfahren oder direkt am Element selbst (`x-data="{}"` reicht als leerer Scope) |
| 13 | `@json()` in einem bereits doppelt-quoted HTML-Attribut (`x-data="...@json()..."`) zerstört das Attribut beim ersten `"` im JSON | Laravel-Standardlösung: Attribut in einfache Anführungszeichen setzen (`x-data='...@json()...'`), `@json()` bleibt unverändert |
| 14 | Datei als „bereits vorhanden auf Server" angenommen, ohne FTP-Verifikation (`willkommen_*.md`) → `abort(404)` im Controller, in Kombination mit Cache-Effekt auch Folge-Requests gestört | Nie von „liegt schon da" ausgehen — vor Verlassen auf eine vorausgesetzte Datei kurz per `ls`/`grep` auf dem Server verifizieren |
| 15 | **Redirect-Loop zwischen zwei Gate-Middlewares** (`CheckPolicyVersion` ↔ `CheckWelcome`): jede schloss nur ihre EIGENE Bestätigungsroute aus, nicht die der anderen — auf der fremden Bestätigungsseite löste sie erneut einen Redirect aus → Ping-Pong (ERR_TOO_MANY_REDIRECTS). Trat auf bei Accounts mit gleichzeitig `show_welcome=1` UND veralteter Policy-Version (typisch: frisch eingeladener/zurückgesetzter Account). Cookie-Löschen half nicht, da die auslösenden Flags serverseitig in der DB stehen | Bei mehreren Gate-Middlewares (Popup-/Zwischenseiten-Pattern) muss JEDE Middleware ALLE Bestätigungsrouten der anderen Gates zusätzlich ausschließen, nicht nur die eigene. Endstand beider Middlewares: `routeIs('*.policy.*') \|\| routeIs('*.welcome*') \|\| routeIs('*.datenschutz.*')`. Tags: `redirect_loop_fix_ok`, `policy_view_link_fix_ok` |
| 16 | **„ansehen"-Link auf Policy-Update-Seite öffnete sich selbst** statt der DS-/Upload-Texte: Der Link in der View war korrekt (zeigte auf `customer.datenschutz.*`), aber die Gate-Middlewares fingen die `datenschutz.*`-Routen ab, solange die Policy noch nicht bestätigt war, und leiteten zurück auf `policy.update`. Jeder Klick auf „ansehen" öffnete daher nur erneut die „Neu"-Meldung im neuen Tab — die eigentlichen Texte waren nie erreichbar | Beide Gate-Middlewares zusätzlich `*.datenschutz.*` ausschließen lassen (siehe Lerneffekt 15). Tag: `policy_view_link_fix_ok` |
| 17 | **Annahmen über Dateirollen ohne Routenprüfung** führten mehrfach zu Fehlern. Beispiel `system/login.blade.php`: am 21.06. wurde sie versehentlich bei einer E-Mail-Feld-Vereinheitlichung mitgeändert → `/backstage`-Ausfall. **KORREKTUR 29.06.:** Die daraus gezogene Schlussfolgerung „tote Datei, nicht anfassen" war jedoch falsch — die Datei ist die AKTIVE syst-Login-View. Der eigentliche Lerneffekt ist: Routenzuordnung VOR jeder Änderung prüfen (welcher Controller rendert welche View), statt Dateirollen aus dem Gedächtnis anzunehmen | Vor jedem Änderungs-Prompt die Routen-/Controllerzuordnung gegen die tatsächliche `return view()`-Anweisung prüfen. Bekannte echte tote Datei: nur `resources/views/welcome.blade.php` (Breeze-Default). `system/login.blade.php` ist NICHT tot |
| 18 | **Laravel `TrimStrings`-Middleware schließt `password`-Felder aus** — `password`, `current_password`, `password_confirmation` werden NICHT getrimmt (Vendor-Default-`$except`). `pw1`–`pw6` hingegen schon (Feldname nicht exempt). Dadurch Mismatch: pw1–pw6 wird beim Speichern getrimmt, beim Anon-Login-Vergleich (Feld `password`) aber nicht → führendes/nachfolgendes Leerzeichen verhinderte Login | Beim Anon-Login `trim($request->input('password'))` vor dem `decrypt()`-Vergleich. Standard-Logins (Hash::check) brauchen kein trim, da Hash-Erzeugung und -Vergleich denselben (ungetrimmten) Wert nutzen. Tag: `touch_and_trim_ok` |
| 19 | **`overflow-hidden` schneidet absolut positionierte Alpine-Dropdowns ab** — das sec_level-Custom-Dropdown öffnete sich (Alpine-State `open=true`), war aber unsichtbar, weil ein Eltern-Card-Container `overflow-hidden` (für rounded-Ecken) hatte. `rounded-xl` funktioniert auch ohne `overflow-hidden` | Bei Custom-Dropdowns / Poppern keinen `overflow-hidden`-Vorfahren; `overflow-visible` setzen. Diagnose lief über Browser-Konsole: `_x_dataStack[0].open` zeigte `true`, `getComputedStyle`-Walk fand den `overflow-hidden`-Vorfahren. Tag: `fixes_23jun_ok` |
| 20 | **iOS Safari feuert `:active` auf Buttons nur mit `touchstart`-Listener** — CSS-`active:`-Klassen allein bleiben auf iOS wirkungslos. Zusätzlich überschreibt Tailwind implizit `user-select` auf Buttons/Links, sodass Text markierbar bleibt und das iOS-Kontextmenü erscheint | Globale Lösung in `app.css` (29.06., Tag `ios_button_feedback_ok`): `button:active{opacity:.75;transform:scale(.95)}` als autoritative Animation; `* {user-select:none}` mit Ausnahme `input,textarea`; `touchstart`-Listener auf `document` in `app.js`. Erfordert `npm run build`. Die früher pro-Button gesetzten `active:`-Klassen und das `submitted`-Pattern wurden zugunsten dieser globalen Regeln entfernt |
| 21 | **`<a href>` löst auf iOS bei langem Tap immer das Kontextmenü aus** (Text markieren, „Öffnen"-Popup) — unabhängig von `user-select` | Button-artige `<a>`-Tags (mit Button-Styling) auf `<button type="button" @click="window.location='...'">` umbauen. Guard-Seiten (mit unsaved-changes-guard) stattdessen `@click="$store.unsavedGuard.requestNav('...')"`, da der Guard nur `a[href]` abfängt. Navigations-Links ohne Button-Styling bleiben `<a>` (29.06.) |
| 22 | **iOS Apple Mail ignoriert `user-select:none` komplett** — auch Inline-Style am `<a>`-Tag wirkt nicht; Button-Text in Einladungsmails bleibt auf iOS markierbar | Bekannte, akzeptierte Einschränkung (29.06.). Nicht per CSS lösbar. Inline-`user-select:none` in den Mail-Templates schadet nicht (wird auf anderen Plattformen genutzt/ignoriert), löst das iOS-Mail-Verhalten aber nicht |
| 23 | **Doppel-Submit auf Login-Buttons** (cust/mand/syst): mehrfaches Antippen verschickte mehrere 2FA-/Sicherheitscode-Mails. Das `submitted`-Pattern deaktiviert den Button korrekt, aber `:disabled` darf erst NACH dem Submit greifen, sonst wird gar nicht abgeschickt | Reihenfolge: `type="button"` + `@click="$el.closest('form').submit(); submitted = true"` und `:disabled="submitted"`. Erst nativ submitten, dann deaktivieren. Nur auf Login-Buttons nötig (mailauslösend), nicht global (29.06.) |
| 24 | **`TRUSTED_DEVICE_DAYS` doppelt in `.env` gesetzt** (Zeile 17 `=1`, Zeile 97 `=7`) — Laravels `phpdotenv` überschreibt bereits gesetzte Variablen nicht, es gewinnt die erste Definition. Effektiv gilt aktuell `1` Tag, nicht `7` | Vor produktiver Umstellung auf 7 Tage die doppelte Zeile in `.env` bereinigen (nur eine der beiden behalten), sonst bleibt der zweite Wert dauerhaft wirkungslos (19.07., gefunden bei Doku-Aktualisierung) |
| 25 | **`sessiondb.session.user_type` war dauerhaft `'anon'`**, weil `SessionDbSessionHandler::write()` den Wert beim `INSERT` hartkodiert und nie per `UPDATE` korrigiert hat — der Session-Handler kennt die Rolle zum Schreibzeitpunkt nicht | Behoben 19.07. (`session_usertype_fix_ok`) via `App::terminating()`-Callback, der nach dem finalen `write()` `user_type`/`cust_id`/`mand_id`/`syst_id` nachträglich per `UPDATE ... WHERE sess_token=...` setzt. Details Abschnitt 10d |
| 26 | **Verwaiste `sessiondb.session`-Zeilen** durch `regenerate()` ohne `$destroy=true` an mehreren Login-Stellen (inkl. anon-Login) | Behoben 19.07. — `regenerate(true)` jetzt an allen 7 Session-Übergängen, keine Ausnahme mehr für anon (frühere Annahme einer bewussten Ausnahme war unzutreffend). Details Abschnitt 10d |
| 27 | **Logout-Button-Komponente:** Trusted-Device-Bestätigungsdialog hat aktuell nur „Abmelden mit Löschen" / „Zurück" (schließt nur den Dialog). Eine besprochene Umbenennung zu „Verlassen ohne Löschen" (Tab schließen statt Dialog schließen) wurde NICHT umgesetzt | Nutzer hat die Änderung abgebrochen — offener Punkt, falls gewünscht künftig nachziehen. `resources/views/components/logout-button.blade.php` |
| 28 | **E-Mail-Footer im Sie-Form trotz Du-Form-Mailtext** in drei Templates: `two-factor-code.blade.php`, `trusted-device-added.blade.php` UND zusätzlich `cust-invite.blade.php` (beim Abgleich am 19.07. gefunden — war in der ursprünglichen Meldung nicht enthalten) — „Bitte antworten Sie nicht auf diese E-Mail." | Niedrige Priorität, nicht behoben. Bei Gelegenheit auf „Bitte antworte nicht auf diese E-Mail." vereinheitlichen |
| 29 | **WinSCP-Skripte ohne `cd fotos.martinwagner.de` nach `lcd` laden am falschen Server-Pfad hoch** — der FTP-Login-Root liegt eine Ebene ÜBER dem eigentlichen Projektverzeichnis (`.../u14bc1w8.host159.alfahosting-server.de/` statt `.../fotos.martinwagner.de/`). Ohne das `cd` meldet WinSCP „erfolgreich hochgeladen", die Dateien landen aber im falschen Verzeichnis, der Server zeigt weiterhin den alten Stand | Führte zu stundenlanger Fehlsuche. WinSCP-Skripte MÜSSEN nach `lcd` ein `cd fotos.martinwagner.de` enthalten, bevor `put`-Befehle folgen |
| 30 | **WinSCP-Skripte werden ohne `option transfer binary`/`open <Verbindung>` am Skriptanfang ausgegeben** | Bewusste Auslassung — der Nutzer ergänzt diese Verbindungszeilen selbst. Ausgegebene Skripte beginnen direkt mit `lcd`, dann `cd`, dann `put` |
| 31 | **Mehrzeilige Commit-Messages in `.bat`-Dateien (cmd.exe) vertragen keine mehrzeiligen Anführungszeichen-Blöcke** (das ist PowerShell-Syntax, in `.bat` nicht gültig) | Stattdessen mehrere separate `-m "..."`-Flags verwenden: `git commit -m "Zeile 1" -m "Zeile 2"` |
| 32 | **`php artisan config:clear` allein genügt nicht bei `env()`-gesteuerten Routen** (z. B. `BACKSTAGE_PATH`, `LOGIN_LOCKOUT_*`, `HONEYPOT_LOCKOUT_MINUTES`) — werden Routen aus `config()`-Werten gebaut (`routes/web.php`: `config('app.backstage_path')`), friert ein zwischenzeitliches `route:cache` den alten Pfad ein | Nach Änderungen an env-gesteuerten Config-/Routen-Werten zusätzlich `php artisan route:clear` ausführen, nicht nur `config:clear` — deckt sich mit der bestehenden Regel „immer alle 4 Cache-Befehle" (Abschnitt 3), hier nochmal explizit für diesen Fall benannt |
| 33 | **„Fix wirkt nicht" wurde mehrfach fälschlich als Code-Bug diagnostiziert, obwohl der Upload schlicht nicht angekommen war** (vgl. #7) | Vor jeder weiteren Fehlersuche bei „Fix wirkt nicht": zuerst per SSH direkt auf dem Server verifizieren — `grep` auf den erwarteten Code-Inhalt, `ls -la` auf den Zeitstempel der Datei — bevor am Code selbst weitergesucht wird |

---

## 13. Git & Tags

- **Repo:** `github.com/fotosite/fotosite` (privat)
- **Aktiver Branch:** `feature/passkey-infra`
- **Lokaler Pfad:** `D:\mwa\Projekte\fotosite\Fotosite_V08\claudescode\fotosite`
- **Meilenstein-Tag:** `user_management_complete_ok` (20.06.2026) — komplette Benutzer-/Sicherheitsverwaltung implementiert, sicherer Rückfallpunkt vor Phase 7 (Content). Auch als Startpunkt für künftige Projekte geeignet (siehe Abschnitt 15). **Einschränkung:** Die darin enthaltene Passkey-Funktionalität ist implementiert, aber noch nicht gründlich getestet (siehe Abschnitt 9) — „complete" bezieht sich auf den Implementierungsstand, nicht auf einen abgeschlossenen Testdurchlauf.
- **Relevante Tags:** `phase5_cust_login_ok`, `p6_passkey_prompt_ok`, `p6_passkey_ui_ok`, `ag_banner_removed_ok`, `sec_level_sync_ok`, `datenschutz_ok`, `cust_invite_ok`, `mand_invite_ok`, `mand_register_ok`, `ui_begriffe_ok`, `mand_adressfelder_ok`, `cust_adressfelder_ok`, `email_pw_modal_ok`, `registrierung_ok`, `rechtliches_ok`, `pw_reset_mail_ok`, `policy_popup_ok`, `cust_delete_mail_ok`, `login_labels_ok`, `unsaved_changes_guard_ok`, `galerien_ajax_ok`, `mand_mitgliederliste_ok`, `welcome_screen_ok`, `faq_feature_ok`, `user_management_complete_ok`, `redirect_loop_fix_ok`, `policy_view_link_fix_ok`, `syst_primary_ok`, `fixes_23jun_ok`, `touch_and_trim_ok`, `pw_eye_ok`, `ios_button_feedback_ok`
- **29.06. (nach `ios_button_feedback_ok`):** syst-Löschlogik-Fix, `MandAccountDeletedMail`, deutsche PW-Fehlermeldungen, PW-Hinweistexte syst min:12 — Tag `stable_2026-06-30_logins_ok` (30.06.).
- **Seit 09.07. (Stand 19.07.):** `ios_longtap_fix_ok`, `ios_longtap_dashboard_ok`, `ios_longtap_complete_ok`, `ios_longtap_policy_ok`, `cust_ds_hinweis_ok`, `trusted_device_cust_ok`, `trusted_device_2fa_skip_complete_ok`, `trusted_device_config_ok`, `trusted_device_config_2FA_ok`, `trusted_device_logout_revoke_ok`, `autologin_pre_live_test`, `autologin_complete_ok`, `session_messages_removed_ok`, `session_usertype_fix_ok`. Details Abschnitt 10d.
- **Seit 29.07. (neu, Stand 31.07.):** `error_messages_dirty_fix_ok`, `login_cleanup_expired_records_ok`, `passkey_hints_markdown_ok`, `invite_placeholder_texts_ok`, `mand_2fa_optin_login_fix_ok`, `mitglied_label_und_inkonsistenz12_ok`, `login_lockout_ip_based_ok`, `backstage_path_configurable_ok`, `honeypot_login_attacks_log_ok`. Details Abschnitt 8a/10e. **Noch uncommitted, kein Tag:** syst-Passwort-Policy-Verschärfung (min:20) + Login-Hard-Block (Abschnitt 8/8a), fünf Doku-Dateien dieser Aktualisierung.
- **01.08.:** `anon_share_link_shortcode_ok` (Commit `15e21bd`) — anon-Login per teilbarem 7-stelligem Kurzcode-Link (neue Tabelle `sessiondb.share_link`), präzise Pro-Stufe-Invalidierung in `MandantPwListController::update()`, Datenschutz-Hinweis-Popup für anon entfernt. Details Abschnitt 10f.
- **03.08. (neu, aktueller Stand):** `trusted_device_cust_nofactor_fix_ok` (Commit `ddf5e55`) — cust-Trusted-Device-Cookie im Nicht-2FA-Login-Pfad nachgezogen (`CustLoginController::issueTrustedDeviceIfRequested()`, analog `MandantLoginController`). Details Abschnitt 10g.
- `.gitignore`: `.env`, `/vendor/`, `/node_modules/`, `/storage/logs/`, `fotosite_DDL_*.sql`

---

## 14. Workflow-Konventionen (dieser Chat)

- Claude Code Prompts: heller Kasten, beginnen mit Projekt-Header (Name + lokaler Pfad)
- Bash/PowerShell/SQL: dunkler Kasten mit Sprach-Label
- FTP-Upload-Listen: dunkler Kasten, Label „FTP-Upload", gefolgt von Git-Commit-Block
- Dateien nur mit ausdrücklicher Zustimmung erzeugen
- Sicherungswürdige Textausgaben (Specs, DDL) formatiert im Chat — Willy kopiert selbst nach Word
- PowerShell: `;` statt `&&`

---

## 15. Wiederverwendbarkeit für künftige Projekte

Stand `user_management_complete_ok` (20.06.2026) ist als **Startimplementierung für künftige Laravel-Projekte** geeignet. Diese Komponenten sind weitgehend domänen-unabhängig und lassen sich mit überschaubarem Aufwand in ein neues Projekt übernehmen:

**Direkt wiederverwendbar (kaum Fotosite-spezifisch):**
- Komplettes Auth-System: Login mit 2FA, Passkey-Infrastruktur (web-auth/webauthn-lib), Passwort-Reset-Flow, E-Mail-Änderung mit Bestätigungslink
- Datenschutz-/Einwilligungs-Mechanismus: Markdown-basierte Erläuterungsseite mit rollenabhängigen Textblöcken, PDF-Auslieferung, Policy-Versions-Popup mit syst-Verwaltungs-UI
- `unsaved-changes-guard`-Partial: eigenständiges, von der einbindenden Seite unabhängiges Alpine-Modal-System (nach den Bugfixes aus Abschnitt 12 robust gegen fehlende `x-data`-Vorfahren)
- FAQ/Infos-System: dateibasiert, dynamisch, kein DB-Bezug, Path-Traversal-sicher
- Willkommensseite-Mechanismus (`show_welcome`-Gate)
- Custom-Dropdown-Komponente (Alpine, kompakt geschlossen/ausgeschrieben offen)
- Sortier-/Such-Logik für Listen (client-seitig, `localeCompare('de')` für korrekte Umlaut-Sortierung)

**Projektspezifisch, aber als Vorlage nützlich:**
- 4-Datenbanken-Architektur (userdb/sessiondb/+2 Domain-DBs) — Trennungsprinzip übertragbar, konkrete Domain-Tabellen nicht
- Rollenmodell syst/mand/cust/anon — Konzept übertragbar (Plattform-Admin / Tenant / Endnutzer / Gast), Bezeichnungen projektspezifisch

**Nicht übertragbar:**
- Foto-/Content-Domäne (Activity Group/Subgroup, Sicherheitsstufen-Filterung) — das ist Phase 7 und folgt erst noch

**Vorgehen für ein neues Projekt:** Tag `user_management_complete_ok` auschecken, Domain-spezifische Tabellen/Models/Controller (fotodb, FotoDB/*) entfernen, Rollen-/Tabellennamen anpassen, Rest as-is übernehmen.

---

## 16. Offene Aufgaben & Anschlusspunkte (Stand 31.07.2026)

Dieser Abschnitt ist der **Einstiegspunkt für den nächsten Chat**. Er listet auf, was unmittelbar offen ist.

### 16a-0. NÄCHSTER SCHRITT: Gründlicher Passkey-Gesamttest
Die Passkey-Funktionalität (Phase 6) ist technisch vollständig implementiert, aber noch **nicht** gründlich getestet — frühere Doku-Stände, die Phase 6 als „✓ Fertig" oder „abgeschlossen" bezeichneten, waren zu stark formuliert (korrigiert 19.07.). Der ausstehende Test ist **kein** reiner iOS-Test, sondern ein **umfassender Test der gesamten Passkey-Funktionalität**: Registrierung, Login, Umbenennen, Löschen, Prompt-/Dismiss-Logik, jeweils für mand UND cust, über Windows/Android/iOS und die jeweils relevanten Browser hinweg, inkl. Grenzfälle (mehrere Rollen auf einem Gerät, Passkey-Widerruf nach Geräteverlust o. Ä.). Dies ist der **erste inhaltliche Punkt für den neuen Chat**, vor Fortsetzung von Phase 7. Details Abschnitt 9, Inkonsistenzen.md #11.

> **Hinweis 31.07.:** Zwischen dem 19.07.-Doku-Stand und heute kam ausschließlich Sicherheits-Härtung des Login-Systems dazwischen (IP-Sperre, Honeypot, Log-Kanäle, syst-Passwort-Policy + Login-Hard-Block, kleinere UI-Fixes — Abschnitt 8/8a/10e) sowie Passkey-Hinweistexte-Auslagerung (Abschnitt 9) und Login-Cleanup-Erweiterung (Abschnitt 4.1/4.2/10e). Am Passkey-**Testbedarf** selbst hat sich nichts geändert — der Gesamttest bleibt unverändert oberste Priorität. Zusätzlich vor Phase 7 zu erledigen: syst-Passwort-Policy + Honeypot-Infrastruktur committen (aktuell uncommitted), `HONEYPOT_LOCKOUT_MINUTES` und `LOG_STACK=daily` auf der Server-`.env` nachtragen (siehe Abschnitt 8a).

### 16a. Noch nicht umgesetzt
1. **`dirty`-Ausblendung nachziehen** bei zwei Views mit noch statischer Fehlermeldung: `system/mandanten/index.blade.php` und `customer/auth/register.blade.php`. Muster: `x-data="{dirty:false}"` + `@input="dirty=true"` + `x-show="!dirty"` auf `@error`-Block. (Weiterhin offen seit 26.06.)
2. **Regressionstest Android/Windows** der globalen Button-Animation. iOS-Button-Animation inkl. Long-Tap-Fix ist getestet und abgeschlossen.
3. **iOS Apple Mail:** Button-Text in Einladungsmails bleibt markierbar (Lerneffekt 22) — akzeptierte Einschränkung, kein offener Handlungsbedarf.
4. **`TRUSTED_DEVICE_DAYS`-Duplikat in `.env` bereinigen** (Zeile 17 vs. 97, siehe Inkonsistenzen #24) — vor produktiver Umstellung von 1 auf 7 Tage zwingend.
5. **Logout-Button-Dialog:** „Zurück"-Button ggf. zu „Verlassen ohne Löschen" umbenennen (besprochen, abgebrochen, siehe Inkonsistenzen #27).
6. **Trusted-Device-Gültigkeit von 1 auf 7 Tage** produktiv umstellen, sobald Testphase abgeschlossen (aktuell bewusst auf 1 Tag für Tests).

### 16b. Abnahmetest cust-Bereich
Weiterhin offen, mehrfach verschoben. Nach Abschluss Tag `cust_complete_ok`. Testplan liegt vor (Word + Excel, siehe Projektdateien).

### 16c. Testplan vorhanden
- `2026-06-20_Fotosite_V08_Testplan.docx` — 147 Testfälle, 26 Abschnitte A–Z, Querformat, 4 Geräte-Spalten (AH/iOS/AT/NB)
- `20260621_Fotosite_V08_Testplan_1.xlsx` — 588 Zeilen (147×4 Geräte), durchgehend nummeriert 1–588, zusätzliche Spalte „Testfall-Nr." (fett, 1–147 je Gerät), Status-Dropdown. Aktuell pausiert.

### 16d. Phase 7 (danach)
Priorität: mand-Content (Fotoupload & Fotoadmin) VOR Cust-UI. Details in Abschnitt 11 / Notfall-Dokument. **Vor Phase 7 steht der gründliche Passkey-Gesamttest (Abschnitt 16a-0) an.**

### 16e. Bekannte Mail-Zustellungs-Einschränkung
E-Mail-Änderungs-Bestätigungsmails landen häufig im Spam (Alfahosting ohne DKIM/SPF). Aktuell per UI-Hinweis (amber) im E-Mail-Ändern-Modal adressiert. Mittelfristig: SPF-/DKIM-Setup auf dem Mailserver prüfen.

### 16f. Dokumentations-Pflege
- Konzeptdatei wurde am 19.07. aktualisiert (siehe Konzept_Fotosite_V8.md).
- Bei jedem neuen stabilen Stand: PROJECT_CONTEXT + Notfall-Dokument + Projektstatus synchron halten.
