<?php

namespace App\Livewire;

use App\Actions\Payroll\GenerateMonthlyPayroll;
use App\Actions\Payroll\ProcessPayrollPayment;
use App\Helpers\BranchHelper;
use App\Models\Branch;
use App\Models\Payroll;
use App\Models\PayrollItem;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class PosPayrollManagement extends Component
{
    public string $filterMonth = '';
    public string $filterBranchId = '';

    // Modal Edit Payroll Item state
    public bool $showEditItemModal = false;
    public ?int $editingItemId = null;
    public float $editAllowanceAmount = 0.0;
    public float $editOtherDeductionAmount = 0.0;
    public string $editNotes = '';

    // Modal Preview Slip Gaji state
    public bool $showPreviewModal = false;
    public ?PayrollItem $previewItem = null;

    public function mount(): void
    {
        $this->filterMonth = now()->format('Y-m');
        $this->filterBranchId = (string) (BranchHelper::getActiveBranchId() ?: '');
    }

    public function generatePayroll(): void
    {
        try {
            $branchId = !empty($this->filterBranchId) ? (int) $this->filterBranchId : null;
            $payroll = app(GenerateMonthlyPayroll::class)->execute($this->filterMonth, $branchId, auth()->user());

            Notification::make()
                ->title('Payroll Berhasil Dihasilkan')
                ->body("Berhasil memproses rekapitulasi gaji untuk {$payroll->total_employees} karyawan.")
                ->success()
                ->send();
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Gagal Memproses')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function openPreviewModal(int $itemId): void
    {
        $this->previewItem = PayrollItem::with(['payroll', 'employee.branch', 'branch'])->find($itemId);
        if ($this->previewItem) {
            $this->showPreviewModal = true;
        }
    }

    public function closePreviewModal(): void
    {
        $this->showPreviewModal = false;
        $this->previewItem = null;
    }

    public function openEditItemModal(int $itemId): void
    {
        $item = PayrollItem::findOrFail($itemId);
        $this->editingItemId = $item->id;
        $this->editAllowanceAmount = (float) $item->allowance_amount;
        $this->editOtherDeductionAmount = (float) $item->other_deduction_amount;
        $this->editNotes = $item->notes ?: '';
        $this->showEditItemModal = true;
    }

    public function saveItemDetails(): void
    {
        if (!$this->editingItemId) {
            return;
        }

        $item = PayrollItem::findOrFail($this->editingItemId);
        $item->allowance_amount = $this->editAllowanceAmount;
        $item->other_deduction_amount = $this->editOtherDeductionAmount;
        $item->notes = $this->editNotes;

        // Recalculate net salary including overtime_amount
        $item->net_salary = max(0.0, ($item->basic_salary + $item->allowance_amount + $item->overtime_amount + $item->commission_amount + $item->kpi_bonus_amount) - ($item->violation_deduction_amount + $item->other_deduction_amount));
        $item->save();

        // Recalculate parent Payroll header totals
        $payroll = $item->payroll;
        $payroll->update([
            'total_allowances' => $payroll->items()->sum(DB::raw('allowance_amount + overtime_amount')),
            'total_deductions' => $payroll->items()->sum(DB::raw('violation_deduction_amount + other_deduction_amount')),
            'total_net_salary' => $payroll->items()->sum('net_salary'),
        ]);

        $this->showEditItemModal = false;

        Notification::make()
            ->title('Detail Gaji Diperbarui')
            ->body('Tunjangan/Potongan manual karyawan berhasil disimpan.')
            ->success()
            ->send();
    }

    public function approvePayroll(int $payrollId): void
    {
        try {
            app(\App\Actions\Payroll\ApprovePayroll::class)->execute($payrollId, auth()->user());

            Notification::make()
                ->title('Payroll Disetujui')
                ->body('Status payroll diperbarui menjadi Approved.')
                ->success()
                ->send();
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Gagal Memproses')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function cancelPayroll(int $payrollId): void
    {
        try {
            app(\App\Actions\Payroll\CancelPayroll::class)->execute($payrollId, auth()->user());

            Notification::make()
                ->title('Payroll Dibatalkan')
                ->body('Payroll berhasil dibatalkan. Anda dapat membuat ulang payroll untuk periode ini.')
                ->warning()
                ->send();
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Gagal Membatalkan')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function processPayment(int $payrollId): void
    {
        try {
            app(ProcessPayrollPayment::class)->execute($payrollId, auth()->user());

            Notification::make()
                ->title('Pembayaran Gaji Selesai')
                ->body('Status payroll diperbarui menjadi Paid.')
                ->success()
                ->send();
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Gagal Memproses')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function exportExcel(int $payrollId)
    {
        $payroll = Payroll::with(['items.employee', 'branch'])->findOrFail($payrollId);

        $filename = "Payroll_Gaji_{$payroll->period}_{$payroll->payroll_code}.csv";

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($payroll) {
            $file = fopen('php://output', 'w');
            // Write CSV Header
            fputcsv($file, [
                'NIK',
                'Nama Karyawan',
                'Bank',
                'No Rekening',
                'Atas Nama',
                'Gaji Pokok',
                'Tunjangan Tetap',
                'Tunjangan Lembur',
                'Komisi Sales',
                'Bonus KPI',
                'Potongan Presensi',
                'Potongan Lain',
                'Gaji Bersih (Take Home Pay)',
            ]);

            foreach ($payroll->items as $item) {
                fputcsv($file, [
                    $item->employee->nik ?? '-',
                    $item->employee->name ?? '-',
                    $item->bank_name ?? 'BCA',
                    $item->bank_account_number ?? '-',
                    $item->bank_account_holder ?? '-',
                    $item->basic_salary,
                    $item->allowance_amount,
                    $item->overtime_amount,
                    $item->commission_amount,
                    $item->kpi_bonus_amount,
                    $item->violation_deduction_amount,
                    $item->other_deduction_amount,
                    $item->net_salary,
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function render()
    {
        $branches = Branch::where('is_active', true)->orderBy('name')->get();

        $branchId = !empty($this->filterBranchId) ? (int) $this->filterBranchId : null;

        $payrollQuery = Payroll::with(['items.employee', 'branch'])
            ->where('period', $this->filterMonth);

        if ($branchId) {
            $payrollQuery->where('branch_id', $branchId);
        }

        $currentPayroll = $payrollQuery->first();

        // Active logo URL for POS layout
        $activeBranchId = BranchHelper::getActiveBranchId();
        $activeBranch = $activeBranchId ? Branch::find($activeBranchId) : null;
        $selectedLogoUrl = ($activeBranch && !empty($activeBranch->logo_path) && trim($activeBranch->logo_path) !== '')
            ? (str_starts_with($activeBranch->logo_path, 'http') ? $activeBranch->logo_path : asset('storage/' . $activeBranch->logo_path))
            : asset('images/default-store-logo.png');

        return view('livewire.pos-payroll-management', [
            'branches' => $branches,
            'currentPayroll' => $currentPayroll,
            'selectedLogoUrl' => $selectedLogoUrl,
        ])->layout('layouts.pos', ['title' => 'Payroll & Gaji Karyawan']);
    }
}
