<?php

namespace App\Filament\Resources\PurchaseOrders\Schemas;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Placeholder;
use App\Models\Supplier;
use App\Models\ProductVariant;

class PurchaseOrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Utama PO')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('po_number')
                                    ->label('Nomor PO')
                                    ->placeholder('AUTO-GENERATED')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->visible(fn ($record) => $record !== null),

                                Select::make('supplier_id')
                                    ->label('Supplier')
                                    ->relationship('supplier', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload(),

                                Select::make('branch_id')
                                    ->label('Cabang Pemesan')
                                    ->relationship('branch', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload(),

                                DatePicker::make('order_date')
                                    ->label('Tanggal Order')
                                    ->default(now())
                                    ->required(),

                                DatePicker::make('eta_date')
                                    ->label('Estimasi Pengiriman (ETA)'),

                                TextInput::make('currency')
                                    ->label('Mata Uang')
                                    ->default('IDR')
                                    ->required(),

                                Select::make('payment_term')
                                    ->label('Termin Pembayaran')
                                    ->options([
                                        'COD' => 'Cash on Delivery (COD)',
                                        '14 Hari' => '14 Hari',
                                        '30 Hari' => '30 Hari',
                                        '60 Hari' => '60 Hari',
                                    ])
                                    ->default('COD'),

                                Select::make('status')
                                    ->label('Status')
                                    ->options([
                                        'draft' => 'Draft',
                                        'approved' => 'Approved',
                                        'closed' => 'Closed',
                                    ])
                                    ->default('draft')
                                    ->required(),
                            ]),

                        Textarea::make('notes')
                            ->label('Catatan PO')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),

                Section::make('Pajak & Biaya Tambahan (Header)')
                    ->schema([
                        Grid::make(4)
                            ->schema([
                                Select::make('tax_mode')
                                    ->label('Mode PPN')
                                    ->options([
                                        'ITEM' => 'Pajak per Barang (Item Level)',
                                        'GLOBAL' => 'Pajak Global (Header)',
                                    ])
                                    ->default('ITEM')
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set, $get) {
                                        if ($state === 'GLOBAL') {
                                            $rate = intval($get('tax_rate') ?? 0);
                                            $items = $get('items') ?? [];
                                            foreach ($items as $key => $item) {
                                                $set("items.{$key}.tax_rate", $rate);
                                            }
                                        }
                                    }),

                                TextInput::make('tax_rate')
                                    ->label('PPN Global (%)')
                                    ->numeric()
                                    ->default(0)
                                    ->suffix('%')
                                    ->visible(fn ($get) => $get('tax_mode') === 'GLOBAL')
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set, $get) {
                                        $items = $get('items') ?? [];
                                        foreach ($items as $key => $item) {
                                            $set("items.{$key}.tax_rate", intval($state));
                                        }
                                    }),

                                TextInput::make('discount_amount')
                                    ->label('Diskon Global')
                                    ->numeric()
                                    ->default(0)
                                    ->prefix('Rp')
                                    ->reactive(),

                                TextInput::make('other_cost')
                                    ->label('Biaya Kirim (Ongkir)')
                                    ->numeric()
                                    ->default(0)
                                    ->prefix('Rp')
                                    ->reactive(),
                            ]),
                    ])
                    ->columnSpanFull(),

                Section::make('Daftar Barang')
                    ->schema([
                        Repeater::make('items')
                            ->label('Daftar Barang PO')
                            ->schema([
                                Grid::make(6)
                                    ->schema([
                                        Select::make('product_variant_id')
                                            ->label('Produk / Varian')
                                            ->required()
                                            ->searchable()
                                            ->getSearchResultsUsing(function (string $search): array {
                                                return ProductVariant::query()
                                                    ->join('products', 'products.id', '=', 'product_variants.product_id')
                                                    ->where('products.name', 'like', "%{$search}%")
                                                    ->orWhere('product_variants.name', 'like', "%{$search}%")
                                                    ->orWhere('product_variants.sku', 'like', "%{$search}%")
                                                    ->select('product_variants.id', 'products.name as product_name', 'product_variants.name as variant_name', 'product_variants.sku')
                                                    ->limit(50)
                                                    ->get()
                                                    ->mapWithKeys(fn ($v) => [
                                                        $v->id => "[{$v->sku}] {$v->product_name}" . ($v->variant_name ? " - {$v->variant_name}" : "")
                                                    ])
                                                    ->toArray();
                                            })
                                            ->getOptionLabelUsing(fn ($value): ?string => 
                                                ($v = ProductVariant::find($value)) 
                                                    ? "[{$v->sku}] {$v->product->name}" . ($v->name ? " - {$v->name}" : "") 
                                                    : null
                                            )
                                            ->options(function (): array {
                                                return ProductVariant::query()
                                                    ->join('products', 'products.id', '=', 'product_variants.product_id')
                                                    ->select('product_variants.id', 'products.name as product_name', 'product_variants.name as variant_name', 'product_variants.sku')
                                                    ->limit(50)
                                                    ->get()
                                                    ->mapWithKeys(fn ($v) => [
                                                        $v->id => "[{$v->sku}] {$v->product_name}" . ($v->variant_name ? " - {$v->variant_name}" : "")
                                                    ])
                                                    ->toArray();
                                            })
                                            ->reactive()
                                            ->afterStateUpdated(function ($state, callable $set) {
                                                if ($state) {
                                                    $variant = ProductVariant::find($state);
                                                    if ($variant) {
                                                        $set('price', $variant->cost_price ?? 0);
                                                    }
                                                }
                                            })
                                            ->columnSpan(2),

                                        TextInput::make('quantity')
                                            ->label('Qty')
                                            ->numeric()
                                            ->required()
                                            ->default(1)
                                            ->reactive(),

                                        TextInput::make('price')
                                            ->label('Harga Beli')
                                            ->numeric()
                                            ->required()
                                            ->prefix('Rp')
                                            ->reactive(),

                                        TextInput::make('discount_amount')
                                            ->label('Diskon Item')
                                            ->numeric()
                                            ->default(0)
                                            ->prefix('Rp')
                                            ->reactive(),

                                        TextInput::make('tax_rate')
                                            ->label('Pajak PPN')
                                            ->numeric()
                                            ->default(0)
                                            ->suffix('%')
                                            ->disabled(fn ($get) => $get('../../tax_mode') === 'GLOBAL')
                                            ->dehydrated() // force sending to backend even when disabled
                                            ->reactive(),

                                        Placeholder::make('item_subtotal')
                                            ->label('Subtotal')
                                            ->content(function ($get) {
                                                $qty = intval($get('quantity') ?? 0);
                                                $price = intval($get('price') ?? 0);
                                                $disc = intval($get('discount_amount') ?? 0);
                                                $taxRate = intval($get('tax_rate') ?? 0);
                                                
                                                $subtotalBeforeTax = ($qty * $price) - $disc;
                                                $taxAmount = (int) round($subtotalBeforeTax * ($taxRate / 100));
                                                $subtotal = $subtotalBeforeTax + $taxAmount;
                                                
                                                return 'Rp ' . number_format($subtotal, 0, ',', '.');
                                            })
                                            ->columnSpan(1),
                                    ]),
                            ])
                            ->minItems(1)
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),

                Section::make('Ringkasan Total Harga')
                    ->schema([
                        Placeholder::make('total_summary_header')
                            ->label('')
                            ->content(function ($get) {
                                $items = $get('items') ?? [];
                                $taxMode = $get('tax_mode') ?? 'ITEM';
                                $globalTaxRate = intval($get('tax_rate') ?? 0);
                                
                                $totalAmount = 0;
                                $totalTax = 0;
                                
                                foreach ($items as $item) {
                                    $qty = intval($item['quantity'] ?? 0);
                                    $price = intval($item['price'] ?? 0);
                                    $disc = intval($item['discount_amount'] ?? 0);
                                    
                                    $subtotalBeforeTax = ($qty * $price) - $disc;
                                    $itemTaxRate = $taxMode === 'GLOBAL' ? $globalTaxRate : intval($item['tax_rate'] ?? 0);
                                    $itemTaxAmount = (int) round($subtotalBeforeTax * ($itemTaxRate / 100));
                                    
                                    $totalAmount += $subtotalBeforeTax;
                                    $totalTax += $itemTaxAmount;
                                }
                                
                                $discHeader = intval($get('discount_amount') ?? 0);
                                $otherCost = intval($get('other_cost') ?? 0);
                                $grandTotal = $totalAmount - $discHeader + $totalTax + $otherCost;
                                
                                return view('backoffice.purchase-orders.summary-placeholder', [
                                    'totalAmount' => $totalAmount,
                                    'taxAmount' => $totalTax,
                                    'discountAmount' => $discHeader,
                                    'otherCost' => $otherCost,
                                    'grandTotal' => $grandTotal,
                                ]);
                            })
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
