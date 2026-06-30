<?php

namespace App\Filament\Resources\StockOpnames\Schemas;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Placeholder;
use App\Models\ProductVariant;
use App\Models\ProductBranchStock;

class StockOpnameForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Utama Stok Opname')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('opname_number')
                                    ->label('Nomor Opname')
                                    ->placeholder('AUTO-GENERATED')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->visible(fn ($record) => $record !== null),

                                Select::make('branch_id')
                                    ->label('Cabang')
                                    ->relationship('branch', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->disabled(fn ($record) => $record !== null && $record->status !== 'draft')
                                    ->reactive()
                                    ->afterStateUpdated(fn ($state, callable $set) => $set('items', [])),

                                DatePicker::make('opname_date')
                                    ->label('Tanggal Opname')
                                    ->default(now())
                                    ->required()
                                    ->disabled(fn ($record) => $record !== null && $record->status !== 'draft'),

                                Select::make('status')
                                    ->label('Status')
                                    ->options([
                                        'draft' => 'Draft',
                                        'completed' => 'Completed (Selesai)',
                                    ])
                                    ->default('draft')
                                    ->required()
                                    ->disabled(fn ($record) => $record !== null && $record->status === 'completed'),
                            ]),

                        Textarea::make('notes')
                            ->label('Catatan')
                            ->rows(3)
                            ->columnSpanFull()
                            ->disabled(fn ($record) => $record !== null && $record->status === 'completed'),
                    ])
                    ->columnSpanFull(),

                Section::make('Pencatatan Fisik Barang')
                    ->schema([
                        Repeater::make('items')
                            ->label('Item Opname')
                            ->schema([
                                Grid::make(4)
                                    ->schema([
                                        Select::make('product_variant_id')
                                            ->label('Produk / Varian')
                                            ->required()
                                            ->searchable()
                                            ->disabled(fn ($get) => empty($get('../../branch_id')))
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
                                            ->reactive()
                                            ->afterStateUpdated(function ($state, callable $set, $get) {
                                                if ($state && $branchId = $get('../../branch_id')) {
                                                    $stock = ProductBranchStock::where([
                                                        'branch_id' => $branchId,
                                                        'product_variant_id' => $state,
                                                    ])->first();
                                                    $set('system_qty', $stock ? $stock->stock : 0);
                                                }
                                            })
                                            ->columnSpan(2),

                                        TextInput::make('system_qty')
                                            ->label('Stok Sistem')
                                            ->numeric()
                                            ->disabled()
                                            ->dehydrated() // Keep it dehydrated so it saves correctly
                                            ->default(0),

                                        TextInput::make('physical_qty')
                                            ->label('Stok Fisik')
                                            ->numeric()
                                            ->required()
                                            ->default(0)
                                            ->minValue(0)
                                            ->reactive()
                                            ->afterStateUpdated(function ($state, callable $set, $get) {
                                                $sys = intval($get('system_qty') ?? 0);
                                                $phys = intval($state ?? 0);
                                                $set('difference', $phys - $sys);
                                            }),
                                    ]),
                            ])
                            ->minItems(1)
                            ->columnSpanFull()
                            ->disabled(fn ($record) => $record !== null && $record->status !== 'draft'),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
