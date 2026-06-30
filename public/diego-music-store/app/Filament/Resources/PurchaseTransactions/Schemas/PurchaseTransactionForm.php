<?php

namespace App\Filament\Resources\PurchaseTransactions\Schemas;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Placeholder;
use App\Models\PurchaseOrder;
use App\Models\ProductVariant;

class PurchaseTransactionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Utama Transaksi')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('transaction_no')
                                    ->label('No. Transaksi')
                                    ->placeholder('AUTO-GENERATED')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->visible(fn ($record) => $record !== null),

                                DatePicker::make('transaction_date')
                                    ->label('Tanggal Transaksi')
                                    ->default(now())
                                    ->required(),

                                Select::make('po_id')
                                    ->label('Rujukan Purchase Order (PO)')
                                    ->options(
                                        PurchaseOrder::whereIn('status', ['approved', 'closed'])
                                            ->get()
                                            ->pluck('po_number', 'id')
                                    )
                                    ->searchable()
                                    ->placeholder('Pilih PO jika ada')
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        if ($state) {
                                            $po = PurchaseOrder::with('items.productVariant.product')->find($state);
                                            if ($po) {
                                                $set('supplier_id', $po->supplier_id);
                                                $set('branch_id', $po->branch_id);
                                                
                                                $items = [];
                                                foreach ($po->items as $item) {
                                                    $items[] = [
                                                        'product_variant_id' => $item->product_variant_id,
                                                        'qty_po' => $item->quantity,
                                                        'qty_received' => $item->quantity, // default to full PO qty
                                                        'unit_id' => $item->productVariant->product->unit_id,
                                                        'price' => $item->price,
                                                        'discount' => $item->discount_amount,
                                                        'tax_rate' => $item->tax_rate,
                                                    ];
                                                }
                                                $set('items', $items);
                                            }
                                        }
                                    }),

                                Select::make('supplier_id')
                                    ->label('Supplier')
                                    ->relationship('supplier', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload(),

                                Select::make('branch_id')
                                    ->label('Cabang Pembelian')
                                    ->relationship('branch', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload(),

                                Select::make('warehouse_id')
                                    ->label('Gudang Penerima')
                                    ->relationship('warehouse', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload(),

                                Select::make('purchase_type')
                                    ->label('Jenis Pembelian')
                                    ->options([
                                        'Tunai' => 'Tunai (Cash)',
                                        'Kredit' => 'Kredit (Tempo)',
                                    ])
                                    ->default('Tunai')
                                    ->required()
                                    ->reactive(),

                                TextInput::make('invoice_number')
                                    ->label('No. Invoice Supplier'),

                                TextInput::make('delivery_note_number')
                                    ->label('No. Surat Jalan'),

                                DatePicker::make('invoice_date')
                                    ->label('Tanggal Invoice'),

                                DatePicker::make('due_date')
                                    ->label('Jatuh Tempo')
                                    ->visible(fn ($get) => $get('purchase_type') === 'Kredit')
                                    ->required(fn ($get) => $get('purchase_type') === 'Kredit'),

                                Select::make('status')
                                    ->label('Status')
                                    ->options([
                                        'draft' => 'Draft',
                                        'posted' => 'Posted',
                                        'cancelled' => 'Cancelled',
                                    ])
                                    ->default('draft')
                                    ->disabled()
                                    ->dehydrated(),
                            ]),
                    ])
                    ->columnSpanFull(),

                Section::make('Biaya & Potongan Tambahan')
                    ->schema([
                        Grid::make(4)
                            ->schema([
                                TextInput::make('discount')
                                    ->label('Diskon Global')
                                    ->numeric()
                                    ->default(0)
                                    ->prefix('Rp')
                                    ->reactive(),

                                TextInput::make('shipping_cost')
                                    ->label('Biaya Kirim (Ongkir)')
                                    ->numeric()
                                    ->default(0)
                                    ->prefix('Rp')
                                    ->reactive(),

                                TextInput::make('other_cost')
                                    ->label('Biaya Lain-Lain')
                                    ->numeric()
                                    ->default(0)
                                    ->prefix('Rp')
                                    ->reactive(),

                                TextInput::make('pph_amount')
                                    ->label('Potongan PPh')
                                    ->numeric()
                                    ->default(0)
                                    ->prefix('Rp')
                                    ->reactive(),
                            ]),
                    ])
                    ->columnSpanFull(),

                Section::make('Daftar Barang Transaksi')
                    ->schema([
                        Repeater::make('items')
                            ->label('Item Barang')
                            ->schema([
                                Grid::make(8)
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
                                                        $set('unit_id', $variant->product->unit_id);
                                                    }
                                                }
                                            })
                                            ->columnSpan(2),

                                        TextInput::make('qty_po')
                                            ->label('Qty PO')
                                            ->numeric()
                                            ->disabled()
                                            ->dehydrated()
                                            ->columnSpan(1),

                                        TextInput::make('qty_received')
                                            ->label('Qty Diterima')
                                            ->numeric()
                                            ->required()
                                            ->default(1)
                                            ->reactive()
                                            ->columnSpan(1),

                                        Select::make('unit_id')
                                            ->label('Satuan')
                                            ->options(\App\Models\Unit::pluck('name', 'id')->toArray())
                                            ->disabled()
                                            ->dehydrated()
                                            ->columnSpan(1),

                                        TextInput::make('price')
                                            ->label('Harga Beli')
                                            ->numeric()
                                            ->required()
                                            ->prefix('Rp')
                                            ->reactive()
                                            ->columnSpan(1),

                                        TextInput::make('discount')
                                            ->label('Diskon Item')
                                            ->numeric()
                                            ->default(0)
                                            ->prefix('Rp')
                                            ->reactive()
                                            ->columnSpan(1),

                                        TextInput::make('tax_rate')
                                            ->label('PPN (%)')
                                            ->numeric()
                                            ->default(0)
                                            ->suffix('%')
                                            ->reactive()
                                            ->columnSpan(1),

                                        Placeholder::make('item_subtotal')
                                            ->label('Subtotal')
                                            ->content(function ($get) {
                                                $qty = intval($get('qty_received') ?? 0);
                                                $price = intval($get('price') ?? 0);
                                                $disc = intval($get('discount') ?? 0);
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

                Section::make('Ringkasan Pembayaran & Akuntansi')
                    ->schema([
                        Placeholder::make('total_summary_header')
                            ->label('')
                            ->content(function ($get) {
                                $items = $get('items') ?? [];
                                $subtotal = 0;
                                $taxAmount = 0;
                                
                                foreach ($items as $item) {
                                    $qty = intval($item['qty_received'] ?? 0);
                                    $price = intval($item['price'] ?? 0);
                                    $disc = intval($item['discount'] ?? 0);
                                    $taxRate = intval($item['tax_rate'] ?? 0);
                                    
                                    $subtotalBeforeTax = ($qty * $price) - $disc;
                                    $itemTaxAmount = (int) round($subtotalBeforeTax * ($taxRate / 100));
                                    
                                    $subtotal += $subtotalBeforeTax;
                                    $taxAmount += $itemTaxAmount;
                                }
                                
                                $discount = intval($get('discount') ?? 0);
                                $shippingCost = intval($get('shipping_cost') ?? 0);
                                $otherCost = intval($get('other_cost') ?? 0);
                                $pphAmount = intval($get('pph_amount') ?? 0);
                                
                                $grandTotal = $subtotal - $discount + $taxAmount + $shippingCost + $otherCost - $pphAmount;
                                
                                return view('backoffice.purchase-transactions.summary-placeholder', [
                                    'subtotal' => $subtotal,
                                    'taxAmount' => $taxAmount,
                                    'discount' => $discount,
                                    'shippingCost' => $shippingCost,
                                    'otherCost' => $otherCost,
                                    'pphAmount' => $pphAmount,
                                    'grandTotal' => $grandTotal,
                                ]);
                            })
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
