# M004: Code Overhaul (DRAFT)

**Gathered:** 2026-06-06
**Status:** Draft — discuss before execution

## Project Description

Light cleanup and standardization of the codebase — import consolidation to Filament native ImportAction, dead code removal, return type additions, and pattern standardization across the 5 Filament resources. This is the "light end" of overhaul: clean up what's there, don't go deep on strict types or test expansion.

## Why This Milestone

The app has accumulated cruft: two competing import mechanisms (old maatwebsite/excel + newer Filament importers that aren't wired in), commented-out code blocks throughout, a production bug where `Potong.php` imports Pest test helpers, inconsistent form patterns (TunkerResource uses TextInput for pegawai_id while others use Select), and model relationships without return types. This milestone makes the codebase predictable and maintainable. Depends on M001 (needs Filament v4 ImportAction API) but is otherwise independent of M002 and M003.

## Draft Scope

### In Scope — S01: Import consolidation

- Delete `app/Imports/ImportGajis.php` and `app/Imports/ImportTunkers.php`
- Delete `resources/views/filament/custom/upload-file.blade.php`
- Update `app/Filament/Imports/GajiImporter.php` to use `firstOrNew()` for update-or-create semantics (currently always `new Gaji()`)
- Create `app/Filament/Imports/TunkerImporter.php` with `firstOrNew()` for update-or-create
- Wire Filament `ImportAction` into `ListGajis` and `ListTunkers` pages (replace custom header + Blade view)
- Verify imports work: create new records and update existing records

### In Scope — S02: Code cleanup

- Remove all commented-out code blocks across the codebase:
  - `GajiResource.php`: commented-out `tahun` column, `headerActions`, `Tabs` schema
  - `TagihanResource.php`: commented-out `potongan` columns, `SelectColumn`, old `Action::make('potongan')` variant
  - `PotongResource.php`: commented-out `Split`/`Stack` layout, Placeholder form, old columns
  - `ListGajis.php`: commented-out `Gaji::create()` block
  - `GajiImporter.php`: commented-out `firstOrNew` block
  - `PegawaiImporter.php`: commented-out `firstOrNew` block
- Add explicit return types to all model relationship methods:
  - `Pegawai`: `gaji(): HasMany`, `tunker(): HasMany`
  - `Gaji`: `pegawai(): BelongsTo`
  - `Tunker`: `pegawai(): BelongsTo`
  - `Tagihan`: `periode(): BelongsTo`, `pegawai(): BelongsTo`, `potongan(): HasMany`
  - `Potong`: `tagihan(): BelongsTo`
  - `Penagih`: `tagihan(): HasManyThrough`, `periode(): HasMany`
  - `periode_tagihan`: `penagih(): BelongsTo`, `tagihan(): HasMany`
- Fix `Potong.php`: remove `use function Pest\Laravel\get;` and `use Attribute;` (unused)
- Standardize `TunkerResource.php`: change `pegawai_id` from `TextInput` to `Select` with relationship (matching GajiResource and TagihanResource pattern)
- Remove unused/duplicate imports from all resource files (e.g., `ListGajis.php` has `Doctrine\DBAL\Schema\View`, multiple `View` aliases)
- Scan for and remove any remaining `dd()`, `dump()`, or debug artifacts not caught in M002

### Out of Scope / Non-Goals

- Adding `declare(strict_types=1)` globally
- Full PHPStan/Larastan static analysis setup
- Refactoring business logic out of Filament resources
- Adding new tests
- Changing database schema (beyond M003 indexes)
- UI redesign or layout changes

## Draft Acceptance Criteria

- `rg 'app/Imports/Import' app/` returns zero results (old importers deleted)
- `rg 'upload-file' resources/` returns zero results (custom Blade deleted)
- `rg 'ImportAction' app/Filament/Resources/GajiResource/Pages/` finds a match (wired in)
- `rg 'ImportAction' app/Filament/Resources/TunkerResource/Pages/` finds a match (wired in)
- Import via Filament modal creates new records and updates existing records (verified by importing same NIK twice)
- `rg '//.*->' app/Filament/` returns zero results (no commented-out code)
- `rg '//.*TextColumn|//.*SelectColumn|//.*Placeholder|//.*Stack|//.*Split' app/` returns zero results (no commented-out Filament components)
- All model files contain `: BelongsTo`, `: HasMany`, or `: HasManyThrough` return types on relationship methods
- `rg 'Pest' app/Models/` returns zero results (production code clean of test imports)
- `rg 'TextInput::make.*pegawai_id' app/Filament/` returns zero results (TunkerResource fixed)
- Existing 2 Pest tests still pass
- Manual smoke test: login, browse all resources, import Gaji via ImportAction, import Tunker via ImportAction, verify data appears

## Open Questions

- The custom `upload-file.blade.php` also had breadcrumbs — does the Filament ImportAction UX need any custom breadcrumb handling?
- The `getHeader()` override in `ListGajis` and `ListTunkers` that injects the custom view — should we remove the override entirely or keep a simplified header?
- `PotongResource` currently uses a `ManagePotongs` page (single-page CRUD, not separate create/edit pages). Should this pattern be kept or changed to match the other resources (List/Create/Edit)?
