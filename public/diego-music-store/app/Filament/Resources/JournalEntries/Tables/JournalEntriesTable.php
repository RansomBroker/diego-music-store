<?php

namespace App\Filament\Resources\JournalEntries\Tables;

use App\Actions\Accounting\PostJournalEntry;
use App\Actions\Accounting\UpdateJournalEntry;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

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
                SelectFilter::make('status')
                    ->label('Status Jurnal')
                    ->options([
                        'draft' => 'Draft',
                        'posted' => 'Posted',
                    ]),

                SelectFilter::make('account_id')
                    ->label('Filter Akun Rekening')
                    ->relationship('items.account', 'name')
                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->code} - {$record->name}")
                    ->searchable()
                    ->preload()
                    ->query(function (Builder $query, array $data): Builder {
                        if (empty($data['value'])) {
                            return $query;
                        }
                        return $query->whereHas('items', function ($q) use ($data) {
                            $q->where('account_id', $data['value']);
                        });
                    }),

                Filter::make('date_range')
                    ->label('Rentang Tanggal')
                    ->form([
                        DatePicker::make('from')->label('Dari Tanggal'),
                        DatePicker::make('until')->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
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
                ViewAction::make()
                    ->label('Detail')
                    ->color('info')
                    ->icon('heroicon-o-eye')
                    ->modalWidth('4xl')
                    ->modalHeading('Rincian Jurnal Umum')
                    ->modalContent(fn ($record) => view('backoffice.accounting.journal-entry-detail', ['record' => $record])),

                EditAction::make()
                    ->modalWidth('xl')
                    ->visible(fn ($record) => $record->status === 'draft')
                    ->using(fn (Model $record, array $data): Model => app(UpdateJournalEntry::class)->execute($record, $data)),

                Action::make('post')
                    ->label('Post')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => $record->status === 'draft')
                    ->action(function ($record) {
                        app(PostJournalEntry::class)->execute($record);
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
