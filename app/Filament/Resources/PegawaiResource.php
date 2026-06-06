<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PegawaiResource\Pages;
use App\Filament\Resources\PegawaiResource\RelationManagers;
use App\Models\Pegawai;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PegawaiResource extends Resource
{
    protected static ?string $model = Pegawai::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\TextInput::make('nik')
                    ->label('NIK')
                    // ->unique()
                    ->required(),
                Forms\Components\TextInput::make('nama')
                    ->required(),
                TextInput::make('norek')
                    ->label('Nomor Rekening'),
                Select::make('golpang')
                    ->label('Golongan Pangkat')
                    ->options([
                       '2C' => 'II/c Pengatur',
                        '2D' => 'II/d Pengatur Tingkat I',
                        '3A' => 'III/a Penata Muda',
                        '3B' => 'III/b Penata Muda Tingkat I',
                        '3C' => 'III/c Penata',
                        '3D' => 'III/d Penata Tingkat I',
                        '4A' => 'IV/a Pembina',
                        '4B' => 'IV/b Pembina Tingkat I',
                        '4C' => 'IV/c Pembina Muda',
                        '4D' => 'IV/d Pembina Madya',
                        '4E' => 'IV/e Pembina Utama'
                    ]),
                Forms\Components\Toggle::make('isActive')
                    ->default('1')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nik')
                    ->searchable(isIndividual: true),
                Tables\Columns\TextColumn::make('nama')
                    ->searchable(isIndividual: true),
                TextColumn::make('norek')
                    ->label('Nomor Rekening'),
                TextColumn::make('golpang')
                    ->label('Golongan Pangkat')
                    ->formatStateUsing(fn (string $state): string => match($state){
                        '2B' => 'II/b Pengatur Muda Tingkat I',
                        '2C' => 'II/c Pengatur',
                        '2D' => 'II/d Pengatur Tingkat I',
                        '3A' => 'III/a Penata Muda',
                        '3B' => 'III/b Penata Muda Tingkat I',
                        '3C' => 'III/c Penata',
                        '3D' => 'III/d Penata Tingkat I',
                        '4A' => 'IV/a Pembina',
                        '4B' => 'IV/b Pembina Tingkat I',
                        '4C' => 'IV/c Pembina Muda',
                        '4D' => 'IV/d Pembina Madya',
                        '4E' => 'IV/e Pembina Utama'
                    })
                    ,
                Tables\Columns\IconColumn::make('isActive')
                    ->boolean(),
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
            ]);
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
            'index' => Pages\ListPegawais::route('/'),
            'create' => Pages\CreatePegawai::route('/create'),
            'edit' => Pages\EditPegawai::route('/{record}/edit'),
        ];
    }
}
