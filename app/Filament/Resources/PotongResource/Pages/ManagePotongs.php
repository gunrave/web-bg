<?php

namespace App\Filament\Resources\PotongResource\Pages;

use App\Filament\Resources\PotongResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManagePotongs extends ManageRecords
{
    protected static string $resource = PotongResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
