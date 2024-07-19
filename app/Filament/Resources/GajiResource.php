<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GajiResource\Pages;
use App\Filament\Resources\GajiResource\RelationManagers;
use App\Models\Gaji;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class GajiResource extends Resource
{
    protected static ?string $model = Gaji::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
        ->schema([
            Tabs::make('Tabs')
                ->tabs([
                    Tab::make('Satuan')
                        ->schema([
                            Forms\Components\DatePicker::make('bulan')
                                ->native(false)
                                ->displayFormat('m'),
                                Forms\Components\DatePicker::make('tahun')
                                ->native(false)
                                ->displayFormat('Y'),
                            Forms\Components\Select::make('pegawai_id')
                                ->searchable()
                                ->relationship('pegawai', 'nama'),
                            Forms\Components\TextInput::make('nominal')
                                ->prefix('Rp')
                                ->numeric(),
                        ]),
                    Tab::make('Borongan')
                        ->schema([
                            FileUpload::make('lampiran')
                            ->acceptedFileTypes(['application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']),
                        ]),
                ])
            ]);

    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('bulan')
                    ->label('Periode')
                    ->formatStateUsing(function ($state, Gaji $gaji){
                        return date('F', mktime(0, 0, 0, $gaji->bulan, 10)) .' '. $gaji->tahun;
                    })
                    ->sortable(),
                // Tables\Columns\TextColumn::make('tahun')
                //     ->sortable(),
                Tables\Columns\TextColumn::make('pegawai.nama')
                    ->sortable(),
                Tables\Columns\TextColumn::make('nominal')
                    ->numeric()
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
            ])
            // ->headerActions([
            //     Tables\Actions\CreateAction::make(),
            // ])
            ;
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGajis::route('/'),
            'create' => Pages\CreateGaji::route('/create'),
            'edit' => Pages\EditGaji::route('/{record}/edit'),
        ];
    }
}
