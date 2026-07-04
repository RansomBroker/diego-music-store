<?php

namespace App\Filament\Resources\CashTransactions\Schemas;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use App\Models\Account;

class CashTransactionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Transaksi Kas & Bank')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('transaction_no')
                                    ->label('Nomor Transaksi')
                                    ->placeholder('AUTO-GENERATED')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->visible(fn ($record) => $record !== null),

                                Select::make('branch_id')
                                    ->label('Cabang')
                                    ->relationship('branch', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->disabled(fn ($record) => $record !== null && $record->status !== 'draft'),

                                DatePicker::make('transaction_date')
                                    ->label('Tanggal Transaksi')
                                    ->default(now())
                                    ->required()
                                    ->disabled(fn ($record) => $record !== null && $record->status !== 'draft'),

                                Select::make('type')
                                    ->label('Tipe Transaksi')
                                    ->options([
                                        'out' => 'Kas Keluar (Biaya / Pengeluaran)',
                                        'in' => 'Kas Masuk (Penerimaan)',
                                        'transfer' => 'Transfer Kas (Mutasi Antar Kas/Bank)',
                                    ])
                                    ->default('out')
                                    ->required()
                                    ->reactive()
                                    ->disabled(fn ($record) => $record !== null && $record->status !== 'draft')
                                    ->afterStateUpdated(function (callable $set) {
                                        $set('source_account_id', null);
                                        $set('destination_account_id', null);
                                    }),

                                Select::make('source_account_id')
                                    ->label(function ($get) {
                                        $type = $get('type') ?? 'out';
                                        if ($type === 'out') return 'Keluar Dari Kas/Bank';
                                        if ($type === 'in') return 'Sumber Penerimaan (Akun Kredit)';
                                        return 'Kirim Dari Kas/Bank (Asal)';
                                    })
                                    ->options(function ($get) {
                                        $type = $get('type') ?? 'out';
                                        // For 'out' and 'transfer', must select a cash/bank asset account
                                        if ($type === 'out' || $type === 'transfer') {
                                            return Account::query()
                                                ->where('is_header', false)
                                                ->where('code', 'like', '1-1%')
                                                ->get()
                                                ->mapWithKeys(fn ($acc) => [$acc->id => "[{$acc->code}] {$acc->name}"])
                                                ->toArray();
                                        }
                                        // For 'in', select non-cash accounts (Revenue, etc.)
                                        return Account::query()
                                            ->where('is_header', false)
                                            ->where('code', 'not like', '1-1%')
                                            ->get()
                                            ->mapWithKeys(fn ($acc) => [$acc->id => "[{$acc->code}] {$acc->name}"])
                                            ->toArray();
                                    })
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->reactive()
                                    ->disabled(fn ($record) => $record !== null && $record->status !== 'draft'),

                                Select::make('destination_account_id')
                                    ->label(function ($get) {
                                        $type = $get('type') ?? 'out';
                                        if ($type === 'out') return 'Dibebankan Ke Akun (Akun Debit)';
                                        if ($type === 'in') return 'Masuk Ke Kas/Bank';
                                        return 'Terima Di Kas/Bank (Tujuan)';
                                    })
                                    ->options(function ($get) {
                                        $type = $get('type') ?? 'out';
                                        // For 'in' and 'transfer', must select a cash/bank asset account
                                        if ($type === 'in' || $type === 'transfer') {
                                            return Account::query()
                                                ->where('is_header', false)
                                                ->where('code', 'like', '1-1%')
                                                ->get()
                                                ->mapWithKeys(fn ($acc) => [$acc->id => "[{$acc->code}] {$acc->name}"])
                                                ->toArray();
                                        }
                                        // For 'out', select non-cash accounts (Expense, etc.)
                                        return Account::query()
                                            ->where('is_header', false)
                                            ->where('code', 'not like', '1-1%')
                                            ->get()
                                            ->mapWithKeys(fn ($acc) => [$acc->id => "[{$acc->code}] {$acc->name}"])
                                            ->toArray();
                                    })
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->disabled(fn ($record) => $record !== null && $record->status !== 'draft'),

                                TextInput::make('amount')
                                    ->label('Jumlah Uang')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->required()
                                    ->minValue(1)
                                    ->disabled(fn ($record) => $record !== null && $record->status !== 'draft'),

                                Select::make('status')
                                    ->label('Status')
                                    ->options([
                                        'draft' => 'Draft',
                                        'posted' => 'Posted (Selesai)',
                                    ])
                                    ->default('draft')
                                    ->required()
                                    ->disabled(fn ($record) => $record !== null && $record->status === 'posted'),
                            ]),

                        Textarea::make('notes')
                            ->label('Keterangan / Memo')
                            ->rows(3)
                            ->columnSpanFull()
                            ->disabled(fn ($record) => $record !== null && $record->status !== 'draft'),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
