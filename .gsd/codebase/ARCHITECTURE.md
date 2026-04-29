# ARCHITECTURE.md

## Overall Architectural Pattern

This is a **Laravel 11 monolith** built around the **Filament v3 admin panel framework**. The architecture follows the **MVC pattern** extended with Filament's Resource-Page-Widget abstraction for the admin UI. There is no separate API layer — all interactions happen through Filament's server-side rendered UI backed by Livewire.

Key packages driving architecture:
- `filament/filament` ^3.2 — admin panel UI framework (Livewire-based)
- `maatwebsite/excel` ^3.1 — Excel import/export
- `laravel/framework` ^11.9 — base framework
- `pestphp/pest` ^2.34 — test runner

---

## Key Layers and Their Responsibilities

### 1. HTTP / Routing Layer
- `routes/web.php` — minimal public routes (only a welcome page redirect)
- `routes/console.php` — Artisan command schedules
- Filament panels handle all admin routes automatically under `/admin` and `/app` path prefixes
- `bootstrap/app.php` — application bootstrap, wires routing, middleware pipeline, and exception handling

### 2. Filament Panel Layer (`app/Filament/`)
The primary UI layer. Two panel providers are registered:

| Panel | Path | Provider |
|-------|------|----------|
| `admin` | `/admin` | `AdminPanelProvider` (default, with login) |
| `app` | `/app` | `AppPanelProvider` (no public login) |

Each panel auto-discovers its **Resources**, **Pages**, **Widgets**, and **Clusters** from their respective directories.

**Resources** (`app/Filament/Resources/`) encapsulate full CRUD UI for a model:
- `form()` — defines create/edit form schema
- `table()` — defines list view columns, filters, and row actions
- `getRelationManagers()` — defines embedded related record tables
- `getPages()` — maps URL pages (List, Create, Edit) to page classes

**Clusters** (`app/Filament/Clusters/`) group related resources under a shared navigation section. Currently one cluster: `Settings`.

**Importers** (`app/Filament/Imports/`) — Filament-native importers using the bulk import action:
- `GajiImporter.php`
- `PegawaiImporter.php`

### 3. Model / Domain Layer (`app/Models/`)
Eloquent models form the core domain. Relationships are defined directly on models:

| Model | Table | Key Relationships |
|-------|-------|-------------------|
| `User` | `users` | Auth user |
| `Pegawai` | `pegawais` | hasMany Gaji, hasMany Tunker |
| `Gaji` | `gajis` | belongsTo Pegawai |
| `Tunker` | `tunkers` | belongsTo Pegawai |
| `Penagih` | `penagihs` | hasManyThrough Tagihan via PeriodeTagihan |
| `periode_tagihan` | `periode_tagihans` | belongsTo Penagih, hasMany Tagihan, hasManyThrough Potong |
| `Tagihan` | `tagihans` | belongsTo Pegawai, belongsTo PeriodeTagihan, hasMany Potong |
| `Potong` | `potongs` | belongsTo Tagihan |

Soft deletes are applied to `Pegawai`. `Penagih.rules` is cast to `array` (JSON column).

### 4. Import Layer (`app/Imports/`)
Legacy Maatwebsite\Excel importers (separate from Filament importers):
- `ImportGajis.php` — maps Excel rows to `Gaji` model, auto-creates `Pegawai` via `firstOrCreate`
- `ImportTunkers.php` — similar pattern for `Tunker` records

### 5. Database / Migration Layer (`database/`)
- Migrations follow Laravel timestamped naming convention
- Seeders in `database/seeders/`
- Model factories in `database/factories/`

### 6. Provider / Bootstrap Layer (`app/Providers/`)
- `AppServiceProvider` — application-level bindings
- `Filament/AdminPanelProvider` — configures the admin panel (auth, colors, middleware, auto-discover)
- `Filament/AppPanelProvider` — configures a second panel (unauthenticated access path)

---

## Data Flow Through the System

### Standard CRUD request (Filament Resource)
```
Browser → HTTP Request
  → Laravel Router (Filament catches /admin/* routes)
    → Filament Middleware stack (auth, session, CSRF, Livewire bindings)
      → Filament Resource Page (ListRecords / EditRecord / CreateRecord)
        → Eloquent Model (query / save)
          → Database
        ← Model response
      ← Livewire re-renders Blade component
    ← HTML response
  ← Browser updates via Livewire diff
```

### Excel Import request
```
Browser → Upload Excel file via Filament ImportAction
  → Filament Importer class (GajiImporter / PegawaiImporter)
    → Queued background job (Laravel queue / jobs table)
      → Maatwebsite\Excel row iteration
        → Eloquent Model::create / firstOrCreate
          → Database
      ← Import result logged to `imports` / `failed_import_rows` tables
  ← UI polls for completion
```

### Public web request
```
Browser → GET /
  → Laravel Router → web.php route
    → Closure returns `view('welcome')`
  ← Blade welcome view rendered
```

---

## Abstractions and Core Concepts

### Filament Resource
The central abstraction. Each resource (`*Resource.php`) is a self-contained CRUD module bound to one Eloquent model. It declares forms, tables, pages, relation managers, and navigation metadata in one class.

### Relation Managers
Filament sub-tables rendered inside a parent resource's Edit page. Used in `TagihanResource` to show `Potong` records inline via `PotonganRelationManager`.

### Filament Cluster
`Settings` cluster groups navigation items under a single collapsible nav section — a purely UI-organizational construct.

### Importer (Filament native)
`app/Filament/Imports/` classes implement Filament's native import contract (distinct from Maatwebsite). They define column mappings and are invoked from a resource's `ImportAction`.

### Maatwebsite Importer (legacy)
`app/Imports/` contains older-style `ToModel` importers still usable via Artisan or direct controller calls.

---

## Entry Points

| Entry Point | Location | Purpose |
|-------------|----------|----------|
| HTTP web routes | `routes/web.php` | Public pages (welcome only) |
| Filament admin panel | `/admin/*` (auto-registered) | Full CRUD admin UI |
| Filament app panel | `/app/*` (auto-registered) | Secondary panel |
| Artisan CLI | `artisan` / `routes/console.php` | CLI commands & schedules |
| Health check | `/up` | Uptime monitoring endpoint |
| Queue workers | `php artisan queue:work` | Processes import jobs |

---

## Request / Response Lifecycle

1. **Entry**: `public/index.php` bootstraps Laravel via `bootstrap/app.php`
2. **Kernel**: Laravel 11 uses the slimmed functional bootstrap — no explicit `Kernel.php`; middleware is configured in `bootstrap/app.php`
3. **Routing**: `routes/web.php` + Filament's auto-registered panel routes
4. **Middleware**: Filament applies session, auth, CSRF, Livewire binding middleware per panel
5. **Controller / Page**: For Filament routes, a `Resource Page` (Livewire component) handles the request
6. **Model**: Page interacts with Eloquent models for DB operations
7. **Response**: Livewire renders a Blade view; subsequent interactions use Livewire's AJAX diff protocol
8. **Queue**: Long-running tasks (imports) are dispatched to Laravel queue and processed asynchronously
