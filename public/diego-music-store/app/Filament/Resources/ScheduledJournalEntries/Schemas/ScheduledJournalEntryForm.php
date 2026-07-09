<?php

namespace App\Filament\Resources\ScheduledJournalEntries\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;

class ScheduledJournalEntryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Pengaturan Jadwal Jurnal')
                    ->columnSpan('full')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                DatePicker::make('start_date')
                                    ->label('Tanggal Mulai')
                                    ->required()
                                    ->default(now()),

                                Select::make('frequency')
                                    ->label('Frekuensi')
                                    ->options([
                                        'daily' => 'Harian',
                                        'weekly' => 'Mingguan',
                                        'monthly' => 'Bulanan',
                                        'yearly' => 'Tahunan',
                                    ])
                                    ->required()
                                    ->default('monthly'),

                                TextInput::make('interval')
                                    ->label('Ulangi Setiap')
                                    ->numeric()
                                    ->required()
                                    ->default(1)
                                    ->suffix(fn ($get) => match ($get('frequency')) {
                                        'daily' => 'Hari',
                                        'weekly' => 'Minggu',
                                        'yearly' => 'Tahun',
                                        'monthly' => 'Bulan',
                                        default => 'Bulan',
                                    })
                                    ->reactive(),
                            ]),

                        Grid::make(2)
                            ->schema([
                                TextInput::make('duration_months')
                                    ->label('Durasi Waktu (Bulan)')
                                    ->numeric()
                                    ->placeholder('Selamanya jika dikosongkan')
                                    ->helperText('Contoh: isi 6 untuk durasi 6 bulan sejak tanggal mulai.'),

                                Select::make('status')
                                    ->label('Status Jadwal')
                                    ->options([
                                        'active' => 'Aktif',
                                        'paused' => 'Jeda',
                                        'completed' => 'Selesai',
                                    ])
                                    ->required()
                                    ->default('active'),
                            ]),
                    ]),

                Section::make('Detail Template Jurnal')
                    ->columnSpan('full')
                    ->schema([
                        Grid::make(2)
                            ->schema([
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
                            ->placeholder('Masukkan keterangan template jurnal, misal: Penyusutan Aset Bulanan...'),
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
