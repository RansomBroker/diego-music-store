<?php

namespace App\Livewire;

use App\Helpers\BranchHelper;
use App\Models\Branch;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithPagination;

class PosCatalog extends Component
{
    use WithPagination;

    public string $search = '';
    public string $category = '';
    public int $perPage = 20;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingCategory(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage($value): void
    {
        $this->perPage = in_array((int) $value, [20, 50, 100], true) ? (int) $value : 20;
        $this->resetPage();
    }

    public function render()
    {
        $branchId = BranchHelper::getActiveBranchId();
        $branch = Branch::findOrFail($branchId);

        $products = Product::query()
            ->where('is_active', true)
            ->whereHas('variants', function (Builder $query) {
                $query->where('is_active', true);
            })
            ->when($this->search !== '', function (Builder $query) {
                $term = '%' . trim($this->search) . '%';
                $query->where(function (Builder $searchQuery) use ($term) {
                    $searchQuery->where('name', 'like', $term)
                        ->orWhere('category', 'like', $term)
                        ->orWhere('brand', 'like', $term)
                        ->orWhereHas('variants', function (Builder $variantQuery) use ($term) {
                            $variantQuery->where('is_active', true)
                                ->where(function (Builder $fields) use ($term) {
                                    $fields->where('name', 'like', $term)
                                        ->orWhere('sku', 'like', $term)
                                        ->orWhere('barcode', 'like', $term);
                                });
                        });
                });
            })
            ->when($this->category !== '', fn (Builder $query) => $query->where('category', $this->category))
            ->with([
                'variants' => fn ($query) => $query->where('is_active', true)->orderBy('name'),
                'variants.branchStocks' => fn ($query) => $query->where('branch_id', $branchId),
                'variants.branchPrices' => fn ($query) => $query->where('branch_id', $branchId),
                'variants.bundleItems.childVariant.branchStocks' => fn ($query) => $query->where('branch_id', $branchId),
            ])
            ->orderBy('name')
            ->paginate($this->perPage);

        $categories = Product::query()
            ->where('is_active', true)
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        return view('livewire.pos-catalog', [
            'products' => $products,
            'categories' => $categories,
            'branch' => $branch,
            'selectedLogoUrl' => $branch->logo_path ? Storage::url($branch->logo_path) : null,
        ])->layout('layouts.pos', ['title' => 'Katalog Produk']);
    }
}
