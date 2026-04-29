# Pitfalls Research — Payroll Deduction System

> Research dimension: Pitfalls
> System: Laravel 11 + Filament v3 + Livewire
> Context: Multi-provider loan deduction management, failed payment tracking, monthly payroll workflow
> Date: 2026-02-25

---

## Data Integrity Pitfalls

### P-DI-01: Floating-Point Precision on Deduction Amounts

**Warning Signs**
- Deduction totals differ by Rp 1–2 from provider invoices
- PHP arithmetic like `0.1 + 0.2 !== 0.3` appearing in financial calculations
- Monthly carry-forward balances accumulating small rounding errors over time

**Prevention**
- Store all monetary values as `BIGINT` in the database (amounts in whole Rupiah or store cents/sen). Never use `DECIMAL` for arithmetic in PHP — only in MySQL aggregates.
- Use `bcmath` or `brick/money` for all calculation chains: installment splitting, partial payment remainder, carry-forward balance.
- Add database-level `CHECK` constraints to reject negative balances at insert time.
- Test: `bcadd('1500000.50', '750000.75', 2)` — never `1500000.50 + 750000.75`.

**Phase to Address**: Foundation / Data Model phase — fix before any calculation logic is written.

---

### P-DI-02: Finalized Payroll Period Mutation

**Warning Signs**
- Finance re-runs a report for a previous month and totals differ from the original
- Deduction records linked to a closed period are editable in Filament admin
- Audit logs show updates to records with `status = 'finalized'`

**Prevention**
- Add a `payroll_periods` table with a `locked_at` timestamp. Once set, all writes to child records (`deductions`, `payslips`, `adjustments`) must be blocked via a `PayrollPeriodLocked` policy checked in Form/Action hooks.
- In Filament, override `canEdit()`, `canDelete()`, `canCreate()` on every Resource and RelationManager tied to deduction data to check `$record->period->isLocked()`.
- Create a Laravel model observer (`DeductionObserver`) that throws a `PeriodLockedException` on `saving` if the period is finalized — this catches bulk updates that bypass Filament.
- Never use `DB::update()` directly without checking period lock.

**Phase to Address**: Data Model phase. Retrofit is painful — design lock mechanism before any Filament resources are built.

---

### P-DI-03: Missing Audit Trail on Manual Overrides

**Warning Signs**
- Finance disputes a deduction amount with no record of who changed it
- `updated_at` timestamp exists but no `updated_by` or change reason
- Manual override amounts are indistinguishable from system-calculated amounts

**Prevention**
- Use `spatie/laravel-activitylog` on all deduction models. Log `old` and `new` values on every `updated` event.
- Add a `deduction_overrides` table: `deduction_id`, `original_amount`, `override_amount`, `reason`, `overridden_by`, `overridden_at`. Never mutate the base amount — store override separately.
- In Filament, when a finance user edits an amount, force a `reason` field (required) before save via a custom Action modal, not an inline edit.
- Generate audit reports per period before locking, so discrepancies surface before finalization.

**Phase to Address**: Data Model + Filament Resource phase.

---

### P-DI-04: Soft Delete vs Hard Delete on Payroll Records

**Warning Signs**
- Deleted deduction records disappear from provider reconciliation report counts
- `withTrashed()` is forgotten in queries, causing reconciliation totals to not match historical payslips
- Re-importing a provider file creates duplicate records for soft-deleted entries

**Prevention**
- Never hard-delete any deduction, payment, or payslip record. Use `SoftDeletes` everywhere.
- Add a `deleted_reason` and `deleted_by` column alongside `deleted_at`.
- Reconciliation report queries must explicitly decide: include or exclude soft-deleted. Document this per report type.
- Unique constraints must account for soft deletes: use unique index on `(employee_id, period_id, provider_id)` where `deleted_at IS NULL`.

**Phase to Address**: Data Model phase.

---

## Deduction Logic Pitfalls

### P-DL-01: No Deduction Priority Ordering

**Warning Signs**
- When net salary is insufficient, random deductions fail rather than lower-priority ones
- Statutory deductions (BPJS, PPh21) fail because a bank loan deduction was processed first
- Finance manually decides ordering each month with no system-enforced rules

**Prevention**
- Add a `priority` integer column to the `loan_providers` table (lower number = higher priority). Default: statutory deductions = 1, koperasi = 2, banks = 3+.
- Deduction engine must process in priority order. After each deduction, recalculate remaining net salary before processing next.
- Define a minimum net salary floor (e.g., UMR or a configurable `min_net_salary` in settings). Stop processing deductions when floor would be breached — auto-fail remaining deductions.
- Document the priority rules in a `DeductionPolicy` class (not buried in a controller).

**Phase to Address**: Deduction Engine phase — before any deduction processing logic is written.

---

### P-DL-02: Negative Net Salary Not Prevented

**Warning Signs**
- Net salary fields showing negative values in payslips
- Finance discovers negative net salary only during payslip printing
- No validation on the total of all active deductions vs gross salary at enrollment time

**Prevention**
- Add a pre-deduction validation step: before committing a payroll run, calculate total committed deductions vs gross for each employee and surface violations as warnings (not errors — some may be intentional with a correction plan).
- Block finalization if any employee has negative net salary unless explicitly acknowledged by a supervisor.
- In Filament, show a running "remaining net salary" indicator when adding new deduction enrollments to an employee.

**Phase to Address**: Deduction Engine + Filament UI phase.

---

### P-DL-03: Race Condition on Concurrent Payroll Runs

**Warning Signs**
- Finance triggers payroll run while a colleague is still editing deductions for the same period
- Duplicate deduction records for the same `(employee_id, period_id, provider_id)`
- `payroll_runs` table shows two simultaneous runs for the same period

**Prevention**
- Use `DB::transaction()` with `SELECT ... FOR UPDATE` (pessimistic locking) on `payroll_periods` when starting a run.
- Implement a `running_at` / `running_by` column on `payroll_periods`. Check and set atomically before processing. Use `updateOrFail` + unique constraint.
- Filament's "Run Payroll" Action should disable and show "In Progress" to all users once triggered — use a Livewire polling component or database-driven status check.
- Add a unique database index on `(employee_id, period_id, provider_id)` to prevent duplicate inserts even if the lock fails.

**Phase to Address**: Deduction Engine phase.

---

### P-DL-04: Failed Deduction Status State Machine Not Enforced

**Warning Signs**
- A deduction can go from `failed` back to `pending` without a cash payment record
- `paid_cash` status exists on a deduction that has no matching `cash_payment` record
- Status transitions happen via direct DB updates bypassing business rules

**Prevention**
- Model deduction status as a strict state machine. Use `spatie/laravel-model-states` or implement a `DeductionStateMachine` service class with explicit allowed transitions:
  - `pending` → `deducted` (payroll run success)
  - `pending` → `failed` (insufficient salary)
  - `failed` → `paid_cash_full` (cash payment recorded, full amount)
  - `failed` → `paid_cash_partial` (cash payment recorded, partial)
  - `paid_cash_partial` → `carried_forward` (remainder added to next period)
  - Any → `waived` (requires supervisor approval)
- Filament Actions trigger state transitions, never direct status field edits.
- Log every transition with actor, timestamp, and reason.

**Phase to Address**: Data Model + Deduction Engine phase.

---

### P-DL-05: Carry-Forward Liability Double-Counting

**Warning Signs**
- An employee's balance with a provider grows unexpectedly over multiple months
- Carry-forward amount is added as a new deduction AND the original deduction remains active
- Partial cash payment is not subtracted before carry-forward is calculated

**Prevention**
- Carry-forward creates a new `deduction_adjustments` record in the next period — it does NOT create a second enrollment. Link it to the originating `deduction_id`.
- Carry-forward formula: `carried_amount = original_installment - cash_partial_paid`. Never carry the full installment if partial cash was collected.
- Reconciliation report must show: `system_deducted + cash_collected + carried_forward = total_monthly_obligation` for each provider. Build this check first as a test before building the carry-forward logic.
- Add a `source_deduction_id` FK on carry-forward records to prevent orphan carry-forwards.

**Phase to Address**: Deduction Engine phase — carry-forward logic specifically.

---

## Reconciliation Pitfalls

### P-RC-01: Cash Payment Double-Counted in Provider Reports

**Warning Signs**
- Provider reconciliation shows higher total than they invoiced
- Cash payments collected by HR are summed alongside payroll deductions without separation
- Provider receives a report showing both the failed deduction AND the cash payment as "collected"

**Prevention**
- Reconciliation report must have explicit columns: `payroll_deducted | cash_paid | total_collected | outstanding_balance`. Never aggregate them into one "paid" column.
- Cash payment records need a `reported_to_provider_at` timestamp. Exclude from payslip deduction total; include in provider reconciliation only.
- Create separate report modes: "Payroll Deductions Only" vs "Full Settlement Report" (includes cash). Finance uses different reports for different audiences.

**Phase to Address**: Reconciliation Report phase.

---

### P-RC-02: Provider Scope Bleed in Reconciliation Queries

**Warning Signs**
- Provider A's reconciliation report includes one or two records from Provider B
- Multi-provider filter on Filament list page doesn't apply to exported data
- `provider_id` filter missing from a subquery inside a complex reconciliation calculation

**Prevention**
- Every query touching deduction data must be scoped with a `provider_id` global or local scope. Never rely on the UI filter applying to exports.
- Create a `ProviderDeductionQuery` query builder class that always requires `->forProvider($providerId)` to be called — fail with an exception if not set.
- Integration test: Export Provider A report, export Provider B report, union both — assert no duplication and that union equals all-providers total.

**Phase to Address**: Reconciliation Report phase.

---

### P-RC-03: Period Boundary Mismatch Between System and Provider

**Warning Signs**
- Provider invoice covers 1–31 January but system payroll period is 25 Dec – 24 Jan
- Carry-forward from December appears on January provider report but not in provider's January invoice
- Reconciliation never balances because period definitions differ

**Prevention**
- Store both `system_period_id` and `provider_period_label` (e.g., "JAN 2026") on deduction records.
- Provider reconciliation report filters by `provider_period_label`, not by system period dates.
- At minimum: make the mapping configurable per provider in the database, not hardcoded.
- Document the period convention for each provider and get sign-off before building reports.

**Phase to Address**: Discovery/Design phase — cannot be retrofitted easily.

---

## Filament-Specific Pitfalls

### P-FI-01: Livewire State Loss on Bulk Payroll Actions

**Warning Signs**
- Finance selects 150 employee records for bulk deduction processing; Livewire times out or loses state partway through
- Browser tab freezes during bulk action because Filament is doing all processing synchronously in the request
- Partial processing: 80 employees deducted, 70 not, no way to tell which

**Prevention**
- Never process bulk payroll actions synchronously in a Filament BulkAction callback for more than ~20 records.
- Use Laravel queued jobs: `BulkAction::make('run_deductions')->action(fn($records) => ProcessDeductionsBatch::dispatch($records->pluck('id'), $periodId))`.
- Show a Filament Notification with progress via database notifications + Livewire polling once the job completes.
- Add a `batch_id` (using Laravel's `Bus::batch()`) to track partial failures and resume.

**Phase to Address**: Filament Resource phase.

---

### P-FI-02: RelationManager Loads Too Much Data

**Warning Signs**
- Employee deduction history page loads slowly for employees with 3+ years of records
- Filament RelationManager for `payments` on a deduction record fetches all payments without pagination
- N+1 queries on the deduction list page (one query per employee to load provider name)

**Prevention**
- Always define `->paginated()` on RelationManagers that can have unbounded records.
- Eager load provider and period relationships in the Resource's `getEloquentQuery()`: `->with(['provider', 'period', 'employee'])`.
- Use Filament's `->modifyQueryUsing()` on RelationManagers to add default ordering and limit.
- Install Laravel Debugbar in dev and test every Filament page with a realistic dataset (500+ employees, 24+ months of history).

**Phase to Address**: Filament Resource phase.

---

### P-FI-03: Form Validation Bypass via Direct Model Saves

**Warning Signs**
- A queued job saves a deduction record without going through Filament form validation
- Import command inserts raw data that bypasses `min_amount` or `provider_active` checks
- Validation rules exist in Filament forms but not in model-level rules or service classes

**Prevention**
- Business rules (amount > 0, provider must be active, period must be open) live in a `DeductionService` class, not in Filament form rules alone.
- Filament form rules are the UI layer. Model observers or service layer are the enforcement layer.
- Write feature tests that save models directly (bypassing Filament) and assert constraints are still enforced.

**Phase to Address**: Data Model + Deduction Engine phase.

---

### P-FI-04: Custom Filament Pages Lose Auth/Policy Checks

**Warning Signs**
- A custom Filament page for "Run Payroll Period" is accessible to all authenticated users, not just finance roles
- Policy checks work on Resources but custom pages skip `Gate::authorize()`
- URL manipulation allows a non-finance user to trigger payroll processing

**Prevention**
- Every custom Filament Page must override `public static function canAccess(): bool` using `Gate::allows()` or `auth()->user()->can()`.
- For destructive actions (lock period, run payroll, export to provider), add a second authorization check inside the action handler method — not just the page access check.
- Add a Filament middleware that validates the user has the `finance` role/permission on any page under the payroll namespace.
- Test: Log in as a non-finance user and assert 403 on all payroll action routes.

**Phase to Address**: Filament Resource phase — auth setup before any custom pages.

---

### P-FI-05: Filament Notifications Lost for Long-Running Jobs

**Warning Signs**
- Finance triggers payroll run, sees "Processing..." indefinitely
- Job completes successfully but Filament notification never appears
- User refreshes page and has no way to know if the job finished or failed

**Prevention**
- Use Laravel's database notification channel combined with Filament's native `DatabaseNotification` support.
- On job completion (both success and failure), dispatch a `PayrollRunCompleted` notification to the triggering user.
- Add a Filament widget on the payroll dashboard that polls every 5 seconds for pending notifications when a run is in progress.
- Always handle job failure: implement `failed()` method on the job that sends a failure notification with the exception message.

**Phase to Address**: Filament Resource phase.

---

## PDF/Export Pitfalls

### P-PDF-01: Memory Exhaustion on Bulk Payslip Generation

**Warning Signs**
- Generating 300+ payslips at once crashes PHP with memory limit error
- `laravel-dompdf` or `barryvdh/laravel-snappy` exhausts memory on large datasets
- PDF generation times out at 30s default PHP execution limit

**Prevention**
- Never generate all payslips in a single request. Queue one job per payslip or use chunked batch jobs.
- For "download all payslips for period" feature, generate a ZIP file asynchronously and notify when ready.
- Use `->chunk(50)` when querying employees for bulk generation.
- Set `QUEUE_CONNECTION=database` (not `sync`) in production. Payslip generation must never be synchronous.
- Consider pre-generating payslips at period finalization time so individual downloads are instant.

**Phase to Address**: PDF/Export phase.

---

### P-PDF-02: Indonesian Character Encoding in PDFs

**Warning Signs**
- Employee names with special characters (é, ñ) or Indonesian municipal names render as `?` or boxes
- PDF downloaded on Windows shows garbled text for UTF-8 characters
- Font used in dompdf does not include extended Latin or local glyph coverage

**Prevention**
- Use a font that supports extended Latin: `DejaVu Sans` (bundled with dompdf) or embed a custom font like `Noto Sans`.
- Set `<meta charset="UTF-8">` in the Blade PDF template and configure dompdf `defaultFont` to the chosen font.
- Test with employee names containing: `Ä`, `Ö`, `é`, and common Indonesian diacritics.
- Store all string data as `utf8mb4` in MySQL (not `utf8`) to support 4-byte characters.
- If using wkhtmltopdf (snappy), pass `--encoding UTF-8` in options.

**Phase to Address**: PDF/Export phase — test with real data before styling is finalized.

---

### P-PDF-03: Payslip Template Breaks on Edge-Case Data

**Warning Signs**
- Employee with 5 active loans causes deduction table to overflow into the footer
- Employee with a very long name or department title breaks table layout
- Zero-deduction employees produce a payslip with an empty table that looks broken

**Prevention**
- Design payslip template defensively: use `word-break: break-all` on long strings; cap visible name lengths with `Str::limit()` as a display-only fallback.
- Test template with: 0 deductions, 1 deduction, 8+ deductions, RP 0 net salary, and maximum-length names.
- Use a Blade component for the deduction line items so table layout is isolated and testable.
- For overflow: if deductions exceed one page, generate a second page automatically via `page-break-inside: avoid` on the table row CSS.

**Phase to Address**: PDF/Export phase.

---

### P-PDF-04: Payslip Data Snapshot Not Stored

**Warning Signs**
- A payslip downloaded 6 months later shows different figures because salary or deduction data was updated
- Payslips are generated on-demand by querying live data — they reflect current state, not period state
- Audit dispute: employee claims their payslip showed X, system now shows Y

**Prevention**
- Store payslip data as a JSON snapshot at finalization time: `payslips.data_snapshot (JSON)` column.
- PDF is always rendered from the snapshot, never from live relational data.
- Snapshot must include: gross salary, all deductions itemized, all allowances, net salary, period, and the generator's user ID.
- Treat payslip records as append-only. Corrections generate a new payslip revision (v2), not an update.

**Phase to Address**: Data Model phase — design snapshot strategy before PDF phase begins.

---

## Migration Pitfalls

### P-MG-01: Adding NOT NULL Columns to Large Tables Without Defaults

**Warning Signs**
- Migration fails in production because `ALTER TABLE` on a 50k-row table times out or locks the table
- Rolling deployment causes app errors: new column exists on some servers but not others
- `php artisan migrate` succeeds locally on 100 rows but fails on production

**Prevention**
- Always add new columns as `nullable()` first, then backfill data in a separate job, then add the constraint in a third migration.
- Use `doctrine/dbal` with `ALGORITHM=INPLACE, LOCK=NONE` for MySQL 5.7+ or use `gh-ost`/`pt-online-schema-change` for zero-downtime migrations.
- For Filament-related columns (e.g., `visible_in_portal` boolean), always provide a sensible default so existing rows are not broken.

**Phase to Address**: Every phase that adds database columns.

---

### P-MG-02: Missing Foreign Key Indexes

**Warning Signs**
- Reconciliation report query takes 30+ seconds on a table with 100k deduction records
- `EXPLAIN` shows full table scan on `provider_id` or `period_id` columns
- `deductions` table has FK constraints but no corresponding indexes

**Prevention**
- Every FK column (`employee_id`, `provider_id`, `period_id`, `deduction_id`) must have an index. Laravel's `foreignId()` creates the FK but does NOT always add the index automatically — verify with `SHOW INDEX FROM deductions`.
- Add composite indexes for reconciliation query patterns: `INDEX(provider_id, period_id, status)`.
- Run `EXPLAIN` on the three most critical queries (payroll run, reconciliation report, payslip list) with realistic data before go-live.

**Phase to Address**: Data Model phase.

---

### P-MG-03: Enum Column Changes Require Full Table Rebuild

**Warning Signs**
- Adding a new deduction status (e.g., `waived`) to an `ENUM` column locks the table during migration
- Removing an enum value that has existing rows causes constraint error
- Status string values are scattered across codebase as raw strings

**Prevention**
- Do NOT use MySQL `ENUM` for deduction status. Use `VARCHAR(30)` with a database check constraint or enforce via application layer only.
- Define status values as a PHP `enum` (backed by string): `DeductionStatus::Pending->value = 'pending'`. This gives type safety without MySQL enum limitations.
- Adding a new status = add to PHP enum + no migration needed. Removing a status = deprecation strategy first.

**Phase to Address**: Data Model phase.

---

### P-MG-04: Seeder Data Conflicts After Schema Changes

**Warning Signs**
- `php artisan db:seed` fails after a migration adds a new required column
- Seeders use hardcoded IDs that break when run in a different order
- Test suite uses `RefreshDatabase` but seeders are not updated to match new schema

**Prevention**
- Seeders must use `firstOrCreate` or `updateOrCreate`, never `create` with hardcoded IDs.
- Run full seeder suite as part of CI pipeline after every migration.
- Keep a `DatabaseSeeder` that includes both dev fixtures AND the production-required seed data (provider list, period config) so both are always tested.
- Never seed production with dev fixture data — use separate `--class` flags.

**Phase to Address**: Every phase that modifies schema.

---

## Cross-Cutting Pitfalls

### P-CC-01: No Payroll Run Dry-Run Mode

**Warning Signs**
- Finance cannot preview what will happen before committing a payroll run
- Mistakes discovered after finalization require manual corrections and re-generation
- No way to test the deduction engine against next month's data before month-end

**Prevention**
- Implement a `dry_run` flag on the payroll processing service. In dry-run mode: calculate all deductions, identify failures, flag negative salaries — but write nothing to the database.
- Expose "Preview Payroll Run" as a Filament Action that shows a summary table (X employees processed, Y deductions failed, Z employees with insufficient salary) without committing.
- Store dry-run results in a temporary session or cache for 1 hour so finance can review before confirming.

**Phase to Address**: Deduction Engine phase.

---

### P-CC-02: Employee Self-Service Panel Exposes Draft Data

**Warning Signs**
- Employee views their payslip portal and sees a payslip that finance has not yet approved
- Deduction status shows "pending" to employees who cannot understand what it means
- Employee can see another employee's records due to missing `employee_id` scope on self-service queries

**Prevention**
- Self-service panel ONLY shows records with `status = 'finalized'` on the period level. Never show in-progress periods.
- All self-service queries must use a global scope tied to `auth()->user()->employee_id`. Test by attempting to fetch another employee's record via URL manipulation.
- Map internal status names to user-friendly labels before display: `'failed' → 'Belum Terpotong'`, `'paid_cash_full' → 'Dilunasi Tunai'`.

**Phase to Address**: Employee Self-Service phase.

---

*Research completed: 2026-02-25*
*Consumer: PLAN.md creation for payroll deduction milestone*
