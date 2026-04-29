# CONCERNS.md — Technical Debt & Areas of Concern

> Generated: 2026-02-25  
> Codebase: `c:\Users\User\Herd\web-bg` (Laravel 11 + Filament 3 + Maatwebsite Excel)

---

## 1. Technical Debt

### 1.1 Non-Standard Model Naming Convention
- `app/Models/periode_tagihan.php` — uses `snake_case` class name, violating PSR-1 and Laravel's own PascalCase convention. This confuses IDE tooling, static analysis, and Eloquent's class-to-table mapping.
- `PeriodeResource.php` imports `use App\Models\Periode;` (line 10) — **this class does not exist**. The real model is `periode_tagihan`. This is a latent fatal error waiting to surface if that import path is ever reached at runtime without an earlier alias shadowing it.

### 1.2 Commented-Out / Dead Code Blocks
Large swaths of commented-out code litter the resources, indicating abandoned or half-finished features:

| File | Lines of commented code | Notes |
|---|---|---|
| `app/Filament/Resources/TagihanResource.php` | ~6 lines | Commented-out `TextColumn::make('potongan')` and `status` column |
| `app/Filament/Resources/GajiResource.php` | ~3 lines | `tahun` column commented out |
| `app/Filament/Resources/TunkerResource.php` | ~2 lines | `tahun` column commented out |
| `app/Filament/Resources/PeriodeResource.php` | ~10 lines | Toggle reactive/`requiredWith` callbacks, `tagihan_count` column |
| `app/Filament/Imports/GajiImporter.php` | ~4 lines | `resolveRecord()` upsert logic commented out |
| `app/Filament/Imports/PegawaiImporter.php` | ~4 lines | `resolveRecord()` upsert logic commented out |
| `app/Models/Potong.php` | ~4 lines | `getSuksesAttribute()` commented out |

### 1.3 Test-Only Import in Production Model
`app/Models/Potong.php` line 9:
```php
use function Pest\Laravel\get;
```
A Pest testing helper is imported inside a production Eloquent model. This adds a dev-only dependency to production code and will throw if the package is absent (e.g., in a `--no-dev` install).

### 1.4 Dual Import Systems (Legacy vs. Filament Importer)
Two parallel import mechanisms exist for the same domain:
- **Legacy** (`app/Imports/ImportGajis.php`, `app/Imports/ImportTunkers.php`) — plain Maatwebsite Excel `ToModel` importers.
- **New** (`app/Filament/Imports/GajiImporter.php`, `app/Filament/Imports/PegawaiImporter.php`) — Filament `Importer` classes.

It is unclear which is actively used. The legacy importers bypass validation; the new Filament importers have `resolveRecord()` commented out. Neither is fully functional.

### 1.5 Incomplete Migration `down()` Methods
The following migrations have empty `down()` bodies, making rollbacks impossible:

- `2024_07_17_063133_update_column_on_table_tagihan.php`
- `2024_07_18_035716_delete_column_periode_in_tagihan.php`
- `2024_07_29_033750_update_column_sukses.php`
- `2024_07_30_073535_update_column_name.php`
- `2024_08_07_043424_add_column_rekening_dan_pangkat_golongan.php`
- `2024_08_07_073147_add_softdeletes_column.php`
- `2024_08_20_034901_add_column_is_rutin.php`

### 1.6 Inconsistent Boolean Column Naming in Database Schema
Columns like `isActive`, `isGapok` use camelCase in SQL schema — unconventional for MySQL/SQLite (typically `snake_case`). Filament forms bind to these directly, making future normalization a breaking schema change.

### 1.7 Orphaned Directory
`app/Filament/Resources/PeriodeTagihanResource/RelationManagers/` exists on disk but there is **no matching `PeriodeTagihanResource.php`** file. The directory is unreachable dead code.

---

## 2. Security Concerns

### 2.1 `APP_DEBUG=true` in `.env`
`.env` line 4: `APP_DEBUG=true`  
If this file is deployed to production (even accidentally), full stack traces with local file paths, environment variables, and SQL queries will be exposed to end users. Should be `false` for any non-development environment.

### 2.2 `APP_ENV=local` in `.env`
`.env` line 2: `APP_ENV=local`  
Confirms the checked-in `.env` is a dev config. If inadvertently reused in staging/production, local-only middleware bypasses and mail/queue log drivers remain active.

### 2.3 NIK Uniqueness Constraint Commented Out
`app/Filament/Resources/PegawaiResource.php` line ~31:
```php
// ->unique()
```
The `nik` (national ID number) uniqueness validation is disabled. Duplicate employee records can be created silently — a data integrity issue that can cause incorrect billing calculations.

### 2.4 No Authorization Policies
No `Policy` classes exist in `app/Policies/`. All Filament resources are accessible to **any authenticated user** with no role or ownership checks. Any logged-in user can create, edit, or delete any `Tagihan`, `Potong`, `Pegawai`, etc.

### 2.5 Session Not Encrypted
`.env` line 32: `SESSION_ENCRYPT=false`  
Session data stored in the database is not encrypted at rest.

### 2.6 `APP_KEY` Committed in `.env`
The actual application key (`base64:3UQLWJ...`) is visible in the `.env` file on disk. While `.env` is gitignored, the key should be rotated if the file was ever committed or shared.

---

## 3. Performance Issues

### 3.1 N+1 Query Risk in PotongResource Table
`app/Filament/Resources/PotongResource.php` displays deeply nested columns:
```
tagihan.periode.periode
tagihan.periode.penagih.nama
tagihan.pegawai.nama
```
Without explicit `with(['tagihan.periode.penagih', 'tagihan.pegawai'])` on the table query, each row triggers separate queries for `tagihan`, then `periode`, then `penagih`, and `pegawai`.

### 3.2 `getGapokAttribute()` Queries Entire Table
`app/Models/Potong.php`:
```php
public function getGapokAttribute()
{
    $gapok = $this->where('isGapok', 1);
    return $gapok;
}
```
`$this->where(...)` on a model instance calls a new query-builder scoped to the **whole table**, not the current record. This is both logically incorrect and a potential full-table scan.

### 3.3 No Database Indexes on Searchable / FK Columns
No migrations define explicit indexes on frequently searched or filtered columns:
- `pegawais.nik` — used in imports (`WHERE nik = ?`) and search
- `pegawais.nama` — searchable in Filament tables
- `tagihans.pegawai_id` / `tagihans.periode_id` — used in aggregated queries (sums across the PeriodeResource table)
- `potongs.tagihan_id` — no explicit index (foreign key without constraint definition means no auto-index in some engines)

### 3.4 Aggregate Columns on PeriodeResource Without Scoping
`app/Filament/Resources/PeriodeResource.php` computes `sum('tagihan', 'jumlah')` and a filtered sum of `potongan.nominal` for every page load of the Periode list table. With large datasets this could be very slow without caching or deferred loading.

### 3.5 Hard-Coded Column Index Access in Importers
`app/Imports/ImportGajis.php` accesses Excel rows by position:
```php
'nik'     => $row[8],
'nama'    => $row[9],
'norek'   => $row[15],
'golpang' => substr($row[48], 0, 2),
'nominal' => $row[43],
```
Any change to the Excel template column order silently produces corrupt data (wrong values in wrong columns) with no runtime error.

---

## 4. Fragile Areas

### 4.1 `Penagih.rules` JSON Column Has No Enforcement
`app/Models/Penagih.php` casts `rules` to `array` (JSON). The `PeriodeResource` create-form allows storing rules with `namaKolom`, `operator`, `nilai`, `nominal`. However, **no code actually reads or evaluates these rules** to auto-calculate `Potong` records. The feature is stored but not implemented.

### 4.2 `GajiResource` "Borongan" File Upload Is Inert
`app/Filament/Resources/GajiResource.php` has a `FileUpload` component in the "Borongan" tab that accepts Excel files. There is **no action, observer, or import hook** wired to actually process the uploaded file. Files will be stored but never parsed.

### 4.3 `ImportTunkers` Silently Skips Missing Employees
`app/Imports/ImportTunkers.php`:
```php
if(!isset($pegawai)){
    return null;
}
```
If a `Tunker` row references an unknown NIK, the row is silently dropped. No failure row is recorded and the user receives no feedback about skipped data.

### 4.4 `TunkerResource` Form Uses Raw TextInput for `pegawai_id`
`app/Filament/Resources/TunkerResource.php` uses `TextInput::make('pegawai_id')` — the user must type a raw integer ID to associate a `Tunker` with an employee. There is no `Select` relationship picker, making the form unusable in practice.

### 4.5 Inconsistent `jumlah` Type (Integer vs. Money)
`app/Database/Migrations/2024_07_08_071350_create_tagihans_table.php` defines `jumlah` as `integer`. However, the resource displays it with `->money('IDR')` formatting. Indonesian Rupiah amounts can be large (e.g., 5,000,000) and cannot hold decimals. If currency precision is ever needed, this column type will require a breaking migration.

### 4.6 `GajiResource` Date Fields Split Across Two DatePickers
`bulan` and `tahun` are stored as separate integer columns but the form presents them as `DatePicker` fields. The displayed date picker format (`'m'` and `'Y'`) means a full date is selected but only part of it is saved — this is fragile and confusing for users.

---

## 5. Missing Error Handling

### 5.1 No Try/Catch in Legacy Import Models
`app/Imports/ImportGajis.php` wraps `Pegawai::firstOrCreate(...)` and `new Gaji([...])` with no error handling. If the database is unavailable or a constraint fails, the import aborts with an unhandled exception and the user sees no actionable error.

### 5.2 `ImportGajis` Does Not Implement `SkipsOnFailure`
The class declaration includes `use Maatwebsite\Excel\Concerns\SkipsOnFailure` in the import statement but the interface is **not implemented** — the `onFailure()` method and `$failures` property are absent. Row failures will throw and abort the entire import.

### 5.3 No Validation on `Tagihan.jumlah`
`TagihanResource` form has `->numeric()->required()` on `jumlah` but no `->minValue(0)` or `->maxValue()`. Negative billing amounts can be entered and silently stored.

---

## 6. Deprecated Dependencies or Patterns

### 6.1 `maatwebsite/excel` Alongside Filament's Native Importer
The project depends on `maatwebsite/excel: ^3.1` (composer.json). Filament 3 ships its own importer system (`Filament\Actions\Imports`). Running both adds unnecessary dependency surface area. The legacy `app/Imports/` classes appear to be superseded but not removed.

### 6.2 `composer/package-versions-deprecated` Transitive Dependency
`composer.lock` line 2769 references `composer/package-versions-deprecated`. This is a transitive dependency of older packages — indicates some upstream package has not updated away from deprecated Composer 1 versioning APIs.

---

## 7. Dead Code & Unused Files

| Path | Issue |
|---|---|
| `app/Filament/Resources/PeriodeTagihanResource/` | Directory with no parent Resource class; unreachable |
| `app/Imports/ImportGajis.php` | Likely superseded by `GajiImporter.php`; both exist |
| `app/Imports/ImportTunkers.php` | No corresponding Filament importer; unclear if used |
| `app/Http/Controllers/Controller.php` | Empty base controller; no custom controllers exist |
| `app/Providers/AppServiceProvider.php` | Both `register()` and `boot()` are empty; no customisations |
| `resources/views/` | Only `welcome.blade.php` exists; entire Filament UI is auto-generated |

---

## 8. Test Coverage

- **Total test files:** 1 (`tests/Feature/ExampleTest.php`)
- **Total tests:** 1 — checks that `GET /` returns HTTP 200
- **Coverage of business logic:** 0%

No tests exist for:
- Model relationships (`Pegawai → Gaji`, `Tagihan → Potong`, etc.)
- Import logic or column-index mapping
- Filament Resource form validation
- `getGapokAttribute()` bug
- `Penagih.rules` interpretation
- `periode_tagihan` naming/binding
