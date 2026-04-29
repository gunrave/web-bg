# INTEGRATIONS.md — External Integrations

## Database Connections

### Primary Database
- **Default driver:** SQLite (development default per `config/database.php` and `.env.example`)
- **SQLite path:** `database/database.sqlite` (auto-created on project install)
- **MySQL/MariaDB:** Configured as alternative connections in `config/database.php` — activated via `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` env vars
- **ORM:** Laravel Eloquent (Active Record)
- **Migrations:** Managed via Artisan, 21 migration files in `database/migrations/`

Relevant env vars:
```
DB_CONNECTION=sqlite
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=root
DB_PASSWORD=
```

---

## Authentication

- **Provider:** Laravel built-in session-based auth (`config/auth.php`)
- **Guard:** `web` (session driver)
- **User model:** `App\Models\User` (Eloquent-backed)
- **Password hashing:** Bcrypt (`BCRYPT_ROUNDS=12`)
- **Admin panel auth:** Filament's `Authenticate` middleware on `/admin` and `/app` paths (`app/Providers/Filament/AdminPanelProvider.php`, `app/Providers/Filament/AppPanelProvider.php`)
- **No OAuth/SSO providers** (no Laravel Socialite or similar packages found)
- **No JWT/API token auth** (no Sanctum, Passport, or JWT packages in `composer.json`)

---

## Excel / File Import & Export

- **Package:** `maatwebsite/excel` ^3.1 (Laravel Excel)
- **Purpose:** Importing employee and salary data from Excel/CSV files
- **Import classes:**
  - `app/Imports/ImportGajis.php` — imports salary (`Gaji`) records, auto-creates `Pegawai` (employee) records from spreadsheet rows using `firstOrCreate`
  - `app/Imports/ImportTunkers.php` — imports tunjangan kinerja (`Tunker`) records
- **Filament import integration:** `app/Filament/Imports/` directory (Filament's native import action wrapper)
- **Export support:** Export migration present (`database/migrations/2024_06_28_031046_create_exports_table.php`); uses Laravel Excel export pipeline
- **Storage disk:** `local` (`FILESYSTEM_DISK=local`)

---

## Mail

- **Default transport:** `log` (dev mode — emails written to log files, not sent)
- **Configured transports in `config/mail.php`:** smtp, sendmail, mailgun, ses, ses-v2, postmark, resend, log, array
- **Current env default:** log mailer (no live mail integration active by default)

Env vars for SMTP:
```
MAIL_MAILER=log
MAIL_HOST=127.0.0.1
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"
```

---

## Queue System

- **Driver:** `database` (queue jobs stored in DB table)
- **Jobs table:** created by migration `0001_01_01_000002_create_jobs_table.php`
- **Failed import rows table:** `database/migrations/2024_06_28_031047_create_failed_import_rows_table.php` (used by Laravel Excel for failed import tracking)

Env var:
```
QUEUE_CONNECTION=database
```

---

## Cache

- **Driver:** `database` (cache stored in DB)
- **Cache table:** created by migration `0001_01_01_000001_create_cache_table.php`
- **Redis config present** but not active by default:

Env vars:
```
CACHE_STORE=database
REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
MEMCACHED_HOST=127.0.0.1
```

---

## Session

- **Driver:** `database` (sessions stored in DB table)
- **Session table:** created within `0001_01_01_000000_create_users_table.php` migrations

Env vars:
```
SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
```

---

## Broadcast / Real-time

- **Driver:** `log` (no real-time integration active)
- Env var: `BROADCAST_CONNECTION=log`
- No Pusher, Ably, or Reverb packages found in `composer.json`

---

## AWS / Cloud Storage

- AWS S3 credentials present in `.env.example` but empty — not actively configured
```
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false
```
- Active filesystem disk: `local` (not S3)

---

## Filament Admin Panel

- **Package:** `filament/filament` ^3.2
- **Panel 1 — Admin:** path `/admin`, login page enabled, primary color Amber (`app/Providers/Filament/AdminPanelProvider.php`)
- **Panel 2 — App:** path `/app`, no public login, clusters enabled (`app/Providers/Filament/AppPanelProvider.php`)
- **Resources (CRUD modules):**
  - `GajiResource` — salary management
  - `PegawaiResource` — employee management
  - `PeriodeResource` / `PeriodeTagihanResource` — billing period management
  - `PotongResource` — deduction management
  - `TagihanResource` — invoice/billing management
  - `TunkerResource` — performance allowance management
- **Clusters:** `app/Filament/Clusters/` (grouped resources)
- **Notifications:** DB notifications table (`database/migrations/2024_06_28_031013_create_notifications_table.php`)

---

## Webhooks & Event Systems

- No outbound webhook integrations found
- Laravel Events/Listeners: standard framework eventing available but no custom listeners detected
- Filament dispatches `DispatchServingFilamentEvent` middleware event internally

---

## Development Environment

- **Laravel Sail** (`laravel/sail` ^1.26) — Docker-based local environment (optional)
- **Laravel Herd** — implied by workspace path `C:\Users\User\Herd\web-bg` (Windows Herd for local PHP/Nginx)
- **Vite dev server** — `npm run dev` starts HMR server for frontend assets

---

## Environment Variables Summary

| Variable | Default | Integration |
|----------|---------|-------------|
| `DB_CONNECTION` | sqlite | Database driver |
| `DB_HOST/PORT/DATABASE` | — | MySQL/MariaDB alt connection |
| `SESSION_DRIVER` | database | Laravel sessions |
| `QUEUE_CONNECTION` | database | Job queue |
| `CACHE_STORE` | database | Cache layer |
| `BROADCAST_CONNECTION` | log | Real-time events (inactive) |
| `FILESYSTEM_DISK` | local | File storage |
| `MAIL_MAILER` | log | Email transport (inactive) |
| `REDIS_HOST/PORT` | 127.0.0.1:6379 | Redis (inactive) |
| `AWS_*` | empty | S3 cloud storage (inactive) |
| `VITE_APP_NAME` | APP_NAME | Frontend env passthrough |
