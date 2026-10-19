<?php

namespace App\Filament\Resources\StockOpnamePeriods\Pages;

use App\Filament\Resources\StockOpnamePeriods\StockOpnamePeriodResource;
use App\Models\StockOpnamePeriod;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\DB;

class EditStockOpnamePeriod extends EditRecord
{
    protected static string $resource = StockOpnamePeriodResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('activate')
                ->label('Aktifkan Periode')
                ->icon('heroicon-o-play')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Aktifkan periode Stock Opname?')
                ->modalDescription('Setelah aktif, daftar penugasan dikunci dan tugas siap dikerjakan karyawan.')
                ->visible(fn ($record) => $record->status === StockOpnamePeriod::STATUS_DRAFT)
                ->action(function (): void {
                    $user = auth()->user();
                    if (!$user || !$user->hasRole(['owner', 'admin', 'super_admin', 'Owner', 'Admin', 'Super Admin'])) {
                        abort(403);
                    }

                    DB::transaction(function (): void {
                        $period = StockOpnamePeriod::query()->lockForUpdate()->findOrFail($this->record->id);

                        if ($period->status !== StockOpnamePeriod::STATUS_DRAFT) {
                            throw new \RuntimeException('Hanya periode draft yang dapat diaktifkan.');
                        }

                        if (!$period->assignments()->exists()) {
                            Notification::make()
                                ->title('Belum ada penugasan')
                                ->body('Tambahkan minimal satu lokasi dan karyawan sebelum mengaktifkan periode.')
                                ->warning()
                                ->send();

                            return;
                        }

                        $period->update(['status' => StockOpnamePeriod::STATUS_ACTIVE]);
                    });

                    if ($this->record->fresh()->status === StockOpnamePeriod::STATUS_ACTIVE) {
                        $this->record->refresh();
                        Notification::make()
                            ->title('Periode Stock Opname aktif')
                            ->success()
                            ->send();
                    }
                }),
            DeleteAction::make()
                ->visible(fn ($record) => $record->status === StockOpnamePeriod::STATUS_DRAFT
                    && $record->assignments()->doesntExist()),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $user = auth()->user();
        if (!$user || !$user->hasRole(['owner', 'admin', 'super_admin', 'Owner', 'Admin', 'Super Admin'])) {
            abort(403);
        }

        $data['branch_id'] = $this->record->branch_id;
        $data['period'] = $this->record->period->toDateString();
        $data['status'] = $this->record->status;
        $data['created_by'] = $this->record->created_by;

        return $data;
    }
}
