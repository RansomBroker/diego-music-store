<?php

namespace App\Filament\Resources\Units\Schemas;

use App\Models\Unit;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;

class UnitForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Informasi Satuan Produk')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->label('Nama Satuan (misal: Pieces, Set, Unit)'),

                        TextInput::make('code')
                            ->required()
                            ->maxLength(255)
                            ->unique(Unit::class, 'code', ignoreRecord: true)
                            ->label('Kode Satuan (misal: pcs, set, unit)'),

                        Select::make('base_unit_id')
                            ->label('Satuan Dasar Acuan (Base Unit)')
                            ->placeholder('Pilih jika ini Satuan Konversi/Besar (misal: Pcs)')
                            ->options(fn ($record) => Unit::query()
                                ->whereNull('base_unit_id')
                                ->where('is_active', true)
                                ->when($record, fn ($q) => $q->where('id', '!=', $record->id))
                                ->pluck('name', 'id')
                                ->toArray()
                            )
                            ->nullable()
                            ->reactive()
                            ->afterStateUpdated(fn ($state, $set) => blank($state) ? $set('conversion_factor', 1) : null),

                        TextInput::make('conversion_factor')
                            ->label('Faktor Konversi (Jumlah Satuan Dasar)')
                            ->helperText('Misal: jika diisi 12 untuk Karton dengan acuan Pcs, artinya 1 Karton = 12 Pcs')
                            ->numeric()
                            ->default(1)
                            ->minValue(1)
                            ->required(fn ($get) => filled($get('base_unit_id')))
                            ->disabled(fn ($get) => blank($get('base_unit_id')))
                            ->dehydrated(),

                        Toggle::make('is_active')
                            ->default(true)
                            ->label('Status Aktif')
                            ->inline(false),
                    ])
            ]);
    }
}
