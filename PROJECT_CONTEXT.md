# PROJECT_CONTEXT.md — Fotosite V08

## 1. Project Overview

Multi-tenant photo website. Each tenant (Mandant) manages their own photo content, activity groups, and customer access. The system supports anonymous browsing and authenticated access across three distinct user roles, with content visibility controlled by five security levels.

**User roles:**
- `syst` — System administrator (platform owner)
- `mand` — Mandant (tenant/content owner)
- `cust` — Customer (end user with optional passcode)
- `anon` — Anonymous visitor (unauthenticated, session-tracked)

**Security levels:** Content items (activity groups, subgroups, photos) carry a `*_sec_code` field that controls visibility per role. Five levels are defined in the data model.

**Session model:** Every visitor — including anonymous ones — gets a session row in the `session` table. Session records track user type, identity references (syst_id / mand_id / cust_id), IP hash, user-agent hash, and expiry. Anonymous sessions time out after a configurable idle period (default 1800 s).

---

## 2. Tech Stack

| Layer | Technology |
|---|---|
| Framework | Laravel 13 (^13.7) |
| Language | PHP 8.5 |
| Templating | Blade |
| Frontend JS | Alpine.js (via Breeze) |
| CSS | Tailwind CSS |
| Database | MariaDB |
| Auth scaffold | Laravel Breeze 2.x |
| Build tool | Vite |
| Package manager | Composer / npm |

**Key composer dependencies:**
- `laravel/framework` ^13.7
- `laravel/tinker` ^3.0
- `laravel/breeze` ^2.4 (dev)
- `laravel/pint` ^1.27 (dev)
- `phpunit/phpunit` ^12.5 (dev)

---

## 3. Server Configuration

- **Document root:** `/public` — only this directory is web-accessible
- **Deployment method:** FTP upload of changed files to the remote server
- **Post-deploy step:** SSH into the server and run `composer install --no-dev` after uploading `composer.json` / `composer.lock`
- **No CI/CD pipeline** — all deployment is manual

---

## 4. Databases

Four separate MariaDB databases, each with a dedicated Laravel connection and DB user. Connection names match the `.env` variable prefix convention `DB_<CONNNAME>_*`.

### 4.1 `userdb` — User Management
Connection: `userdb` | Env prefix: `DB_USERDB_`

| Table | Purpose |
|---|---|
| `syst_user` | System administrator accounts |
| `mand_user` | Mandant (tenant) accounts |
| `cust_user` | Customer accounts |
| `cust_pcode` | Per-mandant passcodes assigned to customers |

### 4.2 `sessiondb` — Session & Password Lists
Connection: `sessiondb` | Env prefix: `DB_SESSIONDB_`

| Table | Purpose |
|---|---|
| `session` | One row per active visitor session (anon + authenticated) |
| `pw_list` | Time-limited password lists per mandant (up to 6 passwords with valid_from / valid_until) |

### 4.3 `fotodb` — Photo Content
Connection: `fotodb` | Env prefix: `DB_FOTODB_`

| Table | Purpose |
|---|---|
| `foto_obj` | Photo/video metadata (filename, title, path, security code) |
| `activity_group` | Top-level content grouping per mandant |
| `activity_subgroup` | Sub-grouping within an activity group |
| `ag_fo_context` | Pivot: activity group ↔ photo (with banner flags) |
| `asg_fo_context` | Pivot: activity subgroup ↔ photo (with banner flag) |
| `mand_profile` | Mandant profile/landing-page content |
| `mp_fo_context` | Pivot: mandant profile ↔ photo |

### 4.4 `fotoblobdb` — Photo Binary Objects
Connection: `fotoblobdb` | Env prefix: `DB_FOTOBLOBDB_`

| Table | Purpose |
|---|---|
| `foto_obj_db` | Raw BLOB storage for photo/video files, linked to `foto_obj` via `fo_id` |

**Important:** Database schema is fully predefined and managed outside Laravel. No Laravel migrations are used for domain tables. The migration files in `database/migrations/` are Breeze scaffolding leftovers and do not reflect actual table structure.

---

## 5. MVC Folder Structure

```
app/
├── Extensions/
│   └── SessionDbSessionHandler.php   # Custom session driver (uses sess_id PK)
├── Http/
│   ├── Controllers/
│   │   ├── Controller.php            # Abstract base
│   │   ├── ProfileController.php     # Breeze profile management
│   │   ├── Auth/                     # Breeze auth controllers (8 classes)
│   │   │   ├── AuthenticatedSessionController.php
│   │   │   ├── ConfirmablePasswordController.php
│   │   │   ├── EmailVerificationNotificationController.php
│   │   │   ├── EmailVerificationPromptController.php
│   │   │   ├── NewPasswordController.php
│   │   │   ├── PasswordController.php
│   │   │   ├── PasswordResetLinkController.php
│   │   │   └── VerifyEmailController.php
│   │   ├── FotoBlobDb/
│   │   │   └── FotoBlobDbController.php   # Abstract base for blob controllers
│   │   ├── FotoDB/
│   │   │   └── FotoDbController.php       # Abstract base for foto controllers
│   │   ├── SessionDb/
│   │   │   └── SessionDbController.php    # Abstract base for session controllers
│   │   └── UserDb/
│   │       └── UserDbController.php       # Abstract base for user controllers
│   └── Middleware/
│       ├── AnonymousSessionTimeout.php
│       ├── NoIndexHeader.php
│       └── SessionHijackProtection.php
├── Models/
│   ├── User.php                       # Laravel default auth model
│   ├── FotoBlobDb/
│   │   ├── FotoBlobDbModel.php        # Base model (connection: fotoblobdb)
│   │   └── FotoObjDb.php
│   ├── FotoDB/
│   │   ├── FotoDbModel.php            # Base model (connection: fotodb)
│   │   ├── ActivityGroup.php
│   │   ├── ActivitySubgroup.php
│   │   ├── AgFoContext.php
│   │   ├── AsgFoContext.php
│   │   ├── FotoObj.php
│   │   ├── MandProfile.php
│   │   └── MpFoContext.php
│   ├── SessionDb/
│   │   ├── SessionDbModel.php         # Base model (connection: sessiondb)
│   │   ├── PwList.php
│   │   └── Session.php
│   └── UserDb/
│       ├── UserDbModel.php            # Base model (connection: userdb)
│       ├── CustPcode.php
│       ├── CustUser.php
│       ├── MandUser.php
│       └── SystUser.php
├── Providers/
│   └── AppServiceProvider.php        # Registers custom 'sessiondb' session driver
└── Services/
    ├── FotoBlobDb/
    │   └── FotoBlobDbService.php      # Abstract base for blob services
    ├── FotoDB/
    │   └── FotoDbService.php          # Abstract base for foto services
    ├── SessionDb/
    │   └── SessionDbService.php       # Abstract base for session services
    └── UserDb/
        └── UserDbService.php          # Abstract base for user services

routes/
├── web.php         # Root and dashboard routes
├── auth.php        # Breeze auth routes
├── system.php      # Prefix: /system — role: syst
├── mandant.php     # Prefix: /mandant — role: mand
└── customer.php    # Prefix: /customer — role: cust / anon
```

---

## 6. All 14 Domain Models

All domain models set `public $timestamps = false`.

### UserDb Models (connection: `userdb`)

| Model | Table | PK | Key Fillable | Relationships |
|---|---|---|---|---|
| `SystUser` | `syst_user` | `syst_id` | syst_uname, syst_email, syst_pw_hash (hidden) | — |
| `MandUser` | `mand_user` | `mand_id` | mand_uname, mand_email, mand_pw_hash (hidden), mand_prefstat | hasMany CustPcode |
| `CustUser` | `cust_user` | `cust_id` | cust_uname, cust_email, cust_pw_hash (hidden) | hasMany CustPcode |
| `CustPcode` | `cust_pcode` | `pcode_id` | mand_id, cust_id, cust_passcode, pcode_prefstat | belongsTo MandUser, belongsTo CustUser |

### SessionDb Models (connection: `sessiondb`)

| Model | Table | PK | Key Fillable | Relationships |
|---|---|---|---|---|
| `Session` | `session` | `sess_id` | sess_token, user_type, syst_id, mand_id, cust_id, cust_passcode, ip_hash, ua_hash, created_at, last_activity, expires_at | — |
| `PwList` | `pw_list` | `pwlist_id` | mand_id, pw1–pw6 (hidden), valid_from, valid_until | — |

### FotoDB Models (connection: `fotodb`)

| Model | Table | PK | Key Fillable | Relationships |
|---|---|---|---|---|
| `ActivityGroup` | `activity_group` | `ag_id` | ag_title, ag_subtitle, ag_text, mand_id, ag_sec_code, ag_prefstat | hasMany ActivitySubgroup, hasMany AgFoContext, belongsToMany FotoObj (pivot: ag_fo_context) |
| `ActivitySubgroup` | `activity_subgroup` | `asg_id` | asg_title, asg_text, mand_id, asg_sec_code, ag_id, asg_prefstat, asg_date | belongsTo ActivityGroup, hasMany AsgFoContext, belongsToMany FotoObj (pivot: asg_fo_context) |
| `FotoObj` | `foto_obj` | `fo_id` | fo_filename, fo_title, fo_is_video, fo_filepath, mand_id, fo_sec_code, fo_prefstat | hasMany AgFoContext, AsgFoContext, MpFoContext; belongsToMany ActivityGroup, ActivitySubgroup, MandProfile |
| `AgFoContext` | `ag_fo_context` | `ag_fo_id` | ag_id, fo_id, ag_banner, ag_is_banner | belongsTo ActivityGroup, belongsTo FotoObj |
| `AsgFoContext` | `asg_fo_context` | `asg_fo_id` | asg_id, fo_id, ags_is_banner | belongsTo ActivitySubgroup, belongsTo FotoObj |
| `MandProfile` | `mand_profile` | `mp_id` | mand_id, mp_name, mp_title, mp_text, mp_title_start, mp_subtitle_start | hasMany MpFoContext, belongsToMany FotoObj (pivot: mp_fo_context) |
| `MpFoContext` | `mp_fo_context` | `mp_fo_id` | mp_id, fo_id | belongsTo MandProfile, belongsTo FotoObj |

### FotoBlobDb Models (connection: `fotoblobdb`)

| Model | Table | PK | Key Fillable | Relationships |
|---|---|---|---|---|
| `FotoObjDb` | `foto_obj_db` | `fod_id` | fo_id, fod_obj (BLOB) | — |

---

## 7. Middleware

### `NoIndexHeader`
**Applied:** globally (every response)
**Purpose:** Adds `X-Robots-Tag: noindex, nofollow` header to every HTTP response. Prevents all search engine indexing of the site.

### `SessionHijackProtection`
**Applied:** `web` middleware group
**Purpose:** On each request, hashes the current IP address and User-Agent and compares them against values stored at session start. On mismatch, invalidates the session, regenerates the CSRF token, and redirects to login with an error. Defends against session fixation and hijacking.

### `AnonymousSessionTimeout`
**Applied:** `web` middleware group
**Purpose:** Enforces an idle timeout for anonymous (`anon`) sessions. Reads `ANON_SESSION_TIMEOUT` from `.env` (default: 1800 s). On timeout, invalidates session and redirects to home with an error. Updates last-activity timestamp on every valid request.

Registration in `bootstrap/app.php`:
```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->append(NoIndexHeader::class);              // global
    $middleware->appendToGroup('web', [
        SessionHijackProtection::class,
        AnonymousSessionTimeout::class,
    ]);
})
```

---

## 8. Route Areas

| File | URL prefix | Middleware | Name prefix | Auth required | Role |
|---|---|---|---|---|---|
| `web.php` | — | web | — | partial | mixed |
| `auth.php` | — | web | — | guest/auth | — |
| `system.php` | `/system` | web, auth | `system.` | yes | syst |
| `mandant.php` | `/mandant` | web, auth | `mandant.` | yes | mand |
| `customer.php` | `/customer` | web | `customer.` | no | cust / anon |

`system.php` and `mandant.php` are currently empty (route stubs). `customer.php` is also a stub. Auth routes are provided by `routes/auth.php` (Breeze scaffold).

---

## 9. Git Repository

**Remote:** `https://github.com/fotosite/fotosite.git`
**Main branch:** `main`
**Local path:** `D:\mwa\Projekte\fotosite\Fotosite_V08\claudescode\fotosite`

Files excluded from version control (`.gitignore`):
- `.env` — contains credentials
- `/vendor/` — restored via `composer install` on server
- `/node_modules/` — restored via `npm install` locally
- `/storage/logs/` — runtime logs
- `fotosite_DDL_*.sql` — DDL files contain database credentials

---

## 10. Development Workflow

1. **Code locally** in `D:\mwa\Projekte\fotosite\Fotosite_V08\claudescode\fotosite`
2. **Test locally** using `php artisan serve` (requires PHP in PATH — see §11)
3. **Commit** changes with `git commit` and push to GitHub
4. **Deploy** by uploading changed files via FTP to the server's document root parent
5. **Post-deploy:** SSH into server and run `composer install --no-dev` if `composer.json` or `composer.lock` changed
6. **Config cache** on server: `php artisan config:cache` after `.env` changes

Database schema changes are applied directly on the server via SQL — not through Laravel migrations.

---

## 11. Windows Development Environment Notes

- **Antivirus (Norton):** May interfere with Composer, npm, or `php artisan` commands. Add the project folder and PHP binary to Norton exclusions if commands hang or fail silently.
- **PHP in PATH:** PHP must be added to the Windows system `PATH` environment variable for `php`, `composer`, and `php artisan` to work in PowerShell/CMD. Verify with `php -v`.
- **Composer:** Installed globally. If Composer commands fail after a Norton update, re-add the PHP directory to PATH and restart the terminal.
- **Shell:** PowerShell is the primary shell. Use `! <command>` syntax inside Claude Code to run shell commands inline.

---

## 12. Coding Rules

### File headers
Every PHP source file begins with a docblock header (project name, version, author, date, description). Maintain this convention on all new files.

### No Laravel migrations for domain tables
The database schema for all four domain databases (`userdb`, `sessiondb`, `fotodb`, `fotoblobdb`) is managed externally via DDL scripts. Do **not** create or run Laravel migrations for these tables. The `database/migrations/` directory contains only the Breeze scaffold files and is otherwise unused.

### DB structure is predefined
Table names, column names, primary keys, and relationships are fixed by the DDL. Models must match the schema exactly — do not rename or add columns in PHP code.

### Custom primary keys
Every domain model uses a named primary key (e.g. `sess_id`, `fo_id`, `ag_id`). Always set `protected $primaryKey` explicitly. Never rely on Laravel's default `id`.

### Custom session driver
Laravel's built-in `database` session driver is replaced by the custom `sessiondb` driver (`App\Extensions\SessionDbSessionHandler`) registered in `AppServiceProvider`. This is necessary because the `session` table uses `sess_id` as its primary key instead of Laravel's hardcoded `id`. The driver name `sessiondb` is set via `SESSION_DRIVER=sessiondb` in `.env`.

### No timestamps on domain models
All domain models set `public $timestamps = false`. Timestamp fields (`created_at`, `last_activity`, `expires_at`) are managed explicitly where needed.

### Base model pattern
Each database group has a base model class that sets the connection:
- `SessionDbModel` → `sessiondb`
- `UserDbModel` → `userdb`
- `FotoDbModel` → `fotodb`
- `FotoBlobDbModel` → `fotoblobdb`

All domain models extend their group's base model, not `Illuminate\Database\Eloquent\Model` directly.

### No unused scaffolding
The Breeze auth scaffold (User model, auth controllers, auth routes) is present but may be replaced or removed as custom authentication is built out. Do not extend or rely on Laravel's default `users` table for domain logic.
