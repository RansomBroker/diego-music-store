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
use App\Helpers\BranchHelper;

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
                                    ->default(fn () => BranchHelper::getActiveBranchId())
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->afterStateUpdated(function (callable $set) {
                                        $set('source_account_id', null);
                                        $set('destination_account_id', null);
                                    })
                                    ->disabled(fn ($record) => $record !== null && ! empty($record->cash_session_id)),

                                DatePicker::make('transaction_date')
                                    ->label('Tanggal Transaksi')
                                    ->default(now())
                                    ->required(),

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
                                        $branchId = $get('branch_id') ?: BranchHelper::getActiveBranchId();
                                        $isCashBank = ($type === 'out' || $type === 'transfer');

                                        return self::getAccountOptions($branchId, $isCashBank);
                                    })
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->reactive(),

                                Select::make('destination_account_id')
                                    ->label(function ($get) {
                                        $type = $get('type') ?? 'out';
                                        if ($type === 'out') return 'Dibebankan Ke Akun (Akun Debit)';
                                        if ($type === 'in') return 'Masuk Ke Kas/Bank';
                                        return 'Terima Di Kas/Bank (Tujuan)';
                                    })
                                    ->options(function ($get) {
                                        $type = $get('type') ?? 'out';
                                        $branchId = $get('branch_id') ?: BranchHelper::getActiveBranchId();
                                        $isCashBank = ($type === 'in' || $type === 'transfer');

                                        return self::getAccountOptions($branchId, $isCashBank);
                                    })
                                    ->required()
                                    ->searchable()
                                    ->preload(),

                                TextInput::make('amount')
                                    ->label('Jumlah Uang')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->required()
                                    ->minValue(1),

                                Select::make('status')
                                    ->label('Status')
                                    ->options([
                                        'draft' => 'Draft',
                                        'posted' => 'Posted (Selesai)',
                                    ])
                                    ->default('draft')
                                    ->required(),
                            ]),

                        Textarea::make('notes')
                            ->label('Keterangan / Memo')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    /**
     * Resolve Cash/Bank or contra account options for the given branch (supporting both 9-digit 1111%/1112% and legacy 1-1% codes).
     *
     * @return array<int, string>
     */
    protected static function getAccountOptions(?int $branchId, bool $cashBankOnly): array
    {
        $buildQuery = function (?int $targetBranchId) use ($cashBankOnly) {
            return Account::query()
                ->when(
                    $targetBranchId !== null,
                    fn ($q) => $q->where('branch_id', $targetBranchId),
                    fn ($q) => $q->whereNull('branch_id')
                )
                ->where('is_header', false)
                ->where('is_active', true)
                ->when($cashBankOnly, function ($q) {
                    $q->where(function ($sub) {
                        $sub->where('code', 'like', '1111%')
                            ->orWhere('code', 'like', '1112%')
                            ->orWhere('code', 'like', '1-1%');
                    });
                }, function ($q) {
                    $q->where(function ($sub) {
                        $sub->where('code', 'not like', '1111%')
                            ->where('code', 'not like', '1112%')
                            ->where('code', 'not like', '1-1%');
                    });
                })
                ->orderBy('code')
                ->get();
        };

        $accounts = $buildQuery($branchId);
        if ($accounts->isEmpty() && $branchId !== null) {
            $accounts = $buildQuery(null);
        }

        return $accounts
            ->mapWithKeys(fn ($acc) => [$acc->id => "[{$acc->code}] {$acc->name}"])
            ->toArray();
    }
}
