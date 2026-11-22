<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\ProductVariant;
use App\Models\Customer;
use App\Models\Branch;
use App\Actions\Sales\CreatePOSSale;
use App\Actions\Customer\CreateCustomer;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;
use Livewire\Attributes\On;

use App\Livewire\Traits\POS\WithCartManagement;
use App\Livewire\Traits\POS\WithCustomerManagement;
use App\Livewire\Traits\POS\WithDraftTransactions;
use App\Livewire\Traits\POS\WithPaymentHandling;
use App\Livewire\Traits\POS\WithVouchers;

class POS extends Component
{
    use WithCartManagement;
    use WithCustomerManagement;
    use WithDraftTransactions;
    use WithPaymentHandling;
    use WithPagination;

    // Livewire states
    public $search = '';
    public $activeCategory = 'Semua';
    public $visibleCategoryTabs = [];
    public $showCategoryFilterModal = false;
    public $sortBy = 'name_asc';
    public $cart = [];
    
    // Customer search & selection
    public $customerSearch = '';
    public $customerLimit = 20;
    public $selectedCustomerId = null;
    public $selectedCustomerName = 'Umum / Walk-in';
    public $customerPhone = '';
    public $isLoyaltyMember = false;
    public $usePoints = false;
    public $customerPoints = 0;
    const POINT_VALUATION = 1000;

    // Create Customer Modal State
    public $showCreateCustomerModal = false;
    public $newCustomerName = '';
    public $newCustomerPhone = '';
    public $newCustomerEmail = '';
    public $newCustomerAddress = '';
    public $newCustomerPricingTierId = null;
    public $newCustomerSaleCategoryId = null;
    public $newCustomerIsLoyaltyMember = false;

    // Held transactions and reprint
    public $showHeldModal = false;
    public $isHeldModalMandatory = false;
    public $lastSaleId = null;
    public $editingSaleId = null;
    public $currentDraftId = null;
    public $lastSavedDraftHash = null;

    // Product search modal
    public $showProductSearchModal = false;
    public int $productPage = 1;
    public string $viewMode = 'grid';
    const PRODUCTS_PER_PAGE = 15;

    // Payment state
    public $notes = '';
    public $discountValue = 0;
    public $discountType = 'percent';
    public $enableTax = false;
    public $taxPercent = 11;
    public $showPaymentModal = false;

    // Available branches and current branch
    public $selectedBranchId = null;
    public $selectedBranchName = '';
    public $selectedStoreName = '';
    public $selectedLogoUrl = '';

    // Pricing Tier selection
    public $selectedPricingTierId = null;

    // Sales Rep and Invoice Date
    public $selectedSalesRepId = null;
    public $selectedSalesRepName = '';
    public $salesSearch = '';
    public $saleCategory = 'Store';
    public string $previewInvoiceNumber = '';
    public $invoiceDate = '';
    public $activeSessionInfo = [];

    public function boot(): void
    {
        $user = Auth::user();
        abort_unless(\App\Helpers\PermissionHelper::canAccessFrontofficeFeature($user, 'fo.pos.view'), 403);
        if ($this->editingSaleId) {
            abort_unless(\App\Helpers\PermissionHelper::can($user, 'fo.pos.edit_sale'), 403);
        }
    }

    public function updatedDiscountValue($value): void
    {
        abort_unless(\App\Helpers\PermissionHelper::can(Auth::user(), 'fo.pos.discount'), 403);
    }

    public function mount()
    {
        $activeBranchId = \App\Helpers\BranchHelper::getActiveBranchId();
        $activeSession = \App\Models\CashSession::with('user')
            ->where('branch_id', $activeBranchId)
            ->where('status', 'open')
            ->first();

        if (!$activeSession) {
            Notification::make()
                ->title('Sesi Kasir Belum Dibuka')
                ->body('Anda harus membuka sesi kasir terlebih dahulu sebelum dapat mengakses POS.')
                ->warning()
                ->send();
            return redirect()->to('/pos/session');
        }

        $this->activeSessionInfo = [
            'id'           => $activeSession->id,
            'opened_at'    => $activeSession->opened_at->format('d M Y H:i'),
            'opening_cash' => $activeSession->opening_cash,
            'opened_by'    => $activeSession->user?->name ?? (Auth::user()?->name ?? 'Kasir'),
        ];

        // Lock selected branch to the active session's branch
        $this->selectedBranchId = $activeSession->branch_id;

        // Initialize pricing tiers from cached collection
        $this->selectedPricingTierId = $this->getDefaultRetailPricingTierId();

        // Initialize sales representatives and invoice date
        $this->selectedSalesRepId = Auth::id();
        $this->selectedSalesRepName = Auth::user() ? Auth::user()->name : '';
        $this->invoiceDate = now()->format('Y-m-d');
        $defaultCategory = $this->saleCategories->first();
        $this->saleCategory = $defaultCategory ? $defaultCategory->name : 'Store';
        $this->updatePreviewInvoiceNumber();
        $this->viewMode = session('pos_view_mode', 'grid');

        $this->visibleCategoryTabs = session('pos_visible_categories', []);
        if (empty($this->visibleCategoryTabs)) {
            // default top 6 categories
            $this->visibleCategoryTabs = collect($this->availableCategories)->take(6)->pluck('name')->toArray();
        }

        $this->updateBranchDetails();

        // Load sale for editing if query parameter is present
        $saleId = request()->query('edit');
        if ($saleId) {
            abort_unless(\App\Helpers\PermissionHelper::can(Auth::user(), 'fo.pos.edit_sale'), 403);
            $sale = \App\Models\Sale::with(['items.variant.product', 'items.variant.tierPrices', 'customer', 'salesRep'])->find($saleId);
            if ($sale) {
                if ($sale->branch_id !== $this->selectedBranchId) {
                    Notification::make()
                        ->title('Cabang Berbeda')
                        ->body('Transaksi ini terdaftar di cabang lain.')
                        ->danger()
                        ->send();
                } else {
                    $this->editingSaleId = $sale->id;
                    $this->previewInvoiceNumber = $sale->invoice_number;
                    if ($sale->customer) {
                        $this->selectedCustomerId = $sale->customer_id;
                        $this->selectedCustomerName = $sale->customer->name;
                        $this->customerPhone = $sale->customer->phone ?? '';
                        $this->isLoyaltyMember = (bool)$sale->customer->is_loyalty_member;
                        $this->customerPoints = (int)$sale->customer->loyalty_points;
                    } else {
                        $this->selectedCustomerId = null;
                        $this->selectedCustomerName = 'Umum / Walk-in';
                        $this->customerPhone = '';
                        $this->isLoyaltyMember = false;
                        $this->customerPoints = 0;
                    }
                    
                    $this->selectedSalesRepId = $sale->sales_rep_id;
                    $this->selectedSalesRepName = $sale->salesRep->name ?? '';
                    $this->saleCategory = $sale->sale_category;
                    $this->discountValue = $sale->discount_amount;
                    $this->discountType = 'fixed';
                    $this->enableTax = $sale->tax_amount > 0;
                    $this->notes = $sale->notes ?? '';
                    $this->invoiceDate = $sale->invoice_date->format('Y-m-d');
                    
                    $this->cart = [];
                    foreach ($sale->items as $item) {
                        $v = $item->variant;
                        if (!$v) continue;
                        
                        $name = $v->product->name;
                        if ($v->name) {
                            $name .= ' (' . $v->name . ')';
                        }

                        $tierPricesMap = [];
                        if ($v->relationLoaded('tierPrices')) {
                            foreach ($v->tierPrices as $tp) {
                                $tierPricesMap[$tp->pricing_tier_id] = (int) $tp->price;
                            }
                        }
                        
                        $this->cart[$v->id] = [
                            'variant_id' => $v->id,
                            'name' => $name,
                            'price' => $item->unit_price,
                            'base_price' => (int) $v->price,
                            'tier_prices' => $tierPricesMap,
                            'qty' => $item->quantity,
                            'type' => $v->product->type,
                            'category' => $v->product->category ?? null,
                            'emoji' => $v->product->isService() ? '🛠️' : ($v->product->isBundle() ? '📦' : '🎸'),
                            'notes' => $item->notes ?? '',
                            'discount_value' => $item->discount_amount / max(1, $item->quantity),
                            'discount_type' => 'percent',
                            'discount_amount' => $item->discount_amount,
                            'pricing_tier_id' => $this->selectedPricingTierId,
                        ];
                    }

                    Notification::make()
                        ->title('Mengedit Transaksi')
                        ->body("Memuat data transaksi {$sale->invoice_number} ke dalam keranjang.")
                        ->info()
                        ->send();
                }
            }
        } else {
            // Automatically open held transactions modal if any exist and we're not editing an existing sale
            if ($this->heldTransactionsCount > 0) {
                $this->showHeldModal = true;
                $this->isHeldModalMandatory = true;
            }
        }
    }

    public function updatedSelectedBranchId($value)
    {
        // Enforce active session branch lock
        $activeSession = \App\Models\CashSession::where('branch_id', $this->selectedBranchId)
            ->where('status', 'open')
            ->first();

        if ($activeSession) {
            $this->selectedBranchId = $activeSession->branch_id;
        }
        $this->updateBranchDetails();
    }

    public function updatedSelectedPricingTierId($value)
    {
        if ($value === 'custom' || empty($this->cart)) {
            return;
        }

        $variants = ProductVariant::with('tierPrices')->whereIn('id', array_keys($this->cart))->get()->keyBy('id');
        foreach ($this->cart as $variantId => $item) {
            $variant = $variants->get($variantId);
            if ($variant) {
                $this->cart[$variantId]['pricing_tier_id'] = $value;
                $this->cart[$variantId]['price'] = $variant->priceForTier($value);
                $this->recalculateItemDiscountAmount($variantId);
            }
        }
    }

    public function setPricingTier($tierId)
    {
        $this->selectedPricingTierId = $tierId;
        $this->updatedSelectedPricingTierId($tierId);
    }

    protected function updateBranchDetails()
    {
        $branch = Branch::find($this->selectedBranchId);
        if ($branch) {
            $this->selectedBranchName = $branch->name;
            $this->selectedStoreName = $branch->store_name ?: $branch->name ?: 'Diego Music Store & Repair';
            $this->selectedLogoUrl = (!empty($branch->logo_path) && trim($branch->logo_path) !== '') ? \Illuminate\Support\Facades\Storage::url($branch->logo_path) : null;
        } else {
            $this->selectedBranchName = '';
            $this->selectedStoreName = 'Diego Music Store & Repair';
            $this->selectedLogoUrl = null;
        }
    }

    /**
     * Ambil semua kategori unik dari DB yang memiliki produk aktif.
     * Digunakan untuk render tab kategori di UI POS.
     * Format: [['name' => 'GITAR ELECTRIC', 'count' => 368], ...]
     */
    public function getAvailableCategoriesProperty()
    {
        return cache()->remember('pos_available_categories', 300, function () {
            return \App\Models\Product::where('is_active', true)
                ->whereNotNull('category')
                ->where('category', '!=', '')
                ->selectRaw('category, COUNT(*) as total')
                ->groupBy('category')
                ->orderByDesc('total')
                ->get()
                ->map(fn($p) => ['name' => $p->category, 'count' => $p->total]);
        });
    }

    public function setViewMode(string $mode): void
    {
        $this->viewMode = in_array($mode, ['grid', 'list']) ? $mode : 'grid';
        session(['pos_view_mode' => $this->viewMode]);
    }

    public function updateVisibleCategories($categories)
    {
        $this->visibleCategoryTabs = $categories;
        session(['pos_visible_categories' => $this->visibleCategoryTabs]);
        $this->showCategoryFilterModal = false;
    }

    public function updatedVisibleCategoryTabs($value)
    {
        if (count($value) > 6) {
            $this->visibleCategoryTabs = array_slice($value, 0, 6);
        }
        session(['pos_visible_categories' => $this->visibleCategoryTabs]);
    }

    // Load products filtered by category and search
    // OPTIMASI TINGGI: join langsung ke products, eager load kolom spesifik, tanpa subquery whereHas
    public function getProductsProperty()
    {
        // Guard: jangan query apapun kalau modal belum terbuka
        if (!$this->showProductSearchModal) {
            return collect();
        }

        $branchId = $this->selectedBranchId;

        $query = ProductVariant::query()
            ->select('product_variants.*')
            ->join('products', 'products.id', '=', 'product_variants.product_id')
            ->with([
                'product:id,name,type,category,is_active',
                'branchStocks' => fn($q) => $q->select('id', 'product_variant_id', 'branch_id', 'stock')
                    ->when($branchId, fn($sq) => $sq->where('branch_id', $branchId)),
                'tierPrices' => fn($q) => $q->select('id', 'product_variant_id', 'pricing_tier_id', 'price'),
                'bundleItems.childVariant.product:id,name,type',
                'bundleItems.childVariant.branchStocks' => fn($q) => $q->select('id', 'product_variant_id', 'branch_id', 'stock')
                    ->when($branchId, fn($sq) => $sq->where('branch_id', $branchId)),
            ])
            ->where('product_variants.is_active', true)
            ->where('products.is_active', true);

        // Filter kategori langsung via kolom products.category
        if ($this->activeCategory !== 'Semua') {
            $query->where('products.category', $this->activeCategory);
        }

        // Search filter (SKU, name, barcode, nama produk)
        if (!empty($this->search)) {
            $search = trim($this->search);
            $likeSearch = '%' . $search . '%';
            
            $query->where(function ($q) use ($likeSearch) {
                $q->where('product_variants.barcode', 'like', $likeSearch)
                  ->orWhere('product_variants.sku', 'like', $likeSearch)
                  ->orWhere('product_variants.name', 'like', $likeSearch)
                  ->orWhere('products.name', 'like', $likeSearch);
            });
        }

        // Apply Sorting
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

        // Apply Pagination
        return $query->paginate(15, ['*'], 'productPage');
    }

    public function getHasMoreProductsProperty(): bool
    {
        return $this->showProductSearchModal && $this->products->hasMorePages();
    }

    // Reset paginasi produk saat search berubah
    public function updatedSearch(): void
    {
        $this->resetPage('productPage');
    }

    // Reset paginasi produk saat kategori berubah
    public function updatedActiveCategory(): void
    {
        $this->resetPage('productPage');
    }

    // Reset paginasi produk saat sorting berubah
    public function updatedSortBy(): void
    {
        $this->resetPage('productPage');
    }

    public function loadMoreCustomers(): void
    {
        $this->customerLimit += 20;
    }

    // Get matching customers for live search dropdown
    public function getCustomersProperty()
    {
        if (empty($this->customerSearch)) {
            return cache()->remember('pos_default_customers_' . $this->customerLimit, 300, function () {
                return Customer::select(['id', 'name', 'phone', 'is_loyalty_member'])
                    ->orderBy('name')
                    ->limit($this->customerLimit)
                    ->get();
            });
        }

        $search = '%' . trim($this->customerSearch) . '%';
        return Customer::select(['id', 'name', 'phone', 'is_loyalty_member'])
            ->where(function ($q) use ($search) {
                $q->where('name', 'like', $search)
                  ->orWhere('phone', 'like', $search);
            })
            ->orderBy('name')
            ->limit($this->customerLimit)
            ->get();
    }

    // Get matching active branch users (all roles: sales, manager toko, kasir, etc.) for sales rep dropdown
    public function getSalesRepsProperty()
    {
        $branchId = $this->selectedBranchId;

        $baseQuery = \App\Models\User::query()
            ->with('roles:id,name')
            ->select(['users.id', 'users.name', 'users.email'])
            ->where('users.is_active', true);

        if ($branchId) {
            $branchScoped = (clone $baseQuery)->where(function ($query) use ($branchId) {
                $query->whereHas('branches', fn ($b) => $b->where('branches.id', $branchId))
                    ->orWhereHas('employee', fn ($e) => $e->where('employees.branch_id', $branchId));
            });

            if ((clone $branchScoped)->exists()) {
                $baseQuery = $branchScoped;
            }
        }

        if (! empty($this->salesSearch)) {
            $search = '%' . trim($this->salesSearch) . '%';
            $baseQuery->where(function ($q) use ($search) {
                $q->where('users.name', 'like', $search)
                    ->orWhere('users.email', 'like', $search);
            });
        }

        return $baseQuery
            ->orderBy('users.name')
            ->limit(50)
            ->get();
    }

    // Get all sale categories
    public function getSaleCategoriesProperty()
    {
        return cache()->remember('pos_sale_categories', 600, fn() => \App\Models\SaleCategory::all());
    }

    public function updatePreviewInvoiceNumber(): void
    {
        $this->previewInvoiceNumber = \App\Models\Sale::generateInvoiceNumber($this->saleCategory);
    }

    public function updatedSaleCategory(): void
    {
        $this->updatePreviewInvoiceNumber();
    }

    /**
     * Preview the next invoice number for the currently selected sale category.
     * This is a candidate number only; checkout generates the definitive number.
     */
    public function getPreviewInvoiceNumberProperty(): string
    {
        if (empty($this->previewInvoiceNumber)) {
            $this->updatePreviewInvoiceNumber();
        }
        return $this->previewInvoiceNumber;
    }

    public function getBranchesProperty()
    {
        return cache()->remember('pos_branches', 86400, fn() => Branch::where('is_active', true)->get());
    }

    public function getPricingTiersProperty()
    {
        return cache()->remember('pos_pricing_tiers', 86400, fn() => \App\Models\PricingTier::all());
    }

    /**
     * Resolve default Retail/Umum pricing tier ID from cached tiers without issuing extra DB queries.
     */
    protected function getDefaultRetailPricingTierId(): ?int
    {
        $tiers = $this->pricingTiers;
        $defaultTier = $tiers->first(function ($tier) {
            $name = strtolower((string) $tier->name);
            return str_contains($name, 'retail') || str_contains($name, 'umum');
        }) ?? $tiers->first();

        return $defaultTier ? (int) $defaultTier->id : null;
    }

    // Get total sales of the active cash session
    public function getTodaySalesTotalProperty()
    {
        if (empty($this->activeSessionInfo['id'])) {
            return 0;
        }

        return cache()->remember('pos_today_sales_' . $this->activeSessionInfo['id'], 60, function () {
            return \App\Models\Sale::where('cash_session_id', $this->activeSessionInfo['id'])
                ->where('status', 'completed')
                ->sum('grand_total');
        });
    }

    // Hitung jumlah item di cart per kategori (untuk badge di tab) secara in-memory tanpa query database
    public function getCategoryCountsProperty()
    {
        $counts = ['Semua' => 0];

        if (empty($this->cart)) {
            return $counts;
        }

        $missingVariantIds = [];

        foreach ($this->cart as $id => $item) {
            $qty = $item['qty'] ?? 0;
            $counts['Semua'] += $qty;
            if (isset($item['category'])) {
                $cat = $item['category'];
                if ($cat) {
                    $counts[$cat] = ($counts[$cat] ?? 0) + $qty;
                }
            } else {
                $missingVariantIds[] = $id;
            }
        }

        // Fallback untuk item lawas di session yang belum punya field category
        if (!empty($missingVariantIds)) {
            $variants = \App\Models\ProductVariant::with(['product:id,category'])
                ->select(['id', 'product_id'])
                ->whereIn('id', $missingVariantIds)
                ->get();

            foreach ($variants as $variant) {
                $qty = $this->cart[$variant->id]['qty'] ?? 0;
                $cat = $variant->product->category ?? null;
                if ($cat) {
                    $counts[$cat] = ($counts[$cat] ?? 0) + $qty;
                }
            }
        }

        return $counts;
    }

    public function openProductSearch()
    {
        $this->showProductSearchModal = true;
        $this->resetPage('productPage');
        $this->dispatch('open-pos-product-search', pricingTierId: $this->selectedPricingTierId);
    }

    protected function syncCartStateToModal(): void
    {
        $quantities = [];
        foreach ($this->cart as $variantId => $item) {
            $quantities[(string) $variantId] = (int) ($item['qty'] ?? 0);
        }

        $this->dispatch('pos-cart-synced', quantities: $quantities, categoryCounts: $this->categoryCounts);
    }

    #[On('pos-add-to-cart')]
    public function handleAddToCartFromModal(int $variantId): void
    {
        $this->addToCart($variantId);
    }

    #[On('pos-set-cart-qty')]
    public function handleSetCartQtyFromModal(int $variantId, int $qty): void
    {
        $this->setCartQty($variantId, $qty);
    }

    public function closeProductSearch()
    {
        $this->showProductSearchModal = false;
        $this->search = '';
        $this->resetPage('productPage');
    }

    public function getCartVariantsProperty()
    {
        if (empty($this->cart)) {
            return collect();
        }

        // Only query variants that don't have tier_prices cached in cart (note: empty array [] means already resolved with no overrides)
        $missingIds = [];
        foreach ($this->cart as $id => $item) {
            if (!array_key_exists('tier_prices', $item)) {
                $missingIds[] = $id;
            }
        }

        if (empty($missingIds)) {
            return collect();
        }

        return \App\Models\ProductVariant::with(['tierPrices:id,product_variant_id,pricing_tier_id,price'])
            ->whereIn('id', $missingIds)
            ->get(['id', 'price'])
            ->keyBy('id');
    }

    public function selectSalesRep($id, ?string $name = null)
    {
        $user = $id ? \App\Models\User::select(['id', 'name'])->find($id) : null;
        $this->selectedSalesRepId = $user?->id ?? $id;
        $this->selectedSalesRepName = $user?->name ?? ($name ?? '');
        $this->salesSearch = '';
    }

    public function clearSalesRep()
    {
        $this->selectedSalesRepId = null;
        $this->selectedSalesRepName = '';
        $this->salesSearch = '';
    }

    #[On('pos-select-sales-rep')]
    public function handleSelectSalesRepFromQueue(array $payload): void
    {
        $this->selectedSalesRepId = $payload['id'] ?? null;
        $this->selectedSalesRepName = $payload['name'] ?? '';
        $this->salesSearch = '';
    }


    public function render()
    {
        $view = view('filament.pages.pos.pos');

        if (\Livewire\Livewire::isLivewireRequest()) {
            return $view->layout('layouts.pos');
        }

        return $view->layout('layouts.pos', [
            'posCashierHeader' => [
                'branches' => $this->branches,
                'selectedBranchId' => $this->selectedBranchId,
                'selectedStoreName' => $this->selectedStoreName,
                'selectedBranchName' => $this->selectedBranchName,
                'activeSessionInfo' => $this->activeSessionInfo,
                'todaySalesTotal' => $this->todaySalesTotal,
            ],
        ]);
    }
}
