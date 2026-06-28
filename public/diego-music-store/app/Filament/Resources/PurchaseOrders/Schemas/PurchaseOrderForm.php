<?php

namespace App\Filament\Resources\PurchaseOrders\Schemas;

use App\Models\ProductVariant;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PurchaseOrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Informasi Utama PO')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('supplier_id')
                                    ->relationship('supplier', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->label('Supplier / Vendor'),

                                TextInput::make('po_number')
                                    ->required()
                                    ->unique('purchase_orders', 'po_number', ignoreRecord: true)
                                    ->default(fn () => 'PO-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(2))))
                                    ->label('Nomor PO'),

                                DatePicker::make('order_date')
                                    ->required()
                                    ->default(now())
                                    ->label('Tanggal Order'),

                                Select::make('status')
                                    ->required()
                                    ->options([
                                        'draft' => 'Draft',
                                        'approved' => 'Approved',
                                        'closed' => 'Closed',
                                    ])
                                    ->default('draft')
                                    ->label('Status PO'),
                            ]),

                        Textarea::make('notes')
                            ->rows(3)
                            ->label('Catatan / Keterangan tambahan'),
                    ]),

                Section::make('Daftar Barang Pesanan')
                    ->schema([
                        Repeater::make('items')
                            ->schema([
                                Select::make('product_variant_id')
                                    ->required()
                                    ->options(function () {
                                        return ProductVariant::all()->mapWithKeys(function ($v) {
                                            $name = $v->product->name . ($v->name ? ' (' . $v->name . ')' : '');
                                            return [$v->id => $name . ' [' . ($v->sku ?? 'No SKU') . ']'];
                                        });
                                    })
                                    ->searchable()
                                    ->preload()
                                    ->label('Varian Produk')
                                    ->columnSpan(2),

                                TextInput::make('quantity')
                                    ->numeric()
                                    ->required()
                                    ->minValue(1)
                                    ->default(1)
                                    ->label('Qty')
                                    ->columnSpan(1),

                                TextInput::make('price')
                                    ->numeric()
                                    ->required()
                                    ->prefix('Rp')
                                    ->label('Harga Satuan')
                                    ->columnSpan(1),
                            ])
                            ->columns(4)
                            ->minItems(1)
                            ->label('Item Barang'),
                    ]),
            ]);
    }
}
