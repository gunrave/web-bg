<?php

namespace App\Filament\Resources\PeriodeResource\RelationManagers;

use App\Filament\Resources\PotongResource;
use App\Filament\Resources\TagihanResource;
use App\Models\Potong;
use App\Models\Tagihan;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TagihanRelationManager extends RelationManager
{
    protected static string $relationship = 'tagihan';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Select::make('pegawai_id')
                    ->label('Nama Pegawai')
                    ->relationship(name: 'pegawai', titleAttribute: 'nama')
                    ->required()
                    ->preload()
                    ->searchable(),
                TextInput::make('jumlah')
                    ->label('Jumlah Tagihan')
                    ->numeric()
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('periode_tagihan')
            ->columns([
                TextColumn::make('pegawai.nama')
                    ->searchable(isIndividual: true)
                ,
                TextColumn::make('jumlah')
                    ->money('IDR', locale: 'id')
                    ->color('success')
                    ->sortable()
                    ->summarize(Sum::make()),
                TextColumn::make('sukses')
                    ->sum(['potongan as sukses' => fn(Builder $query) => $query->where('sukses', true),], 'nominal')
                    ->money('IDR', locale: 'id')
                    ->summarize(Sum::make()),
                // TextColumn::make('gapot')
                //     ->label('Gagal Potong')
                //     ->sum(['potongan as gapot' => fn(Builder $query) => $query->where('sukses', false),], 'nominal')
                //     ->money('IDR', locale: 'id'),
                TextColumn::make('tunker')
                    ->label('Tunjangan Kinerja')
                    ->sum(['potongan as tunker' => fn(Builder $query) => $query->where('isGapok', false),], 'nominal')
                    ->money('IDR', locale: 'id')
                    ->summarize(Sum::make())
                    ,
                TextColumn::make('gapok')
                    ->label('Gaji Pokok')
                    ->sum(['potongan as gapok' => fn(Builder $query) => $query->where('isGapok', true),], 'nominal')
                    ->money('IDR', locale: 'id')
                    ->summarize(Sum::make())
                    ,
                TextColumn::make('outstanding')
                    ->state(function (Tagihan $record): float {
                        return $record->jumlah - ($record->sukses);
                    })
                    ->money('IDR', locale: 'id')
                    ,
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Action::make('Potong')
                    ->link()
                    ->url(fn (Model $record): string => TagihanResource::getUrl('edit', ['record' => $record ]))
                    // ->fillForm(fn (Tagihan $record):array => [
                    //     'tagihan_id' => $record->id,
                    // ])
                    // ->accessSelectedRecords()
                    // ->form([
                    //     Select::make('tagihan_id')
                    //         // ->label('Tagihan')
                    //         ->relationship(name: 'tagihan', titleAttribute: 'id')
                    //         ->getOptionLabelFromRecordUsing(fn (Tagihan $record) => "{$record->periode->periode} || {$record->periode->penagih->nama} || {$record->pegawai->nama} || {$record->jumlah}")
                    //     ,
                    //     Select::make('isGapok')
                    //         ->label('Pilih Pembebanan')
                    //         ->options([
                    //             '0' => 'Tunjangan Kinerja',
                    //             '1' => 'Gaji Pokok',
                    //         ])
                    //         ->required(),
                    //     TextInput::make('nominal')
                    //         ->required()
                    //         ->numeric(),
                    //     Toggle::make('sukses'),
                    // ])
                    // ->action(function (array $data, Potong $record): void {
                    //     $record->tagihan()->associate($data['']);
                    // })
                    ,
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
