<?php

namespace App\Filament\Resources\SupplierPayments\Tables;

use App\Filament\Resources\JournalEntries\JournalEntryResource;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SupplierPaymentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('payment_no')
                    ->label('No. Pembayaran')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('payment_date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('supplier.name')
                    ->label('Supplier')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('branch.name')
                    ->label('Cabang')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('account.name')
                    ->label('Kas / Bank')
                    ->sortable(),

                TextColumn::make('payment_method')
                    ->label('Metode')
                    ->sortable(),

                TextColumn::make('total_amount')
                    ->label('Total Bayar')
                    ->money('idr')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'posted' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft' => 'Draft',
                        'posted' => 'Posted',
                        default => ucfirst($state),
                    }),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make()
                    ->visible(fn ($record) => $record->status === 'draft'),

                Action::make('lihat_jurnal')
                    ->label('Jurnal')
                    ->icon('heroicon-o-document-text')
                    ->color('info')
                    ->visible(fn ($record) => $record->status === 'posted')
                    ->url(fn ($record) => JournalEntryResource::getUrl('index', [
                        'search' => $record->journal_no,
                    ])),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
