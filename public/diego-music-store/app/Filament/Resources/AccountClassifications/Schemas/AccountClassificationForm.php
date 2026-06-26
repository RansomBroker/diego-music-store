<?php

namespace App\Filament\Resources\AccountClassifications\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;

class AccountClassificationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Klasifikasi Akun')
                    ->columnSpan('full')
                    ->schema([
                        TextInput::make('key')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->label('Key / Code')
                            ->placeholder('e.g., asset, liability')
                            ->disabled(fn (string $context): bool => $context === 'edit'),

                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->label('Nama Klasifikasi')
                            ->placeholder('e.g., Aset / Harta, Kewajiban / Hutang'),
                    ])
            ]);
    }
}
