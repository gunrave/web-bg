<?php

namespace App\Filament\Imports;

use App\Models\Gaji;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class GajiImporter extends Importer
{
    protected static ?string $model = Gaji::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('bulan')
                ->requiredMapping()
                ->rules(['required', 'date']),
            ImportColumn::make('tahun')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'integer']),
            ImportColumn::make('pegawai')
                ->requiredMapping()
                ->relationship()
                ->rules(['required']),
            ImportColumn::make('nominal')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'integer']),
        ];
    }

    public function resolveRecord(): ?Gaji
    {
        // return Gaji::firstOrNew([
        //     // Update existing records, matching them by `$this->data['column_name']`
        //     'email' => $this->data['email'],
        // ]);

        return new Gaji();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your gaji import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
