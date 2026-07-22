<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Branch;
use App\Helpers\ReportHelper;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Carbon;

class PosReports extends Component
{
    public string $activeTab = 'sales'; // 'sales', 'ar-aging', 'ar-settlement', 'daily-cash', 'stock-prices'

    // Common Filter States
    public ?string $dateFrom = null;
    public ?string $dateTo = null;
    public ?int $selectedBranchId = null;
    public string $search = '';

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
    }

    public function resetFilters(): void
    {
        $this->dateFrom = Carbon::now()->startOfMonth()->toDateString();
        $this->dateTo   = Carbon::now()->endOfMonth()->toDateString();
        $this->search   = '';
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

        return view('livewire.pos-reports', [
            'branches'        => $branches,
            'selectedLogoUrl' => $selectedLogoUrl,
            'reportData'      => $reportData,
        ])->layout('layouts.pos', ['title' => 'Laporan ERP & Keuangan — POS']);
    }
}
