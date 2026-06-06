<?php

namespace App\Filament\Resources\TagihanResource\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PotonganRelationManager extends RelationManager
{
    protected static string $relationship = 'potongan';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Select::make('isGapok')
                    ->label('Pilih Pembebanan')
                    ->options([
                        '1' => 'Gaji Pokok',
                        '0' => 'Tunjangan Kinerja',
                    ])
                    ->required(),
                TextInput::make('nominal')
                    ->required()
                    ->numeric(),
                Toggle::make('sukses'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('tagihan_id')
            ->columns([
                TextColumn::make('isGapok')
                    ->label('Potongan')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state === 1 ? 'Gaji Pokok' : 'Tunjangan Kinerja')
                    ,
                TextColumn::make('nominal')
                    ->money('IDR', locale: 'id')
                    ->color(fn ($record) => $record->sukses === 1 ? 'success' : 'danger'),
                ToggleColumn::make('sukses'),

            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
