<?php

namespace App\Filament\Resources\Accounts\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use App\Filament\Components\CreatableSelect;

class AccountForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Detail Akun Keuangan')
                    ->columnSpan('full')
                    ->schema([
                        TextInput::make('code')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(50)
                            ->label('Kode Akun')
                            ->placeholder('e.g., 1-1000'),

                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->label('Nama Akun')
                            ->placeholder('e.g., Kas Utama, Piutang Dagang'),

                        CreatableSelect::make('classification')
                            ->required()
                            ->options(function () {
                                $defaults = [
                                    'asset' => 'Aset / Harta',
                                    'liability' => 'Kewajiban / Hutang',
                                    'equity' => 'Ekuitas / Modal',
                                    'revenue' => 'Pendapatan / Penjualan',
                                    'expense' => 'Beban / Biaya',
                                ];

                                $existing = \App\Models\Account::query()
                                    ->distinct()
                                    ->pluck('classification', 'classification')
                                    ->toArray();

                                foreach ($existing as $key => $val) {
                                    if (!isset($defaults[$key])) {
                                        $defaults[$key] = $val;
                                    }
                                }

                                return $defaults;
                            })
                            ->label('Klasifikasi Akun'),

                        Toggle::make('is_active')
                            ->default(true)
                            ->label('Status Aktif'),
                    ])
            ]);
    }
}
