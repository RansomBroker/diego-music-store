<?php

namespace App\Filament\Resources\DeliveryOrders\Schemas;

use App\Models\Branch;
use App\Models\Customer;
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
                Section::make('Informasi Utama Pengiriman (DO)')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('customer_id')
                                    ->relationship('customer', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->label('Pelanggan (Customer)'),

                                Select::make('branch_id')
                                    ->relationship('branch', 'name', modifyQueryUsing: fn ($query) => $query->where('is_active', true))
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->label('Cabang Pengirim'),

                                TextInput::make('do_number')
                                    ->required()
                                    ->unique('delivery_orders', 'do_number', ignoreRecord: true)
                                    ->label('Nomor Surat Jalan DO'),

                                DatePicker::make('shipping_date')
                                    ->required()
                                    ->default(now())
                                    ->label('Tanggal Pengiriman'),

                                TextInput::make('shipping_cost')
                                    ->numeric()
                                    ->default(0)
                                    ->prefix('Rp')
                                    ->label('Total Ongkos Kirim'),

                                Select::make('status')
                                    ->required()
                                    ->options([
                                        'draft' => 'Draft',
                                        'shipped' => 'Shipped (Mengurangi Stok)',
                                        'delivered' => 'Delivered',
                                        'cancelled' => 'Cancelled',
                                    ])
                                    ->default('draft')
                                    ->disabled(fn (string $context, ?\Illuminate\Database\Eloquent\Model $record): bool => 
                                        $context === 'edit' && $record && in_array($record->status, ['shipped', 'delivered', 'cancelled'])
                                    )
                                    ->label('Status DO'),
                            ]),

                        Textarea::make('notes')
                            ->rows(3)
                            ->label('Catatan'),
                    ]),

                Section::make('Item Barang Pengiriman')
                    ->schema([
                        Repeater::make('items')
                            ->schema([
                                Select::make('product_variant_id')
                                    ->required()
                                    ->searchable()
                                    ->getSearchResultsUsing(function (string $search): array {
                                        return ProductVariant::query()
                                            ->join('products', 'products.id', '=', 'product_variants.product_id')
                                            ->where('products.name', 'like', "%{$search}%")
                                            ->orWhere('product_variants.name', 'like', "%{$search}%")
                                            ->orWhere('product_variants.sku', 'like', "%{$search}%")
                                            ->select('product_variants.id', 'products.name as product_name', 'product_variants.name as variant_name', 'product_variants.sku')
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
                                    ->label('Produk / Varian')
                                    ->columnSpan(3),

                                TextInput::make('quantity')
                                    ->numeric()
                                    ->required()
                                    ->minValue(1)
                                    ->default(1)
                                    ->label('Jumlah (Qty)')
                                    ->columnSpan(1),
                            ])
                            ->columns(4)
                            ->minItems(1)
                            ->label('Item Pengiriman'),
                    ]),
            ]);
    }
}
