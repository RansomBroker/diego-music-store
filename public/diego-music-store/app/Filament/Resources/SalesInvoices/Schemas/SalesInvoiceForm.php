<?php

namespace App\Filament\Resources\SalesInvoices\Schemas;

use App\Models\ProductVariant;
use App\Models\SalesQuotation;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SalesInvoiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Utama Faktur Penjualan')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('invoice_number')
                                    ->label('No. Faktur')
                                    ->default(fn () => \App\Models\SalesInvoice::generateInvoiceNumber())
                                    ->required()
                                    ->unique('sales_invoices', 'invoice_number', ignoreRecord: true)
                                    ->maxLength(100),

                                DatePicker::make('invoice_date')
                                    ->label('Tanggal Faktur')
                                    ->default(now())
                                    ->required(),

                                DatePicker::make('due_date')
                                    ->label('Tanggal Jatuh Tempo')
                                    ->default(now()->addDays(30)),

                                Select::make('sales_quotation_id')
                                    ->label('Rujukan Penawaran Harga (SQ)')
                                    ->options(
                                        SalesQuotation::whereIn('status', ['approved', 'sent', 'draft', 'closed'])
                                            ->get()
                                            ->pluck('quotation_number', 'id')
                                    )
                                    ->searchable()
                                    ->placeholder('Pilih SQ jika ada')
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        if ($state && ($sq = SalesQuotation::with('items')->find($state))) {
                                            $set('customer_id', $sq->customer_id);
                                            $set('branch_id', $sq->branch_id);
                                            $set('discount_type', $sq->discount_type);
                                            $set('discount_value', $sq->discount_value);
                                            $set('tax_rate', $sq->tax_rate);

                                            $items = [];
                                            foreach ($sq->items as $item) {
                                                $items[] = [
                                                    'product_variant_id' => $item->product_variant_id,
                                                    'unit_id' => $item->unit_id,
                                                    'quantity' => $item->quantity,
                                                    'price' => $item->price,
                                                    'discount_type' => $item->discount_type,
                                                    'discount_value' => $item->discount_value,
                                                ];
                                            }
                                            $set('items', $items);
                                        }
                                    }),

                                Select::make('customer_id')
                                    ->label('Pelanggan (Customer)')
                                    ->relationship('customer', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload(),

                                Select::make('branch_id')
                                    ->label('Cabang')
                                    ->relationship('branch', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload(),

                                Select::make('payment_type')
                                    ->label('Jenis Pembayaran')
                                    ->options([
                                        'Tunai' => 'Tunai (Cash / Bank)',
                                        'Kredit' => 'Kredit (Tempo / Piutang)',
                                    ])
                                    ->default('Tunai')
                                    ->required(),

                                Select::make('status')
                                    ->label('Status')
                                    ->options([
                                        'draft' => 'Draft',
                                        'posted' => 'Posted (Selesai & Potong Stok)',
                                    ])
                                    ->default('draft')
                                    ->required()
                                    ->disabled(fn ($record) => $record !== null && $record->status === 'posted'),
                            ]),

                        Textarea::make('notes')
                            ->label('Catatan / Keterangan Faktur')
                            ->rows(2)
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),

                Section::make('Diskon, Pajak & Ongkir (Header)')
                    ->schema([
                        Grid::make(4)
                            ->schema([
                                Select::make('discount_type')
                                    ->label('Tipe Diskon Global')
                                    ->options([
                                        'fixed' => 'Nominal (Rp)',
                                        'percent' => 'Persentase (%)',
                                    ])
                                    ->default('fixed')
                                    ->reactive(),

                                TextInput::make('discount_value')
                                    ->label('Nilai Diskon Global')
                                    ->numeric()
                                    ->default(0)
                                    ->prefix(fn ($get) => $get('discount_type') === 'percent' ? null : 'Rp')
                                    ->suffix(fn ($get) => $get('discount_type') === 'percent' ? '%' : null)
                                    ->reactive(),

                                TextInput::make('tax_rate')
                                    ->label('PPN Global (%)')
                                    ->numeric()
                                    ->default(0)
                                    ->suffix('%')
                                    ->reactive(),

                                TextInput::make('shipping_cost')
                                    ->label('Ongkos Kirim')
                                    ->numeric()
                                    ->default(0)
                                    ->prefix('Rp')
                                    ->reactive(),
                            ]),
                    ])
                    ->columnSpanFull(),

                Section::make('Daftar Produk / Barang Faktur')
                    ->schema([
                        Repeater::make('items')
                            ->label('Item Barang')
                            ->schema([
                                Grid::make(4)
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
                                                    ->get()
                                                    ->mapWithKeys(fn ($v) => [
                                                        $v->id => "[{$v->sku}] {$v->product_name}" . ($v->variant_name ? " - {$v->variant_name}" : "")
                                                    ])
                                                    ->toArray();
                                            })
                                            ->reactive()
                                            ->afterStateUpdated(function ($state, callable $set) {
                                                if ($state && ($v = ProductVariant::find($state))) {
                                                    $set('price', $v->price ?? 0);
                                                    $set('unit_id', $v->product->unit_id ?? null);
                                                }
                                            })
                                            ->columnSpan(2),

                                        TextInput::make('quantity')
                                            ->label('Qty')
                                            ->numeric()
                                            ->required()
                                            ->default(1)
                                            ->minValue(1),

                                        TextInput::make('price')
                                            ->label('Harga Jual @')
                                            ->numeric()
                                            ->required()
                                            ->prefix('Rp'),
                                    ]),
                            ])
                            ->minItems(1)
                            ->columnSpanFull()
                            ->disabled(fn ($record) => $record !== null && $record->status === 'posted'),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
