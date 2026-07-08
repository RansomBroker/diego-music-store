<?php

namespace App\Filament\Resources\StockMovements\Tables;

use App\Models\Product;
use App\Models\ProductVariant;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;

class StockMovementsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Waktu')
                    ->dateTime('d M Y H:i')
                    ->sortable(),

                TextColumn::make('productVariant.sku')
                    ->label('SKU')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('productVariant.product.name')
                    ->label('Produk')
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(function ($state, $record) {
                        $variantName = $record->productVariant->name;
                        return $state . ($variantName ? " - {$variantName}" : "");
                    }),

                TextColumn::make('branch.name')
                    ->label('Cabang')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('type')
                    ->label('Tipe')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'in' => 'success',
                        'out' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'in' => 'Masuk',
                        'out' => 'Keluar',
                        default => ucfirst($state),
                    })
                    ->sortable(),

                TextColumn::make('quantity')
                    ->label('Qty')
                    ->numeric()
                    ->alignRight()
                    ->weight('bold')
                    ->color(fn ($record) => $record->type === 'in' ? 'success' : 'danger')
                    ->formatStateUsing(fn ($state, $record) => ($record->type === 'in' ? '+' : '-') . number_format($state)),

                TextColumn::make('unit_cost')
                    ->label('Harga Satuan')
                    ->money('idr')
                    ->alignRight()
                    ->sortable(),

                TextColumn::make('hpp')
                    ->label('HPP Berjalan')
                    ->money('idr')
                    ->alignRight()
                    ->sortable(),

                TextColumn::make('total_value')
                    ->label('Total Nilai')
                    ->state(fn ($record) => $record->quantity * $record->unit_cost)
                    ->money('idr')
                    ->alignRight(),

                TextColumn::make('reference_label')
                    ->label('Referensi Dokumen')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        // Custom search for reference numbers across polymorphs
                        return $query->whereHas('productVariant', function ($q) use ($search) {
                            // Since reference_label uses reference_id and reference_type,
                            // we search the base reference_type or we can let users search via default columns.
                        });
                    }),
            ])
            ->filters([
                SelectFilter::make('product_id')
                    ->label('Produk')
                    ->options(Product::pluck('name', 'id'))
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when($data['value'], function ($q, $value) {
                            $q->whereHas('productVariant', function ($qv) use ($value) {
                                $qv->where('product_id', $value);
                            });
                        });
                    })
                    ->searchable(),

                SelectFilter::make('product_variant_id')
                    ->label('Varian Spesifik')
                    ->options(function () {
                        return ProductVariant::join('products', 'products.id', '=', 'product_variants.product_id')
                            ->select('product_variants.id', 'products.name as product_name', 'product_variants.name as variant_name', 'product_variants.sku')
                            ->get()
                            ->mapWithKeys(fn ($v) => [
                                $v->id => "[{$v->sku}] {$v->product_name}" . ($v->variant_name ? " - {$v->variant_name}" : "")
                            ])
                            ->toArray();
                    })
                    ->searchable(),

                SelectFilter::make('branch_id')
                    ->label('Cabang')
                    ->relationship('branch', 'name')
                    ->preload()
                    ->searchable(),

                SelectFilter::make('type')
                    ->label('Tipe Pergerakan')
                    ->options([
                        'in' => 'Masuk (IN)',
                        'out' => 'Keluar (OUT)',
                    ]),

                Filter::make('created_at')
                    ->label('Tanggal')
                    ->form([
                        DatePicker::make('created_from')->label('Dari Tanggal'),
                        DatePicker::make('created_until')->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['created_from'], fn ($q, $date) => $q->whereDate('created_at', '>=', $date))
                            ->when($data['created_until'], fn ($q, $date) => $q->whereDate('created_at', '<=', $date));
                    }),

                Filter::make('reference')
                    ->query(function (Builder $query): Builder {
                        $request = request();
                        return $query
                            ->when($request->query('reference_type'), fn ($q, $type) => $q->where('reference_type', $type))
                            ->when($request->query('reference_id'), fn ($q, $id) => $q->where('reference_id', $id));
                    }),
            ])
            ->actions([])
            ->bulkActions([]);
    }
}
