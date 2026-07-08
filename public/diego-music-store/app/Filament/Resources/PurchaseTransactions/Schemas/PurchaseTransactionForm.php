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
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Hidden;
use Filament\Schemas\Components\Group;
use App\Models\PurchaseOrder;
use App\Models\ProductVariant;
use App\Models\Unit;
use App\Helpers\FormatHelper;
use Filament\Forms\Get;

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
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, $set, $get) {
                                        $tempo = $get('tempo_days');
                                        if ($tempo !== null && $state) {
                                            $set('due_date', \Carbon\Carbon::parse($state)->addDays(intval($tempo))->format('Y-m-d'));
                                        }
                                    }),

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
                                    ->afterStateUpdated(function ($state, $set, $get) {
                                        if ($state) {
                                            $po = PurchaseOrder::with('items.productVariant.product')->find($state);
                                            if ($po) {
                                                $set('supplier_id', $po->supplier_id);
                                                $set('branch_id', $po->branch_id);
                                                $set('enable_tax', $po->enable_tax);
                                                $set('tax_rate', $po->tax_rate);
                                                $set('discount_type', $po->discount_type);
                                                $set('discount_value', $po->discount_value);
                                                $set('item_discount_type', $po->item_discount_type);
                                                $set('shipping_borne_by', $po->shipping_borne_by);
                                                $set('shipping_carrier_name', $po->shipping_carrier_name);
                                                $set('shipping_cost', $po->other_cost);
                                                
                                                // Auto-populate purchase type, tempo, and due date
                                                $poTerm = $po->payment_term;
                                                if ($poTerm && preg_match('/(\d+)\s*Hari/i', $poTerm, $matches)) {
                                                    $days = intval($matches[1]);
                                                    $set('purchase_type', 'Kredit');
                                                    $set('tempo_days', $days);
                                                    $txDate = $get('transaction_date') ?? now()->format('Y-m-d');
                                                    $set('due_date', \Carbon\Carbon::parse($txDate)->addDays($days)->format('Y-m-d'));
                                                } else {
                                                    $set('purchase_type', 'Tunai');
                                                    $set('tempo_days', null);
                                                    $set('due_date', null);
                                                }

                                                $items = [];
                                                foreach ($po->items as $item) {
                                                    $items[] = [
                                                        'product_variant_id' => $item->product_variant_id,
                                                        'qty_po' => $item->quantity,
                                                        'qty_received' => $item->quantity, // default to full PO qty
                                                        'unit_id' => $item->unit_id ?? $item->productVariant->product->unit_id,
                                                        'price' => $item->price,
                                                        'discount_value' => $item->discount_value,
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
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, $set) {
                                        if ($state === 'Tunai') {
                                            $set('tempo_days', null);
                                            $set('due_date', null);
                                        }
                                    }),

                                TextInput::make('invoice_number')
                                    ->label('No. Invoice Supplier'),

                                TextInput::make('delivery_note_number')
                                    ->label('No. Surat Jalan'),

                                TextInput::make('tax_invoice_no')
                                    ->label('No. Faktur Pajak'),

                                DatePicker::make('invoice_date')
                                    ->label('Tanggal Invoice'),

                                TextInput::make('tempo_days')
                                    ->label('Durasi Jatuh Tempo')
                                    ->numeric()
                                    ->suffix('Hari')
                                    ->visible(fn ($get) => $get('purchase_type') === 'Kredit')
                                    ->required(fn ($get) => $get('purchase_type') === 'Kredit')
                                    ->reactive()
                                    ->dehydrated(false)
                                    ->afterStateHydrated(function ($component, $state, $get, $set) {
                                        $due = $get('due_date');
                                        $tx = $get('transaction_date');
                                        if ($due && $tx) {
                                            $diff = \Carbon\Carbon::parse($tx)->diffInDays(\Carbon\Carbon::parse($due), false);
                                            $set('tempo_days', $diff > 0 ? $diff : 0);
                                        }
                                    })
                                    ->afterStateUpdated(function ($state, $set, $get) {
                                        $txDate = $get('transaction_date');
                                        if ($txDate && $state !== null) {
                                            $set('due_date', \Carbon\Carbon::parse($txDate)->addDays(intval($state))->format('Y-m-d'));
                                        }
                                    }),

                                DatePicker::make('due_date')
                                    ->label('Tanggal Jatuh Tempo')
                                    ->visible(fn ($get) => $get('purchase_type') === 'Kredit')
                                    ->required(fn ($get) => $get('purchase_type') === 'Kredit')
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, $set, $get) {
                                        $txDate = $get('transaction_date');
                                        if ($txDate && $state) {
                                            $diff = \Carbon\Carbon::parse($txDate)->diffInDays(\Carbon\Carbon::parse($state), false);
                                            $set('tempo_days', $diff > 0 ? $diff : 0);
                                        }
                                    }),

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

                                Toggle::make('enable_tax')
                                    ->label('Aktifkan Pajak')
                                    ->inline(false)
                                    ->live()
                                    ->afterStateUpdated(function ($state, callable $set, $get) {
                                        if (!$state) {
                                            $set('discount', 0);
                                            $set('other_cost', 0);
                                            $set('pph_amount', 0);
                                            
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

                                TextInput::make('other_cost')
                                    ->label('Biaya Lain-Lain')
                                    ->numeric()
                                    ->default(0)
                                    ->prefix('Rp')
                                    ->visible(fn ($get) => $get('enable_tax'))
                                    ->reactive(),

                                TextInput::make('pph_amount')
                                    ->label('Potongan PPh')
                                    ->numeric()
                                    ->default(0)
                                    ->prefix('Rp')
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
                                            $set('shipping_cost', 0);
                                            $set('shipping_carrier_name', null);
                                        }
                                    }),

                                TextInput::make('shipping_carrier_name')
                                    ->label('Nama Ekspedisi (Pihak Ke-3)')
                                    ->placeholder('Misal: JNE, J&T, GoSend')
                                    ->required(fn ($get) => $get('shipping_borne_by') === 'third_party')
                                    ->visible(fn ($get) => $get('shipping_borne_by') === 'third_party'),

                                TextInput::make('shipping_cost')
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

                 Section::make('Daftar Barang Transaksi')
                    ->schema([

                        Group::make([
                            Placeholder::make('items_headers')
                                ->hiddenLabel()
                                ->content(fn () => view('backoffice.purchase-transactions.items-table-header'))
                                ->extraAttributes([
                                    'style' => 'min-width: 1800px;'
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

                                                        $qty = floatval($get('qty_received') ?? 0) + floatval($get('qty_bonus') ?? 0);
                                                        if ($unitId) {
                                                            $unit = Unit::find($unitId);
                                                            if ($unit) {
                                                                $factor = $unit->conversion_factor ?? 1;
                                                                $set('qty_base', round($qty * $factor, 2));
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
                                                $qty = floatval($get('qty_received') ?? 0) + floatval($get('qty_bonus') ?? 0);
                                                if ($state && $qty) {
                                                    $unit = Unit::find($state);
                                                    if ($unit) {
                                                        $factor = $unit->conversion_factor ?? 1;
                                                        $set('qty_base', round($qty * $factor, 2));
                                                    }
                                                }
                                            })
                                            ->dehydrated(),

                                        TextInput::make('qty_po')
                                            ->hiddenLabel()
                                            ->placeholder('Qty PO')
                                            ->numeric()
                                            ->disabled()
                                            ->dehydrated(),

                                        TextInput::make('qty_received')
                                            ->hiddenLabel()
                                            ->placeholder('Qty Terima')
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
                                                        $calculatedBase = intval(round((intval($state ?? 0) + intval($get('qty_bonus') ?? 0)) * $factor));
                                                        if (intval($get('qty_base')) !== $calculatedBase) {
                                                            $set('qty_base', $calculatedBase);
                                                        }
                                                    }
                                                }
                                            }),

                                         TextInput::make('qty_bonus')
                                             ->hiddenLabel()
                                             ->placeholder('Qty Bonus')
                                             ->numeric()
                                             ->default(0)
                                             ->live()
                                             ->afterStateUpdated(function ($state, $get, $set) {
                                                 $unitId = $get('unit_id');
                                                 if ($unitId) {
                                                     $unit = Unit::find($unitId);
                                                     if ($unit) {
                                                         $factor = $unit->conversion_factor ?? 1;
                                                         $totalQty = intval($get('qty_received') ?? 0) + intval($state ?? 0);
                                                         $calculatedBase = intval(round($totalQty * $factor));
                                                         if (intval($get('qty_base')) !== $calculatedBase) {
                                                             $set('qty_base', $calculatedBase);
                                                         }
                                                     }
                                                 }
                                             }),

                                        TextInput::make('qty_base')
                                            ->hiddenLabel()
                                            ->placeholder('Qty Terkecil')
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
                                                        if (intval($get('qty_received')) !== $calculatedQty) {
                                                            $set('qty_received', $calculatedQty);
                                                        }
                                                    }
                                                }
                                            })
                                            ->afterStateHydrated(function ($set, $get) {
                                                $qty = floatval($get('qty_received') ?? 0) + floatval($get('qty_bonus') ?? 0);
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
                                            ->reactive()
                                            ->readOnly(fn ($get) => !$get('update_cost_price'))
                                            ->suffixAction(
                                                \Filament\Actions\Action::make('view_pricing_tiers')
                                                    ->icon('heroicon-m-information-circle')
                                                    ->color('info')
                                                    ->tooltip('Lihat Perbandingan Tiers Harga')
                                                    ->modalHeading('Detail Perbandingan & Pricing Tiers')
                                                    ->modalSubmitAction(false)
                                                    ->modalCancelActionLabel('Tutup')
                                                    ->modalContent(fn ($get) => view('backoffice.purchase-transactions.pricing-tiers-comparison', [
                                                        'variantId' => $get('product_variant_id'),
                                                        'newPrice' => intval($get('price') ?? 0),
                                                    ]))
                                            ),

                                        Toggle::make('update_cost_price')
                                             ->hiddenLabel()
                                             ->default(false)
                                             ->live(),

                                        TextInput::make('discount_value')
                                            ->hiddenLabel()
                                            ->placeholder('0')
                                            ->numeric()
                                            ->default(0)
                                            ->prefix(fn ($get) => $get('../../item_discount_type') === 'percent' ? null : 'Rp')
                                            ->suffix(fn ($get) => $get('../../item_discount_type') === 'percent' ? '%' : null)
                                            ->reactive(),

                                        Placeholder::make('item_subtotal')
                                            ->hiddenLabel()
                                            ->content(function ($get) {
                                                $qty = intval($get('qty_received') ?? 0);
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
                                        'class' => 'pt-items-grid'
                                    ])
                                ])
                                ->minItems(1)
                                ->extraAttributes([
                                    'style' => 'min-width: 1800px;'
                                ]),
                        ])
                        ->extraAttributes([
                            'class' => 'overflow-x-auto pb-4 pt-items-table-container',
                            'style' => 'width: 100%;',
                            'x-data' => '{}',
                            'x-on:scroll' => "\$el.querySelectorAll('[data-frozen-header]').forEach(function(el){ el.style.transform = 'translateX(' + \$el.scrollLeft + 'px)'; })",
                        ]),
                    ])
                    ->columnSpanFull(),

                Section::make('Ringkasan Pembayaran & Akuntansi')
                    ->schema([
                        Placeholder::make('total_summary_header')
                            ->label('')
                            ->content(function ($get) {
                                $items = $get('items') ?? [];
                                $globalTaxRate = intval($get('tax_rate') ?? 0);
                                $subtotal = 0;
                                $taxAmount = 0;
                                $uomTotals = [];
                                $baseUnitTotals = [];
                                
                                foreach ($items as $item) {
                                    $qty = intval($item['qty_received'] ?? 0);
                                    $price = intval($item['price'] ?? 0);
                                    $itemDiscType = $get('item_discount_type') ?? 'fixed';
                                    $itemDiscVal = intval($item['discount_value'] ?? 0);
                                    $disc = $itemDiscType === 'percent' ? (int) round(($qty * $price) * ($itemDiscVal / 100)) : $itemDiscVal;
                                    
                                    $subtotalBeforeTax = ($qty * $price) - $disc;
                                    $itemTaxAmount = (int) round($subtotalBeforeTax * ($globalTaxRate / 100));
                                    
                                    $subtotal += $subtotalBeforeTax;
                                    $taxAmount += $itemTaxAmount;

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
                                $discount = $discHeaderType === 'percent' ? (int) round($subtotal * ($discHeaderVal / 100)) : $discHeaderVal;
                                $shippingCost = intval($get('shipping_cost') ?? 0);
                                $otherCost = intval($get('other_cost') ?? 0);
                                $pphAmount = intval($get('pph_amount') ?? 0);
                                $shippingBorneBy = $get('shipping_borne_by') ?? 'self_direct';
                                
                                $shippingCostInGrandTotal = ($shippingBorneBy === 'self_direct') ? $shippingCost : 0;
                                $grandTotal = $subtotal - $discount + $taxAmount + $shippingCostInGrandTotal + $otherCost - $pphAmount;
                                
                                return view('backoffice.purchase-transactions.summary-placeholder', [
                                    'subtotal' => $subtotal,
                                    'taxAmount' => $taxAmount,
                                    'discount' => $discount,
                                    'shippingCost' => $shippingCost,
                                    'otherCost' => $otherCost,
                                    'pphAmount' => $pphAmount,
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
