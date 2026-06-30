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
use Filament\Schemas\Components\Utilities\Get;
use Filament\Support\Enums\Width;
use Filament\Schemas\Schema;
use App\Helpers\ProductHelper;
use Filament\Forms\Components\Placeholder;

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

        // 3. Generate fields for branch stocks (default / single variant)
        $branchStockFields = [];
        foreach ($branches as $branch) {
            $branchStockFields[] = TextInput::make('branch_stocks.' . $branch->id)
                ->numeric()
                ->label('Stok Cabang: ' . $branch->name)
                ->disabled(fn (string $context): bool => $context === 'edit')
                ->default(0);
        }

        // 4. Generate variant-specific tier and stock fields for repeater
        $variantTierFields = [];
        foreach ($pricingTiers as $tier) {
            $variantTierFields[] = TextInput::make('tier_prices.' . $tier->id)
                ->numeric()
                ->label('Harga Tier: ' . $tier->name)
                ->prefix('Rp');
        }

        $variantBranchStockFields = [];
        foreach ($branches as $branch) {
            $variantBranchStockFields[] = TextInput::make('branch_stocks.' . $branch->id)
                ->numeric()
                ->label('Stok Cabang: ' . $branch->name)
                ->disabled(fn (string $context): bool => $context === 'edit')
                ->default(0);
        }

        return $schema
            ->columns(1)
            ->components([
                Tabs::make('Product Details')
                    ->columnSpan('full')
                    ->maxWidth(Width::Full)
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

                                        Select::make('unit_id')
                                            ->relationship('unit', 'name', modifyQueryUsing: fn ($query) => $query->where('is_active', true))
                                            ->required()
                                            ->searchable()
                                            ->preload()
                                            ->label('Satuan Produk'),

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
                                                    ->default(fn () => ProductHelper::generateUniqueSku())
                                                    ->label('SKU (Barcode/Kode Varian)'),

                                                TextInput::make('barcode')
                                                    ->default(fn () => ProductHelper::generateUniqueBarcode())
                                                    ->label('Barcode EAN/UPC'),
                                            ]),

                                        Grid::make(4)
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
                                                    ->reactive()
                                                    ->afterStateUpdated(fn (Get $get, $set) => $set('hpp', (int)$get('cost_price') + (int)$get('estimated_shipping')))
                                                    ->label('Harga Beli Dasar'),

                                                TextInput::make('estimated_shipping')
                                                    ->numeric()
                                                    ->prefix('Rp')
                                                    ->reactive()
                                                    ->afterStateUpdated(fn (Get $get, $set) => $set('hpp', (int)$get('cost_price') + (int)$get('estimated_shipping')))
                                                    ->dehydrated(false)
                                                    ->default(0)
                                                    ->label('Estimasi Ongkir per Unit'),

                                                TextInput::make('hpp')
                                                    ->numeric()
                                                    ->prefix('Rp')
                                                    ->label('HPP Awal setelah Ongkir'),
                                            ]),

                                        Section::make('Harga Tingkatan (Tier Prices) untuk Varian Ini')
                                            ->description(count($pricingTiers) === 0 ? 'Belum ada tingkatan harga terdaftar. Silakan tambahkan tingkatan harga terlebih dahulu.' : null)
                                            ->schema($variantTierFields),

                                        Section::make('Stok Gudang per Cabang untuk Varian Ini')
                                            ->description(count($branches) === 0 ? 'Belum ada cabang terdaftar. Silakan tambahkan cabang terlebih dahulu.' : null)
                                            ->schema($variantBranchStockFields),
                                    ])
                                    ->columns(1)
                                    ->label('Daftar Varian Produk'),

                                // If has_variants = false
                                Grid::make(3)
                                    ->visible(fn (Get $get): bool => ! $get('has_variants'))
                                    ->schema([
                                        TextInput::make('sku')
                                            ->unique(ProductVariant::class, 'sku', ignoreRecord: true)
                                            ->default(fn () => ProductHelper::generateUniqueSku())
                                            ->label('SKU (Barcode/Kode Barang)'),

                                        TextInput::make('barcode')
                                            ->default(fn () => ProductHelper::generateUniqueBarcode())
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
                                            ->reactive()
                                            ->afterStateUpdated(fn (Get $get, $set) => $set('hpp', (int)$get('cost_price') + (int)$get('estimated_shipping')))
                                            ->label('Harga Beli Dasar'),

                                        TextInput::make('estimated_shipping')
                                            ->numeric()
                                            ->prefix('Rp')
                                            ->reactive()
                                            ->afterStateUpdated(fn (Get $get, $set) => $set('hpp', (int)$get('cost_price') + (int)$get('estimated_shipping')))
                                            ->dehydrated(false)
                                            ->default(0)
                                            ->label('Estimasi Ongkir per Unit'),

                                        TextInput::make('hpp')
                                            ->numeric()
                                            ->prefix('Rp')
                                            ->label('HPP Awal setelah Ongkir'),
                                    ]),

                                Section::make('Harga Tingkatan (Tier Prices)')
                                     ->visible(fn (Get $get): bool => ! $get('has_variants'))
                                     ->description(count($pricingTiers) === 0 ? 'Belum ada tingkatan harga terdaftar. Silakan tambahkan tingkatan harga terlebih dahulu.' : null)
                                     ->schema($tierFields),

                                Section::make('Stok Gudang per Cabang')
                                     ->visible(fn (Get $get): bool => ! $get('has_variants'))
                                     ->description(count($branches) === 0 ? 'Belum ada cabang terdaftar. Silakan tambahkan cabang terlebih dahulu.' : null)
                                     ->schema($branchStockFields),
                            ]),

                        Tab::make('Detail Jasa')
                            ->visible(fn (Get $get): bool => $get('type') === 'service')
                            ->schema([
                                Grid::make(3)
                                    ->schema([
                                        TextInput::make('sku')
                                            ->unique(ProductVariant::class, 'sku', ignoreRecord: true)
                                            ->default(fn () => ProductHelper::generateUniqueSku())
                                            ->label('SKU (Kode Jasa)'),

                                        TextInput::make('barcode')
                                            ->default(fn () => ProductHelper::generateUniqueBarcode())
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
                            ]),

                        Tab::make('Komponen Bundling')
                            ->visible(fn (Get $get): bool => $get('type') === 'bundle')
                            ->schema([
                                Grid::make(3)
                                    ->schema([
                                        TextInput::make('sku')
                                            ->unique(ProductVariant::class, 'sku', ignoreRecord: true)
                                            ->default(fn () => ProductHelper::generateUniqueSku())
                                            ->label('SKU Paket (Bundling)'),

                                        TextInput::make('barcode')
                                            ->default(fn () => ProductHelper::generateUniqueBarcode())
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
                                    ->label('Daftar Item dalam Paket Bundling ini'),
                            ]),
                        Tab::make('Akuntansi')
                            ->schema([
                                Grid::make(3)
                                    ->schema([
                                        Select::make('inventory_account_id')
                                            ->label('Akun Persediaan')
                                            ->relationship('inventoryAccount', 'name', modifyQueryUsing: fn ($query) => $query->where('is_active', true)->where('is_header', false))
                                            ->placeholder('Pilih Akun Persediaan (Default: 1-1300)')
                                            ->searchable()
                                            ->preload(),

                                        Select::make('sales_account_id')
                                            ->label('Akun Penjualan')
                                            ->relationship('salesAccount', 'name', modifyQueryUsing: fn ($query) => $query->where('is_active', true)->where('is_header', false))
                                            ->placeholder('Pilih Akun Penjualan (Default: 4-1000)')
                                            ->searchable()
                                            ->preload(),

                                        Select::make('cogs_account_id')
                                            ->label('Akun HPP')
                                            ->relationship('cogsAccount', 'name', modifyQueryUsing: fn ($query) => $query->where('is_active', true)->where('is_header', false))
                                            ->placeholder('Pilih Akun HPP (Default: 5-1000)')
                                            ->searchable()
                                            ->preload(),
                                    ]),
                            ]),
                        Tab::make('Kartu Stok')
                            ->visible(fn (string $context): bool => $context === 'edit')
                            ->schema([
                                Placeholder::make('stock_movement_history')
                                    ->label('Riwayat Pergerakan Stok (Kartu Stok)')
                                    ->content(function ($record) {
                                        if (!$record) return 'Belum ada data.';

                                        $variantIds = $record->variants()->pluck('id')->toArray();
                                        $movements = \App\Models\StockMovement::whereIn('product_variant_id', $variantIds)
                                            ->with(['productVariant', 'branch'])
                                            ->latest()
                                            ->get();

                                        if ($movements->isEmpty()) {
                                            return 'Belum ada riwayat pergerakan stok untuk produk ini.';
                                        }

                                        return view('backoffice.products.stock-movements-table', [
                                            'movements' => $movements,
                                        ]);
                                    })
                            ]),
                    ]),
            ]);
    }
}
