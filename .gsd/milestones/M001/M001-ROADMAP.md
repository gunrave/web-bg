# M001: Dependency Modernization

**Vision:** Upgrade all PHP and JS dependencies to latest stable versions while preserving existing functionality. PHP 8.4, Laravel 12, Filament 4, Tailwind CSS 4, Vite 6 — zero regressions, zero outdated packages.

## Success Criteria

- `composer outdated` returns zero outdated packages
- `vite build` succeeds
- Admin panel renders and all 5 resources function identically on the upgraded stack
- Excel import completes successfully
- Existing 2 Pest tests pass

## Key Risks / Unknowns

- Tailwind CSS v3→v4 migration is the biggest unknown — Filament v4 requires Tailwind CSS v4.1+ → retire in S01 by proving `vite build` succeeds and admin panel renders
- Filament v4 breaking changes not caught by automated upgrade script → retire in S01 by proving all 5 resource pages function correctly
- maatwebsite/excel compatibility with upgraded stack → retire in S01 by proving an Excel import completes
- JS dependency conflicts (Vite 5→6, laravel-vite-plugin) → retire in S01 by proving `vite build` succeeds

## Proof Strategy

- Tailwind CSS v4 migration → retire in S01 by proving `vite build` succeeds and admin panel renders correctly
- Filament v4 compatibility → retire in S01 by proving all 5 resource pages render and CRUD operations work via smoke test
- Import pipeline intact → retire in S01 by proving an Excel file import completes and records appear in the database

## Verification Classes

- Contract verification: `composer outdated` returns empty; `vite build` exits 0; Pest tests pass
- Integration verification: Manual smoke test — login, CRUD browse, create employee, import file
- Operational verification: None (local-only, no server lifecycle)
- UAT / human verification: Manual smoke test across all 5 resources and import flow

## Milestone Definition of Done

This milestone is complete only when all are true:

- `composer outdated` shows zero outdated packages
- `php artisan filament:upgrade` passes without errors
- `vite build` exits with code 0
- `php artisan test` passes (2 existing tests)
- Manual smoke test passes: login works, all 5 resource tables render with data, create Pegawai works, import completes, no PHP errors or warnings
- `composer.json` has PHP constraint `^8.4`, Laravel `^12.0`, Filament `^4.0`
- `package.json` has Vite `^6.0`, laravel-vite-plugin at latest, axios at latest

## Requirement Coverage

- Covers: R001, R002, R003, R004, R005
- Partially covers: none
- Leaves for later: R006–R016 (M002, M003, M004)
- Orphan risks: none

## Slices

- [ ] **S01: Full stack dependency upgrade** `risk:high` `depends:[]`
  > After this: Admin panel renders and works on PHP 8.4 + Laravel 12 + Filament 4 + Tailwind CSS 4; `composer outdated` shows zero; `vite build` succeeds; manual smoke test passes

## Horizontal Checklist

- [ ] Every active R### re-read against new code — still fully satisfied?
- [ ] Auth boundary documented — what's protected vs public
- [ ] Graceful shutdown / cleanup on termination verified

## Boundary Map

### S01 → (M002/S01)

Produces:
- Upgraded `composer.json` and `composer.lock` with all deps at latest stable
- Upgraded `package.json` and `package-lock.json` with all JS deps at latest stable
- Working Tailwind CSS v4 configuration
- Working Vite 6 build configuration
- Functional Filament v4 admin panel with auth

Consumes:
- nothing (first milestone)
