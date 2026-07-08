<?php

namespace App\Filament\Resources\Customers\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use App\Filament\Components\CreatableSelect;
use App\Models\CustomerLabel;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;

class CustomerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
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

                        DatePicker::make('date_of_birth')
                            ->label('Tanggal Lahir'),

                        CreatableSelect::make('customer_label_id')
                            ->options(fn () => CustomerLabel::pluck('name', 'id')->toArray())
                            ->label('Label Pelanggan'),

                        Textarea::make('address')
                            ->maxLength(500)
                            ->rows(3)
                            ->label('Alamat'),
                    ])
                    ->columnSpan(1),

                Section::make('Program Loyalty')
                    ->schema([
                        Toggle::make('is_loyalty_member')
                            ->label('Loyalty Member')
                            ->default(false),

                        TextInput::make('loyalty_points')
                            ->numeric()
                            ->default(0)
                            ->label('Poin Belanja'),

                        Select::make('pricing_tier_id')
                            ->relationship('pricingTier', 'name')
                            ->label('Tingkat Harga Default')
                            ->placeholder('Pilih tingkat harga...')
                            ->nullable(),
                    ])
                    ->columnSpan(1),
            ]);
    }
}
