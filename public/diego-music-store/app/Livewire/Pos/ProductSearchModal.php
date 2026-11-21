<?php

namespace App\Livewire\Pos;

use App\Models\Product;
use App\Models\ProductVariant;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class ProductSearchModal extends Component
{
    use WithPagination;

    public bool $show = false;
    public $branchId = null;
    public $pricingTierId = null;
    public string $search = '';
    public string $activeCategory = 'Semua';
    public string $sortBy = 'name_asc';
    public string $viewMode = 'grid';
    public array $visibleCategoryTabs = [];
    public array $cartQuantities = [];
    public array $categoryCounts = ['Semua' => 0];

    public function mount(
        $branchId = null,
        $pricingTierId = null,
        array $initialCartQuantities = [],
        array $initialCategoryCounts = ['Semua' => 0]
    ): void {
        $this->branchId = $branchId;
        $this->pricingTierId = $pricingTierId;
        $this->cartQuantities = $initialCartQuantities;
        $this->categoryCounts = $initialCategoryCounts;
        $this->viewMode = session('pos_view_mode', 'grid');

        $this->visibleCategoryTabs = session('pos_visible_categories', []);
        if (empty($this->visibleCategoryTabs)) {
            $this->visibleCategoryTabs = collect($this->availableCategories)
                ->take(6)
                ->pluck('name')
                ->toArray();
        }
    }

    #[On('open-pos-product-search')]
    public function openModal($pricingTierId = null, ?array $cartQuantities = null, ?array $categoryCounts = null): void
    {
        $this->show = true;

        if ($pricingTierId !== null) {
            $this->pricingTierId = $pricingTierId;
        }
        if ($cartQuantities !== null) {
            $this->cartQuantities = $cartQuantities;
        }
        if ($categoryCounts !== null) {
            $this->categoryCounts = $categoryCounts;
        }

        $this->resetPage('modalProductPage');
    }

    public function closeModal(): void
    {
        $this->show = false;
        $this->search = '';
        $this->resetPage('modalProductPage');
    }

    public function setCategory(string $category): void
    {
        $this->activeCategory = $category;
        $this->resetPage('modalProductPage');
    }

    public function setViewMode(string $mode): void
    {
        $this->viewMode = in_array($mode, ['grid', 'list'], true) ? $mode : 'grid';
        session(['pos_view_mode' => $this->viewMode]);
    }

    public function updatedSearch(): void
    {
        $this->resetPage('modalProductPage');
    }

    public function updatedActiveCategory(): void
    {
        $this->resetPage('modalProductPage');
    }

    public function updatedSortBy(): void
    {
        $this->resetPage('modalProductPage');
    }

    public function updatedVisibleCategoryTabs($value): void
    {
        $tabs = is_array($value) ? array_values($value) : [];
        if (count($tabs) > 6) {
            $tabs = array_slice($tabs, 0, 6);
        }
        $this->visibleCategoryTabs = $tabs;
        session(['pos_visible_categories' => $this->visibleCategoryTabs]);
    }

    public function getAvailableCategoriesProperty()
    {
        return cache()->remember('pos_available_categories', 300, function () {
            return Product::where('is_active', true)
                ->whereNotNull('category')
                ->where('category', '!=', '')
                ->selectRaw('category, COUNT(*) as total')
                ->groupBy('category')
                ->orderByDesc('total')
                ->get()
                ->map(fn ($p) => ['name' => $p->category, 'count' => $p->total]);
        });
    }

    public function getProductsProperty()
    {
        if (!$this->show) {
            return collect();
        }

        $branchId = $this->branchId;

        $query = ProductVariant::query()
            ->select('product_variants.*')
            ->join('products', 'products.id', '=', 'product_variants.product_id')
            ->with([
                'product:id,name,type,category,is_active',
                'branchStocks' => fn ($q) => $q->select('id', 'product_variant_id', 'branch_id', 'stock')
                    ->when($branchId, fn ($sq) => $sq->where('branch_id', $branchId)),
                'tierPrices' => fn ($q) => $q->select('id', 'product_variant_id', 'pricing_tier_id', 'price'),
                'bundleItems.childVariant.product:id,name,type',
                'bundleItems.childVariant.branchStocks' => fn ($q) => $q->select('id', 'product_variant_id', 'branch_id', 'stock')
                    ->when($branchId, fn ($sq) => $sq->where('branch_id', $branchId)),
            ])
            ->where('product_variants.is_active', true)
            ->where('products.is_active', true);

        if ($this->activeCategory !== 'Semua') {
            $query->where('products.category', $this->activeCategory);
        }

        if (!empty($this->search)) {
            $likeSearch = '%' . trim($this->search) . '%';
            $query->where(function ($q) use ($likeSearch) {
                $q->where('product_variants.barcode', 'like', $likeSearch)
                    ->orWhere('product_variants.sku', 'like', $likeSearch)
                    ->orWhere('product_variants.name', 'like', $likeSearch)
                    ->orWhere('products.name', 'like', $likeSearch);
            });
        }

        switch ($this->sortBy) {
            case 'name_desc':
                $query->orderBy('products.name', 'desc');
                break;
            case 'price_asc':
                $query->orderBy('product_variants.price', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('product_variants.price', 'desc');
                break;
            case 'name_asc':
            default:
                $query->orderBy('products.name', 'asc');
                break;
        }

        return $query->paginate(15, ['*'], 'modalProductPage');
    }

    public function render()
    {
        return view('livewire.pos.product-search-modal');
    }
}

