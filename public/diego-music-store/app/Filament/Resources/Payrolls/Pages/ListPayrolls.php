<?php

namespace App\Filament\Resources\Payrolls\Pages;

use App\Actions\Payroll\GenerateMonthlyPayroll;
use App\Filament\Resources\Payrolls\PayrollResource;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListPayrolls extends ListRecords
{
    protected static string $resource = PayrollResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('generate')
                ->label('Proses Payroll Bulanan')
                ->color('primary')
                ->icon('heroicon-o-arrow-path')
                ->form([
                    Forms\Components\TextInput::make('period')
                        ->label('Periode Bulan (YYYY-MM)')
                        ->default(now()->format('Y-m'))
                        ->required(),

                    Forms\Components\Select::make('branch_id')
                        ->label('Cabang')
                        ->options(\App\Models\Branch::pluck('name', 'id')->toArray())
                        ->placeholder('Semua Cabang (All Branches)'),
                ])
                ->action(function (array $data) {
                    try {
                        $period = $data['period'] ?? now()->format('Y-m');
                        $branchId = !empty($data['branch_id']) ? (int) $data['branch_id'] : null;

                        $payroll = app(GenerateMonthlyPayroll::class)->execute($period, $branchId, auth()->user());

                        Notification::make()
                            ->title('Payroll Dihasilkan')
                            ->body("Berhasil memproses slip gaji untuk {$payroll->total_employees} karyawan pada periode {$period}.")
                            ->success()
                            ->send();
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->title('Gagal Memproses')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }
}
