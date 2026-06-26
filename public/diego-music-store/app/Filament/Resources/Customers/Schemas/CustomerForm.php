<?php

namespace App\Filament\Resources\Customers\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
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

                                DatePicker::make('date_of_birth')
                                    ->label('Tanggal Lahir'),

                                Select::make('customer_label_id')
                                    ->relationship('label', 'name')
                                    ->createOptionForm([
                                        TextInput::make('name')
                                            ->required()
                                            ->maxLength(255)
                                            ->label('Nama Label'),
                                    ])
                                    ->createOptionUsing(function (array $data) {
                                        $key = \Illuminate\Support\Str::slug($data['name']);
                                        return \App\Models\CustomerLabel::create([
                                            'key' => $key,
                                            'name' => $data['name'],
                                        ])->id;
                                    })
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
                            ])
                            ->columnSpan(1),
                    ])
            ]);
    }
}
