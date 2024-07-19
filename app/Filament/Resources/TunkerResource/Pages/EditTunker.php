<?php

namespace App\Filament\Resources\TunkerResource\Pages;

use App\Filament\Resources\TunkerResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTunker extends EditRecord
{
    protected static string $resource = TunkerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
