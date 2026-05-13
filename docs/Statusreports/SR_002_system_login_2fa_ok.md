# Statusreport — Fotosite V08 — vollständig – Git: system_login_2fa_ok

## 1. Infrastruktur

| | Lokal | Server |
|---|---|---|
| Betriebssystem | Windows (PowerShell) | Linux (Alfahosting) |
| PHP | 8.5.6 | 8.4.12 |
| Deployment | FTP + SSH | — |
| Git Remote | github.com/fotosite/fotosite.git | — |
| Lokaler Pfad | D:\mwa\Projekte\fotosite\Fotosite_V08\claudescode\fotosite | /var/www/vhosts/u14bc1w8.host159.alfahosting-server.de/fotos.martinwagner.de |
| Document Root | /public | /public |
| Shell-Alias | fotosite in $PROFILE | — |
| Mailpit | 127.0.0.1:1025 / :8025 | — |

## 2. Laravel Installation

| Komponente | Version |
|---|---|
| Laravel Framework | ^13.8 |
| Laravel Breeze | ^2.4 |
| PHP | 8.5 lokal / 8.4 Server |
| Templating | Blade |
| Frontend JS | Alpine.js |
| CSS | Tailwind CSS |
| Build Tool | Vite |

## 3. Datenbanken

| Connection | Datenbank | Zweck |
|---|---|---|
| userdb | u14bc1w8_v08_userdb | User-Verwaltung |
| sessiondb | u14bc1w8_v08_sessiondb | Sessions + Passwortlisten + 2FA |
| fotodb | u14bc1w8_v08_fotodb | Foto-Content |
| fotoblobdb | u14bc1w8_v08_fotoblobdb | Foto-BLOBs |

### Tabellen userdb

| Tabelle | Inhalt | Änderungen |
|---|---|---|
| syst_user | System-Administratoren | — |
| mand_user | Mandanten | mand_cust_2fa BOOLEAN ergänzt |
| cust_user | Customers | cust_2fa_opt_in BOOLEAN ergänzt |
| cust_pcode | Passcodes je Customer/Mandant | — |

### Tabellen sessiondb

| Tabelle | Inhalt |
|---|---|
| session | Aktive Sessions aller User-Typen |
| pw_list | Zeitlich begrenzte Passwortlisten je Mandant |
| twofa_code | 2FA-Codes (neu) |

### Tabellen fotodb

| Tabelle | Inhalt |
|---|---|
| foto_obj | Foto/Video-Metadaten |
| activity_group | Aktivitätengruppen je Mandant |
| activity_subgroup | Subgruppen je Aktivitätengruppe |
| ag_fo_context | Pivot: Gruppe ↔ Foto |
| asg_fo_context | Pivot: Subgruppe ↔ Foto |
| mand_profile | Mandanten-Profilseite |
| mp_fo_context | Pivot: Profil ↔ Foto |

### Tabellen fotoblobdb

| Tabelle | Inhalt |
|---|---|
| foto_obj_db | BLOB-Speicher für Fotos/Videos |

## 4. Models

| Model | Tabelle | PK | DB | Version |
|---|---|---|---|---|
| SystUser | syst_user | syst_id | userdb | — |
| MandUser | mand_user | mand_id | userdb | v1.1.0 |
| CustUser | cust_user | cust_id | userdb | v1.1.0 |
| CustPcode | cust_pcode | pcode_id | userdb | — |
| Session | session | sess_id | sessiondb | — |
| PwList | pw_list | pwlist_id | sessiondb | — |
| TwofaCode | twofa_code | tfa_id | sessiondb | v1.1.0 |
| ActivityGroup | activity_group | ag_id | fotodb | — |
| ActivitySubgroup | activity_subgroup | asg_id | fotodb | — |
| FotoObj | foto_obj | fo_id | fotodb | — |
| AgFoContext | ag_fo_context | ag_fo_id | fotodb | — |
| AsgFoContext | asg_fo_context | asg_fo_id | fotodb | — |
| MandProfile | mand_profile | mp_id | fotodb | — |
| MpFoContext | mp_fo_context | mp_fo_id | fotodb | — |
| FotoObjDb | foto_obj_db | fod_id | fotoblobdb | — |

## 5. MVC Struktur

```
app/
├── Extensions/
│   └── SessionDbSessionHandler.php v2.3.0
├── Http/
│   ├── Controllers/
│   │   ├── Controller.php
│   │   ├── ProfileController.php
│   │   ├── Auth/ (Breeze, 8 Klassen)
│   │   ├── FotoBlobDb/
│   │   │   └── FotoBlobDbController.php
│   │   ├── FotoDB/
│   │   │   └── FotoDbController.php
│   │   ├── SessionDb/
│   │   │   └── SessionDbController.php
│   │   └── UserDb/
│   │       ├── UserDbController.php
│   │       └── SystemLoginController.php v1.1.0 NEU
│   └── Middleware/
│       ├── AnonymousSessionTimeout.php
│       ├── NoIndexHeader.php
│       └── SessionHijackProtection.php
├── Mail/
│   └── TwoFactorCodeMail.php v1.0.0 NEU
├── Models/
│   ├── User.php
│   ├── FotoBlobDb/
│   │   ├── FotoBlobDbModel.php
│   │   └── FotoObjDb.php
│   ├── FotoDB/
│   │   ├── FotoDbModel.php
│   │   ├── ActivityGroup.php
│   │   ├── ActivitySubgroup.php
│   │   ├── AgFoContext.php
│   │   ├── AsgFoContext.php
│   │   ├── FotoObj.php
│   │   ├── MandProfile.php
│   │   └── MpFoContext.php
│   ├── SessionDb/
│   │   ├── SessionDbModel.php
│   │   ├── PwList.php
│   │   ├── Session.php
│   │   └── TwofaCode.php v1.1.0 NEU
│   └── UserDb/
│       ├── UserDbModel.php
│       ├── CustPcode.php
│       ├── CustUser.php v1.1.0
│       ├── MandUser.php v1.1.0
│       └── SystUser.php
├── Providers/
│   └── AppServiceProvider.php
└── Services/
    ├── FotoBlobDb/
    │   └── FotoBlobDbService.php
    ├── FotoDB/
    │   └── FotoDbService.php
    ├── SessionDb/
    │   ├── SessionDbService.php
    │   └── TwofaService.php v1.1.0 NEU
    └── UserDb/
        └── UserDbService.php

bootstrap/
├── app.php
├── providers.php
└── cache/
    ├── packages.php
    └── services.php

config/
├── app.php
├── auth.php
├── cache.php
├── database.php
├── filesystems.php
├── logging.php
├── mail.php
├── queue.php
├── services.php
└── session.php

database/
└── migrations/ (nur Breeze-Scaffolding, ungenutzt)

public/
├── index.php
├── .htaccess
└── build/
    ├── manifest.json
    └── assets/

resources/
├── css/
│   └── app.css
├── js/
│   └── app.js
└── views/
    ├── welcome.blade.php
    ├── dashboard.blade.php
    ├── emails/
    │   └── two-factor-code.blade.php v1.0.0 NEU
    ├── profile/
    │   └── (Breeze-Views)
    └── system/
        └── login.blade.php v1.1.0 NEU

routes/
├── web.php (Testroute entfernt)
├── auth.php
├── system.php (stub)
├── mandant.php (stub)
└── customer.php (stub)

storage/
└── logs/
    └── laravel.log
```

## 6. Middleware

| Middleware | Scope | Funktion |
|---|---|---|
| NoIndexHeader | Global | X-Robots-Tag: noindex |
| SessionHijackProtection | web | IP+UA-Hash Vergleich |
| AnonymousSessionTimeout | web | Idle-Timeout anon (1800s) |

## 7. Session Handler

Custom Driver `sessiondb` — ersetzt Laravel Standard. PK `sess_id` statt `id`. Registriert in `AppServiceProvider`, aktiv via `SESSION_DRIVER=sessiondb`.

## 8. Email-Infrastruktur

| | Lokal | Server |
|---|---|---|
| Mailer | Mailpit (127.0.0.1:1025) | host159.alfahosting-server.de:587 |
| Absender | noreply@martinwagner.de | noreply@martinwagner.de |
| Anzeige | http://127.0.0.1:8025 | Echtes Postfach |

## 9. 2FA

| Thema | Stand |
|---|---|
| Kanal | Email ✅ |
| Code | 6 Ziffern, 10 Min. ✅ |
| Speicherung | twofa_code in sessiondb ✅ |
| syst Login | vollständig implementiert und getestet ✅ |
| mand Login | ausstehend |
| cust Login | ausstehend |
| Kritische Aktionen | ausstehend |

## 10. Git-Stand

| Tag | Inhalte |
|---|---|
| email_test_ok | Email-Infrastruktur lokal + Server getestet |
| system_login_2fa_ok | System-Login mit 2FA vollständig |

## 11. Nächste Schritte

1. Mandant-Login mit 2FA
2. Customer-Login mit 2FA
3. System-Dashboard aufbauen
4. 2FA bei kritischen Aktionen
5. purgeExpired() per Scheduler einrichten
6. DuckDuckGo-Browser Cookie-Problem analysieren
