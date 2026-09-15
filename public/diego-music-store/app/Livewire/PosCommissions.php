<?php

namespace App\Livewire;

use App\Actions\Commission\ApproveCommissionRecap;

use App\Models\Branch;
use App\Models\CommissionScheme;
use App\Models\Employee;
use App\Models\Product;
use App\Models\SaleCategory;
use App\Models\SalesCommissionLog;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithPagination;
use Throwable;

class PosCommissions extends Component
{
    use WithPagination;

    // ── Filter State ─────────────────────────────────────────────────────
    public ?int $filterBranchId = null;
    public string $filterMonth = '';
    public ?int $selectedEmployeeId = null;
    public string $activeTab = 'recap'; // 'recap', 'schemes', 'logs'
    public string $search = '';

    // ── Modal & Form State (Commission Scheme) ───────────────────────────
    public bool $showSchemeModal = false;
    public ?int $editingSchemeId = null;
    public string $schemeName = '';
    public string $calculationType = 'percentage'; // 'percentage' or 'fixed_amount'
    public float|string $rate = 2.0;
    public string $appliesTo = 'all_sales'; // 'all_sales', 'category', 'product'
    public ?int $targetProductId = null;
    public ?int $targetSaleCategoryId = null;
    public array $targetEmployeeIds = [];
    public float|string $minMonthlySalesTarget = 0;
    public bool $isActive = true;

    public function mount(): void
    {
        $this->filterMonth = now()->format('Y-m');
        $this->filterBranchId = session('pos_active_branch_id') ?: auth()->user()?->branches()->first()?->id;
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function openSchemeModal(?int $id = null): void
    {
        $this->resetSchemeForm();
        if ($id) {
            $scheme = CommissionScheme::with('employees')->findOrFail($id);
            $this->editingSchemeId = $scheme->id;
            $this->schemeName = $scheme->name;
            $this->calculationType = $scheme->calculation_type;
            $this->rate = (float) $scheme->rate;
            $this->appliesTo = $scheme->applies_to;
            $this->targetProductId = $scheme->target_product_id;
            $this->targetSaleCategoryId = $scheme->target_sale_category_id;
            $this->targetEmployeeIds = $scheme->employees->pluck('id')->map(fn($v) => (int)$v)->toArray();
            if ($scheme->employee_id && !in_array($scheme->employee_id, $this->targetEmployeeIds)) {
                $this->targetEmployeeIds[] = (int) $scheme->employee_id;
            }
            $this->minMonthlySalesTarget = (float) $scheme->min_monthly_sales_target;
            $this->isActive = $scheme->is_active;
        }
        $this->showSchemeModal = true;
    }

    public function resetSchemeForm(): void
    {
        $this->editingSchemeId = null;
        $this->schemeName = '';
        $this->calculationType = 'percentage';
        $this->rate = 2.0;
        $this->appliesTo = 'all_sales';
        $this->targetProductId = null;
        $this->targetSaleCategoryId = null;
        $this->targetEmployeeIds = [];
        $this->minMonthlySalesTarget = 0;
        $this->isActive = true;
    }

    public function saveScheme(): void
    {
        if (is_string($this->rate)) {
            $cleaned = preg_replace('/[^\d.]/', '', str_replace(',', '.', $this->rate));
            $this->rate = $cleaned !== '' ? (float) $cleaned : 0;
        }
        if (is_string($this->minMonthlySalesTarget)) {
            $cleaned = preg_replace('/[^\d.]/', '', str_replace(',', '.', $this->minMonthlySalesTarget));
            $this->minMonthlySalesTarget = $cleaned !== '' ? (float) $cleaned : 0;
        }

        $this->validate([
            'schemeName' => 'required|string|max:255',
            'calculationType' => 'required|in:percentage,fixed_amount',
            'rate' => 'required|numeric|min:0',
            'appliesTo' => 'required|in:all_sales,category,product',
            'targetProductId' => 'nullable|exists:products,id',
            'targetSaleCategoryId' => 'nullable|exists:sale_categories,id',
            'targetEmployeeIds' => 'nullable|array',
            'targetEmployeeIds.*' => 'exists:employees,id',
            'minMonthlySalesTarget' => 'nullable|numeric|min:0',
            'isActive' => 'boolean',
        ]);

        try {
            $scheme = CommissionScheme::updateOrCreate(
                ['id' => $this->editingSchemeId],
                [
                    'branch_id' => $this->filterBranchId,
                    'employee_id' => count($this->targetEmployeeIds) === 1 ? $this->targetEmployeeIds[0] : null,
                    'name' => $this->schemeName,
                    'calculation_type' => $this->calculationType,
                    'rate' => $this->rate,
                    'applies_to' => $this->appliesTo,
                    'target_product_id' => $this->targetProductId,
                    'target_sale_category_id' => $this->targetSaleCategoryId,
                    'min_monthly_sales_target' => $this->minMonthlySalesTarget ?: 0,
                    'is_active' => $this->isActive,
                ]
            );

            $scheme->employees()->sync($this->targetEmployeeIds);

            $this->dispatch('toast', [
                'type' => 'success',
                'title' => 'Skema Komisi Disimpan',
                'body' => "Skema komisi \"{$this->schemeName}\" berhasil disimpan."
            ]);

            Notification::make()
                ->title('Skema Komisi Disimpan')
                ->body("Skema komisi \"{$this->schemeName}\" berhasil disimpan.")
                ->success()
                ->send();

            $this->showSchemeModal = false;
        } catch (Throwable $e) {
            $this->dispatch('toast', [
                'type' => 'danger',
                'title' => 'Gagal Menyimpan',
                'body' => $e->getMessage()
            ]);

            Notification::make()
                ->title('Gagal Menyimpan')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function toggleSchemeStatus(int $id): void
    {
        try {
            $scheme = CommissionScheme::findOrFail($id);
            $scheme->is_active = !$scheme->is_active;
            $scheme->save();

            $statusText = $scheme->is_active ? 'diaktifkan' : 'dinonaktifkan';
            $this->dispatch('toast', [
                'type' => 'info',
                'title' => 'Status Skema Berubah',
                'body' => "Skema komisi \"{$scheme->name}\" berhasil {$statusText}."
            ]);
        } catch (Throwable $e) {
            $this->dispatch('toast', [
                'type' => 'danger',
                'title' => 'Gagal Mengubah Status',
                'body' => $e->getMessage(),
            ]);
        }
    }

    public function deleteScheme(int $id): void
    {
        try {
            $scheme = CommissionScheme::findOrFail($id);
            $schemeName = $scheme->name;
            $scheme->delete();

            $this->dispatch('toast', [
                'type' => 'success',
                'title' => 'Skema Komisi Dihapus',
                'body' => "Skema komisi \"{$schemeName}\" berhasil dihapus."
            ]);
        } catch (Throwable $e) {
            $this->dispatch('toast', [
                'type' => 'danger',
                'title' => 'Gagal Menghapus Skema',
                'body' => $e->getMessage(),
            ]);
        }
    }

    public function approveEmployeeRecap(int $employeeId): void
    {
        try {
            $action = app(ApproveCommissionRecap::class);
            $count = $action->execute($employeeId, $this->filterMonth ?: now()->format('Y-m'));

            $employee = Employee::find($employeeId);
            $empName = $employee ? $employee->name : 'Karyawan';

            $this->dispatch('toast', [
                'type' => 'success',
                'title' => 'Rekap Komisi Disetujui',
                'body' => "Sebanyak {$count} transaksi komisi untuk {$empName} periode {$this->filterMonth} berhasil disetujui (Approved)."
            ]);

            Notification::make()
                ->title('Rekap Komisi Disetujui')
                ->body("Sebanyak {$count} transaksi komisi untuk {$empName} berhasil disetujui.")
                ->success()
                ->send();
        } catch (Throwable $e) {
            $this->dispatch('toast', [
                'type' => 'danger',
                'title' => 'Gagal Disetujui',
                'body' => $e->getMessage()
            ]);
        }
    }

    public function exportCsv()
    {
        $yearMonth = explode('-', $this->filterMonth ?: now()->format('Y-m'));
        $year = (int) ($yearMonth[0] ?? now()->year);
        $month = (int) ($yearMonth[1] ?? now()->month);

        $employeesQuery = Employee::with(['user', 'branch'])->where('is_active', true);
        if ($this->filterBranchId) {
            $employeesQuery->where('branch_id', $this->filterBranchId);
        }
        $employees = $employeesQuery->orderBy('name', 'asc')->get();

        $csvFileName = "Rekap_Komisi_Sales_{$this->filterMonth}.csv";
        $headers = [
            "Content-type" => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename={$csvFileName}",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $callback = function () use ($employees, $year, $month) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['NIK', 'Nama Karyawan', 'Cabang', 'Total Penjualan (Rp)', 'Total Komisi (Rp)', 'Status Approved (Rp)', 'Status Pending (Rp)']);

            foreach ($employees as $emp) {
                $logsQuery = SalesCommissionLog::where('employee_id', $emp->id)
                    ->whereYear('date', $year)
                    ->whereMonth('date', $month);

                $salesTotal = (float) $logsQuery->sum('sale_amount');
                $commTotal = (float) $logsQuery->sum('commission_amount');
                $approvedTotal = (float) (clone $logsQuery)->where('status', 'approved')->sum('commission_amount');
                $pendingTotal = (float) (clone $logsQuery)->where('status', 'pending')->sum('commission_amount');

                fputcsv($file, [
                    $emp->nik,
                    $emp->name,
                    $emp->branch?->name ?: 'Cabang Utama',
                    number_format($salesTotal, 0, '', ''),
                    number_format($commTotal, 0, '', ''),
                    number_format($approvedTotal, 0, '', ''),
                    number_format($pendingTotal, 0, '', ''),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function render()
    {
        // Logo cabang untuk sidebar
        $branchId = session('pos_active_branch_id') ?: auth()->user()?->branches()->first()?->id;
        $branch = $branchId ? Branch::find($branchId) : Branch::first();
        $selectedLogoUrl = ($branch && !empty($branch->logo_path) && trim($branch->logo_path) !== '')
            ? Storage::url($branch->logo_path)
            : null;

        $yearMonth = explode('-', $this->filterMonth ?: now()->format('Y-m'));
        $year = (int) ($yearMonth[0] ?? now()->year);
        $month = (int) ($yearMonth[1] ?? now()->month);

        $branches = Branch::where('is_active', true)->get();
        $products = Product::where('is_active', true)->orderBy('name', 'asc')->get();
        $categories = SaleCategory::orderBy('name', 'asc')->get();

        // Staf karyawan
        $employeesQuery = Employee::with(['user', 'branch'])
            ->where('is_active', true);

        if ($this->filterBranchId) {
            $employeesQuery->where('branch_id', $this->filterBranchId);
        }

        $employees = $employeesQuery->orderBy('name', 'asc')->get();

        // 1. Rekapitulasi Komisi Per Karyawan
        $recapData = collect();
        $totalSalesPeriod = 0.0;
        $totalCommissionPeriod = 0.0;
        $totalApprovedPeriod = 0.0;
        $totalPendingPeriod = 0.0;

        foreach ($employees as $emp) {
            $logsQuery = SalesCommissionLog::where('employee_id', $emp->id)
                ->whereYear('date', $year)
                ->whereMonth('date', $month);

            $salesTotal = (float) $logsQuery->sum('sale_amount');
            $commTotal = (float) $logsQuery->sum('commission_amount');
            $approvedTotal = (float) (clone $logsQuery)->where('status', 'approved')->sum('commission_amount');
            $pendingTotal = (float) (clone $logsQuery)->where('status', 'pending')->sum('commission_amount');
            $pendingCount = (clone $logsQuery)->where('status', 'pending')->count();

            $totalSalesPeriod += $salesTotal;
            $totalCommissionPeriod += $commTotal;
            $totalApprovedPeriod += $approvedTotal;
            $totalPendingPeriod += $pendingTotal;

            $recapData->push([
                'employee' => $emp,
                'sales_total' => $salesTotal,
                'commission_total' => $commTotal,
                'approved_total' => $approvedTotal,
                'pending_total' => $pendingTotal,
                'pending_count' => $pendingCount,
            ]);
        }

        // 2. Skema Komisi (Schemes)
        $schemesQuery = CommissionScheme::with(['branch', 'targetProduct', 'targetCategory', 'employees']);
        if ($this->filterBranchId) {
            $schemesQuery->where(function ($q) {
                $q->where('branch_id', $this->filterBranchId)
                    ->orWhereNull('branch_id');
            });
        }
        $schemes = $schemesQuery->orderBy('id', 'desc')->get();

        // 3. Log Transaksi Komisi
        $logsQuery = SalesCommissionLog::with(['employee', 'sale', 'commissionScheme'])
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

        $logs = $logsQuery->orderBy('date', 'desc')->orderBy('id', 'desc')->paginate(15);

        // Untracked / unavailable employee IDs for scheme assignment (1-on-1 constraint)
        $assignedIds = \Illuminate\Support\Facades\DB::table('commission_scheme_employee')
            ->when($this->editingSchemeId, function ($q) {
                $q->where('commission_scheme_id', '!=', $this->editingSchemeId);
            })
            ->pluck('employee_id')
            ->toArray();

        $legacyAssignedIds = CommissionScheme::whereNotNull('employee_id')
            ->when($this->editingSchemeId, function ($q) {
                $q->where('id', '!=', $this->editingSchemeId);
            })
            ->pluck('employee_id')
            ->toArray();

        $unavailableEmployeeIds = array_unique(array_merge($assignedIds, $legacyAssignedIds));

        return view('livewire.pos-commissions', [
            'selectedLogoUrl'        => $selectedLogoUrl,
            'branches'               => $branches,
            'products'               => $products,
            'categories'             => $categories,
            'employees'              => $employees,
            'unavailableEmployeeIds' => $unavailableEmployeeIds,
            'recapData'              => $recapData,
            'schemes'                => $schemes,
            'logs'                   => $logs,
            'totalSalesPeriod'       => $totalSalesPeriod,
            'totalCommissionPeriod'  => $totalCommissionPeriod,
            'totalApprovedPeriod'    => $totalApprovedPeriod,
            'totalPendingPeriod'     => $totalPendingPeriod,
        ])->layout('layouts.pos', ['title' => 'Manajemen Komisi Sales — POS']);
    }
}
