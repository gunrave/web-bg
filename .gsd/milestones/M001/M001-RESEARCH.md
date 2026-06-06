# M001: Dependency Modernization — Research

**Date:** 2026-06-06

## Summary

The codebase is a straightforward Laravel 11.19 + Filament 3.2.97 admin panel with 5 Filament resources (Pegawai, Gaji, Tunker, Tagihan, Potong), Excel imports via both maatwebsite/excel ToModel (old) and Filament Importer classes (new), and Filament authentication. The upgrade to Laravel 12 + Filament 4 + Tailwind CSS 4 is feasible as a single coordinated upgrade since the dependencies are tightly coupled — Filament v4 requires Tailwind CSS v4.1+ and Laravel v11.28+.

The upgrade path is well-documented: Laravel 12 is a relatively minor maintenance release with few breaking changes (primarily Carbon 3), Filament v4 provides an automated upgrade script (`vendor/bin/filament-v4`), and Tailwind CSS v4 migration has automated tooling (`npx @tailwindcss/upgrade`). The main risk is Tailwind CSS v4's paradigm shift — configuration moves from `tailwind.config.js` to CSS `@source` directives, and a custom Filament theme CSS file is now required. Since the project currently has no `tailwind.config.js` or Tailwind npm package installed (Tailwind was bundled via Filament v3's pre-compiled assets), this is a net-new setup rather than a migration of existing configuration.

The existing codebase uses standard Filament v3 patterns that the automated upgrade script handles well. No custom plugins or complex overrides exist. The dual import system (old `app/Imports/` ToModel classes + new `app/Filament/Imports/` Importer classes) and the custom `upload-file.blade.php` Livewire view are the most notable non-standard patterns, but neither blocks the upgrade.

## Recommendation

Upgrade all dependencies in a single coordinated slice using this order:
1. Bump PHP constraint to `^8.4` in `composer.json`
2. Run `composer update --with-all-dependencies` to bump everything to latest
3. Run Filament's automated upgrade script (`vendor/bin/filament-v4`)
4. Install and configure Tailwind CSS v4 (npm packages, theme CSS, Vite plugin)
5. Fix any issues from the automated upgrade
6. Verify with `vite build`, `php artisan test`, and manual smoke test

The interdependencies make a slice-by-slice approach impossible — Filament v4 requires both Tailwind CSS v4.1+ and Laravel v11.28+. Attempting to split Core (Laravel+Filament) from Assets (Tailwind+Vite) would leave the app in a non-functional state between steps. A single risky slice with clear rollback strategy (git stash, restore `composer.lock` and `package-lock.json`) is the pragmatic approach.

## Implementation Landscape

### Key Files

- **`composer.json`** — PHP constraint ^8.2→^8.4; all require/require-dev constraints bumped
- **`package.json`** — vite ^5→^6; laravel-vite-plugin ^1→latest; axios ^1.6→latest; ADD tailwindcss @tailwindcss/vite as devDeps
- **`vite.config.js`** — ADD `@tailwindcss/vite` to plugins array (tailwindcssVite() plugin)
- **`resources/css/app.css`** — Currently empty. Needs full rewrite with Tailwind v4 import + Filament component CSS imports + @source directives for Filament views
- **`app/Providers/Filament/AdminPanelProvider.php`** — v3→v4 API changes (minimal; automated script handles)
- **`app/Filament/Resources/*.php`** — `->actions()` → `->recordActions()`; `->bulkActions()` → `->toolbarActions()` or `->groupedBulkActions()`; import namespace changes (`Filament\Actions\*` vs `Filament\Tables\Actions\*`)
- **`app/Filament/Resources/GajiResource/Pages/ListGajis.php`** — Has unused `Doctrine\DBAL\Schema\View` import; uses old `ImportGajis` + `Excel::import()`
- **`app/Filament/Resources/TunkerResource/Pages/ListTunkers.php`** — Uses old `ImportTunkers` + `Excel::import()`; has unused `Filament\Actions\Imports\Models\Import` import
- **`resources/views/filament/custom/upload-file.blade.php`** — Custom Livewire view with Tailwind utility classes; will need @source path in theme CSS; uses wire:submit/wire:model (compatible with Filament v4/Livewire v3)
- **`app/Imports/ImportGajis.php`** — Old ToModel import (still in use); stays until M004
- **`app/Imports/ImportTunkers.php`** — Old ToModel import (still in use); stays until M004
- **`phpunit.xml`** — Uses old schema (phpunit.xsd); should be updated for latest PHPUnit
- **`.env`** — APP_DEBUG=true (will be hardened in M002; no change needed now)

### Build Order

**Single upgrade slice (S01) — all at once, no split:**

1. **PHP constraint bump.** `^8.2`→`^8.4` in `composer.json`. This unblocks Laravel 12 and all PHP 8.4-optimized packages.

2. **npm update first.** Install `tailwindcss@^4.1` `@tailwindcss/vite` as devDeps, bump `vite` to `^6.0`, `laravel-vite-plugin` to latest. Update Vite config to add `tailwindcssVite()` plugin. Update `resources/css/app.css` with Filament v4 theme imports and Tailwind v4 configuration. This is done first so the build toolchain is ready when Filament v4's assets need compiling.

3. **Composer update.** Run `composer update --with-all-dependencies` targeting all packages. If conflicts arise, fall back to: bump Laravel to ^12.0 first → `composer update` → verify → add tailwindcss → etc. But `--with-all-dependencies` is the right first attempt.

4. **Run Filament v4 upgrade script.** `vendor/bin/filament-v4`. This handles most breaking changes automatically — namespace fixes, action method migrations, directory structure.

5. **Manual fix-ups.** After the automated upgrade: fix any remaining import issues, update `->actions()` → `->recordActions()`, `->bulkActions()` → `->toolbarActions()` or `->groupedBulkActions()` in resources. Fix the `upload-file.blade.php` if Tailwind classes are not rendering.

6. **Verify.** `vite build` (must pass), `php artisan filament:upgrade` (must pass), `php artisan test` (2 tests must pass), manual smoke test.

### Verification Approach

- **After composer update:** `php artisan --version` shows 12.x; `cat vendor/filament/filament/composer.json | grep version` shows 4.x
- **After Filament upgrade script:** `php artisan filament:upgrade` exits cleanly; no deprecation warnings in logs
- **After Tailwind config:** `vite build` exits 0
- **Smoke test command:** Manual login to `/admin`, browse 5 resource tables, create Pegawai record, verify Gaji import works
- **Existing tests:** `php artisan test` or `./vendor/bin/pest` passes both tests
- **Post-upgrade health:** `composer outdated` returns empty; `npm outdated` returns empty

### Requirements Coverage

- R001 (All Composer deps updated) — Verified by `composer outdated` post-upgrade
- R002 (Existing functionality preserved) — Manual smoke test
- R003 (Filament v4) — Verified by version check + filament:upgrade command
- R004 (Tailwind CSS v4) — Verified by `vite build` + working admin panel
- R005 (npm devDeps updated) — Verified by `npm outdated` post-upgrade

## Don't Hand-Roll

| Problem | Existing Solution | Why Use It |
|---------|------------------|------------|
| Filament v3→v4 migration | `vendor/bin/filament-v4` (automated upgrade script via `filament/upgrade` package) | Handles namespace changes, action method signatures, directory structure updates |
| Tailwind CSS v3→v4 migration | `npx @tailwindcss/upgrade` | Automatically adjusts config files and installs v4 packages |
| Vite + Tailwind CSS v4 integration | `@tailwindcss/vite` plugin | The official `@tailwindcss/vite` plugin is the recommended way to integrate Tailwind CSS v4 with Vite (replaces PostCSS approach) |

## Constraints

- **PHP 8.4.11 is on the machine** — The PHP constraint in `composer.json` must be bumped to `^8.4` to allow packages optimized for PHP 8.4.
- **Filament v4 requires Tailwind CSS v4.1+** — This is a hard dependency. The Tailwind CSS v3→v4 migration is non-optional.
- **Filament v4 requires Laravel v11.28+** — Since we're upgrading to Laravel 12, this is satisfied, but the dependency chain means packages must be upgraded together.
- **Filament v4 no longer requires `doctrine/dbal`** — If any code in the app depends on `doctrine/dbal` directly (like migrations that modify columns), it needs to be added explicitly to `composer.json`. Current scan shows only an unused import in `ListGajis.php`.
- **Filament v4 removes ability to override table methods on Livewire component** — Methods like `getTableRecordUrlUsing()` must be replaced with `$table->recordUrl()`. The codebase doesn't seem to use these patterns, but it's worth checking after the automated upgrade.
- **Livewire 3 stays** — Filament v4 uses Livewire v3.x (not v4). This is correct for this codebase. Filament v5 would require Livewire v4, which is explicitly out of scope.

## Common Pitfalls

- **Tailwind CSS classes not rendering in custom views** — In Filament v4, Tailwind classes from Filament views are no longer available without a custom theme. If the admin panel renders but custom views look unstyled, add `@source` paths to `app.css` pointing to `resources/views/filament/**/*` and `app/Filament/**/*`.
- **`vendor/bin/filament-v4` script runs commands that need manual follow-up** — The script outputs commands like `composer require filament/filament:"^4.0" -W --no-update` and `composer update`. The output is unique per application — capture it and run the suggested commands.
- **Actions namespace migration may be incomplete** — The automated script handles most `Tables\Actions\*` → `Filament\Actions\*` changes, but action arguments like `BulkActionGroup::make()` may need manual review. Check all resources after the script runs.
- **`phpunit.xml` schema may be outdated** — The current file references `vendor/phpunit/phpunit/phpunit.xsd` which may change with newer PHPUnit. After upgrade, run `php artisan test` to verify, and update `phpunit.xml` if needed.
- **Windows PowerShell caret issue** — If running on Windows, the `filament/upgrade` package must be installed with `~4.0` instead of `^4.0` because PowerShell ignores carets. The system is running on Windows (WSL or Git Bash detected).

## Open Risks

- **Tailwind CSS v4 paradigm shift** — This is a completely new configuration model. Configuration moves from `tailwind.config.js` (which doesn't exist currently) to CSS `@source` directives. Since there's no existing Tailwind config to migrate, this is net-new setup. If the Vite plugin integration doesn't work as expected, significant debugging time may be needed.
- **maatwebsite/excel compatibility** — The old `ToModel` imports (ImportGajis, ImportTunkers) use `Maatwebsite\Excel\Concerns\ToModel`. A major version bump of maatwebsite/excel could break this interface. Check the latest version's changelog before upgrading. The fallback is to pin to the highest compatible 3.x version.
- **Custom `upload-file.blade.php` Tailwind compatibility** — This view uses Tailwind utility classes directly (shadow, appearance-none, py-2, px-3, etc.). In Filament v4, Tailwind classes from Filament views are not automatically available. The custom theme CSS must include `@source` pointing to `resources/views/filament/` for these classes to be scanned and included in the build.

## Skills Discoveried

| Technology | Skill | Status |
|------------|-------|--------|
| API Design | api-design | installed |
| Decomposition | decompose-into-slices | installed |
| Interface Design | design-an-interface | installed |
| Plan Stress-Test | grill-me | installed |
| Observability | observability | installed |
| Documentation | write-docs | installed |
| Milestone Brief | write-milestone-brief | installed |

No uninstalled community skills were found that would benefit this milestone. The installed skills cover the relevant needs (planning, decomposition, observability).

## Sources

- **Laravel 12 upgrade is relatively minor** — "Much of our focus during this release cycle has been minimizing breaking changes... most Laravel applications may upgrade to Laravel 12 without changing any application code." (source: [Laravel 12 Upgrade Guide](https://laravel.com/docs/12.x/upgrade))
- **Filament v4 breaks many v3 APIs but has automated migration** — The `filament/upgrade` package and `vendor/bin/filament-v4` script handle most breaking changes. Key changes: actions namespace (`Filament\Actions\*` replaces `Filament\Tables\Actions\*`), table action methods (`->actions()` → `->recordActions()`; `->bulkActions()` → `->toolbarActions()`), and Tailwind CSS v4 requirement. (source: [Filament v4 Upgrade Guide](https://filamentphp.com/docs/4.x/upgrade-guide))
- **Filament v4 custom themes require Tailwind CSS v4 with `@source` directives** — "Custom themes in Filament v4 require an upgrade to Tailwind CSS v4. Theme CSS files should now use @source entries to tell Tailwind where to find classes, replacing the old @config 'tailwind.config.js'." The `npx @tailwindcss/upgrade` command helps migrate. (source: [Filament v4 Upgrade Guide](https://filamentphp.com/docs/4.x/upgrade-guide))
