---
estimated_steps: 17
estimated_files: 5
skills_used: []
---

# T05: Contract verification and manual smoke test

Why: Prove the upgrade succeeded — all dependencies are current, the build works, tests pass, and the app functions identically to before. This is the final quality gate before the slice can be marked complete.

Do — Contract checks (automated):
1. Run composer outdated — output must list zero outdated packages (or only packages intentionally held back if any were documented in T01)
2. Run vite build — must exit with code 0
3. Run php artisan test — both existing test files must pass (tests/Feature/ExampleTest.php: GET / returns 200; tests/Unit/ExampleTest.php: assert true is true)
4. Check the Laravel log (storage/logs/laravel.log) for any PHP deprecation notices, warnings, or errors — must be clean

Do — Manual smoke test (integration):
5. Start the dev server (php artisan serve or Herd) and navigate to /admin
6. Verify login page loads with correct styling
7. Log in with existing admin credentials — dashboard must load
8. Navigate to Pegawai resource — table must render with existing data rows and relationship columns
9. Create a new Pegawai record via the Create button — record must appear in the table after creation
10. Navigate to Gaji resource — table must render with pegawai.nama relationship column visible
11. Import an Excel file for Gaji (use existing import mechanism) — import must complete and records must appear in the Gaji table
12. Navigate to Tunker, Tagihan, Potong resources sequentially — each table must render correctly with data and relationship columns
13. No PHP errors or warnings must appear in the browser or Laravel log during any step

Done when: All contract checks pass (composer outdated shows zero, vite build exits 0, php artisan test passes, log is clean) AND manual smoke test completes all 7 steps without errors.

## Inputs

- `composer.json`
- `composer.lock`
- `package.json`
- `vite.config.js`
- `resources/css/app.css`

## Expected Output

- Update the implementation and proof artifacts needed for this task.

## Verification

composer outdated
