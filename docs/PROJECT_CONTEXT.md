# PROJECT_CONTEXT.md — Fotosite V08

> Stand: 29.06.2026 — Tag: `ios_button_feedback_ok` (+ nachfolgende Bugfixes 29.06.). Seit 26.06.: globale iOS/Android-Button-Animation via `app.css` (CSS `:active` + globales `user-select:none`), zahlreiche `<a>`→`<button>`-Umbauten (Zurück-/Aktions-Links), Doppel-Submit-Schutz auf allen Login-Buttons (cust/mand/syst), syst-Löschlogik korrigiert (primary löscht non-primaries), `MandAccountDeletedMail` bei syst-seitiger mand-Löschung, deutsche Passwort-Fehlermeldungen in 7 Controllern, PW-Hinweistexte syst auf min:12 korrigiert. Phase 7 (Foto-Content) ist der nächste Schritt. Abnahmetest cust-Bereich weiterhin offen.
>
> **Offen (Stand 29.06.):** (a) `dirty`-Ausblendung bei `system/mandanten/index.blade.php` + `customer/auth/register.blade.php` nachziehen, (b) Abnahmetest cust-Bereich (Tag: `cust_complete_ok`), (c) iOS-Passkey-Test (Gerät bestellt). Details siehe Abschnitt 16.
>
> **KORREKTUR 29.06. (wichtig):** `resources/views/system/login.blade.php` ist **NICHT** tot — sie ist die **aktive** syst-Login-View. `SystemLoginController@login` rendert `view('system.login')`; Login UND 2FA laufen über diese Datei (2FA-Block via `show_2fa`-Flash-Variable konditionell eingeblendet). Die frühere Einstufung als „tote Datei" (Altlasten 2b, Lerneffekt 17) war falsch und ist hiermit aufgehoben.

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

> **Einziger echter Foreign Key im gesamten Schema:** `fotodb.activity_subgroup.ag_id → fotodb.activity_group.ag_id`. Alle anderen Verknüpfungen sind logisch, ohne FK-Constraint.

### 4.1 `userdb` — User Management
Connection: `userdb`

| Table | PK | Purpose |
|---|---|---|
| `syst_user` | `syst_id` | System-Admin-Accounts (inkl. `is_primary` TINYINT(1) Default 0 — primäre Admins können nicht gelöscht werden) |
| `mand_user` | `mand_id` | Galerist:in-Accounts (inkl. `mand_pw_hash`, `mand_cust_2fa`, `mand_2fa_opt_in`, `has_public_content`, `active`, `ds_accepted_at`, `ds_version`, `upload_terms_accepted_at`, `upload_terms_version`, `show_welcome` TINYINT(1) Default 1) |
| `cust_user` | `cust_id` | Mitglieder-Accounts (inkl. `ds_accepted_at`, `ds_version`, `upload_terms_accepted_at`, `upload_terms_version`, `show_welcome` TINYINT(1) Default 1) |
| `cust_pcode` | `pcode_id` | Sicherheitsstufe je Mitglied+Mandant. Spalten: `cust_passcode` varchar(255) (= sec_level des cust bei diesem mand, „enthält die Ziffer des Securitylevel"), `cust_alias`, `pcode_prefstat`, `mand_id`, `cust_id`, `cust_mailrequest` |
| `invite` | `inv_id` | Einladungen/Reset-Tokens für syst/mand/cust (`inv_type`: register\|pw_reset\|email_change; `inv_user_type`: syst\|mand\|cust; `inv_email` dient bei email_change als Speicher für neue Adresse; `is_primary` TINYINT(1) Default 0 — nur für syst-Einladungen relevant) |
| `passkey` | `pk_id` | WebAuthn-Credentials (`user_type`, `user_id`, `credential_id`, `public_key`, `sign_count`, `device_name`, `last_used_at`) |
| `passkey_dismissed` | `pd_id` | „Nie wieder fragen"-Einträge je User+OS+Gerät (`user_type`, `user_id`, `os`: win\|andr\|ios, `ua_hash`) |
| `cust_invite` | `invite_id` | **Veraltetes Relikt** — Struktur identisch zu `sessiondb.cust_invite`, wird nicht verwendet, aus Vorsicht nicht gelöscht |
| `policy_versions` | `pv_key` | Aktuelle Policy-Versionsnummern (`pv_key`: ds_version\|upload_version, `pv_value`, `updated_at`) — wird von syst per UI erhöht, triggert Popup bei mand/cust beim nächsten Login |

### 4.2 `sessiondb` — Sessions, Kurzzeit-Kennwörter, 2FA
Connection: `sessiondb`

| Table | PK | Purpose |
|---|---|---|
| `session` | `sess_id` | Eine Zeile pro aktiver Session (anon + authentifiziert), `sess_token` für Lookups, `payload` (JSON) |
| `pw_list` | `pwlist_id` | Bis zu 6 zeitlich begrenzte Kurzzeit-Kennwörter je Mandant (`pw1`–`pw6`, AES-verschlüsselt, `valid_from`/`valid_until`) |
| `twofa_code` | `tfa_id` | 2FA-Codes (6-stellig, `tfa_purpose`: login\|pw_change\|critical, `tfa_expires_at`, `tfa_used`) |
| `cust_invite` | `invite_id` | **Führende Tabelle** für Mitglieder-Einladungen (`mand_id`, `cust_email`, `cust_alias`, `sec_level`, `token`, `expires_at`, `used`) |

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

## 5. MVC-Struktur (vollständig, Stand 29.06.2026)

> Diese Übersicht ist gegen die Dateiliste vom 29.06. verifiziert. Maßgeblich
> bleibt der implementierte Code; bei Abweichung gilt der Code.

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
│   ├── SessionDb/   (Session, PwList, CustInvite, TwofaCode, SessionDbModel)
│   └── UserDb/      (SystUser, MandUser, CustUser, CustPcode, Invite,
│                     Passkey, PasskeyDismissed, PolicyVersion, UserDbModel)
├── Mail/
│   ├── InviteMail.php
│   ├── CustInviteMail.php
│   ├── CustAccountDeletedMail.php
│   ├── MandAccountDeletedMail.php
│   ├── EmailChangeMail.php
│   └── TwoFactorCodeMail.php
│   # ModerationMail.php — geplant Phase 7, noch nicht vorhanden
├── Services/
│   ├── FotoDB/FotoDbService.php           # Phase-7-Gerüst (abstrakte Basisklasse, keine Methoden)
│   ├── FotoBlobDb/FotoBlobDbService.php   # Phase-7-Gerüst (abstrakte Basisklasse, keine Methoden)
│   ├── SessionDb/
│   │   ├── SessionDbService.php           # Basisklasse für TwofaService, keine eigenen Methoden
│   │   ├── SessionIntegrityService.php    # genau ein *_id in Session
│   │   └── TwofaService.php               # generate(), verify(), generateCode(), verifyCode(), purgeExpired()
│   ├── UserDb/UserDbService.php           # Phase-7-Gerüst (abstrakte Basisklasse, keine Methoden)
│   └── Passkey/
│       ├── PasskeyRepository.php
│       ├── PasskeySessionStorage.php
│       └── PasskeyUserEntityRepository.php
├── Providers/      (AppServiceProvider, PasskeyServiceProvider)
├── View/Components/ (AppLayout, GuestLayout)
└── helpers.php     # genitivName(), detectOsPlatform(), detectBrowser()

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

> **KORREKTUR 29.06.:** Die zuvor dokumentierten syst-Anforderungen (14 Zeichen, Sonderzeichen, HIBP) entsprachen NICHT der Implementierung. Die Controller (`SystemUserController`, `SystemProfileController`, `SystemMandantController`) erzwingen für syst tatsächlich nur `min:12` ohne weitere Regeln. Die View-Hinweistexte wurden am 29.06. controller-konform auf „Mindestens 12 Zeichen" korrigiert. Die folgende Tabelle gibt jetzt den implementierten Stand wieder.

| Kriterium | syst | mand | cust |
|---|---|---|---|
| Mindestlänge | 12 Zeichen | 12 Zeichen | 10 Zeichen |
| Groß-/Kleinbuchstaben | — (nur min:12) | ✅ | ✅ |
| Ziffern | — (nur min:12) | ✅ | ✅ |
| Sonderzeichen | — (nur min:12) | ✅ | optional |
| HIBP (`uncompromised()`) | — | ✅ | — |
| Username im PW verboten | — | — | — |

> Hinweis: syst ist bewusst nur auf `min:12` gesetzt (kein mixedCase/numbers/symbols/uncompromised). Falls strengere syst-Regeln gewünscht sind, müssten die drei genannten Controller UND die View-Hinweistexte gemeinsam angepasst werden.

**Deutsche Validierungs-Fehlermeldungen (29.06.):** Alle sieben Passwort-verarbeitenden Controller (`CustPasswordResetController`, `MandPasswordResetController`, `CustSelfController`, `MandantSelfController`, `SystemUserController` [2 Stellen], `SystemProfileController`, `SystemMandantController`) erhielten `messages`-Arrays mit deutschen Texten:
- `password.confirmed` → „Die eingegebenen Passwörter stimmen nicht überein."
- `password.min` / `.mixed_case` / `.numbers` / `.symbols` / `.uncompromised` → „Das Passwort erfüllt nicht die Mindestanforderungen."
- `current_password` → „Das eingegebene Passwort ist nicht korrekt."

Zuvor erschienen Laravels englische Standard-Meldungen (kein `lang/`-Verzeichnis vorhanden). Die Anforderungs-Hinweistexte sind in allen PW-Dialogen vorhanden und controller-konform.

`pw_list`-Kurzzeit-Kennwörter (pw1–pw6): min. 8 Zeichen, AES-verschlüsselt (nicht gehasht — mand muss Klartext einsehen können, um sie zu teilen). **Trim-Asymmetrie beachten:** pw1–pw6 werden beim Speichern durch Laravels `TrimStrings`-Middleware getrimmt (Feldname nicht exempt), das eingegebene `password` beim Anon-Login-Vergleich jedoch nicht (`password` ist in `$except`) → Anon-Login trimmt das eingegebene Passwort daher explizit per Code (`CustLoginController`, Tag `touch_and_trim_ok`).

---

## 9. Passkeys (WebAuthn) — Status: Phase 6 abgeschlossen

### Implementiert
- `web-auth/webauthn-lib 5.3.5` direkt integriert (Registrierung + Login für `mand` und `cust`)
- `userVerification: required`, `authenticatorAttachment: platform`, `residentKey: required`
- Passkey-Verwaltung (`/mandant/passkeys`, `/customer/passkeys`): registrieren, umbenennen, löschen, Geräteliste mit `last_used_at`
- **Plattform-/Browser-Erkennung:** `detectOsPlatform()` (win/andr/ios/unknown) und `detectBrowser()` (chrome/firefox/edge/safari/samsung/unknown) in `app/helpers.php`, Session-Keys `_passkey_os`, `_passkey_browser`, `_passkey_uahash`
- **Kontextabhängige Hinweistexte** auf Passkey-Verwaltungsseiten — je OS+Browser-Kombination, nutzerorientiert (wo gilt der Passkey, nicht wo wird er gespeichert)
- **Passkey-Prompt-Logik:** Modal (mand) / Banner (cust) erscheint einmalig nach Login, wenn kein Passkey für dieses OS existiert und kein `passkey_dismissed`-Eintrag (User+OS+Gerät) vorliegt. Buttons: „Einrichten" / „Nie wieder fragen" / „Später". **Hinweis 20.06.:** Die neue Willkommensseite (`show_welcome`, Abschnitt 10c) ist generisches Onboarding (Markdown-Text + „Gelesen") — sie enthält noch KEINEN Passkey-Einrichtungslink. Die ursprünglich geplante strukturelle Lösung des Cust-Passkey-Hinweises über die Welcome-Seite ist damit weiterhin offen, falls gewünscht müsste der Link nachträglich in `willkommen_cust.md` ergänzt oder die Logik im `WelcomeScreenController` erweitert werden.
- Getestet: Windows (Hello aktiv/inaktiv, Chrome/Firefox/Edge), Android (Handy+Tablet, Chrome mit Google-Sync, Firefox lokal), cust-Banner, Grenzfälle (mehrere Rollen auf einem Windows-Konto)
- **Ausstehend:** iOS-Test (Block 8) — Gerät (iPhone SE 2020) bestellt

### Wichtige Erkenntnisse
- Passkey ist an die **Windows-Anmeldung** gebunden (nicht an Fotosite-Account direkt) — Login funktioniert nur mit demselben Windows-Konto wie bei der Registrierung
- Chrome: Sync über Google-Konto (Windows ↔ Android)
- Firefox: lokal, kein Sync — pro Gerät eigener Passkey empfohlen
- Edge: wie Windows Hello, lokal, kein Sync (zusätzlich schlechte Datenschutz-Reputation, daher nicht aktiv empfohlen)
- iOS: iCloud Keychain, alle Browser nutzen denselben Speicher (Apple erzwingt das)
- `passkey_dismissed` schlüsselt auf `(user_type, user_id, os, ua_hash)` — pro Gerät+Browser+OS-Kombination eigener Dismiss-Status

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
- `POST /customer/ds/hinweis-ok` (anon-Popup-Bestätigung, Session-Flag)

**Einwilligung:**
- cust: Pflicht-Checkbox bei Registrierung → `cust_user.ds_accepted_at` + `ds_version`
- mand: zwei Pflicht-Checkboxen → `mand_user.ds_accepted_at`/`ds_version` + `upload_terms_accepted_at`/`upload_terms_version`
- anon: Popup nach korrektem Passcode (Hinweis + Link), Kenntnisnahme via Session-Flag `_ds_hinweis_gezeigt`, KEINE DB-Speicherung
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
Alle Passwort-Felder (Login-Modal, Registrierungen, PW-Reset, PW-Ändern-Modals, pw1–pw6) erhalten ein Auge-Icon (Alpine `x-data="{ show:false }"`, `:type="show ? 'text' : 'password'"`, inline SVG offen/geschlossen, `pr-10` am Input). Jedes Feld eigener Scope; bei Feldern mit bestehendem `x-data` (dirty-State) wird `show` dort ergänzt. `pw1–pw6` hatten bereits einen Toggle. `system/login.blade.php` ausgenommen (tote Datei).

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

---

## 13. Git & Tags

- **Repo:** `github.com/fotosite/fotosite` (privat)
- **Aktiver Branch:** `feature/passkey-infra`
- **Lokaler Pfad:** `D:\mwa\Projekte\fotosite\Fotosite_V08\claudescode\fotosite`
- **Meilenstein-Tag:** `user_management_complete_ok` (20.06.2026) — komplette Benutzer-/Sicherheitsverwaltung abgeschlossen, sicherer Rückfallpunkt vor Phase 7 (Content). Auch als Startpunkt für künftige Projekte geeignet (siehe Abschnitt 15).
- **Relevante Tags:** `phase5_cust_login_ok`, `p6_passkey_prompt_ok`, `p6_passkey_ui_ok`, `ag_banner_removed_ok`, `sec_level_sync_ok`, `datenschutz_ok`, `cust_invite_ok`, `mand_invite_ok`, `mand_register_ok`, `ui_begriffe_ok`, `mand_adressfelder_ok`, `cust_adressfelder_ok`, `email_pw_modal_ok`, `registrierung_ok`, `rechtliches_ok`, `pw_reset_mail_ok`, `policy_popup_ok`, `cust_delete_mail_ok`, `login_labels_ok`, `unsaved_changes_guard_ok`, `galerien_ajax_ok`, `mand_mitgliederliste_ok`, `welcome_screen_ok`, `faq_feature_ok`, `user_management_complete_ok`, `redirect_loop_fix_ok`, `policy_view_link_fix_ok`, `syst_primary_ok`, `fixes_23jun_ok`, `touch_and_trim_ok`, `pw_eye_ok`, `ios_button_feedback_ok`
- **Nach `ios_button_feedback_ok` (29.06., noch ungetaggt):** syst-Löschlogik-Fix, `MandAccountDeletedMail`, deutsche PW-Fehlermeldungen, PW-Hinweistexte syst min:12. Committet/hochgeladen.
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

## 16. Offene Aufgaben & Anschlusspunkte (Stand 26.06.2026)

Dieser Abschnitt ist der **Einstiegspunkt für den nächsten Chat**. Er listet auf, was unmittelbar offen ist.

### 16a. Noch nicht umgesetzt
1. **`dirty`-Ausblendung nachziehen** bei zwei Views mit noch statischer Fehlermeldung: `system/mandanten/index.blade.php` und `customer/auth/register.blade.php`. Muster: `x-data="{dirty:false}"` + `@input="dirty=true"` + `x-show="!dirty"` auf `@error`-Block.
2. **Regressionstest Android/Windows** der globalen Button-Animation (sicherstellen, dass `:active`-Skalierung + `user-select:none` auf Desktop/Android wie gewünscht wirken). iOS-Button-Animation ist getestet und abgeschlossen.
3. **iOS Apple Mail:** Button-Text in Einladungsmails bleibt markierbar (Lerneffekt 22) — akzeptierte Einschränkung, kein offener Handlungsbedarf.

### 16b. Abnahmetest cust-Bereich
Weiterhin offen, mehrfach verschoben. Nach Abschluss Tag `cust_complete_ok`. Testplan liegt vor (Word + Excel, siehe Projektdateien).

### 16c. Testplan vorhanden
- `2026-06-20_Fotosite_V08_Testplan.docx` — 147 Testfälle, 26 Abschnitte A–Z, Querformat, 4 Geräte-Spalten (AH/iOS/AT/NB)
- `20260621_Fotosite_V08_Testplan_1.xlsx` — 588 Zeilen (147×4 Geräte), durchgehend nummeriert 1–588, zusätzliche Spalte „Testfall-Nr." (fett, 1–147 je Gerät), Status-Dropdown. Aktuell pausiert.

### 16d. Phase 7 (danach)
Priorität: mand-Content (Fotoupload & Fotoadmin) VOR Cust-UI. Details in Abschnitt 11 / Notfall-Dokument. iOS-Passkey-Test ausstehend (iPhone SE 2020 bestellt).

### 16e. Bekannte Mail-Zustellungs-Einschränkung
E-Mail-Änderungs-Bestätigungsmails landen häufig im Spam (Alfahosting ohne DKIM/SPF). Aktuell per UI-Hinweis (amber) im E-Mail-Ändern-Modal adressiert. Mittelfristig: SPF-/DKIM-Setup auf dem Mailserver prüfen.

### 16f. Dokumentations-Pflege
- Konzeptdatei ist bekannt veraltet (niedrige Priorität).
- Bei jedem neuen stabilen Stand: PROJECT_CONTEXT + Notfall-Dokument + Projektstatus synchron halten.
