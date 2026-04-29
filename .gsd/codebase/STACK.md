# STACK.md — Technology Stack

## Languages & Runtimes

| Language | Version | Role |
|----------|---------|------|
| PHP | ^8.2 | Primary backend language |
| JavaScript | ES Module | Frontend scripting |
| SQL | — | Database queries via Eloquent ORM |

- PHP version constraint defined in `composer.json`: `"php": "^8.2"`
- JavaScript declared as `"type": "module"` in `package.json`

---

## Frameworks

| Framework | Version | Role |
|-----------|---------|------|
| Laravel | ^11.9 | Full-stack PHP web framework |
| Filament | ^3.2 | Admin panel / CRUD scaffolding on top of Laravel |
| Livewire | (via Filament) | Reactive UI components (Filament dependency) |
| Alpine.js | (via Filament) | Lightweight JS reactivity (Filament dependency) |

- Laravel bootstrapped via `bootstrap/app.php`
- Filament panels defined in `app/Providers/Filament/AdminPanelProvider.php` and `app/Providers/Filament/AppPanelProvider.php`
- Two Filament panels registered: `admin` (path: `/admin`) and `app` (path: `/app`)

---

## Key Production Dependencies

Defined in `composer.json` → `require`:

| Package | Version | Purpose |
|---------|---------|---------|
| `laravel/framework` | ^11.9 | Core framework |
| `filament/filament` | ^3.2 | Admin panel, forms, tables, actions |
| `maatwebsite/excel` | ^3.1 | Excel/CSV import & export (Laravel Excel) |
| `laravel/tinker` | ^2.9 | REPL for artisan console |

---

## Key Development Dependencies

Defined in `composer.json` → `require-dev`:

| Package | Version | Purpose |
|---------|---------|---------|
| `pestphp/pest` | ^2.34 | Modern PHP test runner |
| `pestphp/pest-plugin-laravel` | ^2.4 | Pest integration with Laravel |
| `laravel/pint` | ^1.13 | PHP code style fixer (opinionated, PSR-12) |
| `laravel/sail` | ^1.26 | Docker development environment |
| `fakerphp/faker` | ^1.23 | Fake data generation for tests/seeders |
| `mockery/mockery` | ^1.6 | Mocking library for unit tests |
| `nunomaduro/collision` | ^8.0 | Better error rendering in CLI |

---

## Frontend Dependencies

Defined in `package.json` → `devDependencies`:

| Package | Version | Purpose |
|---------|---------|---------|
| `vite` | ^5.0 | Frontend build tool / dev server |
| `laravel-vite-plugin` | ^1.0 | Laravel integration for Vite |
| `axios` | ^1.6.4 | HTTP client for browser XHR requests |

---

## Build Tools & Bundlers

- **Vite 5** — primary bundler, configured in `vite.config.js`
  - Entry points: `resources/css/app.css`, `resources/js/app.js`
  - Hot Module Replacement enabled via `refresh: true`
- **Laravel Mix** — not used; replaced by Vite
- **Composer** — PHP dependency manager (`composer.json`)
- **npm / package.json** — JS dependency manager

---

## ORM & Database Layer

- **Eloquent ORM** — Laravel's built-in Active Record ORM
- Models located in `app/Models/`:
  - `User.php` — authentication user
  - `Pegawai.php` — employee records
  - `Gaji.php` — salary records
  - `Tagihan.php` — billing/invoices
  - `Tunker.php` — tunjangan kinerja (performance allowances)
  - `Penagih.php` — billing agents
  - `periode_tagihan.php` — billing periods
  - `Potong.php` — deductions
- Migrations in `database/migrations/` (21 migration files spanning 2024)
- Soft deletes enabled (migration `2024_08_07_043424_add_softdeletes_column.php`)

---

## Configuration Files

| File | Purpose |
|------|---------|
| `composer.json` | PHP dependencies and autoloading |
| `package.json` | JS dependencies and npm scripts |
| `vite.config.js` | Vite bundler configuration |
| `phpunit.xml` | PHPUnit and Pest test configuration |
| `.env` / `.env.example` | Environment-specific configuration |
| `config/app.php` | Application settings (name, timezone, locale) |
| `config/database.php` | Database connections (SQLite default, MySQL/MariaDB supported) |
| `config/auth.php` | Authentication guards and providers |
| `config/cache.php` | Cache store configuration |
| `config/mail.php` | Mail transport configuration |
| `config/queue.php` | Queue driver configuration |
| `config/session.php` | Session driver configuration |
| `config/filesystems.php` | Storage disk configuration |
| `config/logging.php` | Log channel configuration |
| `bootstrap/app.php` | Application bootstrapping |
| `bootstrap/providers.php` | Service provider registration |

---

## Dev Tooling

| Tool | Config | Purpose |
|------|--------|---------|
| **Laravel Pint** | (opinionated defaults) | PHP code formatter/linter (PSR-12 based) |
| **Pest** | `phpunit.xml` | Test runner (Unit + Feature suites) |
| **PHPUnit** | `phpunit.xml` | Underlying test engine used by Pest |
| **Laravel Sail** | `composer.json` | Docker-based dev environment |
| **Laravel Tinker** | artisan | Interactive REPL |
| **Artisan CLI** | `artisan` | Laravel task runner (migrations, seeding, etc.) |

---

## Testing Setup

Defined in `phpunit.xml`:
- Test suites: `Unit` (`tests/Unit/`) and `Feature` (`tests/Feature/`)
- Source coverage: `app/` directory
- Test env overrides: `APP_ENV=testing`, `CACHE_STORE=array`, `QUEUE_CONNECTION=sync`, `SESSION_DRIVER=array`, `MAIL_MAILER=array`
- Test files: `tests/Pest.php`, `tests/TestCase.php`
