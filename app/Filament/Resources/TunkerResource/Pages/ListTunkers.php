<?php

namespace App\Filament\Resources\TunkerResource\Pages;

use App\Filament\Resources\TunkerResource;
use App\Imports\ImportTunkers;
use Filament\Actions;
use Filament\Actions\Imports\Models\Import;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Facades\Excel;

class ListTunkers extends ListRecords
{
    protected static string $resource = TunkerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getHeader(): ?View
    {
        $data = Actions\CreateAction::make();
        return view('filament.custom.upload-file', compact('data'));
    }

    public $file = '';

    public function save()
    {
        if($this->file != ''){
            Excel::import(new ImportTunkers, $this->file);
        }
    }
}
