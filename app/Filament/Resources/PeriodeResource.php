<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PeriodeResource\Pages;
use App\Filament\Resources\PeriodeResource\RelationManagers;
use App\Filament\Resources\PeriodeResource\RelationManagers\TagihanRelationManager;
use App\Filament\Resources\PeriodeTagihanResource\RelationManagers\HasManyThroughRelationManager;
use App\Filament\Resources\TagihanResource\RelationManagers\PotonganRelationManager;
use App\Models\Periode;
use App\Models\periode_tagihan;
use App\Models\Tagihan;
use Closure;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\Summarizers\Count;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\Summarizers\Summarizer;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PeriodeResource extends Resource
{
    protected static ?string $model = periode_tagihan::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                DatePicker::make('periode')
                    ->native(false)
                    ->displayFormat('F Y')
                    ->locale('id')
                    ->required(),
                Select::make('penagih_id')
                    ->relationship(name: 'penagih', titleAttribute: 'nama')
                    ->createOptionForm([
                        TextInput::make('nama')
                            ->required(),
                        Toggle::make('isActive')
                            ->default('1')
                            ->required(),
                        Toggle::make('is_rules')
                            ->label('Rules?')
                            // ->reactive()
                            // ->requiredWith('rules')
                            // ->afterStateUpdated(
                            //     fn ($state, callable $set) => $state ? $set('rules', null) : $set('rules', 'hidden')
                            // )
                            ->live()
                            ,
                        Repeater::make('rules')
                            // ->requiredWith('is_rules')
                            ->hidden(fn (Get $get): bool => ! $get('is_rules'))
                            ->schema([
                                TextInput::make('namaKolom'),
                                Select::make('operator')
                                    ->options([
                                        'sama' => '=',
                                        'beda' => '<>',
                                    ]),
                                TextInput::make('nilai'),
                                TextInput::make('nominal'),

                            ])->columns(4)
                        ,
                    ])
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('periode')
                    ->date('F Y'),
                TextColumn::make('penagih.nama'),
                // TextColumn::make('tagihan_count')
                //     ->counts('tagihan')
                // ,
                TextColumn::make('tagihan_sum_jumlah')
                    ->label('Total Tagihan')
                    ->sum('tagihan', 'jumlah')
                    ->money('IDR', locale: 'id')
                    ->color('success'),
                TextColumn::make('sukses')
                    ->sum(['potongan as sukses' => fn(Builder $query) => $query->where('sukses', true),], 'nominal')
                    ->money('IDR', locale: 'id'),
                // TextColumn::make('gapot')
                //     ->sum(['potongan as gapot' => fn(Builder $query) => $query->where('sukses', false),], 'nominal')
                //     ->money('IDR', locale: 'id'),
                TextColumn::make('outstanding')
                    ->state(function (periode_tagihan $record): float {
                        return $record->tagihan_sum_jumlah - ($record->sukses);
                    })
                    ->money('IDR', locale: 'id'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
    public static function getRelations(): array
    {
        return [
            TagihanRelationManager::class,
            // PotonganRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPeriodes::route('/'),
            'create' => Pages\CreatePeriode::route('/create'),
            'edit' => Pages\EditPeriode::route('/{record}/edit'),
        ];
    }
}
