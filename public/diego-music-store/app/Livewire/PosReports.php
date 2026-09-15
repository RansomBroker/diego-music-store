<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\Branch;
use App\Helpers\ReportHelper;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Carbon;

class PosReports extends Component
{
    use WithPagination;

    public string $activeTab = 'sales'; // 'sales', 'ar-aging', 'ar-settlement', 'daily-cash', 'stock-prices'

    // Common Filter States
    public ?string $dateFrom = null;
    public ?string $dateTo = null;
    public ?int $selectedBranchId = null;
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

    public function updated($propertyName): void
    {
        if (in_array($propertyName, ['dateFrom', 'dateTo', 'selectedBranchId', 'search', 'activeTab', 'perPage'])) {
            $this->resetPage();
        }
    }

    public function mount(): void
    {
        $tab = request()->query('tab');
        if (in_array($tab, ['sales', 'ar-aging', 'ar-settlement', 'daily-cash', 'stock-prices'])) {
            $this->activeTab = $tab;
        }

        // Default date range to current month
        $this->dateFrom = Carbon::now()->startOfMonth()->toDateString();
        $this->dateTo   = Carbon::now()->endOfMonth()->toDateString();

        // Lock to current active branch if user belongs to one
        $userBranchId = Auth::user()?->branches()->first()?->id;
        $this->selectedBranchId = $userBranchId;
    }

    public function setTab(string $tab): void
    {
        if (in_array($tab, ['sales', 'ar-aging', 'ar-settlement', 'daily-cash', 'stock-prices'])) {
            $this->activeTab = $tab;
            $this->resetPage();
        }
    }

    public function setQuickDateRange(string $range): void
    {
        switch ($range) {
            case 'today':
                $this->dateFrom = Carbon::today()->toDateString();
                $this->dateTo   = Carbon::today()->toDateString();
                break;
            case 'this_week':
                $this->dateFrom = Carbon::now()->startOfWeek()->toDateString();
                $this->dateTo   = Carbon::now()->endOfWeek()->toDateString();
                break;
            case 'this_month':
                $this->dateFrom = Carbon::now()->startOfMonth()->toDateString();
                $this->dateTo   = Carbon::now()->endOfMonth()->toDateString();
                break;
            case 'this_year':
                $this->dateFrom = Carbon::now()->startOfYear()->toDateString();
                $this->dateTo   = Carbon::now()->endOfYear()->toDateString();
                break;
        }
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->dateFrom = Carbon::now()->startOfMonth()->toDateString();
        $this->dateTo   = Carbon::now()->endOfMonth()->toDateString();
        $this->search   = '';
        $this->resetPage();
    }

    public function render()
    {
        $branches = Branch::where('is_active', true)->get();
        $branch = $this->selectedBranchId ? Branch::find($this->selectedBranchId) : null;
        $selectedLogoUrl = ($branch && !empty($branch->logo_path)) ? Storage::url($branch->logo_path) : null;

        // Generate report payload based on active tab
        $reportData = [];
        switch ($this->activeTab) {
            case 'sales':
                $reportData = ReportHelper::getSalesReport($this->dateFrom, $this->dateTo, $this->selectedBranchId, $this->search);
                break;
            case 'ar-aging':
                $reportData = ReportHelper::getARAgingReport($this->selectedBranchId, $this->search);
                break;
            case 'ar-settlement':
                $reportData = ReportHelper::getARSettlementReport($this->dateFrom, $this->dateTo, $this->selectedBranchId, $this->search);
                break;
            case 'daily-cash':
                $reportData = ReportHelper::getDailyCashReport($this->dateFrom, $this->dateTo, $this->selectedBranchId);
                break;
            case 'stock-prices':
                $reportData = ReportHelper::getStockValuationReport($this->selectedBranchId, $this->search);
                break;
        }

        // Paginate active tab items
        $dataKey = match ($this->activeTab) {
            'sales' => 'sales',
            'ar-aging' => 'items',
            'ar-settlement' => 'settlements',
            'daily-cash' => 'transactions',
            'stock-prices' => 'items',
            default => 'items',
        };

        $rawItems = $reportData[$dataKey] ?? [];
        $totalItems = count($rawItems);

        if ($this->perPage > 0) {
            $currentPage = (int) $this->getPage('page');
            $currentPageItems = array_slice($rawItems, max(0, ($currentPage - 1) * $this->perPage), $this->perPage);
            $paginatedItems = new LengthAwarePaginator(
                $currentPageItems,
                $totalItems,
                $this->perPage,
                $currentPage,
                [
                    'path' => LengthAwarePaginator::resolveCurrentPath(),
                    'pageName' => 'page',
                ]
            );
        } else {
            $paginatedItems = new LengthAwarePaginator(
                $rawItems,
                $totalItems,
                max(1, $totalItems),
                1,
                [
                    'path' => LengthAwarePaginator::resolveCurrentPath(),
                    'pageName' => 'page',
                ]
            );
        }

        $reportData['paginated_' . $dataKey] = $paginatedItems;

        return view('livewire.pos-reports', [
            'branches'        => $branches,
            'selectedLogoUrl' => $selectedLogoUrl,
            'reportData'      => $reportData,
        ])->layout('layouts.pos', ['title' => 'Laporan ERP & Keuangan — POS']);
    }
}
