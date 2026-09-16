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
    public string $editingEmployeeName = '';
    public string $editingEmployeeNik = '';
    public float $editBasicSalary = 0.0;
    public float $editAllowanceAmount = 0.0;
    public float $editOvertimeAmount = 0.0;
    public float $editCommissionAmount = 0.0;
    public float $editKpiBonusAmount = 0.0;
    public float $editViolationDeductionAmount = 0.0;
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

            $this->dispatch('toast', [
                'type' => 'success',
                'message' => "Berhasil memproses rekapitulasi gaji untuk {$payroll->total_employees} karyawan.",
            ]);
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Gagal Memproses')
                ->body($e->getMessage())
                ->danger()
                ->send();

            $this->dispatch('toast', [
                'type' => 'error',
                'message' => 'Gagal Memproses: ' . $e->getMessage(),
            ]);
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
        $item = PayrollItem::with('employee')->findOrFail($itemId);
        $this->editingItemId = $item->id;
        $this->editingEmployeeName = $item->employee->name ?? 'Karyawan';
        $this->editingEmployeeNik = $item->employee->nik ?? '';
        $this->editBasicSalary = (float) $item->basic_salary;
        $this->editAllowanceAmount = (float) $item->allowance_amount;
        $this->editOvertimeAmount = (float) $item->overtime_amount;
        $this->editCommissionAmount = (float) $item->commission_amount;
        $this->editKpiBonusAmount = (float) $item->kpi_bonus_amount;
        $this->editViolationDeductionAmount = (float) $item->violation_deduction_amount;
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
        $item->basic_salary = max(0.0, (float) $this->editBasicSalary);
        $item->allowance_amount = max(0.0, (float) $this->editAllowanceAmount);
        $item->overtime_amount = max(0.0, (float) $this->editOvertimeAmount);
        $item->commission_amount = max(0.0, (float) $this->editCommissionAmount);
        $item->kpi_bonus_amount = max(0.0, (float) $this->editKpiBonusAmount);
        $item->violation_deduction_amount = max(0.0, (float) $this->editViolationDeductionAmount);
        $item->other_deduction_amount = max(0.0, (float) $this->editOtherDeductionAmount);
        $item->notes = $this->editNotes;

        // Recalculate net salary including all components
        $earnings = $item->basic_salary + $item->allowance_amount + $item->overtime_amount + $item->commission_amount + $item->kpi_bonus_amount;
        $deductions = $item->violation_deduction_amount + $item->other_deduction_amount;
        $item->net_salary = max(0.0, $earnings - $deductions);
        $item->save();

        // Recalculate parent Payroll header totals
        $payroll = $item->payroll;
        $payroll->update([
            'total_basic_salary' => (float) $payroll->items()->sum('basic_salary'),
            'total_allowances' => (float) $payroll->items()->sum(DB::raw('allowance_amount + overtime_amount')),
            'total_commissions' => (float) $payroll->items()->sum('commission_amount'),
            'total_kpi_bonuses' => (float) $payroll->items()->sum('kpi_bonus_amount'),
            'total_deductions' => (float) $payroll->items()->sum(DB::raw('violation_deduction_amount + other_deduction_amount')),
            'total_net_salary' => (float) $payroll->items()->sum('net_salary'),
        ]);

        $this->showEditItemModal = false;

        Notification::make()
            ->title('Data Payroll Berhasil Diperbarui')
            ->body('Komponen gaji karyawan berhasil disimpan & disesuaikan.')
            ->success()
            ->send();

        $this->dispatch('toast', [
            'type' => 'success',
            'message' => 'Komponen gaji karyawan berhasil disimpan & disesuaikan.',
        ]);
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

            $this->dispatch('toast', [
                'type' => 'success',
                'message' => 'Status payroll diperbarui menjadi Approved.',
            ]);
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Gagal Memproses')
                ->body($e->getMessage())
                ->danger()
                ->send();

            $this->dispatch('toast', [
                'type' => 'error',
                'message' => 'Gagal Memproses: ' . $e->getMessage(),
            ]);
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

            $this->dispatch('toast', [
                'type' => 'warning',
                'message' => 'Payroll berhasil dibatalkan.',
            ]);
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Gagal Membatalkan')
                ->body($e->getMessage())
                ->danger()
                ->send();

            $this->dispatch('toast', [
                'type' => 'error',
                'message' => 'Gagal Membatalkan: ' . $e->getMessage(),
            ]);
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

            $this->dispatch('toast', [
                'type' => 'success',
                'message' => 'Status payroll diperbarui menjadi Paid.',
            ]);
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Gagal Memproses')
                ->body($e->getMessage())
                ->danger()
                ->send();

            $this->dispatch('toast', [
                'type' => 'error',
                'message' => 'Gagal Memproses: ' . $e->getMessage(),
            ]);
        }
    }

    public function exportExcel(int $payrollId)
    {
        return app(\App\Actions\Payroll\ExportPayrollToExcel::class)->execute($payrollId);
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
