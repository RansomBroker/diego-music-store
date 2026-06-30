<?php

namespace App\Filament\Resources\StockOpnames\Tables;

use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\StockMovements\StockMovementResource;

class StockOpnamesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('opname_number')
                    ->label('Nomor Opname')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('branch.name')
                    ->label('Cabang')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('opname_date')
                    ->label('Tanggal Opname')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'completed' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft' => 'Draft',
                        'completed' => 'Completed',
                        default => ucfirst($state),
                    }),
            ])
            ->actions([
                EditAction::make(),
                \Filament\Actions\Action::make('kartu_stok')
                    ->label('Kartu Stok')
                    ->icon('heroicon-o-queue-list')
                    ->color('info')
                    ->visible(fn ($record) => $record->status === 'completed')
                    ->url(fn ($record) => StockMovementResource::getUrl('index', [
                        'reference_type' => 'Opname',
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
