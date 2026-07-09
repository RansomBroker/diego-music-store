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
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Components\Group;
use App\Models\PurchaseTransaction;
use App\Models\Account;
use App\Models\Supplier;
use App\Helpers\FormatHelper;

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
                                    ->disabled(fn ($record) => $record !== null && $paymentStatus = $record->status !== 'draft'),

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
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        if (!$state) {
                                            $set('items', []);
                                            return;
                                        }

                                        $unpaidTransactions = PurchaseTransaction::query()
                                            ->where('supplier_id', $state)
                                            ->where('purchase_type', 'Kredit')
                                            ->where('status', 'posted')
                                            ->get()
                                            ->filter(fn ($pt) => $pt->getRemainingUnpaidAmount() > 0);

                                        $items = [];
                                        foreach ($unpaidTransactions as $pt) {
                                            $items[] = [
                                                'is_selected' => false,
                                                'purchase_transaction_id' => $pt->id,
                                                'transaction_no' => $pt->transaction_no,
                                                'invoice_number' => $pt->invoice_number,
                                                'transaction_date' => $pt->transaction_date->format('Y-m-d'),
                                                'due_date' => $pt->due_date?->format('Y-m-d'),
                                                'grand_total' => $pt->grand_total,
                                                'amount_due' => $pt->getRemainingUnpaidAmount(),
                                                'amount_paid' => 0,
                                            ];
                                        }

                                        $set('items', $items);
                                    }),

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
                                        Group::make([
                                            Placeholder::make('items_headers')
                                                ->hiddenLabel()
                                                ->content(fn () => view('backoffice.supplier-payments.items-table-header'))
                                                ->extraAttributes([
                                                    'style' => 'min-width: 1030px;'
                                                ]),

                                            Repeater::make('items')
                                                ->hiddenLabel()
                                                ->reorderable(false)
                                                ->addable(false)
                                                ->deletable(false)
                                                ->schema([
                                                    Group::make([
                                                        Checkbox::make('is_selected')
                                                            ->hiddenLabel()
                                                            ->live()
                                                            ->afterStateUpdated(function ($state, callable $set, $get) {
                                                                if ($state) {
                                                                    $set('amount_paid', $get('amount_due'));
                                                                } else {
                                                                    $set('amount_paid', 0);
                                                                }
                                                            })
                                                            ->disabled(fn ($record) => $record !== null && $record->status !== 'draft'),

                                                        Placeholder::make('invoice_info')
                                                            ->hiddenLabel()
                                                            ->content(function ($get) {
                                                                $no = $get('transaction_no') ?? '';
                                                                $inv = $get('invoice_number');
                                                                return $inv ? "{$no} (Inv: {$inv})" : $no;
                                                            }),

                                                        Placeholder::make('transaction_date_formatted')
                                                            ->hiddenLabel()
                                                            ->content(function ($get) {
                                                                $date = $get('transaction_date');
                                                                return $date ? date('d/m/Y', strtotime($date)) : '-';
                                                            }),

                                                        Placeholder::make('due_date_formatted')
                                                            ->hiddenLabel()
                                                            ->content(function ($get) {
                                                                $date = $get('due_date');
                                                                return $date ? date('d/m/Y', strtotime($date)) : '-';
                                                            }),

                                                        Placeholder::make('grand_total_formatted')
                                                            ->hiddenLabel()
                                                            ->content(fn ($get) => FormatHelper::rupiah(intval($get('grand_total') ?? 0))),

                                                        Placeholder::make('amount_due_formatted')
                                                            ->hiddenLabel()
                                                            ->content(fn ($get) => FormatHelper::rupiah(intval($get('amount_due') ?? 0))),

                                                        TextInput::make('amount_paid')
                                                            ->hiddenLabel()
                                                            ->numeric()
                                                            ->prefix('Rp')
                                                            ->required()
                                                            ->minValue(0)
                                                            ->maxValue(fn ($get) => intval($get('amount_due') ?? 0))
                                                            ->disabled(fn ($record) => $record !== null && $record->status !== 'draft')
                                                            ->live()
                                                            ->afterStateUpdated(function ($state, callable $set) {
                                                                $val = intval($state ?? 0);
                                                                if ($val > 0) {
                                                                    $set('is_selected', true);
                                                                } else {
                                                                    $set('is_selected', false);
                                                                }
                                                            }),

                                                        Hidden::make('purchase_transaction_id'),
                                                        Hidden::make('transaction_no'),
                                                        Hidden::make('invoice_number'),
                                                        Hidden::make('transaction_date'),
                                                        Hidden::make('due_date'),
                                                        Hidden::make('grand_total'),
                                                        Hidden::make('amount_due'),
                                                    ])
                                                    ->columns(1)
                                                    ->extraAttributes([
                                                        'class' => 'sp-items-grid'
                                                    ])
                                                ])
                                                ->columnSpanFull()
                                                ->extraAttributes([
                                                    'style' => 'min-width: 1030px;'
                                                ])
                                                ->disabled(fn ($record) => $record !== null && $record->status !== 'draft'),
                                        ])
                                        ->extraAttributes([
                                            'class' => 'overflow-x-auto pb-4 sp-items-table-container',
                                            'style' => 'width: 100%;',
                                            'x-data' => '{}',
                                            'x-on:scroll' => "\$el.querySelectorAll('[data-frozen-header], [data-frozen-header-2]').forEach(function(el){ el.style.transform = 'translateX(' + \$el.scrollLeft + 'px)'; })",
                                        ]),
                                    ])
                                    ->columnSpanFull(),

                Section::make('Ringkasan Pembayaran')
                    ->schema([
                        Placeholder::make('payment_summary')
                            ->hiddenLabel()
                            ->content(function ($get) {
                                $items = $get('items') ?? [];
                                $totalOutstanding = 0;
                                $totalPayment = 0;

                                foreach ($items as $item) {
                                    $totalOutstanding += intval($item['amount_due'] ?? 0);
                                    if ($item['is_selected'] ?? false) {
                                        $totalPayment += intval($item['amount_paid'] ?? 0);
                                    }
                                }

                                return view('backoffice.supplier-payments.summary-placeholder', [
                                    'totalOutstanding' => $totalOutstanding,
                                    'totalPayment' => $totalPayment,
                                ]);
                            })
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
