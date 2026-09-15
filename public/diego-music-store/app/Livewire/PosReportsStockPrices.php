<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Actions\Inventory\GenerateStockListReport;
use App\Models\Branch;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PosReportsStockPrices extends Component
{
    use WithPagination;

    public ?int $selectedBranchId = null;
    public ?string $selectedCategory = null;
    public string $stockStatus = 'all'; // 'all', 'available', 'low', 'out_of_stock'
    public ?string $search = '';
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

    public function updated($propertyName)
    {
        if (in_array($propertyName, ['selectedBranchId', 'selectedCategory', 'stockStatus', 'search', 'perPage'])) {
            $this->resetPage();
        }
    }

    public function resetFilters()
    {
        $this->selectedBranchId = null;
        $this->selectedCategory = null;
        $this->stockStatus = 'all';
        $this->search = '';
        $this->resetPage();
    }

    public function render()
    {
        $branches = \App\Helpers\BranchHelper::getAllowedBranchesQuery()->get();
        $activeBranchId = $this->selectedBranchId ?: \App\Helpers\BranchHelper::getActiveBranchId();
        $currentBranch = Branch::find($activeBranchId);

        $selectedLogoUrl = !empty($currentBranch?->logo_path) ? Storage::url($currentBranch->logo_path) : null;

        $categories = Product::whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->pluck('category');

        $reportData = (new GenerateStockListReport())->execute(
            $activeBranchId,
            $this->selectedCategory,
            $this->stockStatus,
            $this->search
        );

        // Paginate report rows
        $rawRows = $reportData['rows'] ?? [];
        $totalRows = count($rawRows);

        if ($this->perPage > 0) {
            $currentPage = (int) $this->getPage('page');
            $currentPageItems = array_slice($rawRows, max(0, ($currentPage - 1) * $this->perPage), $this->perPage);
            $paginatedRows = new LengthAwarePaginator(
                $currentPageItems,
                $totalRows,
                $this->perPage,
                $currentPage,
                [
                    'path' => LengthAwarePaginator::resolveCurrentPath(),
                    'pageName' => 'page',
                ]
            );
        } else {
            $paginatedRows = new LengthAwarePaginator(
                $rawRows,
                $totalRows,
                max(1, $totalRows),
                1,
                [
                    'path' => LengthAwarePaginator::resolveCurrentPath(),
                    'pageName' => 'page',
                ]
            );
        }

        $reportData['paginated_rows'] = $paginatedRows;

        return view('livewire.pos-reports-stock-prices', [
            'branches'       => $branches,
            'currentBranch'  => $currentBranch,
            'selectedLogoUrl'=> $selectedLogoUrl,
            'categories'     => $categories,
            'reportData'     => $reportData,
        ])->layout('layouts.pos', ['title' => 'Laporan Daftar Stok & Nilai Persediaan — POS']);
    }
}
