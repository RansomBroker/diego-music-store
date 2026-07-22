<?php

namespace App\Filament\Resources\Products\Tables;

use App\Actions\Product\DuplicateProduct;
use App\Actions\Product\UpdateProduct as UpdateProductAction;
use App\Helpers\FormatHelper;
use App\Helpers\ProductHelper;
use App\Filament\Resources\StockMovements\StockMovementResource;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('product.image_path')
                    ->label('Foto')
                    ->circular(),

                TextColumn::make('product.name')
                    ->searchable()
                    ->sortable()
                    ->getStateUsing(function ($record) {
                        $parentName = $record->product?->name ?? '-';
                        if ($record->name) {
                            return $parentName . ' (' . $record->name . ')';
                        }
                        return $parentName;
                    })
                    ->label('Nama Produk'),

                TextColumn::make('product.type')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'physical' => 'Barang Fisik',
                        'bundle' => 'Produk Bundling',
                        'service' => 'Jasa / Layanan',
                        default => $state,
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'physical' => 'info',
                        'bundle' => 'warning',
                        'service' => 'success',
                        default => 'gray',
                    })
                    ->label('Tipe'),

                TextColumn::make('product.category')
                    ->searchable()
                    ->sortable()
                    ->placeholder('-')
                    ->label('Kategori'),

                TextColumn::make('total_stock')
                    ->getStateUsing(fn ($record) => $record->totalStock())
                    ->alignCenter()
                    ->label('Stok'),

                TextColumn::make('barcode')
                    ->getStateUsing(function ($record) {
                        $barcode = $record->barcode;
                        if (!$barcode) {
                            return '<span class="text-xs text-gray-500">-</span>';
                        }
                        $svg = \App\Helpers\BarcodeHelper::generateCode128Svg($barcode, 120, 30);
                        $base64 = base64_encode($svg);
                        return '<div style="display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 4px 0;">
                            <img src="data:image/svg+xml;base64,' . $base64 . '" style="width: 120px; height: 30px; display: block;" />
                            <div style="font-family: monospace; font-size: 10px; margin-top: 4px; font-weight: 600; line-height: 1;" class="text-gray-700 dark:text-gray-300">' . $barcode . '</div>
                        </div>';
                    })
                    ->html()
                    ->alignCenter()
                    ->label('Barcode'),

                TextColumn::make('product.unit.name')
                    ->label('Satuan')
                    ->toggleable(),

                TextColumn::make('sku')
                    ->getStateUsing(fn ($record) => $record->sku ?? '-')
                    ->label('SKU'),

                TextColumn::make('price')
                    ->getStateUsing(fn ($record) => FormatHelper::rupiah($record->price))
                    ->label('Harga Jual Dasar'),

                ToggleColumn::make('is_active')
                    ->label('Aktif'),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('Dibuat Pada'),
            ])
            ->filters([
                //
            ])
            ->actions([
                EditAction::make()
                    ->modalWidth('8xl')
                    ->mutateRecordDataUsing(function (Model $record, array $data): array {
                        // $record is the ProductVariant
                        $product = $record->product;
                        if (!$product) {
                            return $data;
                        }
                        
                        $variantsCount = $product->variants()->count();
                        $firstVariant = $product->variants()->first();

                        $data['name'] = $product->name;
                        $data['type'] = $product->type;
                        $data['unit_id'] = $product->unit_id;
                        $data['category'] = $product->category;
                        $data['brand'] = $product->brand;
                        $data['supplier_id'] = $product->supplier_id;
                        $data['description'] = $product->description;
                        $data['image_path'] = $product->image_path;
                        $data['is_active'] = $product->is_active;
                        $data['minimum_stock'] = $product->minimum_stock;

                        if ($product->isPhysical() && $variantsCount > 1) {
                            $data['has_variants'] = true;
                            $data['variants'] = [];

                            foreach ($product->variants as $variant) {
                                $data['variants'][] = [
                                    'id' => $variant->id,
                                    'name' => $variant->name,
                                    'sku' => $variant->sku,
                                    'barcode' => $variant->barcode,
                                    'price' => $variant->price,
                                    'discount_value' => $variant->discount_value,
                                    'discount_type' => $variant->discount_type,
                                    'tax_value' => $variant->tax_value,
                                    'tax_type' => $variant->tax_type,
                                    'cost_price' => $variant->cost_price,
                                    'hpp' => $variant->hpp,
                                    'tier_prices' => $variant->tierPrices()->pluck('price', 'pricing_tier_id')->toArray(),
                                ];
                            }
                        } else {
                            $data['has_variants'] = false;
                            if ($firstVariant) {
                                $data['sku'] = $firstVariant->sku;
                                $data['barcode'] = $firstVariant->barcode;
                                $data['price'] = $firstVariant->price;
                                $data['discount_value'] = $firstVariant->discount_value;
                                $data['discount_type'] = $firstVariant->discount_type;
                                $data['tax_value'] = $firstVariant->tax_value;
                                $data['tax_type'] = $firstVariant->tax_type;
                                $data['cost_price'] = $firstVariant->cost_price;
                                $data['hpp'] = $firstVariant->hpp;

                                $data['tier_prices'] = $firstVariant->tierPrices()->pluck('price', 'pricing_tier_id')->toArray();

                                if ($product->isBundle()) {
                                    $data['bundle_items'] = [];
                                    foreach ($firstVariant->bundleItems as $item) {
                                        $data['bundle_items'][] = [
                                            'child_variant_id' => $item->child_variant_id,
                                            'quantity' => $item->quantity,
                                        ];
                                    }
                                }
                            }
                        }

                        return $data;
                    })
                    ->using(function (Model $record, array $data): Model {
                        // $record is the ProductVariant
                        if ($record->product) {
                            app(UpdateProductAction::class)->execute($record->product, $data);
                        }
                        return $record;
                    }),
                Action::make('copy_product')
                    ->label('Copy Produk')
                    ->icon('heroicon-o-document-duplicate')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Copy Produk')
                    ->modalDescription('Apakah Anda yakin ingin menyalin produk ini? Semua varian dan harga tingkatan akan disalin dengan SKU dan Barcode baru.')
                    ->action(function ($record) {
                        if ($record->product) {
                            $duplicateAction = app(DuplicateProduct::class);
                            $newProduct = $duplicateAction->execute($record->product);

                            Notification::make()
                                ->title('Produk Berhasil Disalin')
                                ->body('Produk baru: ' . $newProduct->name)
                                ->success()
                                ->send();
                        }
                    }),
                Action::make('kartu_stok')
                    ->label('Kartu Stok')
                    ->icon('heroicon-o-queue-list')
                    ->color('info')
                    ->modalWidth('7xl')
                    ->modalHeading('Kartu Riwayat Pergerakan Stok')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup')
                    ->modalContent(fn ($record) => view('backoffice.products.stock-card-modal', array_merge(
                        ['product' => $record->product],
                        ProductHelper::getStockCardData($record->product)
                    ))),
                DeleteAction::make()
                    ->using(function (Model $record) {
                        try {
                            $record->delete();
                        } catch (\Illuminate\Database\QueryException $e) {
                            if ($e->getCode() == '23000') {
                                $record->update(['is_active' => false]);
                                \Filament\Notifications\Notification::make()
                                    ->title('Variant Dinonaktifkan')
                                    ->body('Varian tidak dapat dihapus karena memiliki transaksi terkait. Status diubah menjadi tidak aktif.')
                                    ->warning()
                                    ->send();
                            } else {
                                throw $e;
                            }
                        }
                    }),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    \Filament\Actions\BulkAction::make('printSelectedBarcodes')
                        ->label('Cetak Barcode Terpilih')
                        ->icon('heroicon-o-printer')
                        ->color('info')
                        ->form(\App\Helpers\ProductHelper::getBarcodePrintFormSchema())
                        ->modalWidth('2xl')
                        ->extraModalActions([
                            \Filament\Actions\Action::make('downloadSelectedPdf')
                                ->label('Download PDF')
                                ->icon('heroicon-o-document-arrow-down')
                                ->color('success')
                                ->action(function ($livewire, \Illuminate\Support\Collection $records, array $data) {
                                    $queue = [];
                                    foreach ($records as $variant) {
                                        $product = $variant->product;
                                        if (!$product) continue;
                                        $name = $product->name;
                                        if ($variant->name) {
                                            $name .= ' (' . $variant->name . ')';
                                        }
                                        $queue[] = [
                                            'variant_id' => $variant->id,
                                            'name'       => $name,
                                            'sku'        => $variant->sku ?? '00000',
                                            'barcode'    => $variant->barcode ?? $variant->sku ?? '',
                                            'price'      => $variant->price,
                                            'qty'        => 1,
                                        ];
                                    }
                                    if (empty($queue)) {
                                        \Filament\Notifications\Notification::make()
                                            ->title('Tidak Ada Barcode')
                                            ->body('Tidak ada varian produk yang terpilih untuk didownload.')
                                            ->warning()
                                            ->send();
                                        return null;
                                    }
                                    return \App\Helpers\ProductHelper::generateBarcodePdfResponse($queue, $formState);
                                }),
                        ])
                        ->action(function ($livewire, \Illuminate\Support\Collection $records, array $data) {
                            $mountedForm = method_exists($livewire, 'getMountedTableBulkActionForm') ? $livewire->getMountedTableBulkActionForm() : null;
                            if (!$mountedForm && method_exists($livewire, 'getMountedActionForm')) {
                                $mountedForm = $livewire->getMountedActionForm();
                            }
                            $formState = $mountedForm ? ($mountedForm->getRawState() ?? $mountedForm->getState(afterValidate: false)) : $data;

                            $queue = [];
                            foreach ($records as $variant) {
                                $product = $variant->product;
                                if (!$product) continue;
                                $name = $product->name;
                                if ($variant->name) {
                                    $name .= ' (' . $variant->name . ')';
                                }
                                $queue[] = [
                                    'variant_id' => $variant->id,
                                    'name'       => $name,
                                    'sku'        => $variant->sku ?? '00000',
                                    'barcode'    => $variant->barcode ?? $variant->sku ?? '',
                                    'price'      => $variant->price,
                                    'qty'        => 1,
                                ];
                            }
                            if (empty($queue)) {
                                \Filament\Notifications\Notification::make()
                                    ->title('Tidak Ada Barcode')
                                    ->body('Tidak ada varian produk yang terpilih untuk dicetak.')
                                    ->warning()
                                    ->send();
                                return;
                            }
                            $params = \App\Helpers\ProductHelper::resolveLayoutParams($formState);
                            $payload = base64_encode(json_encode(array_merge($params, [
                                'queue' => $queue,
                            ])));
                            $livewire->js("window.open('/pos/barcode-print/sheet?data={$payload}', '_blank')");
                        }),
                    DeleteBulkAction::make(),
                ]),
            ])
            ->recordClasses(function ($record) {
                /** @var \App\Models\ProductVariant $record */
                $product = $record->product;
                if (!$product) {
                    return null;
                }

                if ($product->isService()) {
                    return '[&>td]:!bg-emerald-500/10 dark:[&>td]:!bg-emerald-950/20'; // Jasa is always green
                }

                $stock = $record->totalStock();
                $min = $product->minimum_stock ?? 0;

                if ($stock <= 0) {
                    return '[&>td]:!bg-rose-500/15 dark:[&>td]:!bg-rose-950/30'; // Habis (Red)
                }

                if ($stock <= $min) {
                    return '[&>td]:!bg-amber-500/15 dark:[&>td]:!bg-amber-950/30'; // Menipis (Yellow)
                }

                return '[&>td]:!bg-emerald-500/5 dark:[&>td]:!bg-emerald-950/10'; // Banyak (Green)
            });
    }
}
