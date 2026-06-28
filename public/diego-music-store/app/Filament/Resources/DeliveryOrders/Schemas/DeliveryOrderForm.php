<?php

namespace App\Filament\Resources\DeliveryOrders\Schemas;

use App\Models\Branch;
use App\Models\PurchaseOrder;
use App\Models\ProductVariant;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class DeliveryOrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Informasi Utama Penerimaan (DO)')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('purchase_order_id')
                                    ->relationship('purchaseOrder', 'po_number', modifyQueryUsing: fn ($query) => $query->where('status', 'approved'))
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        if (!$state) {
                                            $set('items', []);
                                            return;
                                        }

                                        $po = PurchaseOrder::with('items')->find($state);
                                        if ($po) {
                                            $items = [];
                                            foreach ($po->items as $poItem) {
                                                $items[] = [
                                                    'product_variant_id' => $poItem->product_variant_id,
                                                    'quantity_ordered' => $poItem->quantity,
                                                    'quantity_received' => $poItem->quantity, // default to quantity ordered
                                                ];
                                            }
                                            $set('items', $items);
                                        }
                                    })
                                    ->label('Pilih PO Referensi'),

                                Select::make('branch_id')
                                    ->relationship('branch', 'name', modifyQueryUsing: fn ($query) => $query->where('is_active', true))
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->label('Cabang Penerima'),

                                TextInput::make('do_number')
                                    ->required()
                                    ->unique('delivery_orders', 'do_number', ignoreRecord: true)
                                    ->label('Nomor Surat Jalan DO'),

                                DatePicker::make('received_date')
                                    ->required()
                                    ->default(now())
                                    ->label('Tanggal Penerimaan'),

                                TextInput::make('shipping_cost')
                                    ->numeric()
                                    ->default(0)
                                    ->prefix('Rp')
                                    ->label('Total Ongkos Kirim'),

                                Select::make('status')
                                    ->required()
                                    ->options([
                                        'draft' => 'Draft',
                                        'received' => 'Received (Masuk Stok & HPP)',
                                    ])
                                    ->default('draft')
                                    ->disabled(fn (string $context, ?\Illuminate\Database\Eloquent\Model $record): bool => 
                                        $context === 'edit' && $record && $record->status === 'received'
                                    )
                                    ->label('Status Penerimaan'),
                            ]),

                        Textarea::make('notes')
                            ->rows(3)
                            ->label('Catatan'),
                    ]),

                Section::make('Verifikasi Item Barang Masuk')
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
                                    ->disabled()
                                    ->dehydrated() // ensure it is sent to action even when disabled
                                    ->label('Varian Produk')
                                    ->columnSpan(2),

                                TextInput::make('quantity_ordered')
                                    ->numeric()
                                    ->disabled()
                                    ->dehydrated()
                                    ->label('Qty Dipesan (PO)')
                                    ->columnSpan(1),

                                TextInput::make('quantity_received')
                                    ->numeric()
                                    ->required()
                                    ->minValue(0)
                                    ->label('Qty Diterima Fisik')
                                    ->columnSpan(1),
                            ])
                            ->columns(4)
                            ->disableItemCreation() // cannot add new items directly in DO, must match PO
                            ->disableItemDeletion() // cannot delete items directly in DO
                            ->minItems(1)
                            ->label('Item Penerimaan'),
                    ]),
            ]);
    }
}
