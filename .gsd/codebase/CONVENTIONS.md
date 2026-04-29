Note: The tool simplified the command to ` cat > "c:/Users/User/Herd/web-bg/.gsd/codebase/CONVENTIONS.md" << 'ENDOFFILE'
# Code Conventions

> Analyzed from: Laravel 11 + Filament 3 project (`web-bg`)

---

## Code Style Rules

### Indentation & Whitespace
- **Indentation:** 4 spaces (no tabs) — enforced by `.editorconfig`
- **Line endings:** LF (`end_of_line = lf`)
- **Charset:** UTF-8
- **Trailing newline:** required (`insert_final_newline = true`)
- **Trailing whitespace:** trimmed, except in `.md` files

`.editorconfig` at project root:
```
indent_style = space
indent_size = 4
end_of_line = lf
charset = utf-8
insert_final_newline = true
trim_trailing_whitespace = true
```

YAML files use 2-space indent.

### PHP Style
- PHP 8.2+ minimum (`composer.json`: `"php": "^8.2"`)
- **Laravel Pint** (`laravel/pint ^1.13`) is installed as the PHP code formatter — no custom `pint.json` config found; uses Laravel preset defaults
- Opening `<?php` on line 1, no closing `?>`
- Single blank line between class methods
- Return type hints used where Laravel scaffold generates them (e.g., `BelongsTo`, `array`, `string`, `static`)
- Mixed: some methods have return types (`public function pegawai(): BelongsTo`) and some do not (`public function gaji()`)

### Quotes
- PHP: **single quotes** for strings (standard Laravel), double quotes used inside closures for label/format strings where concatenation is present
- JS/Vite config: **single quotes** (ES module style)

### Semicolons
- PHP: always, standard
- JavaScript: present (default Vite/Axios setup, no custom config)

---

## Naming Conventions

### Files
- **Filament Resources:** `PascalCase` matching model name + `Resource` suffix — e.g., `GajiResource.php`, `PegawaiResource.php`, `TagihanResource.php`
- **Filament Importers:** `PascalCase` + `Importer` suffix — e.g., `GajiImporter.php`, `PegawaiImporter.php`
- **Legacy Importers (Maatwebsite):** `PascalCase` + `Import` prefix + plural model — e.g., `ImportGajis.php`, `ImportTunkers.php`
- **Models:** `PascalCase` — **exception:** `periode_tagihan.php` uses `snake_case`, which is an inconsistency
- **Migrations:** Laravel timestamp convention — `YYYY_MM_DD_HHMMSS_description.php`
- **Tests:** `PascalCase` + `Test` suffix — e.g., `ExampleTest.php`

### Classes
- **PascalCase** for all class names
- Exception: `App\Models\periode_tagihan` violates convention (should be `PeriodeTagihan`)

### Methods
- **camelCase** — e.g., `resolveRecord()`, `getColumns()`, `getPages()`, `getRelations()`
- Relationship methods: camelCase matching related model name — e.g., `pegawai()`, `gaji()`, `tunker()`, `potongan()`

### Variables
- **camelCase** — e.g., `$pegawai_id` (some snake_case mixing), `$failedRowsCount`
- Database column names: **snake_case** — e.g., `pegawai_id`, `periode_id`, `created_at`
- Model properties: mix of camelCase (`isActive`) and snake_case (`norek`, `golpang`)

### Constants / Config Keys
- Config keys: snake_case string keys

---

## Common Patterns

### Filament Admin Panel Pattern (Primary Pattern)
The application is built almost entirely as a Filament v3 admin panel. The standard pattern per entity is:

```
app/Filament/Resources/
  {Model}Resource.php          ← Resource definition (form + table + pages)
  {Model}Resource/
    Pages/
      List{Models}.php
      Create{Model}.php
      Edit{Model}.php
    RelationManagers/           ← optional
```

Each Resource class:
- Extends `Filament\Resources\Resource`
- Defines `$model`, `$navigationIcon` as protected static properties
- Implements `form(Form $form)`, `table(Table $table)`, `getRelations()`, `getPages()` as static methods

### Eloquent ORM Pattern
- Models use `$fillable` mass-assignment protection (no `$guarded`)
- Relationships defined as instance methods returning Eloquent relation objects
- `HasFactory` trait on all models
- `SoftDeletes` trait used selectively (e.g., `Pegawai`)
- PSR-4 autoloading: `App\Models\*` namespace

### Import Pattern (dual approach)
Two import mechanisms coexist:
1. **Legacy (Maatwebsite Excel):** `app/Imports/` — implements `ToModel` concern, direct row mapping
2. **Filament Importer:** `app/Filament/Imports/` — extends `Importer`, uses `ImportColumn` definitions with validation rules

### Service Provider Pattern
`AppServiceProvider` (`app/Providers/AppServiceProvider.php`) is present but empty — no custom bindings or boot logic registered yet.

### No Repository or Service Layer
No repository pattern, service classes, or action classes found. Business logic lives directly in Filament Resources and Import classes.

---

## Error Handling Approach

- **No explicit try/catch blocks** found in application code
- Relies entirely on Laravel's built-in exception handler
- Import failures handled via Filament's `failed_import_rows` table (migration present)
- Validation handled declaratively via Filament form component rules (`->rules([...])`) and `->required()`
- No custom exception classes defined

---

## Comment / Documentation Style

### PHP Docblocks
Standard PHPDoc blocks used in scaffold/generated code:
```php
/**
 * @param array $row
 * @return \Illuminate\Database\Eloquent\Model|null
 */
```

### Inline Comments
`//` single-line comments used liberally, including commented-out code left in place:
```php
// $pegawai = Pegawai::where('nik', $row[3])->first();
// Tables\Columns\TextColumn::make('tahun')->sortable(),
```

### Laravel Block Comments
Laravel's pipe-bordered block comment style used in `Pest.php` and scaffold files:
```php
/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| Description text here...
|
*/
```

No PHPStan annotations or inline `@psalm-*` annotations found.

---

## Import / Use Statement Patterns

- All `use` statements at the top of the file, after the `namespace` declaration
- Grouped loosely: framework facades/classes first, then Filament classes, then models
- Some resources use aliased imports sparingly (e.g., `use Filament\Tables\Columns\TextColumn;` then also `Tables\Columns\TextColumn::make(...)` — mixing both FQCN-prefixed and aliased styles)
- No strict grouping enforced (no CS Fixer group rules found)

---

## Tooling Configuration

| Tool | Config File | Status |
|------|------------|--------|
| Laravel Pint | none found (`pint.json`) | Uses default Laravel preset |
| EditorConfig | `.editorconfig` | Configured |
| PHPUnit | `phpunit.xml` | Configured |
| ESLint | not found | Not in use |
| Prettier | not found | Not in use |
| PHP CS Fixer | not found | Not in use (Pint used instead) |

---

## Known Inconsistencies

1. `App\Models\periode_tagihan` — class and file named in `snake_case` instead of `PascalCase`
2. Mixed return type hint usage: some relationship methods typed, others not
3. Mixed import style in Filament resources (some use `Filament\Forms\Components\TextInput` alias, others use `Forms\Components\TextInput::` prefix)
4. `$pegawai_id` variable uses snake_case in an otherwise camelCase PHP context
ENDOFFILE
echo "Written"`, and this is the output of running that command instead:
$  cat > "c:/Users/User/Herd/web-bg/.gsd/codebase/CONVENTIONS.md" << 'ENDOFFILE'
> # Code Conventions
> 
> > Analyzed from: Laravel 11 + Filament 3 project (`web-bg`)
> 
> ---
> 
> ## Code Style Rules
> 
> ### Indentation & Whitespace
> - **Indentation:** 4 spaces (no tabs) — enforced by `.editorconfig`
> - **Line endings:** LF (`end_of_line = lf`)
> - **Charset:** UTF-8
> - **Trailing newline:** required (`insert_final_newline = true`)
> - **Trailing whitespace:** trimmed, except in `.md` files
> 
> `.editorconfig` at project root:
> ```
> indent_style = space
> indent_size = 4
> end_of_line = lf
> charset = utf-8
> insert_final_newline = true
> trim_trailing_whitespace = true
> ```
> 
> YAML files use 2-space indent.
> 
> ### PHP Style
> - PHP 8.2+ minimum (`composer.json`: `"php": "^8.2"`)
> - **Laravel Pint** (`laravel/pint ^1.13`) is installed as the PHP code formatt
er — no custom `pint.json` config found; uses Laravel preset defaults
> - Opening `<?php` on line 1, no closing `?>`
> - Single blank line between class methods
> - Return type hints used where Laravel scaffold generates them (e.g., `Belongs
To`, `array`, `string`, `static`)
> - Mixed: some methods have return types (`public function pegawai(): BelongsTo
`) and some do not (`public function gaji()`)
> 
> ### Quotes
> - PHP: **single quotes** for strings (standard Laravel), double quotes used in
side closures for label/format strings where concatenation is present
> - JS/Vite config: **single quotes** (ES module style)
> 
> ### Semicolons
> - PHP: always, standard
> - JavaScript: present (default Vite/Axios setup, no custom config)
> 
> ---
> 
> ## Naming Conventions
> 
> ### Files
> - **Filament Resources:** `PascalCase` matching model name + `Resource` suffix
 — e.g., `GajiResource.php`, `PegawaiResource.php`, `TagihanResource.php`       
> - **Filament Importers:** `PascalCase` + `Importer` suffix — e.g., `GajiImport
er.php`, `PegawaiImporter.php`
> - **Legacy Importers (Maatwebsite):** `PascalCase` + `Import` prefix + plural 
model — e.g., `ImportGajis.php`, `ImportTunkers.php`
> - **Models:** `PascalCase` — **exception:** `periode_tagihan.php` uses `snake_
case`, which is an inconsistency
> - **Migrations:** Laravel timestamp convention — `YYYY_MM_DD_HHMMSS_descriptio
n.php`
> - **Tests:** `PascalCase` + `Test` suffix — e.g., `ExampleTest.php`
> 
> ### Classes
> - **PascalCase** for all class names
> - Exception: `App\Models\periode_tagihan` violates convention (should be `Peri
odeTagihan`)
> 
> ### Methods
> - **camelCase** — e.g., `resolveRecord()`, `getColumns()`, `getPages()`, `getR
elations()`
> - Relationship methods: camelCase matching related model name — e.g., `pegawai
()`, `gaji()`, `tunker()`, `potongan()`
> 
> ### Variables
> - **camelCase** — e.g., `$pegawai_id` (some snake_case mixing), `$failedRowsCo
unt`
> - Database column names: **snake_case** — e.g., `pegawai_id`, `periode_id`, `c
reated_at`
> - Model properties: mix of camelCase (`isActive`) and snake_case (`norek`, `go
lpang`)
> 
> ### Constants / Config Keys
> - Config keys: snake_case string keys
> 
> ---
> 
> ## Common Patterns
> 
> ### Filament Admin Panel Pattern (Primary Pattern)
> The application is built almost entirely as a Filament v3 admin panel. The sta
ndard pattern per entity is:
> 
> ```
> app/Filament/Resources/
>   {Model}Resource.php          ← Resource definition (form + table + pages)
>   {Model}Resource/
>     Pages/
>       List{Models}.php
>       Create{Model}.php
>       Edit{Model}.php
>     RelationManagers/           ← optional
> ```
> 
> Each Resource class:
> - Extends `Filament\Resources\Resource`
> - Defines `$model`, `$navigationIcon` as protected static properties
> - Implements `form(Form $form)`, `table(Table $table)`, `getRelations()`, `get
Pages()` as static methods
> 
> ### Eloquent ORM Pattern
> - Models use `$fillable` mass-assignment protection (no `$guarded`)
> - Relationships defined as instance methods returning Eloquent relation object
s
> - `HasFactory` trait on all models
> - `SoftDeletes` trait used selectively (e.g., `Pegawai`)
> - PSR-4 autoloading: `App\Models\*` namespace
> 
> ### Import Pattern (dual approach)
> Two import mechanisms coexist:
> 1. **Legacy (Maatwebsite Excel):** `app/Imports/` — implements `ToModel` conce
rn, direct row mapping
> 2. **Filament Importer:** `app/Filament/Imports/` — extends `Importer`, uses `
ImportColumn` definitions with validation rules
> 
> ### Service Provider Pattern
> `AppServiceProvider` (`app/Providers/AppServiceProvider.php`) is present but e
mpty — no custom bindings or boot logic registered yet.
> 
> ### No Repository or Service Layer
> No repository pattern, service classes, or action classes found. Business logi
c lives directly in Filament Resources and Import classes.
> 
> ---
> 
> ## Error Handling Approach
> 
> - **No explicit try/catch blocks** found in application code
> - Relies entirely on Laravel's built-in exception handler
> - Import failures handled via Filament's `failed_import_rows` table (migration
 present)
> - Validation handled declaratively via Filament form component rules (`->rules
([...])`) and `->required()`
> - No custom exception classes defined
> 
> ---
> 
> ## Comment / Documentation Style
> 
> ### PHP Docblocks
> Standard PHPDoc blocks used in scaffold/generated code:
> ```php
> /**
>  * @param array $row
>  * @return \Illuminate\Database\Eloquent\Model|null
>  */
> ```
> 
> ### Inline Comments
> `//` single-line comments used liberally, including commented-out code left in
 place:
> ```php
> // $pegawai = Pegawai::where('nik', $row[3])->first();
> // Tables\Columns\TextColumn::make('tahun')->sortable(),
> ```
> 
> ### Laravel Block Comments
> Laravel's pipe-bordered block comment style used in `Pest.php` and scaffold fi
les:
> ```php
> /*
> |--------------------------------------------------------------------------
> | Test Case
> |--------------------------------------------------------------------------
> |
> | Description text here...
> |
> */
> ```
> 
> No PHPStan annotations or inline `@psalm-*` annotations found.
> 
> ---
> 
> ## Import / Use Statement Patterns
> 
> - All `use` statements at the top of the file, after the `namespace` declarati
on
> - Grouped loosely: framework facades/classes first, then Filament classes, the
n models
> - Some resources use aliased imports sparingly (e.g., `use Filament\Tables\Col
umns\TextColumn;` then also `Tables\Columns\TextColumn::make(...)` — mixing both
 FQCN-prefixed and aliased styles)
> - No strict grouping enforced (no CS Fixer group rules found)
> 
> ---
> 
> ## Tooling Configuration
> 
> | Tool | Config File | Status |
> |------|------------|--------|
> | Laravel Pint | none found (`pint.json`) | Uses default Laravel preset |
> | EditorConfig | `.editorconfig` | Configured |
> | PHPUnit | `phpunit.xml` | Configured |
> | ESLint | not found | Not in use |
> | Prettier | not found | Not in use |
> | PHP CS Fixer | not found | Not in use (Pint used instead) |
> 
> ---
> 
> ## Known Inconsistencies
> 
> 1. `App\Models\periode_tagihan` — class and file named in `snake_case` instead
 of `PascalCase`
> 2. Mixed return type hint usage: some relationship methods typed, others not
> 3. Mixed import style in Filament resources (some use `Filament\Forms\Componen
ts\TextInput` alias, others use `Forms\Components\TextInput::` prefix)
> 4. `$pegawai_id` variable uses snake_case in an otherwise camelCase PHP contex
t
> ENDOFFILE
