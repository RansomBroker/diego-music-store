<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Helpers\ReportHelper;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\User;
use App\Models\Sale;
use App\Models\Product;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PosReportsSales extends Component
{
    use WithPagination;

    public string $viewMode = 'detail'; // 'detail', 'per_day', 'per_nota', 'top_selling'
    public ?string $dateFrom = null;
    public ?string $dateTo = null;
    public ?int $selectedBranchId = null;
    public ?int $selectedCustomerId = null;
    public ?string $selectedPaymentMethod = null;
    public ?int $selectedSalesRepId = null;
    public ?int $selectedCashierId = null;
    public ?string $selectedSaleCategory = null;
    public ?string $selectedProductCategory = null;
    public ?int $selectedProductId = null;
    public ?string $search = '';
    public int $perPage = 15;

    public function updated($propertyName)
    {
        if (in_array($propertyName, [
            'search', 'viewMode', 'dateFrom', 'dateTo', 'selectedBranchId',
            'selectedCustomerId', 'selectedPaymentMethod', 'selectedSalesRepId',
            'selectedCashierId', 'selectedSaleCategory', 'selectedProductCategory',
            'selectedProductId', 'perPage'
        ])) {
            $this->resetPage();
        }
    }

    public function mount()
    {
        $this->dateFrom = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->dateTo = Carbon::now()->format('Y-m-d');
        if (!$this->selectedBranchId) {
            $this->selectedBranchId = \App\Helpers\BranchHelper::getActiveBranchId();
        }
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

    public function resetFilters()
    {
        $this->selectedBranchId = null;
        $this->selectedCustomerId = null;
        $this->selectedPaymentMethod = null;
        $this->selectedSalesRepId = null;
        $this->selectedCashierId = null;
        $this->selectedSaleCategory = null;
        $this->selectedProductCategory = null;
        $this->selectedProductId = null;
        $this->search = '';
        $this->dateFrom = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->dateTo = Carbon::now()->format('Y-m-d');
        $this->resetPage();
    }

    public function render()
    {
        $branches = \App\Helpers\BranchHelper::getAllowedBranchesQuery()->get();
        $activeBranchId = $this->selectedBranchId ?: \App\Helpers\BranchHelper::getActiveBranchId();
        $currentBranch = Branch::find($activeBranchId);

        $selectedLogoUrl = !empty($currentBranch?->logo_path) ? Storage::url($currentBranch->logo_path) : null;

        $filters = [
            'dateFrom'        => $this->dateFrom,
            'dateTo'          => $this->dateTo,
            'branchId'        => $this->selectedBranchId,
            'customerId'      => $this->selectedCustomerId,
            'paymentMethod'   => $this->selectedPaymentMethod,
            'salesRepId'      => $this->selectedSalesRepId,
            'cashierId'       => $this->selectedCashierId,
            'saleCategory'    => $this->selectedSaleCategory,
            'productCategory' => $this->selectedProductCategory,
            'productId'       => $this->selectedProductId,
            'search'          => $this->search,
        ];

        $reportData = match ($this->viewMode) {
            'per_day' => ReportHelper::getDailySalesPerDayReport($filters),
            'per_nota' => ReportHelper::getDailySalesPerNotaReport($filters),
            'top_selling' => ReportHelper::getDailySalesTopSellingReport($filters),
            default => ReportHelper::getDailySalesDetailReport($filters),
        };

        $summaryData = ReportHelper::getSalesReport($filters);

        // Paginate reportData items
        $rawItems = $reportData['items'] ?? [];
        $totalItems = count($rawItems);

        if ($this->perPage > 0) {
            $currentPage = LengthAwarePaginator::resolveCurrentPage('page');
            $currentPageItems = array_slice($rawItems, ($currentPage - 1) * $this->perPage, $this->perPage);
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

        $paginatedReportData = $reportData;
        $paginatedReportData['paginated_items'] = $paginatedItems;

        // Filter Options Dropdowns
        $customers = Customer::orderBy('name')->get();

        $salesUsers = User::whereHas('roles', fn($q) => $q->whereIn('name', ['sales', 'owner', 'admin']))
            ->orWhereIn('id', Sale::whereNotNull('sales_rep_id')->distinct()->pluck('sales_rep_id'))
            ->orderBy('name')->get();

        $cashierUsers = User::whereHas('roles', fn($q) => $q->whereIn('name', ['cashier', 'kasir', 'owner', 'admin']))
            ->orWhereIn('id', Sale::whereNotNull('created_by')->distinct()->pluck('created_by'))
            ->orderBy('name')->get();

        $paymentMethods = Sale::whereNotNull('payment_method')->where('payment_method', '!=', '')->distinct()->pluck('payment_method');
        $saleCategories = Sale::whereNotNull('sale_category')->where('sale_category', '!=', '')->distinct()->pluck('sale_category');
        $productCategories = Product::whereNotNull('category')->where('category', '!=', '')->distinct()->pluck('category');
        $products = Product::orderBy('name')->get();

        return view('livewire.pos-reports-sales', [
            'branches'             => $branches,
            'currentBranch'        => $currentBranch,
            'selectedLogoUrl'      => $selectedLogoUrl,
            'reportData'           => $reportData,
            'paginatedReportData'  => $paginatedReportData,
            'summaryData'          => $summaryData,
            'customers'            => $customers,
            'salesUsers'           => $salesUsers,
            'cashierUsers'         => $cashierUsers,
            'paymentMethods'       => $paymentMethods,
            'saleCategories'       => $saleCategories,
            'productCategories'    => $productCategories,
            'products'             => $products,
        ])->layout('layouts.pos', ['title' => 'Laporan Penjualan ERP — POS']);
    }
}
