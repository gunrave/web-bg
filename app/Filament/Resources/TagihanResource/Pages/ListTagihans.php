<?php

namespace App\Filament\Resources\TagihanResource\Pages;

use App\Filament\Resources\TagihanResource;
use App\Models\periode_tagihan;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Wizard\Step;
use Filament\Resources\Pages\ListRecords;

class ListTagihans extends ListRecords
{
    protected static string $resource = TagihanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),

            // Action::make('create')
            //     ->steps([
            //         Step::make('Kreditur')
            //             ->description('Input Kreditur')
            //             ->schema([
            //                 Select::make('periode_tagihan')
            //                     ->relationship(name: 'periode', titleAttribute: 'periode')
            //                     ->createOptionForm([
            //                         DatePicker::make('periode')
            //                             ->native(false)
            //                             ->displayFormat('F Y')
            //                             ->locale('id')
            //                             ->required(),
            //                         Select::make('penagih')
            //                             ->relationship('periode_tagihan', 'nama')
            //                             ->createOptionForm([
            //                                 TextInput::make('nama')
            //                                     ->required(),
            //                             ])
            //                     ])
            //                     ->required(),
            //             ]),
            //         Step::make('debitur')
            //             ->description('Input debitur')
            //                 ->schema([
            //                     Repeater::make('Input Pegawai')
            //                         ->schema([
            //                             Select::make('pegawai_id')
            //                                 ->relationship(name: 'pegawai', titleAttribute: 'nama')
            //                                 ->preload()
            //                                 ->searchable()
            //                                 ->required(),
            //                             TextInput::make('jumlah')
            //                                 ->required()
            //                                 ->numeric(),
            //                         ])->columns(2),

            //                 ])

            //     ])
        ];
    }
}
