<?php

namespace App\Filament\Resources\Units\Schemas;

use App\Models\Unit;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
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

                        Toggle::make('is_active')
                            ->default(true)
                            ->label('Status Aktif')
                            ->inline(false),
                    ])
            ]);
    }
}
