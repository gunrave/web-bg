# Architecture Research — Payroll Feature Additions

## Database Changes Required

### Modify: `potongs` table
Add columns to track deduction outcome per `Potong` record:

```php
$table->enum('status', ['pending', 'success', 'failed', 'cash_full', 'cash_partial'])
      ->default('pending');
$table->decimal('cash_amount', 15, 2)->nullable();  // amount paid in cash (full or partial)
$table->string('cash_method')->nullable();           // e.g. 'transfer', 'tunai'
$table->date('cash_paid_at')->nullable();
$table->text('notes')->nullable();
```

**Status semantics:**
- `pending` — not yet processed for the period
- `success` — deducted from payroll successfully
- `failed` — deduction attempted but failed (insufficient salary, etc.)
- `cash_full` — failed deduction recovered in full via cash payment
- `cash_partial` — failed deduction partially recovered via cash; remainder still outstanding

### Modify: `tagihans` table
Add carry-forward tracking and draft state:

```php
$table->enum('state', ['draft', 'active', 'closed'])->default('draft');
$table->unsignedBigInteger('carried_from_id')->nullable(); // FK to previous month's tagihan
$table->foreign('carried_from_id')->references('id')->on('tagihans')->nullOnDelete();
$table->decimal('jumlah_pokok', 15, 2);   // original loan/liability amount (already present or add now)
$table->decimal('sisa_tagihan', 15, 2);   // remaining balance after deductions
```

### New table: `gaji_bulanan` (monthly payroll computation record)
One record per employee per period. Stores computed gross/net so it can be locked after finalization.

```php
Schema::create('gaji_bulanans', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('pegawai_id');
    $table->string('periode');              // e.g. '2026-02' (YYYY-MM)
    $table->decimal('gaji_pokok', 15, 2);  // snapshot of Gaji at time of finalization
    $table->decimal('tunker', 15, 2);      // snapshot of Tunker at time of finalization
    $table->decimal('total_potongan', 15, 2)->default(0);
    $table->decimal('gaji_bersih', 15, 2)->generated(); // or computed at finalization
    $table->enum('status', ['draft', 'final'])->default('draft');
    $table->timestamp('finalized_at')->nullable();
    $table->timestamps();

    $table->foreign('pegawai_id')->references('id')->on('pegawais');
    $table->unique(['pegawai_id', 'periode']);
});
```

> Store snapshots (gaji_pokok, tunker) rather than live joins so finalized payroll is immutable.

### New table: `rekonsiliasi_penagih` (per-provider reconciliation cache — optional but recommended)
Pre-aggregated per `Penagih` per `periode` for reporting performance:

```php
Schema::create('rekonsiliasi_penagih', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('penagih_id');
    $table->string('periode');
    $table->decimal('total_tagihan', 15, 2)->default(0);
    $table->decimal('total_potong_payroll', 15, 2)->default(0);
    $table->decimal('total_cash', 15, 2)->default(0);
    $table->decimal('total_terkumpul', 15, 2)->virtualAs('total_potong_payroll + total_cash');
    $table->decimal('selisih', 15, 2)->virtualAs('total_tagihan - (total_potong_payroll + total_cash)');
    $table->timestamp('generated_at')->nullable();
    $table->timestamps();

    $table->foreign('penagih_id')->references('id')->on('penagihs');
    $table->unique(['penagih_id', 'periode']);
});
```

> Alternatively, compute on the fly in a query scope/service and skip persisting this table until performance requires it.

---

## Domain Logic Layer

### Service Classes (app/Services/)

#### `PayrollComputationService`
Responsible for monthly gross/net calculation.

```
compute(Pegawai $pegawai, string $periode): GajiBulanan
  - Fetch Gaji (base pay) and Tunker (allowance) for pegawai
  - Fetch all Tagihan in 'active' state for periode with Potong records
  - gross = gaji_pokok + tunker
  - total_potongan = sum of Potong where status IN ('pending', 'success')
  - net = gross - total_potongan
  - Persist/upsert GajiBulanan record (status = draft)

finalize(string $periode): void
  - Compute all employees for periode
  - Lock GajiBulanan records (status = final, finalized_at = now())
  - Mark Potong records as 'success' or 'failed' based on net salary floor rule
```

**Business rule — deduction floor:** if net salary after a deduction would go below a configured minimum (e.g. UMR or zero), mark that `Potong` as `failed`.

#### `CarryForwardService`
```
carryForward(string $fromPeriode, string $toPeriode): void
  - For each Tagihan in fromPeriode where sisa_tagihan > 0:
      - Create new Tagihan for toPeriode (state = 'draft')
      - Set carried_from_id = source tagihan id
      - Create Potong records for new Tagihan (status = 'pending')
  - Does NOT copy Tagihan where liability is fully settled
```

#### `RekonsiliasiService`
```
generate(Penagih $penagih, string $periode): RekonsiliasiPenagih
  - Sum all Potong.status = 'success' for Tagihan under penagih's PeriodeTagihan for periode
  - Sum all Potong.cash_amount where status IN ('cash_full', 'cash_partial')
  - Compute total_terkumpul, selisih
  - Upsert RekonsiliasePenagih record
```

#### `PayslipService`
```
generate(GajiBulanan $gajiBulanan): string (PDF path or response)
  - Collect pegawai info, gaji_pokok, tunker, Potong breakdown
  - Render Blade/DomPDF template
  - Return PDF stream or store in storage/
```

### Model Methods (thin, delegate to services)

- `Potong::markCashPayment(float $amount, string $method)` — updates cash_amount, cash_method, cash_paid_at, recalculates status
- `Tagihan::updateSisa()` — recomputes sisa_tagihan from sum of unpaid/failed Potong
- `Pegawai::gajiForPeriode(string $periode): GajiBulanan` — convenience accessor
- `Penagih::rekonsiliasi(string $periode): RekonsiliasiPenagih`

### Enums (app/Enums/)

```php
// app/Enums/PotongStatus.php
enum PotongStatus: string {
    case Pending = 'pending';
    case Success = 'success';
    case Failed = 'failed';
    case CashFull = 'cash_full';
    case CashPartial = 'cash_partial';
}
```

---

## Filament UI Integration Points

### New Resources

| Resource | Panel | Purpose |
|---|---|---|
| `GajiBulananResource` | admin | View/finalize monthly payroll table grouped by periode |
| `RekonsiliasiResource` | admin | Per-provider reconciliation report per period |

### Modified Resources

#### `TagihanResource`
- Add `state` badge column
- Add "Carry Forward" bulk action (calls `CarryForwardService`)
- Add filter by `state`, `periode`

#### `PotongResource` (or Relation Manager on Tagihan)
- Add `status` badge using `PotongStatus` enum with color coding
- Add **"Record Cash Payment"** Action (modal with `cash_amount`, `cash_method` fields → calls `Potong::markCashPayment()`)
- Add filter by status

### New Actions

#### `FinalizePayrollAction` (on GajiBulananResource or as a Page Action)
- Trigger: Finance team clicks "Finalize [Month]"
- Calls `PayrollComputationService::finalize()`
- Requires confirmation modal with "This will lock all payroll records for [period]"
- Checkpoint: only allowed if no `GajiBulanan` in `final` state already exists for that period

#### `GeneratePayslipAction` (on GajiBulananResource row)
- Triggers `PayslipService::generate()`
- Returns downloadable PDF response via Filament stream response

#### `CarryForwardAction` (on PeriodeTagihanResource or bulk on TagihanResource)
- Calls `CarryForwardService::carryForward()`
- Confirms which employee Tagihan will be copied

### Employee Self-Service Panel (`/app` panel)

- Separate Filament panel registered with `->id('karyawan')` and auth guard scope
- `Pegawai` model must have auth (gate policy or panel-level scope)
- Resources in this panel:
  - `MyGajiBulananResource` — read-only, scoped to `auth()->user()->pegawai_id`
  - `MyTagihanResource` — read-only own liabilities
  - Payslip download action

**Scope enforcement pattern:**
```php
// In each karyawan-panel Resource:
public static function getEloquentQuery(): Builder
{
    return parent::getEloquentQuery()
        ->where('pegawai_id', auth()->user()->pegawai->id);
}
```

### Relation Managers

| Parent Resource | Relation Manager | Shows |
|---|---|---|
| `TagihanResource` | `PotongRelationManager` | All deductions with status + cash recording |
| `PenagihResource` | `PeriodeTagihanRelationManager` | Already exists; add rekonsiliasi summary header |
| `PegawaiResource` | `GajiBulananRelationManager` | Employee payroll history |

---

## Key Data Flows

### 1. Monthly Payroll Finalization Flow

```
Finance team opens GajiBulanan for periode X
  → Clicks "Finalize Payroll"
  → FinalizePayrollAction fires
  → PayrollComputationService::finalize('2026-02')
      for each Pegawai:
        gross = gaji_pokok + tunker (snapshot values)
        for each active Tagihan.Potong (status=pending):
          if (gross - cumulative_deductions) >= floor:
            Potong.status = success
            cumulative_deductions += Potong.jumlah
          else:
            Potong.status = failed
        GajiBulanan.total_potongan = sum(success Potong)
        GajiBulanan.gaji_bersih = gross - total_potongan
        GajiBulanan.status = final
        GajiBulanan.finalized_at = now()
  → Finance views final payroll table
```

### 2. Failed Deduction + Cash Payment Flow

```
Finance opens TagihanResource → PotongRelationManager
  → Sees Potong with status = 'failed' (red badge)
  → Clicks "Record Cash Payment" action
  → Modal: cash_amount (required), cash_method (select), date
  → Potong::markCashPayment() called:
      potong.cash_amount = input
      potong.cash_paid_at = input date
      potong.cash_method = input
      if cash_amount >= potong.jumlah:
        potong.status = cash_full
      else:
        potong.status = cash_partial
      tagihan.updateSisa()  // recalculate remaining balance
  → Status badge updates in UI
```

### 3. Per-Provider Reconciliation Flow

```
Finance opens RekonsiliasiResource, selects Penagih + Periode
  → RekonsiliasiService::generate(penagih, periode) called
  → Aggregates:
      total_tagihan = sum(Tagihan.jumlah) for penagih's PeriodeTagihan matching periode
      total_potong_payroll = sum(Potong.jumlah WHERE status='success')
      total_cash = sum(Potong.cash_amount WHERE status IN ('cash_full','cash_partial'))
      total_terkumpul = total_potong_payroll + total_cash
      selisih = total_tagihan - total_terkumpul
  → Upserts RekonsiliasiPenagih record
  → Displayed as summary card + per-employee breakdown table
  → "Export PDF" action available for forwarding to provider
```

### 4. Payslip Generation Flow

```
Finance (or Employee in /app panel) opens GajiBulananResource row
  → Clicks "Download Payslip"
  → GeneratePayslipAction fires
  → PayslipService::generate(GajiBulanan):
      Load pegawai info (nama, NIP, jabatan)
      Load gaji_pokok, tunker (from GajiBulanan snapshot)
      Load all Potong for periode grouped by Tagihan/Penagih
      Render blade view: resources/views/payslip/template.blade.php
      DomPDF::loadHTML(...)->stream("payslip-{NIP}-{periode}.pdf")
  → Browser triggers PDF download
```

---

## Suggested Build Order

Dependencies flow downward. Build top-to-bottom.

### Phase 1 — Database Foundation
1. Migration: add `status`, `cash_amount`, `cash_method`, `cash_paid_at`, `notes` to `potongs`
2. Migration: add `state`, `carried_from_id`, `sisa_tagihan` to `tagihans`
3. Migration: create `gaji_bulanans` table
4. Migration: create `rekonsiliasi_penagih` table (can defer to Phase 3)
5. Add `PotongStatus` enum
6. Update model `$casts`, `$fillable`, relationships

### Phase 2 — Domain Logic
7. `PayrollComputationService` (compute + finalize)
8. `CarryForwardService`
9. Model methods: `Potong::markCashPayment()`, `Tagihan::updateSisa()`
10. Unit tests for computation logic

### Phase 3 — Core Filament UI
11. `PotongRelationManager` — status badge, cash payment action
12. `GajiBulananResource` — payroll table, finalize action
13. `FinalizePayrollAction` with confirmation and guard
14. `CarryForwardAction`

### Phase 4 — Reconciliation
15. `RekonsiliasiService`
16. `RekonsiliasiResource` — summary view + per-employee breakdown
17. Reconciliation PDF export

### Phase 5 — Payslip
18. `PayslipService` + Blade template
19. `GeneratePayslipAction` wired to GajiBulananResource

### Phase 6 — Employee Self-Service
20. Register `/app` Filament panel with scoped auth
21. `MyGajiBulananResource` (read-only, scoped)
22. `MyTagihanResource` (read-only, scoped)
23. Payslip download in employee panel
