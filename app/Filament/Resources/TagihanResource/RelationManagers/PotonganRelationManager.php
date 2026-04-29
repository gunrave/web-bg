<?php

namespace App\Filament\Resources\TagihanResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use phpDocumentor\Reflection\Types\Self_;

class PotonganRelationManager extends RelationManager
{
    protected static string $relationship = 'potongan';

    public function form(Form $form): Form
    {
        return $form
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
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
