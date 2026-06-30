<?php

namespace App\Filament\Resources\JournalEntries\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;

class JournalEntryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Detail Jurnal Umum')
                    ->columnSpan('full')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('entry_no')
                                    ->label('No. Jurnal')
                                    ->disabled()
                                    ->placeholder('JV-YYYYMMDD-XXXX'),

                                DatePicker::make('date')
                                    ->label('Tanggal Jurnal')
                                    ->required()
                                    ->default(now()),

                                Select::make('branch_id')
                                    ->label('Cabang')
                                    ->relationship('branch', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload(),
                            ]),

                        Textarea::make('description')
                            ->label('Keterangan / Deskripsi')
                            ->rows(2)
                            ->maxLength(1000)
                            ->placeholder('Masukkan keterangan pencatatan jurnal umum...'),
                    ]),

                Section::make('Baris Jurnal (Double-Entry)')
                    ->columnSpan('full')
                    ->schema([
                        Repeater::make('items')
                            ->relationship('items')
                            ->schema([
                                Grid::make(4)
                                    ->schema([
                                        Select::make('account_id')
                                            ->label('Akun COA')
                                            ->relationship(
                                                name: 'account',
                                                titleAttribute: 'name',
                                                modifyQueryUsing: fn ($query) => $query->where('is_header', false)
                                            )
                                            ->getOptionLabelFromRecordUsing(fn ($record) => "[{$record->code}] {$record->name}")
                                            ->required()
                                            ->searchable()
                                            ->preload()
                                            ->columnSpan(2),

                                        TextInput::make('debit')
                                            ->label('Debit')
                                            ->numeric()
                                            ->default(0)
                                            ->prefix('Rp'),

                                        TextInput::make('credit')
                                            ->label('Kredit')
                                            ->numeric()
                                            ->default(0)
                                            ->prefix('Rp'),
                                    ]),

                                TextInput::make('notes')
                                    ->label('Catatan Baris')
                                    ->maxLength(255)
                                    ->placeholder('Catatan opsional untuk baris ini...'),
                            ])
                            ->minItems(2)
                            ->label('Daftar Baris Debit & Kredit')
                            ->createItemButtonLabel('Tambah Baris Jurnal')
                            ->columnSpan('full'),
                    ]),
            ]);
    }
}
