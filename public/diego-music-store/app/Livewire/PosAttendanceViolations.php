<?php

namespace App\Livewire;

use App\Models\AttendanceViolationLog;
use App\Models\AttendanceViolationRule;
use App\Models\Branch;
use App\Models\Employee;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithPagination;

class PosAttendanceViolations extends Component
{
    use WithPagination;

    // ── Filters & Navigation ─────────────────────────────────────────────
    public ?int $filterBranchId = null;
    public string $filterMonth = '';
    public ?int $selectedEmployeeId = null;
    public string $activeTab = 'recap'; // 'recap', 'rules', 'logs'
    public string $search = '';
    public int $perPage = 15;

    public function updatingPerPage(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage($value): void
    {
        $this->perPage = (int) $value;
        $this->resetPage();
    }

    // ── Modal & Form State (Violation Rule) ──────────────────────────────
    public bool $showRuleModal = false;
    public ?int $editingRuleId = null;
    public string $ruleName = '';
    public string $violationType = 'late_in'; // 'late_in', 'early_out', 'unexcused_absence', 'leave_over_quota', 'custom'
    public int $minMinutes = 1;
    public ?int $maxMinutes = 15;
    public string $deductionType = 'fixed_amount'; // 'fixed_amount', 'percentage_per_minute', 'percentage_daily_salary'
    public float|string $deductionAmount = 10000;
    public bool $isActive = true;

    // ── Selection State ──────────────────────────────────────────────────
    public array $selectedLogIds = [];

    public function mount(): void
    {
        $this->filterMonth = now()->format('Y-m');
        $this->filterBranchId = session('pos_active_branch_id') ?: auth()->user()?->branches()->first()?->id;
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function openRuleModal(?int $id = null): void
    {
        $this->resetRuleForm();
        if ($id) {
            $rule = AttendanceViolationRule::findOrFail($id);
            $this->editingRuleId = $rule->id;
            $this->ruleName = $rule->name;
            $this->violationType = $rule->violation_type;
            $this->minMinutes = $rule->min_minutes;
            $this->maxMinutes = $rule->max_minutes;
            $this->deductionType = $rule->deduction_type;
            $this->deductionAmount = (float) $rule->deduction_amount;
            $this->isActive = $rule->is_active;
        }
        $this->showRuleModal = true;
    }

    public function resetRuleForm(): void
    {
        $this->editingRuleId = null;
        $this->ruleName = '';
        $this->violationType = 'late_in';
        $this->minMinutes = 1;
        $this->maxMinutes = 15;
        $this->deductionType = 'fixed_amount';
        $this->deductionAmount = 10000;
        $this->isActive = true;
    }

    public function saveRule(): void
    {
        $this->validate([
            'ruleName' => 'required|string|max:255',
            'violationType' => 'required|in:late_in,early_out,unexcused_absence,leave_over_quota,custom',
            'minMinutes' => 'required|integer|min:0',
            'maxMinutes' => 'nullable|integer|gte:minMinutes',
            'deductionType' => 'required|in:fixed_amount,percentage_per_minute,percentage_daily_salary,per_occurrence',
            'deductionAmount' => 'required|numeric|min:0',
            'isActive' => 'boolean',
        ]);

        try {
            AttendanceViolationRule::updateOrCreate(
                ['id' => $this->editingRuleId],
                [
                    'name' => $this->ruleName,
                    'violation_type' => $this->violationType,
                    'min_minutes' => $this->minMinutes,
                    'max_minutes' => $this->maxMinutes,
                    'deduction_type' => $this->deductionType,
                    'deduction_amount' => $this->deductionAmount,
                    'is_active' => $this->isActive,
                ]
            );

            $this->dispatch('toast', [
                'type' => 'success',
                'title' => 'Aturan Pelanggaran Disimpan',
                'body' => "Aturan \"{$this->ruleName}\" berhasil disimpan."
            ]);

            Notification::make()
                ->title('Aturan Pelanggaran Disimpan')
                ->success()
                ->send();

            $this->showRuleModal = false;
            $this->resetRuleForm();
        } catch (\Exception $e) {
            $this->dispatch('toast', [
                'type' => 'error',
                'title' => 'Gagal Menyimpan Aturan',
                'body' => $e->getMessage()
            ]);
        }
    }

    public function toggleRuleStatus(int $id): void
    {
        $rule = AttendanceViolationRule::findOrFail($id);
        $rule->update(['is_active' => !$rule->is_active]);

        $this->dispatch('toast', [
            'type' => 'info',
            'title' => 'Status Aturan Diperbarui',
            'body' => "Status aturan \"{$rule->name}\" diubah menjadi " . ($rule->is_active ? 'Aktif' : 'Non-Aktif')
        ]);
    }

    public function deleteRule(int $id): void
    {
        $rule = AttendanceViolationRule::findOrFail($id);
        $ruleName = $rule->name;
        $rule->delete();

        $this->dispatch('toast', [
            'type' => 'success',
            'title' => 'Aturan Dihapus',
            'body' => "Aturan \"{$ruleName}\" berhasil dihapus."
        ]);
    }

    public function approveLog(int $logId): void
    {
        $log = AttendanceViolationLog::findOrFail($logId);
        $log->update([
            'status' => 'approved',
            'approved_by' => Auth::id(),
        ]);

        $this->dispatch('toast', [
            'type' => 'success',
            'title' => 'Log Pelanggaran Disetujui',
            'body' => "Denda potongan presensi Rp " . number_format($log->deduction_amount, 0, ',', '.') . " disetujui untuk dimasukkan ke Payroll."
        ]);
    }

    public function waiveLog(int $logId): void
    {
        $log = AttendanceViolationLog::findOrFail($logId);
        $log->update([
            'status' => 'waived',
            'approved_by' => Auth::id(),
        ]);

        $this->dispatch('toast', [
            'type' => 'info',
            'title' => 'Denda Dihapuskan (Waived)',
            'body' => "Denda dimaafkan/dihapuskan bagi karyawan {$log->employee?->name}."
        ]);
    }

    public function bulkApproveLogs(): void
    {
        if (empty($this->selectedLogIds)) {
            return;
        }

        $count = AttendanceViolationLog::whereIn('id', $this->selectedLogIds)
            ->where('status', 'pending')
            ->update([
                'status' => 'approved',
                'approved_by' => Auth::id(),
            ]);

        $this->selectedLogIds = [];

        $this->dispatch('toast', [
            'type' => 'success',
            'title' => 'Persetujuan Massal Berhasil',
            'body' => "{$count} log potongan denda presensi berhasil disetujui untuk Payroll."
        ]);
    }

    public function exportCsv()
    {
        [$year, $month] = explode('-', $this->filterMonth ?: now()->format('Y-m'));

        $employeesQuery = Employee::with(['branch'])->where('is_active', true);
        if ($this->filterBranchId) {
            $employeesQuery->where('branch_id', $this->filterBranchId);
        }
        $employees = $employeesQuery->orderBy('name', 'asc')->get();

        $filename = "rekap_potongan_presensi_{$this->filterMonth}.csv";

        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename={$filename}",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function () use ($employees, $year, $month) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF"); // UTF-8 BOM

            fputcsv($file, ['NIK', 'Nama Karyawan', 'Cabang', 'Total Keterlambatan (Menit)', 'Total Pulang Cepat (Menit)', 'Total Denda Presensi (Rp)', 'Denda Disetujui (Rp)', 'Status Payroll']);

            foreach ($employees as $emp) {
                $logsQuery = AttendanceViolationLog::where('employee_id', $emp->id)
                    ->whereYear('date', $year)
                    ->whereMonth('date', $month);

                $lateMins = (int) (clone $logsQuery)->where('violation_type', 'late_in')->sum('late_early_minutes');
                $earlyMins = (int) (clone $logsQuery)->where('violation_type', 'early_out')->sum('late_early_minutes');
                $totalDeduction = (float) $logsQuery->sum('deduction_amount');
                $approvedDeduction = (float) (clone $logsQuery)->where('status', 'approved')->sum('deduction_amount');

                fputcsv($file, [
                    $emp->nik,
                    $emp->name,
                    $emp->branch?->name ?: '-',
                    $lateMins,
                    $earlyMins,
                    $totalDeduction,
                    $approvedDeduction,
                    $approvedDeduction > 0 ? 'Siap Masuk Slip Gaji' : 'Tidak Ada Potongan',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function render()
    {
        $activeBranchId = session('pos_active_branch_id') ?: auth()->user()?->branches()->first()?->id;
        $branchId       = $this->filterBranchId ?: $activeBranchId;
        $branch         = $branchId ? Branch::find($branchId) : Branch::first();

        $selectedLogoUrl = ($branch && !empty($branch->logo_path) && trim($branch->logo_path) !== '')
            ? Storage::url($branch->logo_path)
            : null;

        $branches = Branch::where('is_active', true)->get();

        [$year, $month] = explode('-', $this->filterMonth ?: now()->format('Y-m'));

        $employeesQuery = Employee::where('is_active', true);
        if ($this->filterBranchId) {
            $employeesQuery->where('branch_id', $this->filterBranchId);
        }
        $employees = $employeesQuery->orderBy('name', 'asc')->get();

        // 1. Rekapitulasi Denda Per Karyawan
        $recapData = collect();
        $totalLateMinutesPeriod = 0;
        $totalEarlyMinutesPeriod = 0;
        $totalDeductionPeriod = 0.0;
        $totalApprovedDeductionPeriod = 0.0;

        foreach ($employees as $emp) {
            $logsQuery = AttendanceViolationLog::where('employee_id', $emp->id)
                ->whereYear('date', $year)
                ->whereMonth('date', $month);

            $lateMins = (int) (clone $logsQuery)->where('violation_type', 'late_in')->sum('late_early_minutes');
            $earlyMins = (int) (clone $logsQuery)->where('violation_type', 'early_out')->sum('late_early_minutes');
            $totalDeduction = (float) $logsQuery->sum('deduction_amount');
            $approvedDeduction = (float) (clone $logsQuery)->where('status', 'approved')->sum('deduction_amount');
            $pendingDeduction = (float) (clone $logsQuery)->where('status', 'pending')->sum('deduction_amount');
            $pendingCount = (clone $logsQuery)->where('status', 'pending')->count();

            $totalLateMinutesPeriod += $lateMins;
            $totalEarlyMinutesPeriod += $earlyMins;
            $totalDeductionPeriod += $totalDeduction;
            $totalApprovedDeductionPeriod += $approvedDeduction;

            $recapData->push([
                'employee' => $emp,
                'late_minutes' => $lateMins,
                'early_minutes' => $earlyMins,
                'total_deduction' => $totalDeduction,
                'approved_deduction' => $approvedDeduction,
                'pending_deduction' => $pendingDeduction,
                'pending_count' => $pendingCount,
            ]);
        }

        // 2. Aturan Pelanggaran (Rules)
        $rules = AttendanceViolationRule::orderBy('id', 'asc')->get();

        // 3. Log Detail Pelanggaran
        $logsQuery = AttendanceViolationLog::with(['employee', 'attendance', 'rule', 'approver'])
            ->whereYear('date', $year)
            ->whereMonth('date', $month);

        if ($this->filterBranchId) {
            $logsQuery->whereHas('employee', function ($q) {
                $q->where('branch_id', $this->filterBranchId);
            });
        }

        if ($this->selectedEmployeeId) {
            $logsQuery->where('employee_id', $this->selectedEmployeeId);
        }

        $logs = $logsQuery->orderBy('date', 'desc')->orderBy('id', 'desc')->paginate($this->perPage > 0 ? $this->perPage : 1000);

        return view('livewire.pos-attendance-violations', [
            'selectedLogoUrl'               => $selectedLogoUrl,
            'branches'                      => $branches,
            'employees'                     => $employees,
            'recapData'                     => $recapData,
            'rules'                         => $rules,
            'logs'                          => $logs,
            'totalLateMinutesPeriod'        => $totalLateMinutesPeriod,
            'totalEarlyMinutesPeriod'       => $totalEarlyMinutesPeriod,
            'totalDeductionPeriod'          => $totalDeductionPeriod,
            'totalApprovedDeductionPeriod' => $totalApprovedDeductionPeriod,
        ])->layout('layouts.pos', ['title' => 'Denda & Potongan Presensi — POS']);
    }
}
