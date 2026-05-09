# Fotosite V08 — Project Reference for Claude

## 1. Project Concept

### Summary
A photo-display website where **Mandanten** (tenants) provide photo content and **Customers** browse it within their assigned security level. The site is multi-tenant: each Mandant manages their own content and users independently, with no cross-tenant data access.

### User Roles
| Role | Scope |
|---|---|
| **System-User** | CRUD on Mandanten accounts; no access to photo content |
| **Mandant** | CRUD on own groups, subgroups, photo objects, and assigned customers |
| **Customer (registered)** | Reads photos up to their stored security level (passcode-based) |
| **Customer (anonymous)** | Reads photos by entering a password sequence each session; 30-min timeout |

### Security Levels (Customer scope)
| Level | Meaning |
|---|---|
| 0 | Public — visible to all, including anonymous |
| 1 | Bekannte (acquaintances) |
| 2 | Freunde (friends) |
| 3 | Familie (family) |
| 4 | Vertraulich (confidential) — blobs stored in DB, not filesystem |

### Passcode / Password System
- A Mandant defines up to 6 passwords, each corresponding to one security level digit.
- Entering the correct password sequence generates a 4-character **passcode** (mand_id + 4 chars) stored in `cust_pcode`.
- Passwords have a validity period managed by the Mandant (`pw_list.valid_from` / `valid_until`).
- Registered customers retain their passcode until deleted; anonymous sessions expire after 30 min.

### Data Structure Hierarchy
```
Mandant (mand_user)
└── ActivityGroup (activity_group)
    ├── FotoObj assignments (ag_fo_context)
    └── ActivitySubgroup (activity_subgroup)
        └── FotoObj assignments (asg_fo_context)
FotoObj (foto_obj) — shared across groups via context tables
MandProfile (mand_profile) — mandant branding / start page content
    └── FotoObj assignments (mp_fo_context)
```

### Navigation & Frontend
- Start page shows the first Mandant's groups for the logged-in customer as horizontal preview rows.
- Horizontal navigation scrolls between Mandanten; vertical navigation scrolls between groups/subgroups.
- Login modals are layered: Customer modal (default) → Mandant modal link → System modal link.

### Security Requirements
- No search-engine indexing.
- Strict MVC — public files only under `/public`.
- Minimal cookies: one session cookie only; additional cookies require explicit approval.
- Session hijack mitigations built in.
- Each database secured with its own username/password.
- DB credentials stored encrypted in the filesystem; System-Users can rotate them.

---

## 2. Tech Stack

| Component | Version / Detail |
|---|---|
| **Framework** | Laravel 13 (`laravel/framework ^13.7`) |
| **PHP** | 8.5 |
| **Templates** | Blade (via Laravel Breeze v2) |
| **JS interactivity** | Alpine.js 3 |
| **CSS** | Tailwind CSS 3 + `@tailwindcss/forms` + PostCSS |
| **Build tool** | Vite 8 + `laravel-vite-plugin` |
| **Database engine** | MariaDB on localhost:3306 |
| **Auth scaffold** | Laravel Breeze (Blade stack) |

> The user refers to this project as "Laravel 12". The actual installed version is Laravel 13.

### PHP Extensions required (enabled in `C:\php\php.ini`)
`fileinfo`, `mysqli`, `pdo_mysql`

---

## 3. Application Structure

### Repository layout
```
Fotosite_V08/                  ← project root (this CLAUDE.md lives here)
└── claudescode/
    └── fotosite/              ← Laravel application root
        ├── app/
        │   ├── Http/Controllers/
        │   │   ├── UserDb/
        │   │   ├── SessionDb/
        │   │   ├── FotoDB/
        │   │   └── FotoBlobDb/
        │   ├── Models/
        │   │   ├── UserDb/
        │   │   ├── SessionDb/
        │   │   ├── FotoDB/
        │   │   └── FotoBlobDb/
        │   └── Services/
        │       ├── UserDb/
        │       ├── SessionDb/
        │       ├── FotoDB/
        │       └── FotoBlobDb/
        ├── routes/
        │   ├── web.php
        │   ├── system.php     ← /system/* — system admin area
        │   ├── mandant.php    ← /mandant/* — tenant admin area
        │   └── customer.php   ← /customer/* — customer-facing area
        └── ...
```

### Four Databases

| Connection key | Database name | Purpose |
|---|---|---|
| `userdb` | `u14bc1w8_v08_userdb` | All user accounts (system, mandant, customer) + passcodes |
| `sessiondb` | `u14bc1w8_v08_sessiondb` | Sessions + mandant password lists |
| `fotodb` | `u14bc1w8_v08_fotodb` | Photo metadata, groups, subgroups, mandant profiles |
| `fotoblobdb` | `u14bc1w8_v08_fotoblobdb` | Binary blobs for confidential (level 4) photo objects |

Default `mysql` connection is aliased to `userdb` settings in `config/database.php`.

---

## 4. Eloquent Models

Each model extends an abstract base class that sets `$connection`. All models set `$timestamps = false` (tables have no `created_at`/`updated_at` standard columns unless noted).

### UserDb (`App\Models\UserDb\*` — extends `UserDbModel`)

| Class | Table | Key | Notes |
|---|---|---|---|
| `SystUser` | `syst_user` | `syst_id` | `syst_pw_hash` hidden |
| `CustUser` | `cust_user` | `cust_id` | `cust_pw_hash` hidden · hasMany `CustPcode` |
| `MandUser` | `mand_user` | `mand_id` | `mand_pw_hash` hidden · hasMany `CustPcode` |
| `CustPcode` | `cust_pcode` | `pcode_id` | belongsTo `MandUser`, `CustUser` |

### SessionDb (`App\Models\SessionDb\*` — extends `SessionDbModel`)

| Class | Table | Key | Notes |
|---|---|---|---|
| `PwList` | `pw_list` | `pwlist_id` | `pw1`–`pw6` hidden · `valid_from`/`valid_until` cast datetime |
| `Session` | `session` | `sess_id` | `user_type` enum (anon/cust/mand/syst) · `created_at`, `last_activity`, `expires_at` cast datetime |

### FotoDB (`App\Models\FotoDB\*` — extends `FotoDbModel`)

| Class | Table | Key | Notes |
|---|---|---|---|
| `ActivityGroup` | `activity_group` | `ag_id` | hasMany `ActivitySubgroup`, `AgFoContext` · belongsToMany `FotoObj` |
| `ActivitySubgroup` | `activity_subgroup` | `asg_id` | belongsTo `ActivityGroup` · hasMany `AsgFoContext` · belongsToMany `FotoObj` · `asg_public` cast bool · `asg_date` cast date |
| `FotoObj` | `foto_obj` | `fo_id` | `fo_is_video`/`db_saved` cast bool · `fo_datetime` cast datetime · belongsToMany `ActivityGroup`, `ActivitySubgroup`, `MandProfile` |
| `AgFoContext` | `ag_fo_context` | `ag_fo_id` | Pivot: ActivityGroup ↔ FotoObj · `ag_banner`/`ag_is_banner` cast bool |
| `AsgFoContext` | `asg_fo_context` | `asg_fo_id` | Pivot: ActivitySubgroup ↔ FotoObj · `ags_is_banner` cast bool |
| `MandProfile` | `mand_profile` | `mp_id` | hasMany `MpFoContext` · belongsToMany `FotoObj` |
| `MpFoContext` | `mp_fo_context` | `mp_fo_id` | Pivot: MandProfile ↔ FotoObj |

### FotoBlobDb (`App\Models\FotoBlobDb\*` — extends `FotoBlobDbModel`)

| Class | Table | Key | Notes |
|---|---|---|---|
| `FotoObjDb` | `foto_obj_db` | `fod_id` | BLOB storage for level-4 photo objects |

### Cross-database references
`mand_id` appears in `sessiondb` and `fotodb` tables as a logical foreign key back to `userdb.mand_user`. No Eloquent relationship is defined across these connections (DB-level FK not possible). These columns are plain integers in `$fillable`.

---

## 5. Route Areas

Routes are registered in `bootstrap/app.php` via the `then:` hook.

| File | URL prefix | Middleware | Purpose |
|---|---|---|---|
| `routes/system.php` | `/system` | `web`, `auth` | System-User admin: manage Mandanten, system users |
| `routes/mandant.php` | `/mandant` | `web`, `auth` | Mandant admin: groups, subgroups, photos, customer management |
| `routes/customer.php` | `/customer` | `web` | Customer frontend: browse photos, profile, login |
| `routes/web.php` | `/` | `web` | Breeze auth routes + dashboard |

---

## 6. Middleware

All custom middleware lives in `app/Http/Middleware/` and is registered in `bootstrap/app.php` inside `->withMiddleware()`.

### Registration in `bootstrap/app.php`

```php
->withMiddleware(function (Middleware $middleware): void {
    // Global — applied to every response regardless of route group
    $middleware->append(\App\Http\Middleware\NoIndexHeader::class);

    // Web group — require an active session
    $middleware->web(append: [
        \App\Http\Middleware\SessionHijackProtection::class,
        \App\Http\Middleware\AnonymousSessionTimeout::class,
    ]);
})
```

### `NoIndexHeader`
- **File:** `app/Http/Middleware/NoIndexHeader.php`
- **Stack:** global (every response)
- **Purpose:** Appends `X-Robots-Tag: noindex, nofollow` to every HTTP response, preventing the site from being indexed by search engines.
- **No configuration required.**

### `SessionHijackProtection`
- **File:** `app/Http/Middleware/SessionHijackProtection.php`
- **Stack:** `web` group
- **Purpose:** Guards against session hijacking by binding a session to the client that created it.
- **Behaviour:**
  - On the first request of a new session: SHA-256 hashes of `request->ip()` and `request->userAgent()` are stored as `_ip_hash` and `_ua_hash` in the session.
  - On every subsequent request: the stored hashes are compared to the current values using `hash_equals()` (constant-time, safe against timing attacks).
  - On mismatch: `session()->invalidate()` + `regenerateToken()` + redirect to `route('login')` with an error message.
- **No configuration required.**

### `AnonymousSessionTimeout`
- **File:** `app/Http/Middleware/AnonymousSessionTimeout.php`
- **Stack:** `web` group
- **Purpose:** Enforces an idle timeout for anonymous customer sessions as required by the project spec (30 min).
- **Behaviour:**
  - Only acts when the session key `_user_type` equals `'anon'` (set at anonymous login time).
  - On each request: compares `time()` against `_anon_last_activity`. If the difference exceeds the configured timeout → `invalidate()` + `regenerateToken()` + redirect to `/` with an error message.
  - On a valid request: refreshes `_anon_last_activity = time()`.
- **Configuration:** `ANON_SESSION_TIMEOUT` in `.env` (integer, seconds, default `1800`).
  - The value can be changed without a deployment by updating `.env`. Per the project spec, the timeout is a fixed operational value that can be adjusted per SQL / env — not a per-user setting.

### Session keys used by middleware

#### `_ip_hash`
- **Written by:** `SessionHijackProtection` — on the first request of every new session
- **Read by:** `SessionHijackProtection` — on every subsequent request
- **Value:** `hash('sha256', $request->ip())` — 64-character hex string
- **Lifetime:** lives for the duration of the session; destroyed with it on invalidation
- **Action on mismatch:** session invalidated, redirect to `login`

#### `_ua_hash`
- **Written by:** `SessionHijackProtection` — on the first request of every new session, alongside `_ip_hash`
- **Read by:** `SessionHijackProtection` — on every subsequent request
- **Value:** `hash('sha256', $request->userAgent() ?? '')` — 64-character hex string
- **Lifetime:** lives for the duration of the session; destroyed with it on invalidation
- **Action on mismatch:** session invalidated, redirect to `login`

#### `_user_type`
- **Written by:** login controllers (to be implemented) — set immediately after successful authentication
- **Read by:** `AnonymousSessionTimeout` — on every request to decide whether to apply the timeout
- **Value:** one of `'anon'` · `'cust'` · `'mand'` · `'syst'`
- **Lifetime:** lives for the duration of the authenticated session
- **Contract:** any login controller that creates an anonymous session **must** write `_user_type = 'anon'` to the session, or `AnonymousSessionTimeout` will not trigger

#### `_anon_last_activity`
- **Written by:** `AnonymousSessionTimeout` — on every request where `_user_type === 'anon'` and the session is still valid
- **Read by:** `AnonymousSessionTimeout` — on every request to calculate idle time
- **Value:** `time()` — Unix timestamp (integer)
- **Lifetime:** reset on each valid request; absent on the very first anonymous request (treated as no prior activity, timeout does not fire)
- **Action on timeout:** `time() - _anon_last_activity > ANON_SESSION_TIMEOUT` → session invalidated, redirect to `/`

---

## 7. Coding Rules

### Strict MVC
- **Controllers** — HTTP only: validate input, call a Service, return a response. No business logic, no direct DB queries.
- **Services** — All business logic lives here. One Service class per domain/feature, grouped under the relevant DB namespace.
- **Models** — Data shape only: `$fillable`, `$casts`, `$hidden`, relationships. No business logic.

### No Migrations
> **The database structure is designed and maintained exclusively by the project owner.**
- Never create, modify, or run migration files.
- Never run `php artisan migrate` unless explicitly instructed.
- Schema change suggestions may be offered as SQL scripts, not as migration files.

### File Header (required on every code file)
Every PHP file must begin with a header block in this format:

```php
<?php
/**
 * FILE:        app/Http/Controllers/UserDb/ExampleController.php
 * VERSION:     1.0.0
 *
 * FUNCTIONS:   index()         — Lists all items; reads userdb.syst_user.*
 *              store()         — Creates a new item; writes userdb.syst_user.*
 *
 * CALLS:       App\Services\UserDb\ExampleService::create()
 *              App\Models\UserDb\SystUser::all()
 *
 * DB ACCESS:   userdb.syst_user.syst_id, syst_uname, syst_email
 */
```

Fields:
- `FILE` — file path relative to the Laravel app root
- `VERSION` — incremented with every change to the file
- `FUNCTIONS` — each method with a one-line description and the data it reads/writes
- `CALLS` — all calls to methods in own or other files (fully qualified)
- `DB ACCESS` — every table and column accessed, in `database.table.column` notation

### Session Keys
Any controller or service that creates or modifies a session must respect the following reserved keys. Writing unknown values to these keys or omitting required ones will break middleware behaviour.

| Key | Type | Owner | Required value |
|---|---|---|---|
| `_ip_hash` | `string` | `SessionHijackProtection` | Do not write — set automatically on session creation |
| `_ua_hash` | `string` | `SessionHijackProtection` | Do not write — set automatically on session creation |
| `_user_type` | `string` | Login controllers | **Must** be set on every login: `'anon'` · `'cust'` · `'mand'` · `'syst'` |
| `_anon_last_activity` | `int` | `AnonymousSessionTimeout` | Do not write — updated automatically on every anonymous request |

**Rules:**
- `_ip_hash` and `_ua_hash` are owned exclusively by `SessionHijackProtection`. Never write them in application code.
- `_user_type` **must** be written by the login controller immediately after authentication. `AnonymousSessionTimeout` will not fire unless `_user_type === 'anon'` is present.
- `_anon_last_activity` is owned exclusively by `AnonymousSessionTimeout`. Never write it in application code.

### General
- All new features branch from the Breeze auth scaffold.
- Column names with `+` (e.g. `syst_street+nr`) are accessed via `$model->{'syst_street+nr'}`.
- `mand_profile.mp_name`, `mp_title`, `mp_text` are `BIGINT` as per the DDL — treat as integer IDs.
- `foto_obj_db.fod_obj` is a `BLOB` — do not cast it.
- Session cookie only — no additional cookies without explicit approval.
