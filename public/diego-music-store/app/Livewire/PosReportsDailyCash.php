<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Helpers\ReportHelper;
use App\Models\Branch;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PosReportsDailyCash extends Component
{
    use WithPagination;

    public ?string $dateFrom = null;
    public ?string $dateTo = null;
    public ?int $selectedBranchId = null;
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

    public function updated($propertyName): void
    {
        if (in_array($propertyName, ['dateFrom', 'dateTo', 'selectedBranchId', 'perPage'])) {
            $this->resetPage();
        }
    }

    public function mount()
    {
        $this->dateFrom = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->dateTo = Carbon::now()->format('Y-m-d');
    }

    public function resetFilters(): void
    {
        $this->dateFrom = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->dateTo = Carbon::now()->format('Y-m-d');
        $this->selectedBranchId = null;
        $this->resetPage();
    }

    public function setQuickDateRange(string $preset)
    {
        switch ($preset) {
            case 'today':
                $this->dateFrom = Carbon::today()->format('Y-m-d');
                $this->dateTo = Carbon::today()->format('Y-m-d');
                break;
            case 'this_week':
                $this->dateFrom = Carbon::now()->startOfWeek()->format('Y-m-d');
                $this->dateTo = Carbon::now()->endOfWeek()->format('Y-m-d');
                break;
            case 'this_month':
                $this->dateFrom = Carbon::now()->startOfMonth()->format('Y-m-d');
                $this->dateTo = Carbon::now()->endOfMonth()->format('Y-m-d');
                break;
            case 'this_year':
                $this->dateFrom = Carbon::now()->startOfYear()->format('Y-m-d');
                $this->dateTo = Carbon::now()->endOfYear()->format('Y-m-d');
                break;
        }
        $this->resetPage();
    }

    public function render()
    {
        $branches = Branch::all();
        $userBranchId = Auth::user()?->branches()->first()?->id;
        $currentBranch = $this->selectedBranchId
            ? Branch::find($this->selectedBranchId)
            : ($userBranchId ? Branch::find($userBranchId) : Branch::first());

        $selectedLogoUrl = !empty($currentBranch?->logo_path) ? Storage::url($currentBranch->logo_path) : null;

        $reportData = ReportHelper::getDailyCashReport(
            $this->dateFrom,
            $this->dateTo,
            $this->selectedBranchId
        );

        // Paginate cash transactions
        $rawTransactions = $reportData['transactions'] ?? [];
        if ($rawTransactions instanceof \Illuminate\Support\Collection) {
            $rawTransactions = $rawTransactions->all();
        }
        $totalTransactions = count($rawTransactions);

        if ($this->perPage > 0) {
            $currentPage = (int) $this->getPage('page');
            $currentPageItems = array_slice($rawTransactions, max(0, ($currentPage - 1) * $this->perPage), $this->perPage);
            $paginatedTransactions = new LengthAwarePaginator(
                $currentPageItems,
                $totalTransactions,
                $this->perPage,
                $currentPage,
                [
                    'path' => LengthAwarePaginator::resolveCurrentPath(),
                    'pageName' => 'page',
                ]
            );
        } else {
            $paginatedTransactions = new LengthAwarePaginator(
                $rawTransactions,
                $totalTransactions,
                max(1, $totalTransactions),
                1,
                [
                    'path' => LengthAwarePaginator::resolveCurrentPath(),
                    'pageName' => 'page',
                ]
            );
        }

        $reportData['paginated_transactions'] = $paginatedTransactions;

        return view('livewire.pos-reports-daily-cash', [
            'branches' => $branches,
            'currentBranch' => $currentBranch,
            'selectedLogoUrl' => $selectedLogoUrl,
            'reportData' => $reportData,
        ])->layout('layouts.pos', ['title' => 'Laporan Kas Harian ERP — POS']);
    }
}
