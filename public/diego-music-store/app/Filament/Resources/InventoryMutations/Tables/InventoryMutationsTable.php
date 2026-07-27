<?php

namespace App\Filament\Resources\InventoryMutations\Tables;

use App\Actions\InventoryMutation\DeleteInventoryMutation;
use App\Actions\InventoryMutation\UpdateInventoryMutation as UpdateInventoryMutationAction;
use App\Filament\Resources\StockMovements\StockMovementResource;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class InventoryMutationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('mutation_number')
                    ->label('Nomor Mutasi')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('senderBranch.name')
                    ->label('Cabang Pengirim')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('receiverBranch.name')
                    ->label('Cabang Penerima')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('mutation_date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'transit' => 'warning',
                        'received' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft' => 'Draft',
                        'transit' => 'In-Transit',
                        'received' => 'Received',
                        default => ucfirst($state),
                    }),
            ])
            ->actions([
                EditAction::make()
                    ->modalWidth('4xl')
                    ->mutateRecordDataUsing(function (Model $record, array $data): array {
                        $data['items'] = [];
                        foreach ($record->items as $item) {
                            $data['items'][] = [
                                'product_variant_id' => $item->product_variant_id,
                                'quantity' => $item->quantity,
                            ];
                        }
                        return $data;
                    })
                    ->using(fn (Model $record, array $data): Model => app(UpdateInventoryMutationAction::class)->execute($record, $data)),
                Action::make('dokumen_mutasi')
                    ->label('Dokumen Mutasi')
                    ->icon('heroicon-o-document-text')
                    ->color('info')
                    ->url(fn ($record) => route('backoffice.inventory-mutations.print', $record))
                    ->openUrlInNewTab(),
                DeleteAction::make()
                    ->using(fn (Model $record) => app(DeleteInventoryMutation::class)->execute($record)),
                Action::make('kartu_stok')
                    ->label('Kartu Stok')
                    ->icon('heroicon-o-queue-list')
                    ->color('info')
                    ->visible(fn ($record) => in_array($record->status, ['transit', 'received']))
                    ->url(fn ($record) => StockMovementResource::getUrl('index', [
                        'reference_type' => 'Mutation',
                        'reference_id' => $record->id,
                    ])),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
