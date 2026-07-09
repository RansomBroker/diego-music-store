<?php

namespace App\Filament\Resources\ScheduledJournalEntries\Tables;

use App\Actions\Accounting\ProcessScheduledJournalEntries;
use App\Actions\Accounting\UpdateScheduledJournalEntry;
use App\Models\ScheduledJournalEntry;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;

class ScheduledJournalEntriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('description')
                    ->label('Deskripsi Template')
                    ->searchable()
                    ->sortable()
                    ->placeholder('Tanpa deskripsi')
                    ->weight('bold'),

                TextColumn::make('branch.name')
                    ->label('Cabang')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('frequency')
                    ->label('Jadwal')
                    ->formatStateUsing(function (ScheduledJournalEntry $record) {
                        $freq = match ($record->frequency) {
                            'daily' => 'Hari',
                            'weekly' => 'Minggu',
                            'monthly' => 'Bulan',
                            'yearly' => 'Tahun',
                            default => 'Bulan',
                        };
                        return "Setiap {$record->interval} {$freq}";
                    }),

                TextColumn::make('start_date')
                    ->label('Tgl Mulai')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('duration_months')
                    ->label('Durasi')
                    ->formatStateUsing(fn ($state) => $state ? "{$state} Bulan" : 'Selamanya'),

                TextColumn::make('last_run_at')
                    ->label('Terakhir Dijalankan')
                    ->date('d M Y')
                    ->placeholder('-'),

                TextColumn::make('next_run_at')
                    ->label('Rencana Berikutnya')
                    ->date('d M Y')
                    ->placeholder('-'),

                TextColumn::make('debit_total')
                    ->label('Total Nominal')
                    ->money('idr')
                    ->state(fn ($record) => $record->items->sum('debit')),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'paused' => 'warning',
                        'completed' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'active' => 'Aktif',
                        'paused' => 'Jeda',
                        'completed' => 'Selesai',
                        default => ucfirst($state),
                    })
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status Jadwal')
                    ->options([
                        'active' => 'Aktif',
                        'paused' => 'Jeda',
                        'completed' => 'Selesai',
                    ]),

                SelectFilter::make('branch_id')
                    ->label('Cabang')
                    ->relationship('branch', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Action::make('run_now')
                    ->label('Jalankan')
                    ->icon('heroicon-o-play')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Jalankan Jadwal Jurnal')
                    ->modalDescription('Apakah Anda yakin ingin menjalankan jadwal ini sekarang secara manual? Jurnal umum otomatis akan dibuat untuk tanggal rencana berikutnya.')
                    ->visible(fn (ScheduledJournalEntry $record) => $record->status === 'active' && $record->next_run_at !== null)
                    ->action(function (ScheduledJournalEntry $record) {
                        $count = app(ProcessScheduledJournalEntries::class)->execute($record, true);
                        if ($count > 0) {
                            Notification::make()
                                ->title('Jurnal Berhasil Dibuat')
                                ->body("Berhasil membuat {$count} jurnal umum secara otomatis.")
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('Tidak Ada Jurnal Dibuat')
                                ->body('Jadwal tidak memerlukan eksekusi saat ini.')
                                ->warning()
                                ->send();
                        }
                    }),

                EditAction::make()
                    ->modalWidth('4xl')
                    ->using(fn (Model $record, array $data): Model => app(UpdateScheduledJournalEntry::class)->execute($record, $data)),

                DeleteAction::make(),
            ]);
    }
}
