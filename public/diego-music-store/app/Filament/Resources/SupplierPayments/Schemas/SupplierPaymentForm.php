<?php

namespace App\Filament\Resources\SupplierPayments\Schemas;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Repeater;
use App\Models\PurchaseTransaction;
use App\Models\Account;
use App\Models\Supplier;

class SupplierPaymentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Utama Pelunasan Hutang')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('payment_no')
                                    ->label('Nomor Pembayaran')
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
                                    ->disabled(fn ($record) => $record !== null && $paymentStatus = $record->status !== 'draft')
                                    ->reactive()
                                    ->afterStateUpdated(fn ($state, callable $set) => $set('items', [])),

                                DatePicker::make('payment_date')
                                    ->label('Tanggal Pembayaran')
                                    ->default(now())
                                    ->required()
                                    ->disabled(fn ($record) => $record !== null && $record->status !== 'draft'),

                                Select::make('supplier_id')
                                    ->label('Supplier')
                                    ->relationship('supplier', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->disabled(fn ($record) => $record !== null && $record->status !== 'draft')
                                    ->reactive()
                                    ->afterStateUpdated(fn ($state, callable $set) => $set('items', [])),

                                Select::make('account_id')
                                    ->label('Akun Kas / Bank')
                                    ->options(fn () => Account::where('classification', 'asset')
                                        ->where('is_header', false)
                                        ->where('code', 'like', '1-1%') // Cash and cash equivalents
                                        ->get()
                                        ->pluck('name', 'id')
                                    )
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->disabled(fn ($record) => $record !== null && $record->status !== 'draft'),

                                Select::make('payment_method')
                                    ->label('Metode Pembayaran')
                                    ->options([
                                        'Cash' => 'Tunai / Cash',
                                        'Bank Transfer' => 'Transfer Bank',
                                        'Giro' => 'Giro',
                                        'Cheque' => 'Cek',
                                    ])
                                    ->default('Bank Transfer')
                                    ->required()
                                    ->disabled(fn ($record) => $record !== null && $record->status !== 'draft'),

                                TextInput::make('payment_reference')
                                    ->label('Referensi Pembayaran (No. Rek / Bukti)')
                                    ->placeholder('Misal: Ref #12345')
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
                            ->label('Catatan')
                            ->rows(3)
                            ->columnSpanFull()
                            ->disabled(fn ($record) => $record !== null && $record->status === 'posted'),
                    ])
                    ->columnSpanFull(),

                Section::make('Rincian Invoice Pembelian yang Dibayar')
                    ->schema([
                        Repeater::make('items')
                            ->label('Item Tagihan')
                            ->schema([
                                Grid::make(3)
                                    ->schema([
                                        Select::make('purchase_transaction_id')
                                            ->label('Invoice Pembelian (Kredit)')
                                            ->required()
                                            ->searchable()
                                            ->disabled(fn ($get) => empty($get('../../supplier_id')))
                                            ->options(function ($get): array {
                                                $supplierId = $get('../../supplier_id');
                                                if (!$supplierId) {
                                                    return [];
                                                }

                                                return PurchaseTransaction::query()
                                                    ->where('supplier_id', $supplierId)
                                                    ->where('purchase_type', 'Kredit')
                                                    ->where('status', 'posted')
                                                    ->get()
                                                    ->filter(fn ($pt) => $pt->getRemainingUnpaidAmount() > 0)
                                                    ->mapWithKeys(fn ($pt) => [
                                                        $pt->id => "[{$pt->transaction_no}]" . ($pt->invoice_number ? " No. Invoice: {$pt->invoice_number}" : "") . " (Tgl: {$pt->transaction_date->format('d/m/Y')} | Total: " . \App\Helpers\FormatHelper::rupiah($pt->grand_total) . " | Sisa: " . \App\Helpers\FormatHelper::rupiah($pt->getRemainingUnpaidAmount()) . ")"
                                                    ])
                                                    ->toArray();
                                            })
                                            ->getOptionLabelUsing(function ($value): ?string {
                                                $pt = PurchaseTransaction::find($value);
                                                if (!$pt) {
                                                    return null;
                                                }
                                                return "[{$pt->transaction_no}]" . ($pt->invoice_number ? " No. Invoice: {$pt->invoice_number}" : "") . " (Tgl: {$pt->transaction_date->format('d/m/Y')} | Total: " . \App\Helpers\FormatHelper::rupiah($pt->grand_total) . " | Sisa: " . \App\Helpers\FormatHelper::rupiah($pt->getRemainingUnpaidAmount()) . ")";
                                            })
                                            ->getSearchResultsUsing(function (string $search, $get): array {
                                                $supplierId = $get('../../supplier_id');
                                                if (!$supplierId) {
                                                    return [];
                                                }

                                                return PurchaseTransaction::query()
                                                    ->where('supplier_id', $supplierId)
                                                    ->where('purchase_type', 'Kredit')
                                                    ->where('status', 'posted')
                                                    ->where(function ($query) use ($search) {
                                                        $query->where('transaction_no', 'like', "%{$search}%")
                                                            ->orWhere('invoice_number', 'like', "%{$search}%");
                                                    })
                                                    ->get()
                                                    ->filter(fn ($pt) => $pt->getRemainingUnpaidAmount() > 0)
                                                    ->mapWithKeys(fn ($pt) => [
                                                        $pt->id => "[{$pt->transaction_no}]" . ($pt->invoice_number ? " No. Invoice: {$pt->invoice_number}" : "") . " (Tgl: {$pt->transaction_date->format('d/m/Y')} | Total: " . \App\Helpers\FormatHelper::rupiah($pt->grand_total) . " | Sisa: " . \App\Helpers\FormatHelper::rupiah($pt->getRemainingUnpaidAmount()) . ")"
                                                    ])
                                                    ->toArray();
                                            })
                                            ->reactive()
                                            ->afterStateUpdated(function ($state, callable $set) {
                                                if ($state) {
                                                    $pt = PurchaseTransaction::find($state);
                                                    if ($pt) {
                                                        $due = $pt->getRemainingUnpaidAmount();
                                                        $set('amount_due', $due);
                                                        $set('amount_paid', $due);
                                                    }
                                                } else {
                                                    $set('amount_due', 0);
                                                    $set('amount_paid', 0);
                                                }
                                            })
                                            ->columnSpan(1),

                                        TextInput::make('amount_due')
                                            ->label('Sisa Tagihan')
                                            ->numeric()
                                            ->prefix('Rp')
                                            ->disabled()
                                            ->dehydrated()
                                            ->default(0),

                                        TextInput::make('amount_paid')
                                            ->label('Jumlah Bayar')
                                            ->numeric()
                                            ->prefix('Rp')
                                            ->required()
                                            ->minValue(1)
                                            ->maxValue(fn ($get) => intval($get('amount_due') ?? 0))
                                            ->default(0),
                                    ]),
                            ])
                            ->minItems(1)
                            ->columnSpanFull()
                            ->disabled(fn ($record) => $record !== null && $record->status !== 'draft'),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
