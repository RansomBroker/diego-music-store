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
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Hidden;
use Filament\Schemas\Components\Group;
use App\Models\Supplier;
use App\Models\ProductVariant;
use App\Models\Unit;
use App\Helpers\FormatHelper;
use Filament\Forms\Get;

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

                                Toggle::make('enable_tax')
                                    ->label('Aktifkan Pajak')
                                    ->inline(false)
                                    ->live()
                                    ->afterStateUpdated(function ($state, callable $set, $get) {
                                        if (!$state) {
                                            $set('tax_mode', 'ITEM');
                                            $set('tax_rate', 0);
                                            $set('discount_amount', 0);
                                            
                                            // Reset tax_rate on all items in repeater
                                            $items = $get('items') ?? [];
                                            foreach ($items as $key => $item) {
                                                $set("items.{$key}.tax_rate", 0);
                                            }
                                        }
                                    }),

                                Select::make('item_discount_type')
                                    ->label('Tipe Diskon Item')
                                    ->options([
                                        'fixed' => 'Nominal (Rp)',
                                        'percent' => 'Persentase (%)',
                                    ])
                                    ->default('fixed')
                                    ->selectablePlaceholder(false)
                                    ->live(),
                            ]),

                        Textarea::make('notes')
                            ->label('Catatan PO')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),

                Section::make('Pajak & Diskon Global (Header)')
                    ->schema([
                        Hidden::make('tax_mode')->default('GLOBAL'),
                        Grid::make(3)
                            ->schema([
                                Select::make('discount_type')
                                    ->label('Tipe Diskon Global')
                                    ->options([
                                        'fixed' => 'Nominal (Rp)',
                                        'percent' => 'Persentase (%)',
                                    ])
                                    ->default('fixed')
                                    ->selectablePlaceholder(false)
                                    ->live(),

                                TextInput::make('discount_value')
                                    ->label('Diskon Global')
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
                                    ->visible(fn ($get) => $get('enable_tax'))
                                    ->reactive(),
                            ]),
                    ])
                    ->columnSpanFull(),

                Section::make('Informasi Pengiriman (Logistik)')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Select::make('shipping_borne_by')
                                    ->label('Pembebanan Ongkir')
                                    ->options([
                                        'self_direct' => 'Langsung (Ditagih Supplier)',
                                        'third_party' => 'Pihak Ke-3 (Ekspedisi)',
                                        'supplier' => 'Ditanggung Supplier (Free Ongkir)',
                                    ])
                                    ->default('self_direct')
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        if ($state === 'supplier') {
                                            $set('other_cost', 0);
                                            $set('shipping_carrier_name', null);
                                        }
                                    }),

                                TextInput::make('shipping_carrier_name')
                                    ->label('Nama Ekspedisi (Pihak Ke-3)')
                                    ->placeholder('Misal: JNE, J&T, GoSend')
                                    ->required(fn ($get) => $get('shipping_borne_by') === 'third_party')
                                    ->visible(fn ($get) => $get('shipping_borne_by') === 'third_party'),

                                TextInput::make('other_cost')
                                    ->label('Biaya Kirim (Ongkir)')
                                    ->numeric()
                                    ->default(0)
                                    ->prefix('Rp')
                                    ->required(fn ($get) => $get('shipping_borne_by') !== 'supplier')
                                    ->visible(fn ($get) => $get('shipping_borne_by') !== 'supplier')
                                    ->reactive(),
                            ]),
                    ])
                    ->columnSpanFull(),

                Section::make('Daftar Barang')
                    ->schema([

                        Group::make([
                            Placeholder::make('items_headers')
                                ->hiddenLabel()
                                ->content(fn () => view('backoffice.purchase-orders.items-table-header'))
                                ->extraAttributes([
                                    'style' => 'min-width: 1350px;'
                                ]),

                            Repeater::make('items')
                                ->hiddenLabel()
                                ->reorderable(false)
                                ->schema([
                                    Group::make([
                                        Select::make('product_variant_id')
                                            ->hiddenLabel()
                                            ->placeholder('Pilih Produk / Varian')
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
                                            ->live()
                                            ->afterStateUpdated(function ($state, callable $set, $get) {
                                                if ($state) {
                                                    $variant = ProductVariant::find($state);
                                                    if ($variant) {
                                                        $set('price', $variant->cost_price ?? 0);
                                                        $unitId = $variant->product?->unit_id;
                                                        $set('unit_id', $unitId);

                                                        $qty = intval($get('quantity') ?? 1);
                                                        if ($unitId) {
                                                            $unit = Unit::find($unitId);
                                                            if ($unit) {
                                                                $factor = $unit->conversion_factor ?? 1;
                                                                $set('qty_base', ($qty * $factor) . ' ' . ($unit->baseUnit?->code ?: $unit->code));
                                                            }
                                                        }
                                                    }
                                                }
                                            }),

                                        Select::make('unit_id')
                                            ->hiddenLabel()
                                            ->placeholder('Satuan')
                                            ->required()
                                            ->options(function ($get) {
                                                $variantId = $get('product_variant_id');
                                                if (!$variantId) {
                                                    return [];
                                                }
                                                $variant = ProductVariant::find($variantId);
                                                if (!$variant || !$variant->product) {
                                                    return [];
                                                }
                                                $productUnit = $variant->product->unit;
                                                if (!$productUnit) {
                                                    return [];
                                                }
                                                
                                                $baseUnitId = $productUnit->base_unit_id ?: $productUnit->id;
                                                
                                                $units = Unit::query()
                                                    ->where(function ($query) use ($baseUnitId) {
                                                        $query->where('id', $baseUnitId)
                                                              ->orWhere('base_unit_id', $baseUnitId);
                                                    })
                                                    ->where('is_active', true)
                                                    ->get();
                                                    
                                                return $units->mapWithKeys(function ($unit) {
                                                    $label = $unit->name;
                                                    if ($unit->base_unit_id) {
                                                        $label .= " (isi {$unit->conversion_factor})";
                                                    }
                                                    return [$unit->id => $label];
                                                })->toArray();
                                            })
                                            ->live()
                                            ->afterStateUpdated(function ($state, $get, $set) {
                                                $qty = floatval($get('quantity') ?? 1);
                                                if ($state && $qty) {
                                                    $unit = Unit::find($state);
                                                    if ($unit) {
                                                        $factor = $unit->conversion_factor ?? 1;
                                                        $set('qty_base', round($qty * $factor, 2));
                                                    }
                                                }
                                            }),

                                        TextInput::make('quantity')
                                            ->hiddenLabel()
                                            ->placeholder('Qty')
                                            ->numeric()
                                            ->required()
                                            ->default(1)
                                            ->live()
                                            ->afterStateUpdated(function ($state, $get, $set) {
                                                $unitId = $get('unit_id');
                                                if ($state && $unitId) {
                                                    $unit = Unit::find($unitId);
                                                    if ($unit) {
                                                        $factor = $unit->conversion_factor ?? 1;
                                                        $calculatedBase = intval(round($state * $factor));
                                                        if (intval($get('qty_base')) !== $calculatedBase) {
                                                            $set('qty_base', $calculatedBase);
                                                        }
                                                    }
                                                }
                                            }),

                                        TextInput::make('qty_base')
                                            ->hiddenLabel()
                                            ->placeholder('Qty Dasar')
                                            ->numeric()
                                            ->live()
                                            ->suffix(function ($get) {
                                                $unitId = $get('unit_id');
                                                if ($unitId) {
                                                    $unit = Unit::find($unitId);
                                                    return $unit?->baseUnit?->code ?: $unit?->code;
                                                }
                                                return null;
                                            })
                                            ->afterStateUpdated(function ($state, $get, $set) {
                                                $unitId = $get('unit_id');
                                                if ($state && $unitId) {
                                                    $unit = Unit::find($unitId);
                                                    if ($unit) {
                                                        $factor = $unit->conversion_factor ?? 1;
                                                        $calculatedQty = intval(ceil($state / $factor));
                                                        if (intval($get('quantity')) !== $calculatedQty) {
                                                            $set('quantity', $calculatedQty);
                                                        }
                                                    }
                                                }
                                            })
                                            ->afterStateHydrated(function ($set, $get) {
                                                $qty = floatval($get('quantity') ?? 0);
                                                $unitId = $get('unit_id');
                                                if ($qty > 0 && $unitId) {
                                                    $unit = Unit::find($unitId);
                                                    if ($unit) {
                                                        $factor = $unit->conversion_factor ?? 1;
                                                        $set('qty_base', intval(round($qty * $factor)));
                                                    }
                                                }
                                            }),

                                        TextInput::make('price')
                                            ->hiddenLabel()
                                            ->placeholder('Harga Beli')
                                            ->numeric()
                                            ->required()
                                            ->prefix('Rp')
                                            ->reactive(),

                                        TextInput::make('discount_value')
                                            ->hiddenLabel()
                                            ->placeholder('0')
                                            ->numeric()
                                            ->default(0)
                                            ->prefix(fn ($get) => $get('../../item_discount_type') === 'percent' ? null : 'Rp')
                                            ->suffix(fn ($get) => $get('../../item_discount_type') === 'percent' ? '%' : null)
                                            ->reactive()
                                            ->columnSpan(1),



                                        Placeholder::make('item_subtotal')
                                            ->hiddenLabel()
                                            ->content(function ($get) {
                                                $qty = intval($get('quantity') ?? 0);
                                                $price = intval($get('price') ?? 0);
                                                $discType = $get('../../item_discount_type') ?? 'fixed';
                                                $discVal = intval($get('discount_value') ?? 0);
                                                $disc = $discType === 'percent' ? (int) round(($qty * $price) * ($discVal / 100)) : $discVal;
                                                
                                                $subtotal = ($qty * $price) - $disc;
                                                
                                                return FormatHelper::rupiah($subtotal);
                                            }),
                                    ])
                                    ->columns(1)
                                    ->extraAttributes([
                                        'class' => 'po-items-grid'
                                    ])
                                ])
                                ->minItems(1)
                                ->extraAttributes([
                                    'style' => 'min-width: 1350px;'
                                ]),
                        ])
                        ->extraAttributes([
                            'class' => 'overflow-x-auto pb-4 po-items-table-container',
                            'style' => 'width: 100%;',
                            'x-data' => '{}',
                            'x-on:scroll' => "\$el.querySelectorAll('[data-frozen-header]').forEach(function(el){ el.style.transform = 'translateX(' + \$el.scrollLeft + 'px)'; })",
                        ]),
                    ])
                    ->columnSpanFull(),

                Section::make('Ringkasan Total Harga')
                    ->schema([
                        Placeholder::make('total_summary_header')
                            ->label('')
                            ->content(function ($get) {
                                $items = $get('items') ?? [];
                                $globalTaxRate = intval($get('tax_rate') ?? 0);
                                
                                $totalAmount = 0;
                                $totalTax = 0;
                                $uomTotals = [];
                                $baseUnitTotals = [];
                                
                                foreach ($items as $item) {
                                    $qty = intval($item['quantity'] ?? 0);
                                    $price = intval($item['price'] ?? 0);
                                    $itemDiscType = $get('item_discount_type') ?? 'fixed';
                                    $itemDiscVal = intval($item['discount_value'] ?? 0);
                                    $disc = $itemDiscType === 'percent' ? (int) round(($qty * $price) * ($itemDiscVal / 100)) : $itemDiscVal;
                                    
                                    $subtotalBeforeTax = ($qty * $price) - $disc;
                                    $itemTaxAmount = (int) round($subtotalBeforeTax * ($globalTaxRate / 100));
                                    
                                    $totalAmount += $subtotalBeforeTax;
                                    $totalTax += $itemTaxAmount;

                                    $unitId = $item['unit_id'] ?? null;
                                    if ($qty > 0 && $unitId) {
                                        $unit = Unit::find($unitId);
                                        if ($unit) {
                                            $uomTotals[$unit->name] = ($uomTotals[$unit->name] ?? 0) + $qty;
                                            
                                            $baseUnit = $unit->baseUnit ?: $unit;
                                            $convertedQty = $qty * ($unit->conversion_factor ?? 1);
                                            $baseUnitTotals[$baseUnit->code] = ($baseUnitTotals[$baseUnit->code] ?? 0) + $convertedQty;
                                        }
                                    }
                                }
                                
                                $physicalQtyString = collect($uomTotals)->map(fn($q, $u) => "{$q} {$u}")->implode(', ') ?: '-';
                                $smallestQtyString = collect($baseUnitTotals)->map(fn($q, $c) => "{$q} {$c}")->implode(', ') ?: '-';
                                
                                $discHeaderType = $get('discount_type') ?? 'fixed';
                                $discHeaderVal = intval($get('discount_value') ?? 0);
                                $discHeader = $discHeaderType === 'percent' ? (int) round($totalAmount * ($discHeaderVal / 100)) : $discHeaderVal;
                                $otherCost = intval($get('other_cost') ?? 0);
                                $shippingBorneBy = $get('shipping_borne_by') ?? 'self_direct';
                                
                                $shippingCostInGrandTotal = ($shippingBorneBy === 'self_direct') ? $otherCost : 0;
                                $grandTotal = $totalAmount - $discHeader + $totalTax + $shippingCostInGrandTotal;
                                
                                return view('backoffice.purchase-orders.summary-placeholder', [
                                    'totalAmount' => $totalAmount,
                                    'taxAmount' => $totalTax,
                                    'discountAmount' => $discHeader,
                                    'otherCost' => $otherCost,
                                    'shippingBorneBy' => $shippingBorneBy,
                                    'grandTotal' => $grandTotal,
                                    'physicalQtyString' => $physicalQtyString,
                                    'smallestQtyString' => $smallestQtyString,
                                ]);
                            })
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
