# Fotosite V08 — Setup Guide

## 1. Prerequisites

### PHP 8.5

1. Download the **Thread Safe** ZIP from [https://windows.php.net/download/](https://windows.php.net/download/)
2. Extract to `C:\php`
3. Copy `php.ini-development` → `php.ini` and enable required extensions:
   ```ini
   extension=curl
   extension=fileinfo
   extension=mbstring
   extension=openssl
   extension=pdo_mysql
   extension=zip
   ```
4. Add `C:\php` to the system PATH (see PATH section below)
5. Verify: `php -v`

### Composer

1. Download and run **Composer-Setup.exe** from [https://getcomposer.org/download/](https://getcomposer.org/download/)
2. The installer detects `php.exe` automatically — point it to `C:\php\php.exe` if asked
3. Verify: `composer --version`

### Node.js (LTS)

1. Download the **LTS installer** from [https://nodejs.org/](https://nodejs.org/)
2. The installer adds Node and npm to PATH automatically
3. Verify: `node -v` and `npm -v`

---

## 2. Windows-Specific Notes

### Norton HTTPS Scanning (SSL errors during `composer install` / `npm install`)

Norton intercepts HTTPS traffic and can break package manager certificate chains. If you see SSL/TLS errors:

- **Temporary fix:** Disable *HTTPS Scanning* in Norton → Settings → Firewall → HTTPS Scanning → Off
- Re-enable after installation is complete
- Alternative: add `C:\php\php.exe`, `composer.bat`, and `node.exe` to Norton's exclusion list

### PATH Environment Variables

To add a directory to the system PATH:

1. Press `Win + S` → search **"Edit the system environment variables"**
2. Click **Environment Variables…**
3. Under **System variables**, select `Path` → **Edit** → **New**
4. Add the required path (e.g. `C:\php`)
5. Click OK on all dialogs and **restart your terminal**

Required PATH entries for this project:

| Tool | Path |
|---|---|
| PHP | `C:\php` |
| Composer | Added automatically by installer |
| Node / npm | Added automatically by installer |

### PowerShell — Paste with Right-Click

In Windows Terminal / PowerShell, the fastest way to paste is **right-click** (no Ctrl+V needed). This also works for pasting long `cd` paths copied from Explorer.

---

## 3. Starting Claude Code

```powershell
# Navigate to the project root
cd D:\mwa\Projekte\fotosite\Fotosite_V08

# Launch Claude Code
claude
```

Claude Code reads `CLAUDE.md` from the working directory automatically. Always start from `Fotosite_V08` so Claude has the full project context.

---

## 4. Project Directory Structure

```
D:\mwa\Projekte\fotosite\Fotosite_V08\
├── CLAUDE.md                          # Claude Code project instructions
├── SETUP.md                           # This file
│
├── claudescode/
│   └── fotosite/                      # Laravel application root
│       ├── app/
│       │   ├── Http/
│       │   │   ├── Controllers/
│       │   │   │   ├── Auth/          # 11 auth controllers
│       │   │   │   ├── FotoBlobDb/    # FotoBlobDbController.php
│       │   │   │   ├── FotoDB/        # FotoDbController.php
│       │   │   │   ├── SessionDb/     # SessionDbController.php
│       │   │   │   └── UserDb/        # UserDbController.php
│       │   │   ├── Middleware/
│       │   │   └── Requests/Auth/
│       │   ├── Models/
│       │   │   ├── FotoBlobDb/        # FotoBlobDbModel.php, FotoObjDb.php
│       │   │   ├── FotoDB/            # 12 models (foto metadata)
│       │   │   ├── SessionDb/         # Session.php, PwList.php
│       │   │   └── UserDb/            # 4 user models
│       │   ├── Services/
│       │   │   ├── FotoBlobDb/        # FotoBlobDbService.php
│       │   │   ├── FotoDB/            # FotoDbService.php
│       │   │   ├── SessionDb/         # SessionDbService.php
│       │   │   └── UserDb/            # UserDbService.php
│       │   └── Providers/
│       ├── bootstrap/
│       ├── config/
│       │   ├── database.php           # 4 DB connection definitions
│       │   ├── app.php
│       │   ├── auth.php
│       │   └── ...
│       ├── database/
│       │   ├── migrations/
│       │   ├── factories/
│       │   └── seeders/
│       ├── public/                    # Web server document root
│       ├── resources/
│       │   ├── css/
│       │   ├── js/
│       │   └── views/
│       │       ├── auth/
│       │       ├── components/
│       │       └── layouts/
│       ├── routes/
│       │   ├── web.php
│       │   ├── auth.php
│       │   ├── customer.php
│       │   ├── mandant.php
│       │   ├── system.php
│       │   └── console.php
│       ├── storage/
│       ├── tests/
│       ├── .env                       # Local environment (not in git)
│       ├── .env.example               # Template — copy to .env
│       ├── artisan
│       ├── composer.json
│       ├── package.json
│       ├── tailwind.config.js
│       └── vite.config.js
│
├── db_entwurf/
│   └── SQL/
│       ├── 2026-05-07_DB_DLL/         # Latest DDL definitions
│       │   ├── fotosite_DDL_fotoblobdb.sql
│       │   ├── fotosite_DDL_fotodb.sql
│       │   ├── fotosite_DDL_sessiondb.sql
│       │   └── fotosite_DDL_userdb.sql
│       ├── customer-Frontend/
│       └── views_admin/
│
└── (concept & documentation .docx files)
```

---

## 5. Database Connections

The project uses **4 separate MySQL databases**. Configuration lives in two files:

- `claudescode/fotosite/config/database.php` — connection definitions
- `claudescode/fotosite/.env` — actual credentials (never commit this file)

Copy `.env.example` to `.env` and fill in the values below.

### Connection 1 — UserDB (user accounts)

```env
DB_USERDB_HOST=localhost
DB_USERDB_PORT=3306
DB_USERDB_DATABASE=<userdb_database_name>
DB_USERDB_USERNAME=<userdb_username>
DB_USERDB_PASSWORD=<userdb_password>
```

Stores system users, mandant users, and customer users.

### Connection 2 — SessionDB (session management)

```env
DB_SESSIONDB_HOST=localhost
DB_SESSIONDB_PORT=3306
DB_SESSIONDB_DATABASE=<sessiondb_database_name>
DB_SESSIONDB_USERNAME=<sessiondb_username>
DB_SESSIONDB_PASSWORD=<sessiondb_password>
```

Stores session data and password lists.

### Connection 3 — FotoDB (photo metadata)

```env
DB_FOTODB_HOST=localhost
DB_FOTODB_PORT=3306
DB_FOTODB_DATABASE=<fotodb_database_name>
DB_FOTODB_USERNAME=<fotodb_username>
DB_FOTODB_PASSWORD=<fotodb_password>
```

Stores photo metadata, activity groups/subgroups, mandant profiles, and access contexts.

### Connection 4 — FotoBlobDB (binary photo storage)

```env
DB_FOTOBLOBDB_HOST=localhost
DB_FOTOBLOBDB_PORT=3306
DB_FOTOBLOBDB_DATABASE=<fotoblobdb_database_name>
DB_FOTOBLOBDB_USERNAME=<fotoblobdb_username>
DB_FOTOBLOBDB_PASSWORD=<fotoblobdb_password>
```

Stores the actual photo binary data (BLOB fields).

---

## 6. Running the Dev Server

Open **two separate PowerShell terminals** from the Laravel root:

```powershell
cd D:\mwa\Projekte\fotosite\Fotosite_V08\claudescode\fotosite
```

**Terminal 1 — PHP dev server (Laravel)**

```powershell
php artisan serve
```

Starts at `http://127.0.0.1:8000` by default.

**Terminal 2 — Vite (asset hot-reloading)**

```powershell
npm run dev
```

Vite watches `resources/css` and `resources/js` and reloads the browser on changes.

### First-time setup

Run these once before starting the servers:

```powershell
# Install PHP dependencies
composer install

# Install JS dependencies
npm install

# Generate application key
php artisan key:generate

# Run database migrations
php artisan migrate
```

---

## 7. Deployment (Shared Hosting via FTP)

### Step 1 — Build assets locally

Before uploading, compile the production assets on your local machine:

```powershell
cd D:\mwa\Projekte\fotosite\Fotosite_V08\claudescode\fotosite
npm run build
```

This writes optimised CSS/JS into `public/build/`.

### Step 2 — Upload files via FTP

Connect with an FTP client (e.g. FileZilla) and upload the entire Laravel project to the server root **except** the following two folders — they must not be uploaded:

| Folder | Why |
|---|---|
| `vendor/` | Rebuilt on the server via `composer install` |
| `node_modules/` | Not needed on the server (assets are already compiled) |

Everything else goes up, including the compiled `public/build/` folder.

### Step 3 — Set the document root to `/public`

In your hosting control panel (cPanel, Plesk, etc.) set the **document root** (also called *web root* or *public_html target*) to the `public/` subfolder of the uploaded project, for example:

```
/home/<username>/fotosite/public
```

This ensures the web server only exposes `public/index.php` and compiled assets — the rest of the Laravel source code is not directly accessible from the browser.

### Step 4 — Create and configure `.env` on the server

Upload or create a `.env` file in the project root on the server. Use `.env.example` as the template and set at minimum:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

# Database credentials for all 4 connections (see Section 5)
DB_USERDB_HOST=localhost
DB_USERDB_DATABASE=<userdb_database_name>
...
```

Never set `APP_DEBUG=true` in production.

### Step 5 — Run `composer install` on the server

SSH into the server and run:

```bash
cd /path/to/project/root
composer install --no-dev --optimize-autoloader
```

`--no-dev` skips development-only packages. `--optimize-autoloader` generates a faster class map for production.

If SSH is not available, some hosts offer a terminal in the control panel (cPanel → Terminal).

### Step 6 — Finish setup via SSH / server terminal

```bash
# Generate application key (only needed on first deployment)
php artisan key:generate

# Run migrations against the production databases
php artisan migrate --force

# Cache config and routes for better performance
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Redeployment (subsequent uploads)

For updates, repeat Steps 1–2 (build + upload changed files), then on the server:

```bash
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```
