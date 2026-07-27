<?php

namespace App\Filament\Resources\SalesQuotations\Schemas;

use App\Models\ProductVariant;
use App\Models\Unit;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SalesQuotationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Penawaran Harga (Sales Quotation)')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('quotation_number')
                                    ->label('No. Penawaran')
                                    ->default(fn () => \App\Models\SalesQuotation::generateQuotationNumber())
                                    ->required()
                                    ->unique('sales_quotations', 'quotation_number', ignoreRecord: true)
                                    ->maxLength(100),

                                DatePicker::make('quotation_date')
                                    ->label('Tanggal Penawaran')
                                    ->default(now())
                                    ->required(),

                                DatePicker::make('valid_until')
                                    ->label('Berlaku Sampai')
                                    ->default(now()->addDays(14)),

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

                                Select::make('status')
                                    ->label('Status')
                                    ->options([
                                        'draft' => 'Draft',
                                        'sent' => 'Sent (Terkirim)',
                                        'approved' => 'Approved (Disetujui)',
                                        'rejected' => 'Rejected (Ditolak)',
                                        'closed' => 'Closed (Selesai)',
                                    ])
                                    ->default('draft')
                                    ->required(),
                            ]),

                        Textarea::make('notes')
                            ->label('Catatan / Syarat Penawaran')
                            ->rows(2)
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),

                Section::make('Diskon & Pajak Header')
                    ->schema([
                        Grid::make(3)
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
                            ]),
                    ])
                    ->columnSpanFull(),

                Section::make('Daftar Produk / Barang')
                    ->schema([
                        Repeater::make('items')
                            ->label('Item Penawaran')
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
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
