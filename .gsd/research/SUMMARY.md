# Research Summary — Payroll Deduction Management

> Synthesized from: STACK.md, FEATURES.md, ARCHITECTURE.md, PITFALLS.md
> Context: Brownfield Laravel 11 + Filament v3 — adding multi-provider loan deduction tracking, failed deduction handling, per-provider reconciliation, PDF payslips, and employee self-service.
> Date: 2026-02-25

---

## Recommended Stack Additions

| Package | Purpose | Install |
|---|---|---|
| `barryvdh/laravel-dompdf:^3.0` | PDF payslips — pure PHP, no Chromium binary, works in Herd | `composer require barryvdh/laravel-dompdf:^3.0` |
| `spatie/laravel-activitylog` | Audit trail for manual deduction overrides — required for finance compliance | `composer require spatie/laravel-activitylog` |
| `spatie/laravel-model-states` | Enforce deduction status state machine with allowed transitions | `composer require spatie/laravel-model-states` |

**No new package needed for:**
- Excel export — `maatwebsite/excel` already installed; add Export classes only
- Monetary precision — use PHP's native `bcmath` extension (already available in Laravel 11)

---

## Table Stakes Features

Must ship in v1. All rated Low–Medium complexity.

**Liability Management**
- Record monthly loan installment per employee per provider per period
- Prevent duplicate entry: unique constraint on `(pegawai_id, penagih_id, periode_tagihan_id)`
- Track outstanding principal across providers per employee

**Deduction Processing**
- Auto-generate deductions from confirmed `tagihan` when payroll is run
- Net salary floor check — block deduction if net would go below minimum (configurable)
- Status lifecycle: `pending → success / failed`
- Lock all deduction records once period is finalized — no edits after close
- Batch deduction action for entire billing period
- Deduction priority ordering: statutory (BPJS/PPh21) before koperasi before banks

**Failed Deduction Handling**
- Mark deduction failed with categorized reason code (not just a flag)
- Record cash payment against failed deduction: full or partial, with date and receiving staff
- Track outstanding cash per employee per provider
- Auto carry-forward unresolved remainder to next period with audit trail

**Reports & Exports**
- Per-provider reconciliation: `payroll_deducted | cash_paid | total_collected | outstanding`
- Export reconciliation to Excel per provider per period
- Failed deduction register per period with reason codes
- Cash collection ledger grouped by provider
- Payroll summary: gaji pokok, tunjangan, total potongan, gaji bersih per period

**Employee Self-Service**
- Read-only view of own payslip — current and historical periods
- Payslip shows itemized deductions by provider
- PDF download of payslip
- View active loan liabilities and failed deduction status (with user-friendly labels, not internal status codes)

**Dashboard**
- Current period status: open / processing / closed
- Count of failed deductions requiring cash collection
- Per-provider widget: expected vs. collected vs. outstanding

---

## Architecture Decisions

The team must align on these **before writing any code**:

1. **Monetary storage** — Store all Rupiah amounts as `BIGINT` (whole rupiah) in the database, never `DECIMAL` for PHP arithmetic. Use `bcmath` for all calculation chains. Changing this after data exists is painful.

2. **Payslip data strategy** — Payslips must render from a stored **JSON snapshot** (captured at finalization), not from live relational queries. Live-query payslips will silently show wrong figures after any data correction. Add `data_snapshot JSON` column to `gaji_bulanans` before the PDF phase starts.

3. **Deduction status as PHP enum + VARCHAR(30)** — Do NOT use MySQL `ENUM` column for `potong.status`. Use `VARCHAR(30)` + PHP backed enum (`PotongStatus::Pending->value = 'pending'`). Adding a new status later (e.g., `waived`) requires only a PHP change, not a table-locking migration.

4. **Reconciliation caching** — The `rekonsiliasi_penagih` table is optional for v1. Start with computed-on-the-fly queries. Add the cache table only if reporting queries exceed acceptable response times with real data volume.

5. **Queue strategy for bulk operations** — Payroll finalization and bulk payslip generation must use queued jobs (`QUEUE_CONNECTION=database`), never synchronous processing. Define this in Phase 1 so Filament actions are built queue-first from the start.

6. **Period locking enforcement** — Lock mechanism must be a **model observer** (`DeductionObserver`) that throws `PeriodLockedException` on `saving`, not just a Filament `canEdit()` check. Filament checks are bypassed by queued jobs and import commands.

---

## Top 5 Pitfalls to Avoid

1. **Floating-point money arithmetic** (P-DI-01) — Never use PHP `+`, `-`, `*` on Rupiah amounts; always `bcadd()`, `bcsub()`, `bcmul()`. Rounding errors compound across carry-forwards.

2. **Finalized period mutation** (P-DI-02) — Build the period lock observer in Phase 1, before any Filament resources exist. Retrofitting lock checks across 10+ resources and bulk actions is where bugs hide.

3. **Carry-forward double-counting** (P-DL-05) — Formula is `carried = installment - cash_partial_paid`, not the full installment. Write the reconciliation check test (`deducted + cash + carried = monthly_obligation`) *before* building the carry-forward service. Also: carry-forward creates a new `Tagihan`, never a second `Potong` on the same `Tagihan`.

4. **No deduction priority ordering** (P-DL-01) — Without a `priority` column on `penagih`, random deductions fail when salary is short. BPJS/PPh21 failing because a bank loan ran first is a compliance issue, not just a UX complaint.

5. **Provider scope bleed in reconciliation** (P-RC-02) — Every query on deduction data must be explicitly scoped with `provider_id`. Build a `ProviderDeductionQuery` builder class that **requires** `->forProvider($id)` or throws — never rely on UI filters reaching the export layer.

---

## Build Order Recommendation

Dependencies flow downward. Complete each phase fully before starting the next.

| Phase | Focus | Key Outputs |
|---|---|---|
| **1 — Database Foundation** | Migrations, enums, model casts, period lock observer | `potongs` columns, `tagihans` columns, `gaji_bulanans` table, `PotongStatus` enum, `DeductionObserver` |
| **2 — Domain Logic** | Service classes, model methods, unit tests | `PayrollComputationService`, `CarryForwardService`, `Potong::markCashPayment()`, `Tagihan::updateSisa()` |
| **3 — Core Filament UI** | Admin resources, status badges, actions | `PotongRelationManager` with cash payment action, `GajiBulananResource` with finalize action, `FinalizePayrollAction` |
| **4 — Reconciliation** | Per-provider reports and Excel export | `RekonsiliasiService`, `RekonsiliasiResource`, `RekonsiliasiPenagihExport` |
| **5 — Payslips** | PDF generation from snapshot | `PayslipService`, Blade template (DejaVu font, UTF-8), `GeneratePayslipAction` |
| **6 — Employee Self-Service** | Read-only `/app` Filament panel | Separate panel with scoped auth, `MyGajiBulananResource`, `MyTagihanResource`, payslip download |

---

## Open Questions

These could not be definitively resolved from research alone — team must decide before Phase 1:

1. **Minimum net salary floor value** — Is the floor `0` (no negative salary), the regional UMR, or a configurable percentage? This value gates the entire deduction engine logic.

2. **Deduction priority tiers** — Which providers are statutory (tier 1), which are koperasi (tier 2), which are banks (tier 3+)? Does this need to be configurable per employee or is it system-wide?

3. **Provider period boundary convention** — Does the system payroll period (e.g., 25 Dec – 24 Jan) align with provider invoice periods (1–31 Jan)? If not, each provider may need a `provider_period_label` mapping. Get sign-off from each provider before building reconciliation reports.

4. **Carry-forward auto-trigger vs. manual** — Should carry-forward run automatically on period close, or should finance trigger it explicitly per tagihan? Auto is cleaner but removes a human checkpoint.

5. **Payslip pre-generation vs. on-demand** — Generate and store PDFs at period finalization (instant download, storage cost) or generate on demand (no storage, compute cost per request)? Decision affects Phase 5 job design and storage provisioning.
