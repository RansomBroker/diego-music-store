<?php

namespace App\Filament\Resources\Customers\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;

class CustomerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(2)
                    ->schema([
                        Section::make('Informasi Kontak')
                            ->schema([
                                TextInput::make('name')
                                    ->required()
                                    ->maxLength(255)
                                    ->label('Nama Pelanggan'),

                                TextInput::make('phone')
                                    ->tel()
                                    ->unique(ignoreRecord: true)
                                    ->label('Nomor Telepon')
                                    ->placeholder('e.g., 08123456789'),

                                TextInput::make('email')
                                    ->email()
                                    ->maxLength(255)
                                    ->label('Email'),

                                Textarea::make('address')
                                    ->maxLength(500)
                                    ->rows(3)
                                    ->label('Alamat'),
                            ])
                            ->columnSpan(1),

                        Section::make('Loyalty & Deposit')
                            ->schema([
                                Toggle::make('is_loyalty_member')
                                    ->label('Loyalty Member')
                                    ->default(false),

                                TextInput::make('loyalty_points')
                                    ->numeric()
                                    ->default(0)
                                    ->label('Poin Belanja'),

                                TextInput::make('deposit_balance')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->default(0.00)
                                    ->label('Saldo Deposit'),
                            ])
                            ->columnSpan(1),
                    ])
            ]);
    }
}
