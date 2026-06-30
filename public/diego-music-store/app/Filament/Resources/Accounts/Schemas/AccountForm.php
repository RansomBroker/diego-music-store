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
                            ->options(fn () => \App\Models\AccountClassification::pluck('name', 'key')->toArray())
                            ->label('Klasifikasi Akun'),

                        Toggle::make('is_header')
                            ->label('Akun Header / Induk')
                            ->default(false)
                            ->helperText('Jika aktif, akun ini bertindak sebagai folder kategori dan tidak bisa dipilih untuk transaksi langsung.'),

                        Select::make('parent_id')
                            ->label('Akun Induk (Parent)')
                            ->options(fn ($record) => \App\Models\Account::where('is_header', true)
                                ->when($record, fn($q) => $q->where('id', '!=', $record->id))
                                ->get()
                                ->mapWithKeys(fn($acc) => [$acc->id => "{$acc->code} - {$acc->name}"])
                                ->toArray())
                            ->placeholder('Tanpa Induk (Tingkat Atas / Root)')
                            ->searchable()
                            ->preload(),

                        Toggle::make('is_active')
                            ->default(true)
                            ->label('Status Aktif'),
                    ])
            ]);
    }
}
