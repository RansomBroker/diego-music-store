<?php

namespace App\Filament\Resources\JournalEntries\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class JournalEntriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('entry_no')
                    ->label('No. Jurnal')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('branch.name')
                    ->label('Cabang')
                    ->searchable()
                    ->sortable()
                    ->placeholder('Multi-cabang / Pusat'),

                TextColumn::make('description')
                    ->label('Keterangan')
                    ->searchable()
                    ->limit(50),

                TextColumn::make('debit_total')
                    ->label('Total Nominal')
                    ->money('idr')
                    ->state(fn ($record) => $record->items->sum('debit')),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'posted' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->sortable(),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('status')
                    ->label('Status Jurnal')
                    ->options([
                        'draft' => 'Draft',
                        'posted' => 'Posted',
                    ]),

                \Filament\Tables\Filters\SelectFilter::make('account_id')
                    ->label('Filter Akun Rekening')
                    ->relationship('items.account', 'name')
                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->code} - {$record->name}")
                    ->searchable()
                    ->preload()
                    ->query(function (\Illuminate\Database\Eloquent\Builder $query, array $data): \Illuminate\Database\Eloquent\Builder {
                        if (empty($data['value'])) {
                            return $query;
                        }
                        return $query->whereHas('items', function ($q) use ($data) {
                            $q->where('account_id', $data['value']);
                        });
                    }),

                \Filament\Tables\Filters\Filter::make('date_range')
                    ->label('Rentang Tanggal')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('from')->label('Dari Tanggal'),
                        \Filament\Forms\Components\DatePicker::make('until')->label('Sampai Tanggal'),
                    ])
                    ->query(function (\Illuminate\Database\Eloquent\Builder $query, array $data): \Illuminate\Database\Eloquent\Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn ($q, $date) => $q->whereDate('date', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn ($q, $date) => $q->whereDate('date', '<=', $date),
                            );
                    })
            ])
            ->actions([
                \Filament\Actions\ViewAction::make()
                    ->label('Detail')
                    ->color('info')
                    ->icon('heroicon-o-eye')
                    ->modalWidth('4xl')
                    ->modalHeading('Rincian Jurnal Umum')
                    ->modalContent(fn ($record) => view('backoffice.accounting.journal-entry-detail', ['record' => $record])),

                EditAction::make()
                    ->modalWidth('xl')
                    ->visible(fn ($record) => $record->status === 'draft')
                    ->using(fn (\Illuminate\Database\Eloquent\Model $record, array $data): \Illuminate\Database\Eloquent\Model => app(\App\Actions\Accounting\UpdateJournalEntry::class)->execute($record, $data)),

                Action::make('post')
                    ->label('Post')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => $record->status === 'draft')
                    ->action(function ($record) {
                        app(\App\Actions\Accounting\PostJournalEntry::class)->execute($record);
                    }),

                DeleteAction::make()
                    ->visible(fn ($record) => $record->status === 'draft'),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
