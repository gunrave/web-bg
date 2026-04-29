# Stack Research — Payroll Management Additions

## PDF Generation

### Recommended: `barryvdh/laravel-dompdf` ^3.0

**Why:** Zero external binary dependencies (unlike Browsershot/Puppeteer), pure PHP renderer via DOMPDF. Works in shared hosting and standard Herd environments. v3.x is the Laravel 11-compatible release. Payslips are structurally simple (tables, headers, totals) — DOMPDF handles this without quality loss. The entire team workflow is web-only with no CI/CD pipeline where headless Chrome would be manageable.

**Installation:**
```bash
composer require barryvdh/laravel-dompdf:^3.0
```

**Filament integration pattern — Action on Tagihan/Gaji resource:**
```php
use Barryvdh\DomPDF\Facade\Pdf;

Action::make('cetak_payslip')
    ->label('Cetak Slip Gaji')
    ->icon('heroicon-o-document-text')
    ->action(function (Gaji $record) {
        $pdf = Pdf::loadView('payslips.slip-gaji', [
            'gaji'   => $record,
            'potongan' => $record->potong()->with('penagih')->get(),
        ])
        ->setPaper('a4', 'portrait');

        return response()->streamDownload(
            fn () => print($pdf->output()),
            "slip-gaji-{$record->pegawai->nama}-{$record->periode}.pdf"
        );
    })
```

Blade view at `resources/views/payslips/slip-gaji.blade.php` uses inline CSS only (no Tailwind, no external fonts at runtime — DOMPDF does not execute JavaScript or fetch remote assets by default).

**Alternatives considered:**

| Package | Reason to reject |
|---|---|
| `spatie/laravel-pdf` (Browsershot) | Requires Chromium binary + `spatie/browsershot` + Node/Puppeteer. Significant ops overhead in Herd; no benefit for simple payslip layouts. |
| `mpdf/mpdf` via `niklasravnsborg/laravel-pdf` | Heavier memory footprint, less maintained Laravel wrapper, UTF-8/Arabic font setup more complex than DOMPDF for Indonesian locale. |
| `tecnickcom/tcpdf` | Extremely verbose API, no Blade/HTML template support — requires constructing PDF primitively. |

**Confidence: High**

---

## Excel Export

### Already installed: `maatwebsite/excel` ^3.1 — extend with Export classes

No new package needed. The package handles both import and export. The existing installation is sufficient.

**Export usage pattern — two export classes needed:**

**1. Payroll summary export (all employees, one period):**
```php
// app/Exports/RekapGajiExport.php
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class RekapGajiExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize
{
    public function __construct(protected string $periode) {}

    public function collection()
    {
        return Gaji::with(['pegawai', 'potong.penagih'])
            ->where('periode', $this->periode)
            ->get()
            ->map(fn ($g) => [
                'NIP'           => $g->pegawai->nip,
                'Nama'          => $g->pegawai->nama,
                'Gaji Pokok'    => $g->gaji_pokok,
                'Tunker'        => $g->tunker_amount,
                'Total Potong'  => $g->potong->sum('jumlah'),
                'Gaji Bersih'   => $g->gaji_bersih,
            ]);
    }
}
```

**2. Per-provider reconciliation export:**
```php
// app/Exports/RekonsiliasiPenagihExport.php
// One sheet per Penagih using WithMultipleSheets
// Each sheet: employee NIP, Tagihan amount, Potong amount, status (lunas/gagal/cash)
```

**Filament trigger:**
```php
Action::make('export_rekapitulasi')
    ->action(fn ($livewire) => Excel::download(
        new RekapGajiExport($livewire->tableFilters['periode']['value']),
        'rekapitulasi-' . now()->format('Y-m') . '.xlsx'
    ))
```

**Confidence: High** — maatwebsite/excel export is well-documented; no new dependency required.

---

## Deduction Status Tracking

### Database approach — add status column to `potong` table

**Migration strategy:**
```php
Schema::table('potong', function (Blueprint $table) {
    $table->enum('status', ['pending', 'lunas', 'gagal', 'cash_penuh', 'cash_sebagian'])
          ->default('pending')
          ->after('jumlah');
    $table->decimal('jumlah_cash', 15, 2)->nullable()->after('status'); // partial cash amount
    $table->text('keterangan_gagal')->nullable()->after('jumlah_cash'); // failure reason
    $table->timestamp('dibayar_pada')->nullable()->after('keterangan_gagal');
});
```

**Status flow:**
```
pending → lunas         (deduction successfully applied from salary)
pending → gagal         (salary insufficient or rejected)
gagal   → cash_penuh    (employee paid full amount in cash)
gagal   → cash_sebagian (employee paid partial amount in cash)
cash_sebagian → (carry-forward remainder — see below)
```

**Filament UI pattern — status badge + override action on Potong resource:**
```php
// In PotongResource table columns:
Tables\Columns\BadgeColumn::make('status')
    ->colors([
        'success' => 'lunas',
        'danger'  => 'gagal',
        'warning' => ['cash_penuh', 'cash_sebagian'],
        'gray'    => 'pending',
    ])

// Override action (shown only when status = gagal):
Tables\Actions\Action::make('tandai_cash')
    ->label('Tandai Bayar Cash')
    ->visible(fn ($record) => $record->status === 'gagal')
    ->form([
        Forms\Components\Select::make('tipe')
            ->options(['cash_penuh' => 'Lunas Cash', 'cash_sebagian' => 'Sebagian Cash'])
            ->required(),
        Forms\Components\TextInput::make('jumlah_cash')
            ->numeric()
            ->required()
            ->visible(fn ($get) => $get('tipe') === 'cash_sebagian'),
    ])
    ->action(function ($record, array $data) {
        $record->update([
            'status'       => $data['tipe'],
            'jumlah_cash'  => $data['jumlah_cash'] ?? $record->jumlah,
            'dibayar_pada' => now(),
        ]);
    })
```

**No additional package needed.** Filament's built-in action forms + badge columns handle this entirely.

**Confidence: High**

---

## Carry-Forward Logic

### Recommended approach: Service class + event/observer pattern, no external package

**Pattern:** When a `Potong` is marked `gagal` or `cash_sebagian` at period close, a service creates a new `Tagihan` record in the next `PeriodeTagihan` for the remaining balance.

```php
// app/Services/CarryForwardService.php
class CarryForwardService
{
    public function carryForward(PeriodeTagihan $closedPeriod, PeriodeTagihan $nextPeriod): void
    {
        // Find all unresolved deductions in the closed period
        $unpaid = Potong::where('periode_tagihan_id', $closedPeriod->id)
            ->whereIn('status', ['gagal', 'cash_sebagian'])
            ->get();

        foreach ($unpaid as $potong) {
            $sisa = $potong->jumlah - ($potong->jumlah_cash ?? 0);

            if ($sisa > 0) {
                // Create a new Tagihan in the next period for the remainder
                Tagihan::create([
                    'pegawai_id'         => $potong->tagihan->pegawai_id,
                    'penagih_id'         => $potong->tagihan->penagih_id,
                    'periode_tagihan_id' => $nextPeriod->id,
                    'jumlah'             => $sisa,
                    'is_carry_forward'   => true,
                    'potong_asal_id'     => $potong->id, // traceability
                ]);
            }
        }
    }
}
```

**Supporting migration additions to `tagihan`:**
```php
$table->boolean('is_carry_forward')->default(false);
$table->foreignId('potong_asal_id')->nullable()->constrained('potong')->nullOnDelete();
```

**Trigger:** A Filament `Action` on the `PeriodeTagihan` resource — "Tutup Periode & Carry Forward" — calls `CarryForwardService::carryForward()`. No queue/job needed for typical payroll sizes (<500 employees).

**Why no package:** Laravel's Eloquent + a simple service class is sufficient. Packages like `cybercog/laravel-money` or accounting libraries add overhead without solving the domain-specific carry-forward logic, which depends entirely on the existing `Tagihan`/`Potong` relationship structure.

**Confidence: High**

---

## Summary Table

| Feature | Package / Approach | New Install? | Confidence |
|---|---|---|---|
| PDF payslip | `barryvdh/laravel-dompdf:^3.0` | YES | High |
| Excel export | `maatwebsite/excel` (already installed) | NO | High |
| Deduction status | Enum column + Filament BadgeColumn/Action | NO | High |
| Carry-forward | `CarryForwardService` + migration columns | NO | High |

**Only one new Composer package is required for the entire feature set.**

```bash
composer require barryvdh/laravel-dompdf:^3.0
php artisan vendor:publish --provider="Barryvdh\DomPDF\ServiceProvider"
```

---

## What NOT to Use

| Package | Reason |
|---|---|
| `spatie/laravel-pdf` | Needs Chromium — unnecessary for payslip complexity level |
| `phpoffice/phpspreadsheet` directly | Already abstracted by maatwebsite/excel; direct usage is verbose |
| `laravel/cashier` | Stripe/Paddle billing — completely wrong domain |
| `akaunting/laravel-money` | Adds value-object overhead; `decimal` columns + PHP arithmetic is sufficient here |
| Any queue-based PDF library | Payslips are generated on-demand per employee; synchronous is correct |
