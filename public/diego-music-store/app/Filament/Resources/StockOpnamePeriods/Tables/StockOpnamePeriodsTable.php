<?php

namespace App\Filament\Resources\StockOpnamePeriods\Tables;

use App\Actions\StockOpname\ActivateStockOpnamePeriod;
use App\Actions\StockOpname\FinalizeStockOpnamePeriod;
use App\Models\StockOpnameAssignment;
use App\Models\StockOpnamePeriod;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Validation\ValidationException;

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
                ViewAction::make()
                    ->modalWidth('4xl'),

                EditAction::make()
                    ->modalWidth('4xl')
                    ->visible(fn (StockOpnamePeriod $record) => $record->status === StockOpnamePeriod::STATUS_DRAFT)
                    ->mutateFormDataUsing(function (array $data, StockOpnamePeriod $record): array {
                        $user = auth()->user();
                        $allowedRoles = ['owner', 'admin', 'super_admin', 'Owner', 'Admin', 'Super Admin'];
                        if (!$user || !$user->hasRole($allowedRoles)) {
                            abort(403);
                        }

                        $data['branch_id'] = $record->branch_id;
                        $data['period'] = $record->period->toDateString();
                        $data['status'] = $record->status;
                        $data['created_by'] = $record->created_by;

                        return $data;
                    }),

                Action::make('activate')
                    ->label('Aktifkan')
                    ->icon('heroicon-o-play')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Aktifkan Periode Stock Opname?')
                    ->modalDescription('Setelah aktif, daftar penugasan dikunci dan tugas siap dikerjakan karyawan.')
                    ->visible(fn (StockOpnamePeriod $record) => $record->status === StockOpnamePeriod::STATUS_DRAFT)
                    ->action(function (StockOpnamePeriod $record): void {
                        $user = auth()->user();
                        $allowedRoles = ['owner', 'admin', 'super_admin', 'Owner', 'Admin', 'Super Admin'];
                        if (!$user || !$user->hasRole($allowedRoles)) {
                            abort(403);
                        }

                        try {
                            app(ActivateStockOpnamePeriod::class)->execute($record);

                            Notification::make()
                                ->title('Periode Stock Opname berhasil diaktifkan')
                                ->success()
                                ->send();
                        } catch (ValidationException $e) {
                            Notification::make()
                                ->title('Gagal mengaktifkan periode')
                                ->body(collect($e->errors())->flatten()->first() ?? 'Terjadi kesalahan validasi.')
                                ->danger()
                                ->send();
                        }
                    }),

                Action::make('review')
                    ->label('Review')
                    ->icon('heroicon-o-clipboard-document-check')
                    ->color('warning')
                    ->url(fn (StockOpnamePeriod $record) => \App\Filament\Pages\StockOpnameReview::getUrl(['period_id' => $record->id]))
                    ->visible(fn (StockOpnamePeriod $record) => in_array($record->status, [StockOpnamePeriod::STATUS_REVIEW, StockOpnamePeriod::STATUS_ACTIVE])),

                Action::make('finalize')
                    ->label('Finalisasi')
                    ->icon('heroicon-o-lock-closed')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Finalisasi Periode Stok Opname')
                    ->modalDescription('Finalisasi akan menjumlahkan hitungan fisik seluruh lokasi, menghitung selisih stok cabang, lalu menerbitkan penyesuaian stok dan jurnal akuntansi otomatis.')
                    ->modalSubmitActionLabel('Ya, Finalisasi')
                    ->visible(fn (StockOpnamePeriod $record) => in_array($record->status, [StockOpnamePeriod::STATUS_ACTIVE, StockOpnamePeriod::STATUS_REVIEW], true)
                        && $record->assignments()->exists()
                        && $record->assignments()->where('status', '!=', StockOpnameAssignment::STATUS_COMPLETED)->doesntExist())
                    ->action(function (StockOpnamePeriod $record): void {
                        $user = auth()->user();
                        $allowedRoles = ['owner', 'admin', 'super_admin', 'Owner', 'Admin', 'Super Admin'];
                        if (!$user || !$user->hasRole($allowedRoles)) {
                            abort(403);
                        }

                        try {
                            $opname = app(FinalizeStockOpnamePeriod::class)->execute($record);

                            Notification::make()
                                ->title('Periode Stock Opname berhasil difinalisasi')
                                ->body('Dokumen: ' . $opname->opname_number . '. Penyesuaian stok dan jurnal telah diproses.')
                                ->success()
                                ->send();
                        } catch (ValidationException $e) {
                            Notification::make()
                                ->title('Gagal memfinalisasi periode')
                                ->body(collect($e->errors())->flatten()->first() ?? 'Terjadi kesalahan validasi.')
                                ->danger()
                                ->send();
                        }
                    }),

                Action::make('cancel')
                    ->label('Batalkan')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Batalkan Periode Stock Opname?')
                    ->modalDescription('Periode ini akan ditandai dibatalkan dan tidak dapat dilanjutkan kembali.')
                    ->visible(fn (StockOpnamePeriod $record) => in_array($record->status, [StockOpnamePeriod::STATUS_DRAFT, StockOpnamePeriod::STATUS_ACTIVE]))
                    ->action(function (StockOpnamePeriod $record): void {
                        $user = auth()->user();
                        $allowedRoles = ['owner', 'admin', 'super_admin', 'Owner', 'Admin', 'Super Admin'];
                        if (!$user || !$user->hasRole($allowedRoles)) {
                            abort(403);
                        }

                        $record->update(['status' => StockOpnamePeriod::STATUS_CANCELLED]);

                        Notification::make()
                            ->title('Periode Stock Opname dibatalkan')
                            ->warning()
                            ->send();
                    }),

                DeleteAction::make()
                    ->visible(fn (StockOpnamePeriod $record) => $record->status === StockOpnamePeriod::STATUS_DRAFT
                        && $record->assignments()->doesntExist()),
            ]);
    }
}
