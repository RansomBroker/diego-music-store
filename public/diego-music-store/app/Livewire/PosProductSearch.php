<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\ProductVariant;

class PosProductSearch extends Component
{
    public $search = '';
    public $activeCategory = 'Semua';
    public $productPage = 1;
    public $viewMode = 'grid';
    public $showModal = false;

    protected $listeners = ['openProductSearch' => 'openModal'];

    public function mount()
    {
        $this->viewMode = session('pos_view_mode', 'grid');
    }

    public function openModal()
    {
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->search = '';
        $this->productPage = 1;
    }

    public function updatedSearch()
    {
        $this->productPage = 1;
    }

    public function updatedActiveCategory()
    {
        $this->productPage = 1;
    }

    public function setCategory($category)
    {
        $this->activeCategory = $category;
        $this->productPage = 1;
    }

    public function setViewMode(string $mode): void
    {
        $this->viewMode = in_array($mode, ['grid', 'list']) ? $mode : 'grid';
        session(['pos_view_mode' => $this->viewMode]);
    }

    public function loadMoreProducts()
    {
        $this->productPage++;
    }

    public function getProductsProperty()
    {
        if (!$this->showModal) {
            return collect();
        }

        $take = 15;
        $offset = ($this->productPage - 1) * $take;

        $query = ProductVariant::query()
            ->select('product_variants.*')
            ->join('products', 'products.id', '=', 'product_variants.product_id')
            ->with([
                'product:id,name,type,category,is_active',
                'branchStocks' => fn($q) => $q->select('id', 'product_variant_id', 'branch_id', 'stock'),
                'tierPrices' => fn($q) => $q->select('id', 'product_variant_id', 'pricing_tier_id', 'price'),
                'bundleItems.childVariant.branchStocks' => fn($q) => $q->select('id', 'product_variant_id', 'branch_id', 'stock'),
            ])
            ->where('product_variants.is_active', true)
            ->where('products.is_active', true);

        if ($this->activeCategory !== 'Semua') {
            $query->where('products.category', $this->activeCategory);
        }

        if (!empty($this->search)) {
            $search = trim($this->search);
            $likeSearch = '%' . $search . '%';
            $query->where(function ($q) use ($search, $likeSearch) {
                $q->where('product_variants.barcode', $search)
                  ->orWhere('product_variants.sku', $search)
                  ->orWhere('product_variants.name', 'like', $likeSearch)
                  ->orWhere('products.name', 'like', $likeSearch);
            });
        }

        // Return exact 15 items per page using offset
        return $query->offset($offset)->limit($take)->get();
    }

    public function getHasMoreProductsProperty(): bool
    {
        if (!$this->showModal) {
            return false;
        }
        $take = 15;
        
        // This is a bit inefficient to count, but okay for now
        // We can just check if the current page returned exactly 15 items
        return $this->products->count() === $take;
    }

    public function addToCart($variantId)
    {
        $this->dispatch('add-to-cart', variantId: $variantId);
    }

    public function render()
    {
        return view('livewire.pos-product-search');
    }
}
