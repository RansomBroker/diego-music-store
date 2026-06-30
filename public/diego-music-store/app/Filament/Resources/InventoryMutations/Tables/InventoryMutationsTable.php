<?php

namespace App\Filament\Resources\InventoryMutations\Tables;

use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\StockMovements\StockMovementResource;

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
                EditAction::make(),
                \Filament\Actions\Action::make('kartu_stok')
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
