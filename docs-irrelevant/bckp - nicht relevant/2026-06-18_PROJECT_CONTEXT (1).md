# PROJECT_CONTEXT.md — Fotosite V08

> Stand: 18.06.2026 — Phase 6 abgeschlossen. Umfangreiche Admin/Auth-Features implementiert (Adressfelder, E-Mail/PW-Modal, Policy-Popup, UI-Begriffe). Phase 7 (Content) steht an.

## 1. Project Overview

Multi-Tenant-Fotogalerie-Plattform (Hobbyprojekt, Ziel: fertige, nutzbare Website). Jeder Mandant (Galerist:in) verwaltet eigenen Foto-Content (Activity Groups, Subgroups), eigene Mitglieder und eine Profilseite. Vier Rollen mit unterschiedlichen Frontends.

**User-Rollen:**
- `syst` — System-Admin (Plattformbetreiber), UI: „System-Admin"
- `mand` — Mandant/Tenant, UI: „Galerist:in"
- `cust` — Mitglied (registriert oder anonym mit Kurzzeit-Kennwort), UI: „Mitglied/Mitglieder"
- `anon` — Anonymer Besucher (sessionbasiert, kein Login)

**Sicherheitsstufen:** Foto-Objekte, Activity Groups und Subgroups tragen ein `*_sec_level`-Feld (`TINYINT UNSIGNED`, Werte 0–6), das die Sichtbarkeit steuert. Davon zu unterscheiden ist `sec_code` (varchar): ein mand-spezifischer Anon-Zugangscode, genau einer Sicherheitsstufe und einem Mandanten zugeordnet. `sec_code` ≠ `sec_level`. (Typ später per `ALTER ... MODIFY` problemlos auf INT erweiterbar, falls je >255 Stufen nötig.)

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
| `syst_user` | `syst_id` | System-Admin-Accounts |
| `mand_user` | `mand_id` | Galerist:in-Accounts (inkl. `mand_pw_hash`, `mand_cust_2fa`, `mand_2fa_opt_in`, `has_public_content`, `active`, `ds_accepted_at`, `ds_version`, `upload_terms_accepted_at`, `upload_terms_version`) |
| `cust_user` | `cust_id` | Mitglieder-Accounts (inkl. `ds_accepted_at`, `ds_version`, `upload_terms_accepted_at`, `upload_terms_version`) |
| `cust_pcode` | `pcode_id` | Passcode/Sicherheitsstufe je Mitglied+Mandant (`cust_pcode`, `cust_alias`, `pcode_prefstat`) |
| `invite` | `inv_id` | Einladungen/Reset-Tokens für syst/mand/cust (`inv_type`: register\|pw_reset\|email_change; `inv_user_type`: syst\|mand\|cust; `inv_email` dient bei email_change als Speicher für neue Adresse) |
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
| `mand_profile` | `mp_id` | **Cust-sichtbare Profilseite** des Mandanten (Selbstvorstellung) — gehört zum Foto-Content, nicht zur Eigenverwaltung. `mp_name`/`mp_title` varchar(255), `mp_text` text, `mp_title_start`/`mp_subtitle_start` (Startseiten-Überschriften) |
| `mp_fo_context` | — | Pivot: Mandant-Profil ↔ Foto |

### 4.4 `fotoblobdb` — Binärdaten Stufe 6
Connection: `fotoblobdb`

| Table | PK | Purpose |
|---|---|---|
| `foto_obj` | `fod_id` | BLOB-Speicher (`fod_obj`) für Fotos/Videos der Sicherheitsstufe 6, verknüpft über `fo_id` |

**Wichtig:** Schema ist vollständig vordefiniert, außerhalb von Laravel verwaltet. Keine Laravel-Migrationen für Domain-Tabellen — DDL direkt per SSH/phpMyAdmin auf dem Server. `database/migrations/` enthält nur Breeze-Reste.

---

## 5. MVC-Struktur (Auszug, Stand Phase 6)

```
app/
├── Extensions/
│   └── SessionDbSessionHandler.php   # Custom Session-Driver (sess_id PK)
├── Http/
│   ├── Controllers/
│   │   ├── Passkey/
│   │   │   ├── MandPasskeyController.php   # index, registrationOptions, register, rename, destroy, dismiss
│   │   │   └── CustPasskeyController.php   # identisch für cust
│   │   └── UserDb/
│   │       ├── SystemLoginController.php
│   │       ├── SystemUserController.php       # inkl. Passwort-Reset-Vorlage (syst)
│   │       ├── MandantLoginController.php     # Login, 2FA, OS/Browser-Erkennung, Passkey-Prompt-Logik
│   │       ├── MandantDashboardController.php
│   │       ├── MandantSelfController.php
│   │       ├── MandantCustController.php
│   │       ├── MandantPwListController.php
│   │       ├── MandPasswordResetController.php   # NEU: Passwort-Reset mand
│   │       ├── CustLoginController.php
│   │       ├── CustRegisterController.php
│   │       ├── CustDashboardController.php
│   │       └── CustPasswordResetController.php   # NEU: Passwort-Reset cust
│   ├── DatenschutzController.php              # NEU 16.06.: Erläuterung (Markdown→HTML), PDF-Auslieferung, anon-Hinweis
│   └── Middleware/
│       ├── NoIndexHeader.php
│       ├── SessionHijackProtection.php          # IP-Hash + UA-Hash
│       ├── SessionIdleTimeout.php               # rollenspezifisch
│       ├── ValidateUserExists.php
│       ├── RequireRole.php
│       ├── SessionIntegrityService.php          # genau ein *_id in Session
│       └── MandantActiveCheck.php
├── Models/
│   ├── FotoDB/        (ActivityGroup, ActivitySubgroup, FotoObj, MandProfile, AgFoContext, AsgFoContext, MpFoContext)
│   ├── FotoBlobDb/     (FotoObjDb)
│   ├── SessionDb/     (Session, PwList, CustInvite)
│   └── UserDb/        (SystUser, MandUser, CustUser, CustPcode, Invite, Passkey, PasskeyDismissed)
├── Mail/
│   ├── InviteMail.php             # register + pw_reset (syst/mand/cust)
│   ├── CustInviteMail.php         # Mitglieder-Einladung, personalisiert mit cust_alias
│   ├── TwoFactorCodeMail.php
│   └── ModerationMail.php         # geplant Phase 7
├── Services/Passkey/
│   ├── PasskeyRepository.php
│   ├── PasskeySessionStorage.php
│   └── PasskeyUserEntityRepository.php
├── Providers/
│   ├── AppServiceProvider.php      # registriert sessiondb Session-Driver
│   └── PasskeyServiceProvider.php
└── helpers.php                     # genitivName(), detectOsPlatform(), detectBrowser()

routes/
├── web.php       # Route 'home' (GET /) → auth/login-modal.blade.php
├── system.php    # Prefix /system, Rolle syst
├── mandant.php   # Prefix /mandant, Rolle mand
└── customer.php  # Prefix /customer, Rolle cust/anon
```

**Wichtige Korrektur:** Die Home-Route (`GET /`) rendert `auth/login-modal.blade.php`, **nicht** `welcome.blade.php`. `welcome.blade.php` ist eine tote Datei (keine Route verweist darauf) — als Altlast markiert, zur Bereinigung vorgemerkt.

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

| Kriterium | syst | mand | cust |
|---|---|---|---|
| Mindestlänge | 14 Zeichen | 12 Zeichen | 10 Zeichen |
| Groß-/Kleinbuchstaben | ✅ | ✅ | ✅ |
| Ziffern | ✅ | ✅ | ✅ |
| Sonderzeichen | ✅ | ✅ | optional |
| HIBP (`uncompromised()`) | ✅ | ✅ | — |
| Username im PW verboten | ✅ | ✅ | ✅ |

`pw_list`-Kurzzeit-Kennwörter (pw1–pw6): min. 8 Zeichen, AES-verschlüsselt (nicht gehasht — mand muss Klartext einsehen können, um sie zu teilen).

---

## 9. Passkeys (WebAuthn) — Status: Phase 6 abgeschlossen

### Implementiert
- `web-auth/webauthn-lib 5.3.5` direkt integriert (Registrierung + Login für `mand` und `cust`)
- `userVerification: required`, `authenticatorAttachment: platform`, `residentKey: required`
- Passkey-Verwaltung (`/mandant/passkeys`, `/customer/passkeys`): registrieren, umbenennen, löschen, Geräteliste mit `last_used_at`
- **Plattform-/Browser-Erkennung:** `detectOsPlatform()` (win/andr/ios/unknown) und `detectBrowser()` (chrome/firefox/edge/safari/samsung/unknown) in `app/helpers.php`, Session-Keys `_passkey_os`, `_passkey_browser`, `_passkey_uahash`
- **Kontextabhängige Hinweistexte** auf Passkey-Verwaltungsseiten — je OS+Browser-Kombination, nutzerorientiert (wo gilt der Passkey, nicht wo wird er gespeichert)
- **Passkey-Prompt-Logik:** Modal (mand) / Banner (cust) erscheint einmalig nach Login, wenn kein Passkey für dieses OS existiert und kein `passkey_dismissed`-Eintrag (User+OS+Gerät) vorliegt. Buttons: „Einrichten" / „Nie wieder fragen" / „Später"
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

### Erfolgsmeldungen bei Kontoerstellung
Nach cust-Registrierung: „Konto erfolgreich angelegt. Bitte melde dich jetzt als Mitglied an." Nach mand-Registrierung: „... als Galerist:in an." + `login_page=mand` für korrektes Modal-Tab.

### Datenschutz-Buttons in Einstellungen
mand + cust: Links zu Datenschutz-Erläuterung und Upload-Bedingungen direkt in der Einstellungsseite.

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
- `_sec_level`-Session-Key in Content-Filterung einbinden (aktuell gesetzt, aber ungenutzt)
- Welcome-Seite (`welcome.blade.php`): Willkommenstext + Passkey-Einrichtungslink für cust — löst zugleich den Cust-Passkey-Hinweis strukturell
- `ModerationMail` implementieren (erst nach Content-Upload)

---

## 12. Bekannte Inkonsistenzen / Altlasten

| # | Befund | Status |
|---|---|---|
| 1 | `userdb.cust_invite` ist Relikt, `sessiondb.cust_invite` ist führend | Bewusst nicht gelöscht (Vorsicht) |
| 2 | `resources/views/welcome.blade.php` von keiner Route gerendert | Kein Altlast — vorgesehene Willkommensseite, Logik noch offen (Phase 7) |
| 3 | `_last_activity` liegt im JSON-Session-Payload, nicht als DB-Spalte | SQL-Updates auf die DB-Spalte wirken sich nicht aus — nur `session()->put()` verwenden |
| 4 | `ag_fo_context.ag_banner` (obsolet) aus DDL und Code entfernt | Erledigt 14.06. (Tag `ag_banner_removed_ok`); `ag_is_banner` bleibt |
| 5 | `*_sec_code` → `*_sec_level` (TINYINT UNSIGNED) in fotodb-DDL + Models | **Erledigt 16.06.** (Tag `sec_level_sync_ok`); `fo_is_video` ergänzt |
| 6 | 403 ohne Logeintrag → `abort(403)`/403-Responses erscheinen NICHT im Laravel-Log | Bei 403 zuerst Controller auf `abort(403)` prüfen, nicht Server/Middleware |
| 7 | Korrigierte Datei lokal sauber, aber 403/Fehler bleibt → Datei war nie per FTP hochgeladen | Nach Claude-Code-Fix per grep Server-Datei gegen lokale prüfen, bevor weiter diagnostiziert wird |
| 8 | Alfahosting: kein mod_security-Block auf „datenschutz" (Annahme war falsch) | URL-Segment `/ds/` bleibt trotzdem bestehen; echte Ursache war `abort(403)` im Controller |
| 9 | Veralteter Laravel-Cache korrumpiert Daten nach Controller-Änderungen | Nach JEDER Änderung an Controllern, Routen, Config: `php artisan route:clear ; php artisan config:clear ; php artisan view:clear ; php artisan cache:clear` — bestätigt durch Hash-Korruption nach E-Mail-Änderung ohne cache:clear |

---

## 13. Git & Tags

- **Repo:** `github.com/fotosite/fotosite` (privat)
- **Aktiver Branch:** `feature/passkey-infra`
- **Lokaler Pfad:** `D:\mwa\Projekte\fotosite\Fotosite_V08\claudescode\fotosite`
- **Relevante Tags:** `phase5_cust_login_ok`, `p6_passkey_prompt_ok`, `p6_passkey_ui_ok`, `ag_banner_removed_ok`, `sec_level_sync_ok`, `datenschutz_ok`, `cust_invite_ok`, `mand_invite_ok`, `mand_register_ok`, `ui_begriffe_ok`, `mand_adressfelder_ok`, `cust_adressfelder_ok`, `email_pw_modal_ok`, `registrierung_ok`, `rechtliches_ok`, `pw_reset_mail_ok`, `policy_popup_ok`
- `.gitignore`: `.env`, `/vendor/`, `/node_modules/`, `/storage/logs/`, `fotosite_DDL_*.sql`

---

## 14. Workflow-Konventionen (dieser Chat)

- Claude Code Prompts: heller Kasten, beginnen mit Projekt-Header (Name + lokaler Pfad)
- Bash/PowerShell/SQL: dunkler Kasten mit Sprach-Label
- FTP-Upload-Listen: dunkler Kasten, Label „FTP-Upload", gefolgt von Git-Commit-Block
- Dateien nur mit ausdrücklicher Zustimmung erzeugen
- Sicherungswürdige Textausgaben (Specs, DDL) formatiert im Chat — Willy kopiert selbst nach Word
- PowerShell: `;` statt `&&`
