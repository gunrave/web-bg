---
estimated_steps: 8
estimated_files: 1
skills_used: []
---

# T02: Run Filament v4 automated upgrade script

Why: Filament v3→v4 has PHP API and configuration changes. The automated upgrade script handles most migrations (panel config, resource syntax, middleware changes). Running this immediately after composer update catches API breakage before Tailwind/vite work.

Do:
1. Run php artisan filament:upgrade
2. Review the script output carefully — it reports each change made and any manual fixes required
3. If the script reports unresolved issues, manually fix them (likely areas: AdminPanelProvider.php panel config, Filament resource class signatures, import classes)
4. Re-run php artisan filament:upgrade to confirm zero remaining changes needed
5. Common Filament v4 changes to watch for: Panel configuration API (colors(), middleware() chain), Import classes base class changes, Resource method signatures

Done when: php artisan filament:upgrade runs without errors and reports no remaining unapplied changes (output confirms all migrations complete).

## Inputs

- `composer.json`
- `composer.lock`
- `app/Providers/Filament/AdminPanelProvider.php`

## Expected Output

- `app/Providers/Filament/AdminPanelProvider.php`

## Verification

php artisan filament:upgrade
