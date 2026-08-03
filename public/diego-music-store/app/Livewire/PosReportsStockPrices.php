<?php

namespace App\Livewire;

use Livewire\Component;
use App\Actions\Inventory\GenerateStockListReport;
use App\Models\Branch;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PosReportsStockPrices extends Component
{
    public ?int $selectedBranchId = null;
    public ?string $selectedCategory = null;
    public string $stockStatus = 'all'; // 'all', 'available', 'low', 'out_of_stock'
    public ?string $search = '';

    public function resetFilters()
    {
        $this->selectedBranchId = null;
        $this->selectedCategory = null;
        $this->stockStatus = 'all';
        $this->search = '';
    }

    public function render()
    {
        $branches = Branch::all();
        $userBranchId = Auth::user()?->branches()->first()?->id;
        $currentBranch = $this->selectedBranchId
            ? Branch::find($this->selectedBranchId)
            : ($userBranchId ? Branch::find($userBranchId) : Branch::first());

        $selectedLogoUrl = !empty($currentBranch?->logo_path) ? Storage::url($currentBranch->logo_path) : null;

        $categories = Product::whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->pluck('category');

        $reportData = (new GenerateStockListReport())->execute(
            $this->selectedBranchId,
            $this->selectedCategory,
            $this->stockStatus,
            $this->search
        );

        return view('livewire.pos-reports-stock-prices', [
            'branches'       => $branches,
            'currentBranch'  => $currentBranch,
            'selectedLogoUrl'=> $selectedLogoUrl,
            'categories'     => $categories,
            'reportData'     => $reportData,
        ])->layout('layouts.pos', ['title' => 'Laporan Daftar Stok & Nilai Persediaan — POS']);
    }
}
