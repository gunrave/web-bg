<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PotongResource\Pages;
use App\Filament\Resources\PotongResource\RelationManagers;
use App\Models\Potong;
use App\Models\Tagihan;
use Filament\Forms;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PotongResource extends Resource
{
    protected static ?string $model = Potong::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Select::make('tagihan_id')
                    ->label('Tagihan')
                    ->relationship(name: 'tagihan', titleAttribute: 'id')
                    ->getOptionLabelFromRecordUsing(fn (Model $record) => "{$record->periode->periode} || {$record->periode->penagih->nama} || {$record->pegawai->nama} || {$record->jumlah}")
                ,
                Select::make('isGapok')
                    ->label('Pilih Pembebanan')
                    ->options([
                        '0' => 'Tunjangan Kinerja',
                        '1' => 'Gaji Pokok',
                    ])
                    ->required(),
                TextInput::make('nominal')
                    ->required()
                    ->numeric(),
                Toggle::make('sukses'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tagihan.periode.periode')
                    // ->searchable(isIndividual: true)
                    ->date('F Y')
                    ->sortable(),
                TextColumn::make('tagihan.periode.penagih.nama')
                    // ->searchable(isIndividual: true)
                    ->sortable(),
                Tables\Columns\TextColumn::make('tagihan.pegawai.nama')
                    // ->searchable(isIndividual: true)
                    ->sortable(),
                Tables\Columns\TextColumn::make('tagihan.jumlah')
                    ->money('IDR', locale: 'id')
                    ->color('success')
                    ->sortable(),
                TextColumn::make('isGapok')
                    ->label('Potongan')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state === 1 ? 'Gaji Pokok' : 'Tunjangan Kinerja')
                    ,
                TextColumn::make('nominal')
                    ->money('IDR', locale: 'id')
                    ->color('danger'),

                IconColumn::make('sukses')->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([

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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManagePotongs::route('/'),
        ];
    }
}
