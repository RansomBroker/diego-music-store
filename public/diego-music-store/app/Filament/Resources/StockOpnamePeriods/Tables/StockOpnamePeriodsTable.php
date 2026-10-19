<?php

namespace App\Filament\Resources\StockOpnamePeriods\Tables;

use App\Models\StockOpnamePeriod;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class StockOpnamePeriodsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Periode')->searchable()->sortable(),
                TextColumn::make('branch.name')->label('Cabang')->sortable(),
                TextColumn::make('period')->label('Bulan')->date('F Y')->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        StockOpnamePeriod::STATUS_DRAFT => 'gray',
                        StockOpnamePeriod::STATUS_ACTIVE => 'info',
                        StockOpnamePeriod::STATUS_REVIEW => 'warning',
                        StockOpnamePeriod::STATUS_COMPLETED => 'success',
                        StockOpnamePeriod::STATUS_CANCELLED => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('assignments_count')
                    ->counts('assignments')
                    ->label('Jumlah Tugas')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->options([
                    StockOpnamePeriod::STATUS_DRAFT => 'Draft',
                    StockOpnamePeriod::STATUS_ACTIVE => 'Aktif',
                    StockOpnamePeriod::STATUS_REVIEW => 'Review',
                    StockOpnamePeriod::STATUS_COMPLETED => 'Selesai',
                    StockOpnamePeriod::STATUS_CANCELLED => 'Dibatalkan',
                ]),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make()
                    ->visible(fn ($record) => $record->status === StockOpnamePeriod::STATUS_DRAFT
                        && $record->assignments()->doesntExist()),
            ]);
    }
}
