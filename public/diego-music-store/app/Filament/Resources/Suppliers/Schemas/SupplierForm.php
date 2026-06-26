<?php

namespace App\Filament\Resources\Suppliers\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;

class SupplierForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Section::make('Informasi Utama')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->label('Nama Supplier / Vendor'),

                        TextInput::make('contact_person')
                            ->maxLength(255)
                            ->label('Contact Person'),

                        TextInput::make('phone')
                            ->tel()
                            ->maxLength(255)
                            ->label('Nomor Telepon'),

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

                Section::make('Keuangan & Rekening Bank')
                    ->schema([
                        TextInput::make('bank_name')
                            ->maxLength(255)
                            ->label('Nama Bank'),

                        TextInput::make('bank_account_number')
                            ->maxLength(255)
                            ->label('Nomor Rekening'),

                        TextInput::make('bank_account_name')
                            ->maxLength(255)
                            ->label('Atas Nama Rekening'),

                        TextInput::make('outstanding_debt')
                            ->numeric()
                            ->prefix('Rp')
                            ->default(0.00)
                            ->label('Saldo Hutang Berjalan'),
                    ])
                    ->columnSpan(1),
            ]);
    }
}
