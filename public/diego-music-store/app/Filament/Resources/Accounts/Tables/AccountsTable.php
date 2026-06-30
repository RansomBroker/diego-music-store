<?php

namespace App\Filament\Resources\Accounts\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Table;

class AccountsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->searchable()
                    ->sortable()
                    ->label('Kode Akun'),

                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->label('Nama Akun'),

                TextColumn::make('classificationRelation.name')
                    ->badge()
                    ->color(fn (\App\Models\Account $record): string => match (strtolower($record->classification ?? '')) {
                        'asset' => 'info',
                        'liability' => 'warning',
                        'equity' => 'success',
                        'revenue' => 'primary',
                        'expense' => 'danger',
                        default => 'gray',
                    })
                    ->searchable()
                    ->sortable()
                    ->label('Klasifikasi'),

                TextColumn::make('balance')
                    ->label('Saldo Saat Ini')
                    ->money('idr')
                    ->alignEnd()
                    ->state(function (\App\Models\Account $record) {
                        $sums = \App\Models\JournalItem::query()
                            ->selectRaw('SUM(debit) as total_debit, SUM(credit) as total_credit')
                            ->where('account_id', $record->id)
                            ->whereHas('journalEntry', fn($q) => $q->where('status', 'posted'))
                            ->first();

                        $debits = $sums->total_debit ?? 0;
                        $credits = $sums->total_credit ?? 0;

                        $normal = $record->getNormalBalance();
                        return $normal === 'debit' ? ($debits - $credits) : ($credits - $debits);
                    }),

                IconColumn::make('is_active')
                    ->boolean()
                    ->sortable()
                    ->label('Aktif'),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('classification')
                    ->label('Klasifikasi')
                    ->relationship('classificationRelation', 'name')
                    ->preload(),
            ])
            ->actions([
                \Filament\Actions\Action::make('ledger')
                    ->label('Buku Besar')
                    ->icon('heroicon-o-book-open')
                    ->color('success')
                    ->modalWidth('7xl')
                    ->modalHeading('Rincian Buku Besar (Ledger)')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup')
                    ->modalContent(function (\App\Models\Account $record) {
                        $items = \App\Models\JournalItem::where('account_id', $record->id)
                            ->whereHas('journalEntry', fn($q) => $q->where('status', 'posted'))
                            ->with('journalEntry')
                            ->get()
                            ->sortBy(fn($item) => $item->journalEntry->date . '_' . $item->journalEntry->id);

                        return view('backoffice.accounting.account-ledger', [
                            'account' => $record,
                            'items' => $items,
                        ]);
                    }),
                EditAction::make()
                    ->modalWidth('md')
                    ->using(fn (\Illuminate\Database\Eloquent\Model $record, array $data): \Illuminate\Database\Eloquent\Model => app(\App\Actions\Account\UpdateAccount::class)->execute($record, $data)),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
