<?php

namespace App\Filament\Resources\GajiResource\Pages;

use App\Filament\Resources\GajiResource;
use App\Imports\ImportGajis;
use App\Models\Gaji;
use Doctrine\DBAL\Schema\View;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\View\View as ViewView;
use Illuminate\View\View as IlluminateViewView;
use Maatwebsite\Excel\Facades\Excel;

class ListGajis extends ListRecords
{
    protected static string $resource = GajiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getHeader(): ?IlluminateViewView
    {
        $data = Actions\CreateAction::make();
        $crumbs = ['/admin/gajis' => 'List Gaji'];
        return view('filament.custom.upload-file', compact('data', 'crumbs'));
    }

    public $file="";

    public function save(){
        if($this->file != ''){
            Excel::import(new ImportGajis, $this->file);
        }
        // Gaji::create([
        //     'bulan' => '01',
        //     'tahun' => '2024',
        //     'pegawai_id' => '5',
        //     'nominal' => '5000000',

        // ]);
    }
}
