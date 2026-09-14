<?php

namespace App\Filament\Resources\KpiEvaluations\Pages;

use App\Filament\Resources\KpiEvaluations\KpiEvaluationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListKpiEvaluations extends ListRecords
{
    protected static string $resource = KpiEvaluationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('recalculate')
                ->label('Kalkulasi Realtime KPI')
                ->color('info')
                ->icon('heroicon-o-arrow-path')
                ->action(function () {
                    $action = app(\App\Actions\Kpi\CalculateEmployeeKpi::class);
                    $employees = \App\Models\Employee::where('is_active', true)->get();
                    $period = now()->format('Y-m');

                    $count = 0;
                    foreach ($employees as $emp) {
                        $action->execute($emp, $period);
                        $count++;
                    }

                    \Filament\Notifications\Notification::make()
                        ->title('Kalkulasi Selesai')
                        ->body("Berhasil memperbarui skor KPI & bonus untuk {$count} karyawan.")
                        ->success()
                        ->send();
                }),
        ];
    }
}
