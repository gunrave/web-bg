# STRUCTURE.md

## Full Directory Tree with Explanations

```
web-bg/
├── app/                          # Application source code (PSR-4: App\\)
│   ├── Filament/                 # Filament admin panel components
│   │   ├── Clusters/             # Navigation grouping clusters
│   │   │   └── Settings.php      # "Settings" nav cluster definition
│   │   ├── Imports/              # Filament-native bulk importers
│   │   │   ├── GajiImporter.php  # Column-mapped importer for Gaji
│   │   │   └── PegawaiImporter.php # Column-mapped importer for Pegawai
│   │   └── Resources/            # Filament CRUD resource modules
│   │       ├── GajiResource.php
│   │       ├── GajiResource/
│   │       │   └── Pages/        # List, Create, Edit page classes
│   │       ├── PegawaiResource.php
│   │       ├── PegawaiResource/
│   │       │   └── Pages/
│   │       ├── PeriodeResource.php (if present)
│   │       ├── PeriodeTagihanResource/
│   │       │   └── Pages/
│   │       ├── PotongResource.php
│   │       ├── PotongResource/
│   │       │   └── Pages/
│   │       ├── TagihanResource.php
│   │       ├── TagihanResource/
│   │       │   ├── Pages/        # List, Create, Edit
│   │       │   └── RelationManagers/ # PotonganRelationManager
│   │       ├── TunkerResource.php
│   │       └── TunkerResource/
│   │           └── Pages/
│   ├── Http/
│   │   └── Controllers/          # Standard Laravel HTTP controllers (minimal usage)
│   ├── Imports/                  # Legacy Maatwebsite\Excel importers
│   │   ├── ImportGajis.php       # ToModel importer for Gaji from Excel
│   │   └── ImportTunkers.php     # ToModel importer for Tunker from Excel
│   ├── Models/                   # Eloquent ORM models
│   │   ├── Gaji.php              # Salary record (bulan, tahun, nominal)
│   │   ├── Pegawai.php           # Employee (NIK, nama, norek, golpang, isActive)
│   │   ├── Penagih.php           # Bill collector entity (nama, rules JSON)
│   │   ├── periode_tagihan.php   # Billing period (penagih_id, periode)
│   │   ├── Potong.php            # Payment deduction record
│   │   ├── Tagihan.php           # Bill/invoice record
│   │   ├── Tunker.php            # Tunjangan kerja (work allowance)
│   │   └── User.php              # Auth user
│   └── Providers/
│       ├── AppServiceProvider.php
│       └── Filament/
│           ├── AdminPanelProvider.php  # Main admin panel at /admin
│           └── AppPanelProvider.php    # Secondary panel at /app
│
├── bootstrap/
│   ├── app.php                   # Laravel 11 functional bootstrap (routing, middleware, exceptions)
│   ├── providers.php             # Auto-discovered service providers list
│   └── cache/                    # Compiled package/service caches
│
├── config/                       # Application configuration files
│   ├── app.php                   # App name, locale, timezone, providers
│   ├── auth.php                  # Auth guards and providers
│   ├── cache.php                 # Cache driver configuration
│   ├── database.php              # DB connections (SQLite default)
│   ├── filesystems.php           # Storage disk configuration
│   ├── logging.php               # Log channel configuration
│   ├── mail.php                  # Mailer configuration
│   ├── queue.php                 # Queue connection configuration
│   ├── services.php              # Third-party service credentials
│   └── session.php               # Session driver configuration
│
├── database/
│   ├── factories/
│   │   └── UserFactory.php       # User model factory for seeding/tests
│   ├── migrations/               # Timestamped schema migration files
│   │   ├── 0001_01_01_*          # Core Laravel tables (users, cache, jobs)
│   │   ├── 2024_06_27_*          # pegawais, gajis tables
│   │   ├── 2024_06_28_*          # notifications, imports, exports, failed_import_rows
│   │   ├── 2024_07_03_*          # tunkers table
│   │   ├── 2024_07_08_*          # tagihans, penagihs tables
│   │   ├── 2024_07_17_*          # periode_tagihans, tagihan column updates
│   │   ├── 2024_07_18_*          # tagihan column removals
│   │   ├── 2024_07_19_*          # potongs table
│   │   └── 2024_08_*            # Column additions (rekening, pangkat, softdeletes, is_rutin)
│   └── seeders/
│       └── DatabaseSeeder.php
│
├── public/                       # Web server document root
│   ├── index.php                 # Application entry point
│   ├── robots.txt
│   ├── css/                      # Compiled CSS assets
│   └── js/                       # Compiled JS assets
│
├── resources/
│   ├── css/                      # Source CSS (e.g., app.css)
│   ├── js/                       # Source JS (e.g., app.js)
│   └── views/                    # Blade templates
│
├── routes/
│   ├── web.php                   # Web HTTP routes (public; minimal — only welcome)
│   └── console.php               # Artisan scheduled commands
│
├── storage/
│   ├── app/                      # User-uploaded / generated files
│   ├── framework/                # Cache, sessions, compiled views
│   └── logs/                     # Application log files
│
├── tests/
│   ├── Pest.php                  # Pest configuration
│   ├── TestCase.php              # Base test case
│   ├── Feature/                  # Feature/integration tests
│   └── Unit/                     # Unit tests
│
├── vendor/                       # Composer dependencies (not committed)
├── artisan                       # Laravel CLI entry point
├── composer.json                 # PHP dependency manifest
├── package.json                  # Node dependency manifest (Vite)
├── phpunit.xml                   # PHPUnit / Pest configuration
└── vite.config.js                # Vite asset bundler configuration
```

---

## Key File Locations

| Category | Path |
|----------|------|
| App bootstrap | `bootstrap/app.php` |
| Web routes | `routes/web.php` |
| CLI routes / schedules | `routes/console.php` |
| Admin panel config | `app/Providers/Filament/AdminPanelProvider.php` |
| App panel config | `app/Providers/Filament/AppPanelProvider.php` |
| Eloquent models | `app/Models/*.php` |
| Filament resources | `app/Filament/Resources/*Resource.php` |
| Filament importers | `app/Filament/Imports/*.php` |
| Excel importers (legacy) | `app/Imports/*.php` |
| Database migrations | `database/migrations/` |
| Environment config | `.env`, `.env.example` |
| PHP dependencies | `composer.json` |
| JS/CSS pipeline | `vite.config.js`, `package.json` |
| Test suite entry | `tests/Pest.php` |

---

## Naming Conventions

| Element | Convention | Example |
|---------|------------|---------|
| Models | PascalCase singular | `Pegawai`, `Tagihan`, `PeriodeTagihan` |
| Exception (model) | snake_case singular | `periode_tagihan.php` — deviates from convention |
| Filament Resources | `{Model}Resource` | `PegawaiResource`, `TagihanResource` |
| Resource page dirs | `{Model}Resource/Pages/` | `TagihanResource/Pages/` |
| Relation managers | `{Relation}RelationManager` | `PotonganRelationManager` |
| Importers (Filament) | `{Model}Importer` | `GajiImporter` |
| Importers (Excel) | `Import{Models}` | `ImportGajis`, `ImportTunkers` |
| Migrations | `YYYY_MM_DD_HHMMSS_description` | `2024_07_08_071350_create_tagihans_table` |
| Providers | `{Context}ServiceProvider` / `{Panel}PanelProvider` | `AppServiceProvider`, `AdminPanelProvider` |
| Boolean fields | `is{State}` camelCase | `isActive`, `isGapok` |
| Foreign keys | `{model}_id` | `pegawai_id`, `penagih_id`, `periode_id` |
| Domain terms (Indonesian) | Mixed Indonesian/English | `gaji` (salary), `tagihan` (bill), `tunker` (work allowance), `potong` (deduction), `penagih` (collector) |

---

## Module / Package Organization Strategy

The codebase uses **feature-grouped namespacing inside the `app/` directory** rather than per-domain subdirectories:

- **`app/Models/`** — all Eloquent models flat, regardless of domain
- **`app/Filament/Resources/`** — one directory per resource, each containing a `Pages/` and optionally `RelationManagers/` subdirectory
- **`app/Filament/Imports/`** — Filament-native importers, separate from legacy importers
- **`app/Imports/`** — Maatwebsite Excel importers (legacy layer, may be superseded by Filament importers)
- **`app/Providers/Filament/`** — panel providers co-located under a `Filament/` subfolder within Providers

The two-panel strategy (`admin` / `app`) allows for future role-based UI separation, though currently only the `admin` panel has populated resources.

Asset bundling uses **Vite** (`vite.config.js`) for CSS/JS compilation; Filament ships its own assets independently via `php artisan filament:upgrade`.
