# M001: Dependency Modernization

**Gathered:** 2026-06-06
**Status:** Ready for planning

## Project Description

Modernize a Laravel 11 + Filament 3.2 payroll/billing admin panel by upgrading all dependencies to their latest stable versions. The app manages employee data (Pegawai), salaries (Gaji), allowances (Tunker), bills (Tagihan), deductions (Potong), and collectors (Penagih). Runs locally on Laravel Herd with SQLite.

## Why This Milestone

The app is 2+ major versions behind on its core stack — Laravel 11.9, Filament 3.2, Tailwind CSS 3, Vite 5. PHP 8.4.11 is already on the machine but the `composer.json` constrains to `^8.2`. All subsequent milestones (security, performance, code overhaul) depend on being on a current, supported foundation. This is the riskiest milestone because it touches every layer at once, but it unblocks everything else.

## User-Visible Outcome

### When this milestone is complete, the user can:

- Log into `/admin` and use all 5 Filament resources (Pegawai, Gaji, Tunker, Tagihan, Potong) with identical behavior to before
- Import Excel files for Gaji and Tunker data using the existing import mechanism
- Run `composer outdated` and see zero outdated packages
- Run `vite build` and get a successful build

### Entry point / environment

- Entry point: `http://localhost/admin` (Filament panel)
- Environment: Laravel Herd (local dev)
- Live dependencies involved: SQLite database

## Completion Class

- Contract complete means: `composer outdated` returns empty; `vite build` succeeds; `php artisan filament:upgrade` passes; existing 2 Pest tests pass.
- Integration complete means: Manual smoke test passes — login to `/admin`, browse all resource tables, create a Pegawai record, import a Gaji file, verify data appears.
- Operational complete means: None (local-only, no server lifecycle concerns).

## Final Integrated Acceptance

To call this milestone complete, we must prove:

- The Filament admin panel renders and is fully functional after upgrading to Laravel 12 + Filament 4 + Tailwind CSS 4
- An Excel import runs successfully from file upload through to database records appearing in the resource table
- All 5 resource tables render correctly with their relationship columns (pegawai.nama, periode.penagih.nama, etc.)

## Architectural Decisions

### Upgrade strategy: straight `composer update`, surgical fallback

**Decision:** Attempt a single `composer update --with-all-dependencies` to bump everything at once. If conflicts or breakage occur, fall back to a package-by-package surgical upgrade (Laravel first, test; Filament next, test; Tailwind CSS, test; etc.).

**Rationale:** The straight update is fastest. The surgical fallback provides an escape hatch if dependency conflicts make the straight path unworkable. This mirrors the user's stated preference: "Use straight update, if possible. Then go to surgical if error occurred or too complicated."

**Alternatives Considered:**
- Full surgical from the start — safer but slower; rejected because the straight path is likely to succeed with this standard Laravel stack.
- Individual package updates with separate commits — overkill for a local-only app with no CI; adds ceremony without benefit.

### Filament target: v4 (not v5)

**Decision:** Upgrade Filament to v4.x (latest stable), not v5.

**Rationale:** Filament v5 requires Livewire v4.0+ which is a significant migration burden. v4 gets us to the current stable line, provides the automated upgrade script (`vendor/bin/filament-v4`), and this codebase uses no deprecated v3 patterns that would complicate the upgrade. v5 can be evaluated later when it's more mature and when Livewire v4 migration is a separate, planned effort.

**Alternatives Considered:**
- Filament v5 — available but preemptive; adds Livewire v4 migration to M001 scope unnecessarily.
- Stay on Filament 3.2 — defeats the purpose of the modernization.

### Tailwind CSS v4 migration (forced by Filament v4)

**Decision:** Migrate Tailwind CSS from v3 to v4 as part of the Filament v4 upgrade.

**Rationale:** Filament v4 requires Tailwind CSS v4.1+. This is non-optional. This is expected to be the main manual work in the upgrade — the automated Filament upgrade script handles PHP-side changes, but Tailwind configuration changes may need manual attention.

**Alternatives Considered:**
- None — Filament v4 requirement, no alternative.

## Error Handling Strategy

- **Composer conflicts:** If `composer update` fails with dependency resolution errors, switch to surgical upgrade (package by package, each verified). Document any package that cannot be upgraded.
- **Filament upgrade script failures:** Run `vendor/bin/filament-v4`. If it fails or leaves unresolved issues, fix manually. Re-run the script afterward to confirm it reports no remaining changes needed.
- **Tailwind CSS build failures:** Any `vite build` error blocks completion. Fix Tailwind config/syntax until the build passes. This is the highest-risk piece of the upgrade.
- **Behavioral regressions:** After each major dependency bump, run the manual smoke test. Any failure (login broken, table won't render, import crashes, relationship columns empty) blocks and must be fixed before proceeding.
- **PHP version mismatch:** The machine has PHP 8.4.11. If any package has an upper PHP version constraint that blocks 8.4, document it and pin to the highest compatible version.

## Risks and Unknowns

- **Tailwind CSS v3→v4 migration** — This is the biggest unknown. Filament v4 requires Tailwind CSS v4.1+. The automated upgrade may not catch all Tailwind configuration changes. Build failures here could take significant debugging. → Retire in S01 by proving `vite build` succeeds and admin panel renders correctly.
- **Filament v4 breaking changes** — While the automated upgrade script handles most changes, any manual overrides or customizations in the app could surface edge cases not covered by the script. → Retire in S01 by proving all 5 resource pages render and function correctly.
- **maatwebsite/excel compatibility** — The old `ImportGajis` and `ImportTunkers` classes use raw `ToModel` from maatwebsite/excel. Version bumps in this package could break the import pipeline. → Retire in S01 by proving an Excel import completes successfully.
- **JS dependency conflicts** — Vite 5→6 and laravel-vite-plugin version bumps may require configuration changes. → Retire in S01 by proving `vite build` succeeds.

## Existing Codebase / Prior Art

- `composer.json` — Current constraints: PHP ^8.2, Laravel ^11.9, Filament ^3.2, maatwebsite/excel ^3.1, Pest ^2.34. All need bumping.
- `package.json` — Current devDeps: Vite ^5.0, axios ^1.6.4, laravel-vite-plugin ^1.0. All need bumping.
- `vite.config.js` — Vite config uses `laravel-vite-plugin`. May need updates for Vite 6.
- `app/Providers/Filament/AdminPanelProvider.php` — Filament panel config. Uses Filament v3 API; automated upgrade script should handle v4 API changes.
- `resources/css/app.css` — Tailwind CSS entry point. Will need v4 configuration changes.
- `tailwind.config.js` — If it exists, will need migration to Tailwind CSS v4 config format.

## Relevant Requirements

- R001 — All Composer deps updated
- R002 — Existing functionality preserved
- R003 — Filament v4
- R004 — Tailwind CSS v4
- R005 — npm devDeps updated

## Scope

### In Scope

- Bump PHP constraint to `^8.4` in `composer.json`
- Upgrade `laravel/framework` from ^11.9 to ^12.0
- Upgrade `filament/filament` from ^3.2 to ^4.0
- Upgrade `maatwebsite/excel` to latest stable
- Upgrade `laravel/tinker` to latest stable
- Upgrade all require-dev packages (pest, faker, pint, sail, mockery, collision)
- Migrate Tailwind CSS from v3 to v4 (configuration, imports, any custom styles)
- Upgrade Vite from ^5.0 to ^6.0
- Upgrade `laravel-vite-plugin` and `axios` to latest
- Verify existing 2 Pest tests still pass
- Manual smoke test: login, CRUD browse, create employee, import file

### Out of Scope / Non-Goals

- Adding new features or changing existing behavior
- Database engine migration (stays SQLite)
- Adding tests beyond the existing 2
- Livewire v4 migration (Filament v5 blocked)
- PHP `declare(strict_types=1)`

## Technical Constraints

- PHP 8.4.11 is the target runtime (already installed on the machine)
- SQLite database must remain working (no migrations destructively altered)
- Custom Blade view `upload-file.blade.php` must still function (import consolidation happens in M004)
- Filament auth must still work (login, session, middleware)

## Integration Points

- None — the app is self-contained (no external APIs, no third-party services).

## Testing Requirements

- Existing 2 Pest tests (`tests/Feature/ExampleTest.php`, `tests/Unit/ExampleTest.php`) must pass.
- Manual smoke test is the primary integration verification:
  1. Visit `/admin` — login page loads
  2. Log in — dashboard loads
  3. Navigate to Pegawai — table renders with data
  4. Create a new Pegawai — record appears in table
  5. Navigate to Gaji — table renders with relationship columns (pegawai.nama)
  6. Import an Excel file for Gaji — records appear
  7. Navigate to Tunker, Tagihan, Potong — tables render correctly
- `vite build` must exit zero

## Acceptance Criteria

- `composer outdated` returns zero packages (all at latest compatible)
- `php artisan filament:upgrade` completes without errors
- `vite build` exits with code 0
- `php artisan test` passes (2 tests)
- Manual smoke test passes all 7 steps above
- No PHP deprecation notices or warnings during smoke test

## Open Questions

- None — all architectural decisions resolved during discussion.
