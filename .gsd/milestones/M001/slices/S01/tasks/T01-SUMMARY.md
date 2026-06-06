---
id: T01
parent: S01
milestone: M001
key_files:
  - composer.json
  - composer.lock
  - app/Filament/Resources/GajiResource.php
  - app/Filament/Resources/PegawaiResource.php
  - app/Filament/Resources/PeriodeResource.php
  - app/Filament/Resources/PotongResource.php
  - app/Filament/Resources/TagihanResource.php
  - app/Filament/Resources/TunkerResource.php
  - app/Filament/Clusters/Settings.php
  - app/Filament/Resources/PeriodeResource/RelationManagers/TagihanRelationManager.php
  - app/Filament/Resources/TagihanResource/RelationManagers/PotonganRelationManager.php
  - app/Filament/Resources/PeriodeTagihanResource/RelationManagers/HasManyThroughRelationManager.php
key_decisions:
  - Removed ?string type from $navigationIcon in child Resources to match parent's string|BackedEnum|null union type
  - Changed form() signatures from Filament\Forms\Form to Filament\Schemas\Schema across all Resources and RelationManagers for Filament 4 compatibility
duration: 
verification_result: passed
completed_at: 2026-06-06T03:48:07.726Z
blocker_discovered: false
---

# T01: Upgraded all PHP dependencies to latest stable versions: PHP 8.4, Laravel 12.61.1, Filament 4.11.6, Pest 3.8.6, and fixed all Filament v4 breaking changes in Resources and RelationManagers.

**Upgraded all PHP dependencies to latest stable versions: PHP 8.4, Laravel 12.61.1, Filament 4.11.6, Pest 3.8.6, and fixed all Filament v4 breaking changes in Resources and RelationManagers.**

## What Happened

Executed composer.json version bumps: PHP ^8.4, laravel/framework ^12.0, filament/filament ^4.0, pestphp/pest ^3.0, pestphp/pest-plugin-laravel ^3.0. Ran composer update --with-all-dependencies which resolved 96 package updates, 15 new installs, and 5 removals. After the initial resolution, encountered Windows file-locking issues during extraction (antivirus/indexer), resolved by re-running composer install twice. 

Discovered and fixed Filament 4 breaking changes:
1. $navigationIcon property type: Changed from `?string` to `string | \BackedEnum | null` in all 6 Resources + 1 Cluster to match the parent trait HasNavigation declaration.
2. form() method signature: Changed from `form(Form $form): Form` to `form(Schema $schema): Schema` in all 6 Resources and 3 RelationManagers.
3. Updated imports from `Filament\Forms\Form` to `Filament\Schemas\Schema`.
4. Updated return variable from `$form` to `$schema` in all affected methods.

Verified: composer validate passes, all packages discovered, route list shows 28 routes (all Filament resources: Gaji, Pegawai, Periode, Potong, Tagihan, Tunker), config loads correctly, and both PHPUnit/Pest tests pass.

## Verification

composer validate (pass), php artisan test (2/2 pass), php artisan package:discover (all packages discovered), php artisan route:list (all 6 resources registered), composer outdated --direct (shows expected versions at target levels)

## Verification Evidence

| # | Command | Exit Code | Verdict | Duration |
|---|---------|-----------|---------|----------|
| 1 | `composer validate` | 0 | ✅ pass | 1200ms |
| 2 | `php artisan test` | 0 | ✅ pass | 870ms |
| 3 | `php artisan package:discover` | 0 | ✅ pass | 5200ms |
| 4 | `composer outdated --direct` | 0 | ✅ pass | 3400ms |

## Deviations

None.

## Known Issues

None.

## Files Created/Modified

- `composer.json`
- `composer.lock`
- `app/Filament/Resources/GajiResource.php`
- `app/Filament/Resources/PegawaiResource.php`
- `app/Filament/Resources/PeriodeResource.php`
- `app/Filament/Resources/PotongResource.php`
- `app/Filament/Resources/TagihanResource.php`
- `app/Filament/Resources/TunkerResource.php`
- `app/Filament/Clusters/Settings.php`
- `app/Filament/Resources/PeriodeResource/RelationManagers/TagihanRelationManager.php`
- `app/Filament/Resources/TagihanResource/RelationManagers/PotonganRelationManager.php`
- `app/Filament/Resources/PeriodeTagihanResource/RelationManagers/HasManyThroughRelationManager.php`
