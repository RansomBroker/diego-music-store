<?php

namespace App\Filament\Resources\CustomerLabels\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;

class CustomerLabelForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Label Pelanggan')
                    ->columnSpan('full')
                    ->schema([
                        TextInput::make('key')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->label('Key / Code')
                            ->placeholder('e.g., perorangan, instansi')
                            ->disabled(fn (string $context): bool => $context === 'edit'),

                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->label('Nama Label')
                            ->placeholder('e.g., Perorangan, Instansi / Lembaga'),
                    ])
            ]);
    }
}
