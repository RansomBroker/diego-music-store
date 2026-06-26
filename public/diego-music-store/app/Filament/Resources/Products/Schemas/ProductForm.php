<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Models\Branch;
use App\Models\PricingTier;
use App\Models\ProductVariant;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Forms\Get;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        // 1. Fetch dynamic pricing tiers and branches
        $pricingTiers = PricingTier::all();
        $branches = Branch::all();

        // 2. Generate fields for pricing tiers (default / single variant)
        $tierFields = [];
        foreach ($pricingTiers as $tier) {
            $tierFields[] = TextInput::make('tier_prices.' . $tier->id)
                ->numeric()
                ->label('Harga Tier: ' . $tier->name)
                ->prefix('Rp');
        }

        // 3. Generate fields for branch prices (default / single variant)
        $branchPriceFields = [];
        foreach ($branches as $branch) {
            $branchPriceFields[] = TextInput::make('branch_prices.' . $branch->id)
                ->numeric()
                ->label('Harga Cabang: ' . $branch->name)
                ->prefix('Rp');
        }

        // 4. Generate fields for branch stocks (default / single variant)
        $branchStockFields = [];
        foreach ($branches as $branch) {
            $branchStockFields[] = TextInput::make('branch_stocks.' . $branch->id)
                ->numeric()
                ->label('Stok Cabang: ' . $branch->name)
                ->default(0);
        }

        // 5. Generate variant-specific tier, price, and stock fields for repeater
        $variantTierFields = [];
        foreach ($pricingTiers as $tier) {
            $variantTierFields[] = TextInput::make('tier_prices.' . $tier->id)
                ->numeric()
                ->label('Harga Tier: ' . $tier->name)
                ->prefix('Rp');
        }

        $variantBranchPriceFields = [];
        foreach ($branches as $branch) {
            $variantBranchPriceFields[] = TextInput::make('branch_prices.' . $branch->id)
                ->numeric()
                ->label('Harga Cabang: ' . $branch->name)
                ->prefix('Rp');
        }

        $variantBranchStockFields = [];
        foreach ($branches as $branch) {
            $variantBranchStockFields[] = TextInput::make('branch_stocks.' . $branch->id)
                ->numeric()
                ->label('Stok Cabang: ' . $branch->name)
                ->default(0);
        }

        return $schema
            ->components([
                Tabs::make('Product Details')
                    ->tabs([
                        Tab::make('Informasi Umum')
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        TextInput::make('name')
                                            ->required()
                                            ->maxLength(255)
                                            ->label('Nama Produk'),

                                        Select::make('type')
                                            ->required()
                                            ->options([
                                                'physical' => 'Barang Fisik',
                                                'bundle' => 'Produk Bundling',
                                                'service' => 'Jasa / Layanan',
                                            ])
                                            ->reactive()
                                            ->label('Tipe Produk'),

                                        FileUpload::make('image_path')
                                            ->image()
                                            ->directory('products')
                                            ->label('Foto Produk'),

                                        Toggle::make('is_active')
                                            ->default(true)
                                            ->label('Status Aktif')
                                            ->inline(false),
                                    ]),

                                Textarea::make('description')
                                    ->rows(3)
                                    ->maxLength(1000)
                                    ->label('Deskripsi Produk'),
                            ]),

                        Tab::make('Varian / Spesifikasi')
                            ->visible(fn (Get $get): bool => $get('type') === 'physical')
                            ->schema([
                                Toggle::make('has_variants')
                                    ->label('Produk ini memiliki beberapa varian (warna, ukuran, dll.)')
                                    ->reactive()
                                    ->dehydrated(false),

                                // If has_variants = true
                                Repeater::make('variants')
                                    ->visible(fn (Get $get): bool => (bool) $get('has_variants'))
                                    ->schema([
                                        Grid::make(3)
                                            ->schema([
                                                TextInput::make('name')
                                                    ->required()
                                                    ->label('Nama Varian (misal: Hitam, Natural)'),

                                                TextInput::make('sku')
                                                    ->unique(ProductVariant::class, 'sku', ignoreRecord: true)
                                                    ->label('SKU (Barcode/Kode Varian)'),

                                                TextInput::make('barcode')
                                                    ->label('Barcode EAN/UPC'),
                                            ]),

                                        Grid::make(3)
                                            ->schema([
                                                TextInput::make('price')
                                                    ->numeric()
                                                    ->required()
                                                    ->prefix('Rp')
                                                    ->label('Harga Jual Dasar'),

                                                TextInput::make('cost_price')
                                                    ->numeric()
                                                    ->required()
                                                    ->prefix('Rp')
                                                    ->label('Harga Beli Dasar'),

                                                TextInput::make('hpp')
                                                    ->numeric()
                                                    ->prefix('Rp')
                                                    ->label('HPP Awal setelah Ongkir')
                                                    ->placeholder('Bila kosong, disamakan dengan harga beli'),
                                            ]),

                                        Section::make('Harga Tingkatan (Tier Prices) untuk Varian Ini')
                                            ->schema($variantTierFields)
                                            ->collapsed(),

                                        Section::make('Harga Khusus Cabang untuk Varian Ini')
                                            ->schema($variantBranchPriceFields)
                                            ->collapsed(),

                                        Section::make('Stok Gudang per Cabang untuk Varian Ini')
                                            ->schema($variantBranchStockFields)
                                            ->collapsed(),
                                    ])
                                    ->columns(1)
                                    ->label('Daftar Varian Produk'),

                                // If has_variants = false
                                Grid::make(3)
                                    ->visible(fn (Get $get): bool => ! $get('has_variants'))
                                    ->schema([
                                        TextInput::make('sku')
                                            ->unique(ProductVariant::class, 'sku', ignoreRecord: true)
                                            ->label('SKU (Barcode/Kode Barang)'),

                                        TextInput::make('barcode')
                                            ->label('Barcode EAN/UPC'),

                                        TextInput::make('price')
                                            ->numeric()
                                            ->required(fn (Get $get): bool => ! $get('has_variants'))
                                            ->prefix('Rp')
                                            ->label('Harga Jual Dasar'),

                                        TextInput::make('cost_price')
                                            ->numeric()
                                            ->required(fn (Get $get): bool => ! $get('has_variants'))
                                            ->prefix('Rp')
                                            ->label('Harga Beli Dasar'),

                                        TextInput::make('hpp')
                                            ->numeric()
                                            ->prefix('Rp')
                                            ->label('HPP Awal setelah Ongkir')
                                            ->placeholder('Bila kosong, disamakan dengan harga beli'),
                                    ]),

                                Section::make('Harga Tingkatan (Tier Prices)')
                                    ->visible(fn (Get $get): bool => ! $get('has_variants'))
                                    ->schema($tierFields)
                                    ->collapsed(),

                                Section::make('Harga Khusus Cabang')
                                    ->visible(fn (Get $get): bool => ! $get('has_variants'))
                                    ->schema($branchPriceFields)
                                    ->collapsed(),

                                Section::make('Stok Gudang per Cabang')
                                    ->visible(fn (Get $get): bool => ! $get('has_variants'))
                                    ->schema($branchStockFields)
                                    ->collapsed(),
                            ]),

                        Tab::make('Detail Jasa')
                            ->visible(fn (Get $get): bool => $get('type') === 'service')
                            ->schema([
                                Grid::make(3)
                                    ->schema([
                                        TextInput::make('sku')
                                            ->unique(ProductVariant::class, 'sku', ignoreRecord: true)
                                            ->label('SKU (Kode Jasa)'),

                                        TextInput::make('barcode')
                                            ->label('Barcode EAN/UPC'),

                                        TextInput::make('price')
                                            ->numeric()
                                            ->required(fn (Get $get): bool => $get('type') === 'service')
                                            ->prefix('Rp')
                                            ->label('Harga Jual Dasar'),

                                        TextInput::make('cost_price')
                                            ->numeric()
                                            ->prefix('Rp')
                                            ->label('Biaya/Modal Jasa'),

                                        TextInput::make('hpp')
                                            ->numeric()
                                            ->prefix('Rp')
                                            ->label('HPP Jasa'),
                                    ]),

                                Section::make('Harga Tingkatan (Tier Prices) untuk Jasa')
                                    ->schema($tierFields)
                                    ->collapsed(),

                                Section::make('Harga Khusus Cabang untuk Jasa')
                                    ->schema($branchPriceFields)
                                    ->collapsed(),
                            ]),

                        Tab::make('Komponen Bundling')
                            ->visible(fn (Get $get): bool => $get('type') === 'bundle')
                            ->schema([
                                Grid::make(3)
                                    ->schema([
                                        TextInput::make('sku')
                                            ->unique(ProductVariant::class, 'sku', ignoreRecord: true)
                                            ->label('SKU Paket (Bundling)'),

                                        TextInput::make('barcode')
                                            ->label('Barcode EAN/UPC'),

                                        TextInput::make('price')
                                            ->numeric()
                                            ->required(fn (Get $get): bool => $get('type') === 'bundle')
                                            ->prefix('Rp')
                                            ->label('Harga Jual Paket'),

                                        TextInput::make('cost_price')
                                            ->numeric()
                                            ->prefix('Rp')
                                            ->label('Harga Beli Dasar Paket'),

                                        TextInput::make('hpp')
                                            ->numeric()
                                            ->prefix('Rp')
                                            ->label('HPP Awal Paket'),
                                    ]),

                                Section::make('Harga Tingkatan (Tier Prices) untuk Paket')
                                    ->schema($tierFields)
                                    ->collapsed(),

                                Section::make('Harga Khusus Cabang untuk Paket')
                                    ->schema($branchPriceFields)
                                    ->collapsed(),

                                Repeater::make('bundle_items')
                                    ->schema([
                                        Select::make('child_variant_id')
                                            ->required()
                                            ->options(function () {
                                                return ProductVariant::whereHas('product', function ($q) {
                                                    $q->whereIn('type', ['physical', 'service']);
                                                })
                                                ->get()
                                                ->mapWithKeys(function ($v) {
                                                    $name = $v->product->name . ($v->name ? ' (' . $v->name . ')' : '');
                                                    return [$v->id => $name . ' [' . ($v->sku ?? 'No SKU') . ']'];
                                                });
                                            })
                                            ->searchable()
                                            ->preload()
                                            ->label('Pilih Produk Fisik/Jasa'),

                                        TextInput::make('quantity')
                                            ->numeric()
                                            ->required()
                                            ->default(1)
                                            ->minValue(1)
                                            ->label('Jumlah Qty'),
                                    ])
                                    ->columns(2)
                                    ->label('Daftar Item dalam Paket Bundling ini')
                                    ->placeholder('Tambahkan item pembentuk paket...'),
                            ]),
                    ]),
            ]);
    }
}
