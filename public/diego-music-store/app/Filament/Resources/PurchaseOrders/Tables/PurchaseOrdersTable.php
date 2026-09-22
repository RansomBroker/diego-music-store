<?php

namespace App\Filament\Resources\PurchaseOrders\Tables;

use App\Actions\Procurement\UpdatePurchaseOrder as UpdatePurchaseOrderAction;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Support\Enums\Width;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('po_number')
                    ->label('Nomor PO')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('supplier.name')
                    ->label('Supplier')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('branch.name')
                    ->label('Cabang')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('order_date')
                    ->label('Tanggal Order')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'approved' => 'info',
                        'closed' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),

                TextColumn::make('grand_total')
                    ->label('Grand Total')
                    ->money('idr')
                    ->sortable(),
            ])
            ->actions([
                EditAction::make()
                    ->modalWidth('7xl')
                    ->visible(fn (Model $record) => $record->status === 'draft')
                    ->mutateRecordDataUsing(function (Model $record, array $data): array {
                        $data['discount_type'] = $record->discount_type ?? 'fixed';
                        $data['discount_value'] = $record->discount_value ?? 0;
                        $data['items'] = [];
                        foreach ($record->items as $item) {
                            $data['items'][] = [
                                'product_variant_id' => $item->product_variant_id,
                                'quantity' => $item->quantity,
                                'price' => $item->price,
                                'discount_type' => $item->discount_type ?? 'fixed',
                                'discount_value' => $item->discount_value ?? 0,
                                'tax_rate' => $item->tax_rate,
                                'notes' => $item->notes,
                                'unit_id' => $item->unit_id,
                            ];
                        }
                        return $data;
                    })
                    ->using(fn (Model $record, array $data): Model => app(UpdatePurchaseOrderAction::class)->execute($record, $data)),

                ViewAction::make()
                    ->modalWidth('7xl')
                    ->visible(fn (Model $record) => $record->status !== 'draft')
                    ->mutateRecordDataUsing(function (Model $record, array $data): array {
                        $data['discount_type'] = $record->discount_type ?? 'fixed';
                        $data['discount_value'] = $record->discount_value ?? 0;
                        $data['items'] = [];
                        foreach ($record->items as $item) {
                            $data['items'][] = [
                                'product_variant_id' => $item->product_variant_id,
                                'quantity' => $item->quantity,
                                'price' => $item->price,
                                'discount_type' => $item->discount_type ?? 'fixed',
                                'discount_value' => $item->discount_value ?? 0,
                                'tax_rate' => $item->tax_rate,
                                'notes' => $item->notes,
                                'unit_id' => $item->unit_id,
                            ];
                        }
                        return $data;
                    }),

                Action::make('print')
                    ->label('Cetak')
                    ->icon('heroicon-o-printer')
                    ->color('info')
                    ->url(fn ($record) => route('backoffice.purchase-orders.print', $record))
                    ->openUrlInNewTab(),

                DeleteAction::make()
                    ->visible(fn (Model $record) => $record->status === 'draft'),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
