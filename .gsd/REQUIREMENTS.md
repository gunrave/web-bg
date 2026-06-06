# Requirements

This file is the explicit capability and coverage contract for the project.

Use it to track what is actively in scope, what has been validated by completed work, what is intentionally deferred, and what is explicitly out of scope.

## Active

### R001 — All Composer dependencies updated to latest stable
- Class: core-capability
- Status: active
- Description: Every package in `composer.json` (require and require-dev) is updated to its latest stable version compatible with PHP 8.4 and Laravel 12.
- Why it matters: Foundation for all subsequent milestones; eliminates known vulnerabilities in outdated packages.
- Source: user
- Primary owning slice: M001/S01
- Supporting slices: none
- Validation: mapped
- Notes: Includes PHP constraint bump to ^8.4, Laravel 11→12, Filament 3→4, maatwebsite/excel, Pest, and all dev deps.

### R002 — Existing CRUD, import, and auth behavior preserved
- Class: constraint
- Status: active
- Description: All existing functionality — employee CRUD, salary/allowance management, billing, deductions, Excel imports, and Filament login — must work identically after every milestone.
- Why it matters: This is the core-value anchor. No regression is acceptable.
- Source: user
- Primary owning slice: M001/S01
- Supporting slices: M002/S01, M003/S01, M004/S01, M004/S02
- Validation: mapped
- Notes: Verified via manual smoke test after each milestone.

### R003 — Filament panel upgraded to v4
- Class: core-capability
- Status: active
- Description: The Filament admin panel runs on version 4.x with all resources, pages, and widgets functioning correctly.
- Why it matters: Filament 3.2 is behind; v4 brings security fixes, performance improvements, and current API surface.
- Source: user
- Primary owning slice: M001/S01
- Supporting slices: none
- Validation: mapped
- Notes: Use automated upgrade script (`vendor/bin/filament-v4`) as primary path; manual fixes as fallback.

### R004 — Tailwind CSS migrated to v4
- Class: core-capability
- Status: active
- Description: The app builds and renders correctly with Tailwind CSS v4, which is required by Filament v4.
- Why it matters: Non-optional dependency of the Filament v4 upgrade.
- Source: inferred
- Primary owning slice: M001/S01
- Supporting slices: none
- Validation: mapped
- Notes: This is the main manual migration piece of the Filament v4 upgrade.

### R005 — All npm devDependencies updated to latest stable
- Class: core-capability
- Status: active
- Description: Vite, axios, laravel-vite-plugin, and all JS dev dependencies are at their latest stable versions. `vite build` succeeds.
- Why it matters: Keeps the frontend build pipeline current.
- Source: inferred
- Primary owning slice: M001/S01
- Supporting slices: none
- Validation: mapped
- Notes: Vite 5→6, axios, laravel-vite-plugin.

### R006 — Login rate-limited against brute force
- Class: compliance/security
- Status: active
- Description: The Filament `/admin/login` route is protected by Laravel's throttle middleware (default: 5 attempts per minute).
- Why it matters: The admin panel handles financial/salary data; brute-force login protection is table stakes.
- Source: user
- Primary owning slice: M002/S01
- Supporting slices: none
- Validation: mapped
- Notes: Use Laravel's built-in `ThrottleRequests` middleware on the Filament login route.

### R007 — Import files validated for type and content
- Class: compliance/security
- Status: active
- Description: Excel import files are validated at upload time — rejected if not xlsx/xls format, and malformed rows produce user-visible error messages rather than silent failures or crashes.
- Why it matters: Prevents malicious file uploads and gives users actionable feedback on bad data.
- Source: user
- Primary owning slice: M002/S01
- Supporting slices: none
- Validation: mapped
- Notes: Filament's native `acceptedFileTypes` and import row validation handle this; errors appear in Filament's import results.

### R008 — Debug mode and dev helpers absent from production env
- Class: compliance/security
- Status: active
- Description: `APP_DEBUG` is set to `false` in `.env`. No `dd()`, `dump()`, `var_dump`, or similar debug calls remain in source code.
- Why it matters: Prevents sensitive application internals from leaking in error pages or responses.
- Source: inferred
- Primary owning slice: M002/S01
- Supporting slices: none
- Validation: mapped
- Notes: `rg` scan for debug function calls across the codebase.

### R009 — No N+1 queries in Filament table views
- Class: quality-attribute
- Status: active
- Description: Every Filament table view loads its data without triggering additional database queries per row (N+1 problem). Relationship columns use eager loading.
- Why it matters: Table views with hundreds of employees/salaries would degrade significantly without this.
- Source: user
- Primary owning slice: M003/S01
- Supporting slices: none
- Validation: mapped
- Notes: Verified via Laravel Debugbar query log; target is zero duplicate/relationship queries per page load.

### R010 — Database indexes on all foreign key columns
- Class: quality-attribute
- Status: active
- Description: Every foreign key column (`pegawai_id`, `tagihan_id`, `penagih_id`, `periode_tagihan`) has a database index.
- Why it matters: JOINs and WHERE clauses on foreign keys are the most common query patterns; unindexed columns cause full table scans.
- Source: research
- Primary owning slice: M003/S01
- Supporting slices: none
- Validation: mapped
- Notes: Add via a new migration; verify with `PRAGMA index_list` or schema inspection.

### R011 — Import processing free of per-row N+1 queries
- Class: quality-attribute
- Status: active
- Description: Import pipelines do not execute individual database queries per row for relationship lookups (e.g., looking up Pegawai by NIK for every imported Gaji row).
- Why it matters: Large imports (hundreds/thousands of rows) would be extremely slow otherwise.
- Source: inferred
- Primary owning slice: M003/S01
- Supporting slices: none
- Validation: mapped
- Notes: Pre-load Pegawai lookup map before import loop; use `firstOrNew()` with proper eager loading.

### R012 — Imports consolidated to Filament native ImportAction
- Class: core-capability
- Status: active
- Description: All Excel imports use Filament's native `ImportAction` (modal-based CSV mapping flow). The old `app/Imports/` classes and custom `upload-file.blade.php` view are deleted. Importers use `firstOrNew()` for update-or-create semantics.
- Why it matters: Single import pattern, better UX (column mapping, error reporting), less code, removes fragile custom Blade view.
- Source: user
- Primary owning slice: M004/S01
- Supporting slices: none
- Validation: mapped
- Notes: GajiImporter and a new TunkerImporter handle both import flows; PegawaiImporter already exists.

### R013 — Dead code and commented-out blocks removed
- Class: operability
- Status: active
- Description: All commented-out code blocks, unused imports, and unreferenced methods are removed from the codebase.
- Why it matters: Reduces noise, makes the codebase easier to understand and maintain.
- Source: user
- Primary owning slice: M004/S02
- Supporting slices: none
- Validation: mapped
- Notes: `rg` scan before deletion to confirm no references exist.

### R014 — Model relationship methods have explicit return types
- Class: operability
- Status: active
- Description: Every Eloquent relationship method in models declares an explicit return type (`BelongsTo`, `HasMany`, `HasManyThrough`).
- Why it matters: Improves IDE support, static analysis, and code clarity.
- Source: user
- Primary owning slice: M004/S02
- Supporting slices: none
- Validation: mapped
- Notes: `Pegawai`, `Gaji`, `Tunker`, `Tagihan`, `Potong`, `Penagih`, `periode_tagihan`.

### R015 — Filament resources follow consistent form/table patterns
- Class: operability
- Status: active
- Description: All 5 Filament resources use consistent patterns — `Select` for foreign key fields (not `TextInput`), consistent column formatting, no orphaned commented-out columns.
- Why it matters: Makes the codebase predictable and reduces bugs from inconsistent approaches.
- Source: user
- Primary owning slice: M004/S02
- Supporting slices: none
- Validation: mapped
- Notes: TunkerResource currently uses `TextInput` for `pegawai_id` — should be `Select` like other resources.

### R016 — Production code free of test/development imports
- Class: operability
- Status: active
- Description: No production source file imports test helpers or development-only packages. Specifically, `Potong.php` must not import `Pest\Laravel\get`.
- Why it matters: Test imports in production code cause runtime errors when test packages aren't installed or autoloaded.
- Source: user
- Primary owning slice: M004/S02
- Supporting slices: none
- Validation: mapped
- Notes: The `use function Pest\Laravel\get;` line in `Potong.php` is a clear bug.

## Deferred

### R017 — Full test coverage for CRUD and import flows
- Class: quality-attribute
- Status: deferred
- Description: Comprehensive Pest tests covering all CRUD operations, import flows, and edge cases.
- Why it matters: Would provide regression safety net for future changes.
- Source: user
- Primary owning slice: none
- Supporting slices: none
- Validation: unmapped
- Notes: Explicitly deferred — only existing 2 placeholder tests must continue to pass.

### R018 — CI/CD pipeline
- Class: operability
- Status: deferred
- Description: Automated testing and deployment pipeline (GitHub Actions or similar).
- Why it matters: Would catch regressions automatically.
- Source: user
- Primary owning slice: none
- Supporting slices: none
- Validation: unmapped
- Notes: App runs locally on Herd; CI not needed now.

### R019 — Database engine migration from SQLite
- Class: operability
- Status: deferred
- Description: Migrate from SQLite to MySQL or PostgreSQL.
- Why it matters: SQLite has concurrency limitations for multi-user scenarios.
- Source: user
- Primary owning slice: none
- Supporting slices: none
- Validation: unmapped
- Notes: Herd local only; SQLite is sufficient.

## Out of Scope

### R020 — `declare(strict_types=1)` globally
- Class: anti-feature
- Status: out-of-scope
- Description: Adding `declare(strict_types=1)` to every PHP file.
- Why it matters: Prevents scope creep — this is the "deep end" of overhaul that was explicitly rejected.
- Source: user
- Primary owning slice: none
- Supporting slices: none
- Validation: n/a
- Notes: "First end of overhaul deep" was chosen — light end, not full strict types.

### R021 — Queue workers for import processing
- Class: operability
- Status: out-of-scope
- Description: Background queue processing for large Excel imports.
- Why it matters: Explicitly excluded — imports remain synchronous.
- Source: user
- Primary owning slice: none
- Supporting slices: none
- Validation: n/a
- Notes: Not needed for local Herd usage.

### R022 — Production deployment
- Class: launchability
- Status: out-of-scope
- Description: Deploying the app to a production server or cloud environment.
- Why it matters: Explicitly excluded — Herd local only.
- Source: user
- Primary owning slice: none
- Supporting slices: none
- Validation: n/a
- Notes: No deployment configuration needed.

## Traceability

| ID | Class | Status | Primary owner | Supporting | Proof |
|---|---|---|---|---|---|
| R001 | core-capability | active | M001/S01 | none | mapped |
| R002 | constraint | active | M001/S01 | M002/S01, M003/S01, M004/S01, M004/S02 | mapped |
| R003 | core-capability | active | M001/S01 | none | mapped |
| R004 | core-capability | active | M001/S01 | none | mapped |
| R005 | core-capability | active | M001/S01 | none | mapped |
| R006 | compliance/security | active | M002/S01 | none | mapped |
| R007 | compliance/security | active | M002/S01 | none | mapped |
| R008 | compliance/security | active | M002/S01 | none | mapped |
| R009 | quality-attribute | active | M003/S01 | none | mapped |
| R010 | quality-attribute | active | M003/S01 | none | mapped |
| R011 | quality-attribute | active | M003/S01 | none | mapped |
| R012 | core-capability | active | M004/S01 | none | mapped |
| R013 | operability | active | M004/S02 | none | mapped |
| R014 | operability | active | M004/S02 | none | mapped |
| R015 | operability | active | M004/S02 | none | mapped |
| R016 | operability | active | M004/S02 | none | mapped |
| R017 | quality-attribute | deferred | none | none | unmapped |
| R018 | operability | deferred | none | none | unmapped |
| R019 | operability | deferred | none | none | unmapped |
| R020 | anti-feature | out-of-scope | none | none | n/a |
| R021 | operability | out-of-scope | none | none | n/a |
| R022 | launchability | out-of-scope | none | none | n/a |

## Coverage Summary

- Active requirements: 16
- Mapped to slices: 16
- Validated: 0
- Unmapped active requirements: 0
