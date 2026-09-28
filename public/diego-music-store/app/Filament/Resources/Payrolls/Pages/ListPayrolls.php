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
            Actions\Action::make('exportRekapExcel')
                ->label('Export Rekap Excel (Semua)')
                ->color('success')
                ->icon('heroicon-o-arrow-down-tray')
                ->extraAttributes([
                    'style' => 'color: #ffffff !important;',
                    'class' => '!text-white [&_*]:!text-white [&_svg]:!text-white',
                ])
                ->form([
                    Forms\Components\Select::make('payroll_id')
                        ->label('Pilih Batch Payroll / Periode')
                        ->options(fn () => \App\Models\Payroll::with('branch')->latest()->get()->mapWithKeys(fn ($p) => [
                            $p->id => "{$p->payroll_code} - Periode {$p->period} (" . ($p->branch?->name ?? 'Semua Cabang') . ")",
                        ]))
                        ->default(fn () => \App\Models\Payroll::latest()->value('id'))
                        ->required(),
                ])
                ->action(function (array $data) {
                    return redirect()->route('pos.payroll.export-excel', $data['payroll_id']);
                }),

            Actions\Action::make('payPayroll')
                ->label('Bayar Payroll & Buat Jurnal GL')
                ->color('success')
                ->icon('heroicon-o-banknotes')
                ->form([
                    Forms\Components\Select::make('payroll_id')
                        ->label('Pilih Batch Payroll')
                        ->options(fn () => \App\Models\Payroll::where('status', '!=', 'paid')->where('status', '!=', 'cancelled')->latest()->get()->mapWithKeys(fn ($p) => [
                            $p->id => "{$p->payroll_code} - Periode {$p->period} (THP: Rp " . number_format($p->total_net_salary, 0, ',', '.') . ")",
                        ]))
                        ->required(),
                    Forms\Components\Select::make('payment_account_id')
                        ->label('Rekening Kas/Bank Pembayaran')
                        ->options(function () {
                            return \App\Models\Account::where('classification', 'asset')
                                ->where(function ($q) {
                                    $q->where('code', 'like', '1111%')
                                      ->orWhere('code', 'like', '1112%');
                                })
                                ->where('is_active', true)
                                ->pluck('name', 'id');
                        })
                        ->default(fn () => \App\Helpers\AccountHelper::resolveAccountId('111201001', 'BANK BCA', 'asset'))
                        ->required(),
                    Forms\Components\DatePicker::make('paid_at')
                        ->label('Tanggal Pembayaran')
                        ->default(now()->format('Y-m-d'))
                        ->required(),
                ])
                ->action(function (array $data) {
                    try {
                        $payroll = app(\App\Actions\Payroll\ProcessPayrollPayment::class)->execute(
                            (int) $data['payroll_id'],
                            auth()->user(),
                            !empty($data['payment_account_id']) ? (int) $data['payment_account_id'] : null,
                            $data['paid_at'] ?? null
                        );

                        Notification::make()
                            ->title('Payroll Berhasil Dibayarkan')
                            ->body("Payroll {$payroll->payroll_code} berhasil dibayar dan dibukukan ke jurnal {$payroll->journal_no}.")
                            ->success()
                            ->send();
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->title('Gagal Membayar')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

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
