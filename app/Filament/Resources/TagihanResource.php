<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TagihanResource\Pages;
use App\Filament\Resources\TagihanResource\RelationManagers;
use App\Models\Pegawai;
use App\Models\periode_tagihan;
use App\Models\Potong;
use App\Models\Tagihan;
use Filament\Actions\CreateAction;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Wizard\Step;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TagihanResource extends Resource
{
    protected static ?string $model = Tagihan::class;

    protected static ?string $navigationIcon = 'heroicon-s-list-bullet';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('periode_tagihan')
                    ->label('Periode Tagihan')
                    ->relationship(
                        name: 'periode',
                        titleAttribute:'periode'
                    )
                    ->getOptionLabelFromRecordUsing(fn (Model $record) => "{$record->periode} {$record->penagih->nama}")
                    ->createOptionForm([
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
                            ])
                            ->required(),
                    ])
                    ->required(),
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

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('periode.periode')
                    ->searchable(isIndividual: true)
                    ->date('F Y')
                    ->sortable()
                    ,
                TextColumn::make('periode.penagih.nama')
                    ->searchable(isIndividual: true)
                    ->sortable(),

                Tables\Columns\TextColumn::make('pegawai.nama')
                    ->searchable(isIndividual: true)
                    ->sortable(),
                Tables\Columns\TextColumn::make('jumlah')
                    ->money('IDR', locale: 'id')
                    ->color('success')
                    ->sortable(),
                // Tables\Columns\TextColumn::make('potongan.isGapok')
                //     // ->label('potgaji')
                //     ->money('IDR', locale: 'id')
                //     ->color('success')
                //     ->sortable(),
                // // SelectColumn::make('potongan.isGapok')
                // //     ->options([
                // //         '0' => 'Gaji Pokok',
                // //         '1' => 'Tunjangan Kinerja',
                // //     ]),
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
                //
            ])
            ->actions([
                ActionGroup::make([
                    // Action::make('potongan')
                    //     ->model(Potong::class)
                    //     ->form([

                    //         Placeholder::make('tagihan')
                    //             ->label('Total Tagihan')
                    //             ->content(fn (Tagihan $record): ?string => $record->jumlah),
                    //         Select::make('isGapok')
                    //             ->label('Pilih Pembebanan')
                    //             ->options([
                    //                 '0' => 'Tunjangan Kinerja',
                    //                 '1' => 'Gaji Pokok',
                    //             ])
                    //             ->required(),
                    //         TextInput::make('nominal')
                    //             ->required()
                    //             ->numeric(),
                    //     ])
                    //     ->icon('heroicon-o-banknotes')
                    //     ,
                    CreateAction::make('potongan')
                        ->model(Potong::class)
                        ->form([

                            Placeholder::make('tagihan')
                                ->label('Total Tagihan')
                                ->content(fn (Tagihan $record): ?string => $record->jumlah),
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
                        ])
                        // // ->using(function (array $data, string $model): Model{
                        // //     return $model::create($data);
                        // })
                        ,
                    Tables\Actions\EditAction::make(),
                ]),

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
            'index' => Pages\ListTagihans::route('/'),
            'create' => Pages\CreateTagihan::route('/create'),
            'edit' => Pages\EditTagihan::route('/{record}/edit'),
        ];
    }
}
