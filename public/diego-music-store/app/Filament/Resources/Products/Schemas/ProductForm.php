<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Models\PricingTier;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StockMovement;
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
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Support\Enums\Width;
use Filament\Schemas\Schema;
use App\Helpers\ProductHelper;
use Filament\Forms\Components\Placeholder;
use Illuminate\Validation\Rules\Unique;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        // 1. Fetch dynamic pricing tiers
        $pricingTiers = PricingTier::all();

        $skuUniqueRule = function (Unique $rule, ?Product $record) {
            if ($record) {
                $variantIds = $record->variants()->pluck('id')->toArray();
                return $rule->whereNotIn('id', $variantIds);
            }
            return $rule;
        };

        // 2. Generate fields for pricing tiers (default / single variant)
        $tierFields = [];
        foreach ($pricingTiers as $tier) {
            $tierFields[] = TextInput::make('tier_prices.' . $tier->id)
                ->numeric()
                ->label('Harga Tier: ' . $tier->name)
                ->prefix('Rp')
                ->readOnly($tier->price_follows_hpp);
        }

        // 3. Generate variant-specific tier fields for repeater
        $variantTierFields = [];
        foreach ($pricingTiers as $tier) {
            $variantTierFields[] = TextInput::make('tier_prices.' . $tier->id)
                ->numeric()
                ->hiddenLabel()
                ->placeholder($tier->name)
                ->prefix('Rp')
                ->readOnly($tier->price_follows_hpp);
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
                                Group::make([
                                    Placeholder::make('variant_headers')
                                        ->hiddenLabel()
                                        ->content(fn () => view('backoffice.products.variant-table-header', [
                                            'pricingTiers' => $pricingTiers,
                                        ]))
                                        ->extraAttributes([
                                            'style' => 'min-width: ' . (1210 + (140 * count($pricingTiers)) + 56) . 'px;'
                                        ]),

                                    Repeater::make('variants')
                                        ->hiddenLabel()
                                        ->reorderable(false)
                                        ->schema([
                                            Group::make([
                                                TextInput::make('name')
                                                    ->required()
                                                    ->hiddenLabel()
                                                    ->placeholder('Nama Varian'),

                                                TextInput::make('sku')
                                                    ->unique(ProductVariant::class, 'sku', ignoreRecord: false, modifyRuleUsing: $skuUniqueRule)
                                                    ->default(fn () => ProductHelper::generateUniqueSku())
                                                    ->hiddenLabel()
                                                    ->placeholder('SKU'),

                                                TextInput::make('barcode')
                                                    ->default(fn () => ProductHelper::generateUniqueBarcode())
                                                    ->hiddenLabel()
                                                    ->placeholder('Barcode'),

                                                TextInput::make('price')
                                                    ->numeric()
                                                    ->required()
                                                    ->prefix('Rp')
                                                    ->hiddenLabel()
                                                    ->placeholder('Harga Jual'),

                                                TextInput::make('cost_price')
                                                    ->numeric()
                                                    ->required()
                                                    ->prefix('Rp')
                                                    ->reactive()
                                                    ->afterStateUpdated(function (Get $get, $set) use ($pricingTiers) {
                                                        $hpp = (int)$get('cost_price') + (int)$get('estimated_shipping');
                                                        $set('hpp', $hpp);
                                                        foreach ($pricingTiers as $tier) {
                                                            if ($tier->price_follows_hpp) {
                                                                $set('tier_prices.' . $tier->id, $hpp);
                                                            }
                                                        }
                                                    })
                                                    ->hiddenLabel()
                                                    ->placeholder('Harga Beli'),

                                                TextInput::make('estimated_shipping')
                                                    ->numeric()
                                                    ->prefix('Rp')
                                                    ->readOnly()
                                                    ->dehydrated(false)
                                                    ->default(0)
                                                    ->hiddenLabel()
                                                    ->placeholder('Ongkir'),

                                                TextInput::make('hpp')
                                                    ->numeric()
                                                    ->prefix('Rp')
                                                    ->readOnly()
                                                    ->hiddenLabel()
                                                    ->placeholder('HPP'),

                                                ...$variantTierFields
                                            ])
                                            ->columns(1)
                                            ->extraAttributes([
                                                'class' => 'variant-grid'
                                            ])
                                        ])
                                        ->columns(1)
                                        ->extraAttributes([
                                            'style' => 'min-width: ' . (1210 + (140 * count($pricingTiers)) + 56) . 'px;'
                                        ]),
                                ])
                                ->visible(fn (Get $get): bool => (bool) $get('has_variants'))
                                ->extraAttributes([
                                    'class' => 'overflow-x-auto pb-4 variant-table-container',
                                    'style' => 'width: 100%;'
                                ]),

                                // If has_variants = false
                                Grid::make(3)
                                    ->visible(fn (Get $get): bool => ! $get('has_variants'))
                                    ->schema([
                                        TextInput::make('sku')
                                            ->unique(ProductVariant::class, 'sku', ignoreRecord: false, modifyRuleUsing: $skuUniqueRule)
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
                                            ->afterStateUpdated(function (Get $get, $set) use ($pricingTiers) {
                                                $hpp = (int)$get('cost_price') + (int)$get('estimated_shipping');
                                                $set('hpp', $hpp);
                                                foreach ($pricingTiers as $tier) {
                                                    if ($tier->price_follows_hpp) {
                                                        $set('tier_prices.' . $tier->id, $hpp);
                                                    }
                                                }
                                            })
                                            ->label('Harga Beli Dasar'),

                                        TextInput::make('estimated_shipping')
                                            ->numeric()
                                            ->prefix('Rp')
                                            ->readOnly()
                                            ->dehydrated(false)
                                            ->default(0)
                                            ->label('Estimasi Ongkir per Unit'),

                                        TextInput::make('hpp')
                                            ->numeric()
                                            ->prefix('Rp')
                                            ->readOnly()
                                            ->label('HPP Awal setelah Ongkir'),
                                    ]),

                                Group::make([
                                    Placeholder::make('single_tier_prices_title')
                                        ->hiddenLabel()
                                        ->content(fn () => new \Illuminate\Support\HtmlString('<div class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-3 mt-6 border-b pb-2 border-gray-200 dark:border-gray-700">Harga Tingkatan (Tier Prices)</div>')),
                                    Grid::make(3)
                                        ->schema($tierFields),
                                ])
                                ->visible(fn (Get $get): bool => ! $get('has_variants')),
                            ]),

                        Tab::make('Detail Jasa')
                            ->visible(fn (Get $get): bool => $get('type') === 'service')
                            ->schema([
                                Grid::make(3)
                                    ->schema([
                                        TextInput::make('sku')
                                            ->unique(ProductVariant::class, 'sku', ignoreRecord: false, modifyRuleUsing: $skuUniqueRule)
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
                                            ->unique(ProductVariant::class, 'sku', ignoreRecord: false, modifyRuleUsing: $skuUniqueRule)
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

                                Group::make([
                                    Placeholder::make('bundle_tier_prices_title')
                                        ->hiddenLabel()
                                        ->content(fn () => new \Illuminate\Support\HtmlString('<div class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-3 mt-6 border-b pb-2 border-gray-200 dark:border-gray-700">Harga Tingkatan (Tier Prices) untuk Paket</div>')),
                                    Grid::make(3)
                                        ->schema($tierFields),
                                 ]),

                                Group::make([
                                    Placeholder::make('bundle_headers')
                                        ->hiddenLabel()
                                        ->content(fn () => view('backoffice.products.bundle-table-header')),

                                    Repeater::make('bundle_items')
                                        ->hiddenLabel()
                                        ->reorderable(false)
                                        ->schema([
                                            Group::make([
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
                                                    ->hiddenLabel()
                                                    ->placeholder('Pilih Produk Fisik/Jasa'),

                                                TextInput::make('quantity')
                                                    ->numeric()
                                                    ->required()
                                                    ->default(1)
                                                    ->minValue(1)
                                                    ->hiddenLabel()
                                                    ->placeholder('Qty'),
                                            ])
                                            ->columns(1)
                                            ->extraAttributes([
                                                'class' => 'bundle-grid'
                                            ])
                                        ])
                                        ->columns(1),
                                ])
                                ->extraAttributes([
                                    'class' => 'bundle-table-container',
                                    'style' => 'width: 100%;'
                                ]),
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
                                        $movements = StockMovement::whereIn('product_variant_id', $variantIds)
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
