# S01: Full-Stack Dependency Upgrade

**Goal:** Upgrade all PHP and JS dependencies to latest stable versions while preserving existing functionality — PHP 8.4, Laravel 12, Filament 4, Tailwind CSS 4, Vite 6 — with zero regressions and `composer outdated` returning empty.
**Demo:** `composer outdated` shows zero packages at latest; `vite build` exits 0; visit /admin, log in, browse all 5 Filament resources, create a Pegawai, import a Gaji Excel file — all works identically to pre-upgrade behavior.

## Must-Haves

- `composer outdated` returns zero outdated packages
- `vite build` succeeds with exit code 0
- `php artisan test` passes (2 existing tests: Feature/ExampleTest and Unit/ExampleTest)
- Admin panel renders and all 5 core resources (Pegawai, Gaji, Tunker, Tagihan, Potong) function correctly — tables load with data, relationship columns display, CRUD operations work
- Excel import (Gaji) completes and records appear in the database
- No PHP errors, deprecation notices, or warnings during runtime

## Proof Level

- This slice proves: contract — automated build and test checks pass; integration and UAT verified via manual smoke test per milestone verification plan

## Integration Closure

None — this is the first milestone (M001); no upstream dependency slices. Produces the upgraded foundation (composer.lock, package-lock.json, compiled Vite assets) consumed by M002 (Security Hardening), M003 (Performance & Observability), and M004 (Code Overhaul). What remains for end-to-end usability: manual smoke test must pass after this slice before the milestone can be validated.

## Verification

- Runtime signals: `composer outdated` output (zero packages = healthy), `vite build` exit code (0 = build success), Laravel error log for PHP deprecation/warnings during smoke test. Inspection surfaces: `composer outdated --direct`, `vite build --debug`, `php artisan test`, browser DevTools for CSS/JS errors on admin panel. Failure visibility: Build failures block completion immediately; PHP runtime errors surface in the Laravel log and browser; CSS compilation errors surface in vite build output.

## Tasks

- [x] **T01: Upgrade PHP dependencies** `est:30m`
  Why: Foundation for the entire upgrade — bump PHP constraint, Laravel 11→12, Filament 3→4, and all Composer dependencies to latest stable versions compatible with PHP 8.4.
  - Files: `composer.json`, `composer.lock`
  - Verify: composer validate

- [x] **T02: Run Filament v4 automated upgrade script** `est:15m`
  Why: Filament v3→v4 has PHP API and configuration changes. The automated upgrade script handles most migrations (panel config, resource syntax, middleware changes). Running this immediately after composer update catches API breakage before Tailwind/vite work.
  - Files: `app/Providers/Filament/AdminPanelProvider.php`
  - Verify: php artisan filament:upgrade

- [x] **T03: Upgrade JS dependencies and configure Vite 6** `est:15m`
  Why: Vite 5→6, laravel-vite-plugin, and axios need bumping. Tailwind CSS v4 requires the @tailwindcss/vite plugin which must be added to the Vite plugin chain before the laravel plugin.
  - Files: `package.json`, `package-lock.json`, `vite.config.js`
  - Verify: test -f node_modules/tailwindcss/package.json

- [x] **T04: Configure Tailwind CSS v4** `est:20m`
  Why: This is the highest-risk piece of the upgrade. Tailwind v4 uses CSS-first configuration (no tailwind.config.js). The empty resources/css/app.css must be set up with proper v4 theme directives. Filament v4 relies on Tailwind v4.1+ for its CSS. The custom Blade view at resources/views/filament/custom/upload-file.blade.php uses Tailwind utility classes (flex, justify-between, font-bold, text-3xl, shadow, border, rounded, bg-blue-500, etc.) that must be covered by @source so they don't get purged.
  - Files: `resources/css/app.css`
  - Verify: vite build

- [x] **T05: Contract verification and manual smoke test** `est:20m`
  Why: Prove the upgrade succeeded — all dependencies are current, the build works, tests pass, and the app functions identically to before. This is the final quality gate before the slice can be marked complete.
  - Verify: composer outdated

## Files Likely Touched

- composer.json
- composer.lock
- app/Providers/Filament/AdminPanelProvider.php
- package.json
- package-lock.json
- vite.config.js
- resources/css/app.css
