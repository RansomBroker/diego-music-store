<?php

namespace App\Filament\Resources\SalesQuotations\Tables;

use App\Actions\Sales\UpdateSalesQuotation;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class SalesQuotationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('quotation_number')
                    ->label('No. Penawaran')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('customer.name')
                    ->label('Pelanggan')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('branch.name')
                    ->label('Cabang')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('quotation_date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('valid_until')
                    ->label('Berlaku s/d')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('grand_total')
                    ->label('Grand Total')
                    ->money('IDR', locale: 'id')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'sent' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        'closed' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),
            ])
            ->actions([
                EditAction::make()
                    ->modalWidth('4xl')
                    ->mutateRecordDataUsing(function (Model $record, array $data): array {
                        $data['items'] = [];
                        foreach ($record->items as $item) {
                            $data['items'][] = [
                                'product_variant_id' => $item->product_variant_id,
                                'unit_id' => $item->unit_id,
                                'quantity' => $item->quantity,
                                'price' => $item->price,
                                'discount_type' => $item->discount_type,
                                'discount_value' => $item->discount_value,
                            ];
                        }
                        return $data;
                    })
                    ->using(fn (Model $record, array $data): Model => app(UpdateSalesQuotation::class)->execute($record, $data)),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
