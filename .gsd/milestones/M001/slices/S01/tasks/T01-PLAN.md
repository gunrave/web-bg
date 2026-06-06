---
estimated_steps: 8
estimated_files: 2
skills_used: []
---

# T01: Upgrade PHP dependencies

Why: Foundation for the entire upgrade — bump PHP constraint, Laravel 11→12, Filament 3→4, and all Composer dependencies to latest stable versions compatible with PHP 8.4.

Do:
1. Edit composer.json require: set php to "^8.4", laravel/framework to "^12.0", filament/filament to "^4.0", maatwebsite/excel to latest compatible, laravel/tinker to latest stable
2. Edit composer.json require-dev: pestphp/pest to "^3.0", pestphp/pest-plugin-laravel to "^3.0", bump fakerphp/faker, laravel/pint, laravel/sail, mockery/mockery, nunomaduro/collision to latest stable
3. Run composer update --with-all-dependencies
4. If dependency resolution conflicts arise, fall back to surgical upgrade: composer update laravel/framework --with-dependencies first, test; then composer update filament/filament --with-dependencies, test; then update remaining packages in batches. Document any package that cannot reach latest.
5. After successful update, run composer validate to confirm composer.json and composer.lock are valid

Done when: composer.json has updated constraints (PHP ^8.4, Laravel ^12.0, Filament ^4.0), composer.lock is regenerated with all packages at latest compatible versions, composer validate passes.

## Inputs

- `composer.json`

## Expected Output

- `composer.json`
- `composer.lock`

## Verification

composer validate
