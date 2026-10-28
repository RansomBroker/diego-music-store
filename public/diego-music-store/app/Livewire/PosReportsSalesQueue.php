<?php

namespace App\Livewire;

use App\Helpers\BranchHelper;
use App\Helpers\SalesQueueHelper;
use App\Models\Branch;
use App\Models\SalesDutyLog;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class PosReportsSalesQueue extends Component
{
    use WithPagination;

    public ?string $selectedDate = null;
    public ?int $selectedBranchId = null;
    public string $selectedStatus = 'all';
    public ?int $selectedUserId = null;
    public string $search = '';
    public string $activeTab = 'logs';
    public int $perPage = 15;

    public function updatingActiveTab(): void
    {
        $this->resetPage();
    }

    public function updatingPerPage(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage($value): void
    {
        $this->perPage = (int) $value;
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->selectedBranchId = (int) BranchHelper::getActiveBranchId();
        $this->selectedStatus = 'all';
        $this->selectedUserId = null;
        $this->selectedDate = Carbon::today()->format('Y-m-d');
        $this->resetPage();
    }

    public function exportExcel()
    {
        return app(\App\Actions\SalesQueue\ExportSalesQueueToExcel::class)->execute(
            selectedDate: $this->selectedDate,
            branchId: $this->selectedBranchId ?: (int) BranchHelper::getActiveBranchId(),
            userId: $this->selectedUserId,
            status: $this->selectedStatus,
            search: $this->search
        );
    }

    public function boot(): void
    {
        abort_if(Auth::user()?->hasRole(['sales', 'Sales']), 403);
    }

    public function mount(): void
    {
        $this->selectedDate = Carbon::today()->format('Y-m-d');
        $this->selectedBranchId = (int) BranchHelper::getActiveBranchId();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingSelectedDate(): void
    {
        $this->resetPage();
    }

    public function updatingSelectedBranchId(): void
    {
        $this->resetPage();
    }

    public function updatingSelectedStatus(): void
    {
        $this->resetPage();
    }

    public function updatingSelectedUserId(): void
    {
        $this->resetPage();
    }

    public function setQuickDate(string $preset): void
    {
        if ($preset === 'today') {
            $this->selectedDate = Carbon::today()->format('Y-m-d');
        } elseif ($preset === 'yesterday') {
            $this->selectedDate = Carbon::yesterday()->format('Y-m-d');
        }
        $this->resetPage();
    }

    public function getBranchesProperty()
    {
        return BranchHelper::getAllowedBranchesQuery()->get();
    }

    public function getSalesUsersProperty()
    {
        return User::role('sales')
            ->where('is_active', true)
            ->orderBy('name', 'asc')
            ->get();
    }

    public function getSummaryStatsProperty(): array
    {
        $branchId = $this->selectedBranchId ?: (int) BranchHelper::getActiveBranchId();
        return SalesQueueHelper::getDailyStats($branchId, $this->selectedDate);
    }

    public function getLeaderboardProperty()
    {
        $branchId = $this->selectedBranchId ?: (int) BranchHelper::getActiveBranchId();

        $logs = SalesDutyLog::with(['user.employee'])
            ->where('branch_id', $branchId)
            ->whereDate('started_at', $this->selectedDate)
            ->get();

        return $logs->groupBy('user_id')->map(function ($userLogs) {
            $first = $userLogs->first();
            $totalServings = $userLogs->count();
            $closings = $userLogs->filter(fn($l) => !empty($l->sale_id) || $l->closing_amount > 0)->count();
            $revenue = $userLogs->sum('closing_amount');
            $completed = $userLogs->where('status', 'completed');
            $avgDuration = $completed->count() > 0 ? (int) round($completed->avg('duration_seconds') / 60) : 0;
            $rate = $totalServings > 0 ? round(($closings / $totalServings) * 100, 1) : 0;

            return [
                'user_id' => $first->user_id,
                'name' => $first->user->name ?? 'Sales',
                'employee_nik' => $first->user->employee->nik ?? '-',
                'total_servings' => $totalServings,
                'total_closings' => $closings,
                'closing_rate' => $rate,
                'total_revenue' => $revenue,
                'avg_duration_minutes' => $avgDuration,
            ];
        })->sortByDesc('total_revenue')->values();
    }

    public function render()
    {
        $branchId = $this->selectedBranchId ?: (int) BranchHelper::getActiveBranchId();

        $query = SalesDutyLog::with(['user.employee', 'sale', 'creator'])
            ->where('branch_id', $branchId)
            ->whereDate('started_at', $this->selectedDate);

        if ($this->selectedStatus !== 'all') {
            $query->where('status', $this->selectedStatus);
        }

        if ($this->selectedUserId) {
            $query->where('user_id', $this->selectedUserId);
        }

        if (!empty($this->search)) {
            $search = '%' . trim($this->search) . '%';
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', function ($u) use ($search) {
                    $u->where('name', 'like', $search);
                })->orWhereHas('sale', function ($s) use ($search) {
                    $s->where('invoice_number', 'like', $search);
                });
            });
        }

        $perPage = $this->perPage > 0 ? $this->perPage : max(1, $query->count());
        $logs = $query->orderBy('started_at', 'desc')->paginate($perPage);

        $currentBranch = Branch::find($branchId);
        $selectedLogoUrl = ($currentBranch && !empty($currentBranch->logo_path))
            ? \Illuminate\Support\Facades\Storage::url($currentBranch->logo_path)
            : null;

        return view('livewire.pos-reports-sales-queue', [
            'logs' => $logs,
            'summaryStats' => $this->summaryStats,
            'leaderboard' => $this->leaderboard,
            'currentBranch' => $currentBranch,
            'selectedLogoUrl' => $selectedLogoUrl,
        ])->layout('layouts.pos');
    }
}
