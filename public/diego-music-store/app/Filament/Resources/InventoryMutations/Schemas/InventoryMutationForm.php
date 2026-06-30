<?php

namespace App\Filament\Resources\InventoryMutations\Schemas;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Repeater;
use App\Models\ProductVariant;

class InventoryMutationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Mutasi Barang')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('mutation_number')
                                    ->label('Nomor Mutasi')
                                    ->placeholder('AUTO-GENERATED')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->visible(fn ($record) => $record !== null),

                                Select::make('sender_branch_id')
                                    ->label('Cabang Pengirim')
                                    ->relationship('senderBranch', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->disabled(fn ($record) => $record !== null && $record->status !== 'draft'),

                                Select::make('receiver_branch_id')
                                    ->label('Cabang Penerima')
                                    ->relationship('receiverBranch', 'name')
                                    ->required()
                                    ->different('sender_branch_id')
                                    ->validationMessages([
                                        'different' => 'Cabang penerima tidak boleh sama dengan cabang pengirim.',
                                    ])
                                    ->searchable()
                                    ->preload()
                                    ->disabled(fn ($record) => $record !== null && $record->status !== 'draft'),

                                DatePicker::make('mutation_date')
                                    ->label('Tanggal Mutasi')
                                    ->default(now())
                                    ->required()
                                    ->disabled(fn ($record) => $record !== null && $record->status !== 'draft'),

                                Select::make('status')
                                    ->label('Status')
                                    ->options([
                                        'draft' => 'Draft',
                                        'transit' => 'In-Transit (Kirim)',
                                        'received' => 'Received (Diterima)',
                                    ])
                                    ->default('draft')
                                    ->required()
                                    // Disable status dropdown if it's already received (final state)
                                    // Or limit transitions. We can also let the action handle status transitions.
                                    ->disabled(fn ($record) => $record !== null && $record->status === 'received'),
                            ]),

                        Textarea::make('notes')
                            ->label('Catatan')
                            ->rows(3)
                            ->columnSpanFull()
                            ->disabled(fn ($record) => $record !== null && $record->status === 'received'),
                    ])
                    ->columnSpanFull(),

                Section::make('Daftar Barang')
                    ->schema([
                        Repeater::make('items')
                            ->label('Item Mutasi')
                            ->schema([
                                Grid::make(3)
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
                                            ->columnSpan(2),

                                        TextInput::make('quantity')
                                            ->label('Qty')
                                            ->numeric()
                                            ->required()
                                            ->default(1)
                                            ->minValue(1),
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
