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
use Filament\Forms\Form;
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

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Placeholder::make('tagihan')
                //     ->label('Total Tagihan')
                //     ->content(fn (Tagihan $record): ?string => $record->jumlah),
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
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // Split::make([
                //     Stack::make([
                //         TextColumn::make('tagihan.periode.periode')
                //             // ->searchable(isIndividual: true)
                //             ->date('F Y')
                //             ->sortable(),
                //         TextColumn::make('tagihan.periode.penagih.nama')
                //             // ->searchable(isIndividual: true)
                //             ->sortable(),
                //         Tables\Columns\TextColumn::make('tagihan.pegawai.nama')
                //             ->searchable(isIndividual: true)
                //             ->sortable(),
                //         Tables\Columns\TextColumn::make('tagihan.jumlah')
                //             ->money('IDR', locale: 'id')
                //             ->color('success')
                //             ->sortable(),
                //     ]),
                //     IconColumn::make('isGapok')
                //         ->label('potongan')
                //         ->boolean(),
                //     TextColumn::make('nominal')
                //         ->money('IDR', locale: 'id')
                //         ->color('danger'),
                //     Tables\Columns\TextColumn::make('created_at')
                //         ->dateTime()
                //         ->sortable()
                //         ->toggleable(isToggledHiddenByDefault: true),
                //     Tables\Columns\TextColumn::make('updated_at')
                //         ->dateTime()
                //         ->sortable()
                //         ->toggleable(isToggledHiddenByDefault: true),
                //     Tables\Columns\TextColumn::make('deleted_at')
                //         ->dateTime()
                //         ->sortable()
                //         ->toggleable(isToggledHiddenByDefault: true),
                // ])->from('md'),
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
                IconColumn::make('isGapok')
                    ->label('Potong Gaji')
                    ->boolean(),
                // TextColumn::make('isGapok')
                //     ->label('Potongan')
                //     ,
                TextColumn::make('nominal')
                    ->money('IDR', locale: 'id')
                    ->color('danger'),
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
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
