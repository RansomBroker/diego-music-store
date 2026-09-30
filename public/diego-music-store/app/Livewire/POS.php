<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\ProductVariant;
use App\Models\Customer;
use App\Models\Branch;
use App\Actions\Sales\CreatePOSSale;
use App\Actions\Customer\CreateCustomer;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;

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
    use WithVouchers;

    // Livewire states
    public $search = '';
    public $activeCategory = 'Semua';
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
    const PRODUCTS_PER_PAGE = 40;

    // Payment state
    public $notes = '';
    public $paymentMethod = 'cash';
    public $discountValue = 0;
    public $discountType = 'percent';
    public $enableTax = false;
    public $taxPercent = 11;
    public $amountPaid = 0;
    public $showPaymentModal = false;

    // Split payment state
    public $selectedPaymentMethods = ['cash'];
    public $amountCash = 0;
    public $amountDebit = 0;
    public $amountCredit = 0;
    public $debitRef = '';
    public array $paymentAmounts = [];
    public array $paymentRefs = [];
    public array $paymentSubMethods = [];

    // Live Voucher validation state
    public string $voucherCodeInput = '';
    public ?\App\Models\Voucher $appliedVoucher = null;
    public string $voucherValidationMessage = '';
    public bool $voucherIsValid = false;

    // Available branches and current branch
    public $branches = [];
    public $selectedBranchId = null;
    public $selectedBranchName = '';
    public $selectedStoreName = '';
    public $selectedLogoUrl = '';

    // Pricing Tier selection
    public $pricingTiers = [];
    public $selectedPricingTierId = null;

    // Sales Rep and Invoice Date
    public $selectedSalesRepId = null;
    public $selectedSalesRepName = '';
    public $salesSearch = '';
    public $saleCategory = 'Store';
    public $invoiceDate = '';
    public $activeSessionInfo = [];

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

        $this->branches = Branch::where('is_active', true)->get();
        // Lock selected branch to the active session's branch
        $this->selectedBranchId = $activeSession->branch_id;

        // Initialize pricing tiers
        $this->pricingTiers = \App\Models\PricingTier::all();
        $defaultTier = \App\Models\PricingTier::where('name', 'like', '%retail%')
            ->orWhere('name', 'like', '%umum%')
            ->first() ?? \App\Models\PricingTier::first();
        $this->selectedPricingTierId = $defaultTier ? $defaultTier->id : null;

        // Initialize sales representatives and invoice date
        $this->selectedSalesRepId = Auth::id();
        $this->selectedSalesRepName = Auth::user() ? Auth::user()->name : '';
        $this->invoiceDate = now()->format('Y-m-d');
        $defaultCategory = \App\Models\SaleCategory::first();
        $this->saleCategory = $defaultCategory ? $defaultCategory->name : 'Store';

        $this->updateBranchDetails();

        // Load sale for editing if query parameter is present
        $saleId = request()->query('edit');
        if ($saleId) {
            $sale = \App\Models\Sale::with(['items.variant.product', 'customer', 'salesRep'])->find($saleId);
            if ($sale) {
                if ($sale->branch_id !== $this->selectedBranchId) {
                    Notification::make()
                        ->title('Cabang Berbeda')
                        ->body('Transaksi ini terdaftar di cabang lain.')
                        ->danger()
                        ->send();
                } else {
                    $this->editingSaleId = $sale->id;
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
                    
                    // Parse payment method
                    $this->paymentMethod = 'cash';
                    if (str_contains(strtolower($sale->payment_method), 'debit')) {
                        $this->paymentMethod = 'debit';
                    } elseif (str_contains(strtolower($sale->payment_method), 'credit') || str_contains(strtolower($sale->payment_method), 'piutang')) {
                        $this->paymentMethod = 'credit';
                    }
                    
                    $this->cart = [];
                    foreach ($sale->items as $item) {
                        $v = $item->variant;
                        if (!$v) continue;
                        
                        $name = $v->product->name;
                        if ($v->name) {
                            $name .= ' (' . $v->name . ')';
                        }
                        
                        $this->cart[$v->id] = [
                            'variant_id' => $v->id,
                            'name' => $name,
                            'price' => $item->unit_price,
                            'qty' => $item->quantity,
                            'type' => $v->product->type,
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
            $heldCount = \App\Models\PosHeldTransaction::where('user_id', Auth::id())
                ->where('branch_id', $this->selectedBranchId)
                ->count();
            if ($heldCount > 0) {
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
        if ($value === 'custom') {
            return;
        }

        foreach ($this->cart as $variantId => $item) {
            $variant = ProductVariant::find($variantId);
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
        return \App\Models\Product::where('is_active', true)
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->selectRaw('category, COUNT(*) as total')
            ->groupBy('category')
            ->orderByDesc('total')
            ->get()
            ->map(fn($p) => ['name' => $p->category, 'count' => $p->total]);
    }

    // Load products filtered by category and search
    // OPTIMASI: hanya query ketika modal terbuka, filter kategori langsung di SQL
    public function getProductsProperty()
    {
        // Guard: jangan query apapun kalau modal belum terbuka
        if (!$this->showProductSearchModal) {
            return collect();
        }

        $take = self::PRODUCTS_PER_PAGE * $this->productPage;

        $query = ProductVariant::with(['product', 'branchStocks'])
            ->where('is_active', true)
            ->whereHas('product', function ($q) {
                $q->where('is_active', true);

                // Filter langsung pakai nilai category dari DB
                if ($this->activeCategory !== 'Semua') {
                    $q->where('category', $this->activeCategory);
                }
            });

        // Search filter (SKU, name, barcode, nama produk)
        if (!empty($this->search)) {
            $search = '%' . $this->search . '%';
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', $search)
                  ->orWhere('sku', 'like', $search)
                  ->orWhere('barcode', 'like', $search)
                  ->orWhereHas('product', function ($pq) use ($search) {
                      $pq->where('name', 'like', $search);
                  });
            });
        }

        // Ambil $take + 1 untuk deteksi apakah masih ada data, lalu potong ke $take
        return $query->limit($take + 1)->get()->take($take);
    }

    /**
     * Computed property: apakah masih ada produk berikutnya.
     * Dibandingkan jumlah produk yang sudah dimuat vs kapasitas halaman saat ini.
     * Kalau produk yang tampil == $take (kapasitas penuh), artinya masih ada lebih.
     */
    public function getHasMoreProductsProperty(): bool
    {
        if (!$this->showProductSearchModal) {
            return false;
        }
        $take = self::PRODUCTS_PER_PAGE * $this->productPage;
        return $this->products->count() >= $take;
    }

    // Muat halaman produk berikutnya (dipanggil oleh infinite scroll di frontend)
    public function loadMoreProducts(): void
    {
        $this->productPage++;
    }


    // Reset paginasi produk saat search berubah
    public function updatedSearch(): void
    {
        $this->productPage = 1;
    }

    // Reset paginasi produk saat kategori berubah
    public function updatedActiveCategory(): void
    {
        $this->productPage = 1;
    }

    public function loadMoreCustomers(): void
    {
        $this->customerLimit += 20;
    }

    // Get matching customers for live search dropdown
    public function getCustomersProperty()
    {
        if (empty($this->customerSearch)) {
            return Customer::orderBy('name')->limit($this->customerLimit)->get();
        }

        $search = '%' . $this->customerSearch . '%';
        return Customer::where('name', 'like', $search)
            ->orWhere('phone', 'like', $search)
            ->limit($this->customerLimit)
            ->get();
    }

    // Get matching users with "sales" role for live search dropdown
    public function getSalesRepsProperty()
    {
        $query = \App\Models\User::role('sales');
        
        if (!empty($this->salesSearch)) {
            $search = '%' . $this->salesSearch . '%';
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', $search)
                  ->orWhere('email', 'like', $search);
            });
        } else {
            $query->orderBy('name');
        }
        
        return $query->limit(5)->get();
    }

    // Get all sale categories
    public function getSaleCategoriesProperty()
    {
        return \App\Models\SaleCategory::all();
    }

    // Get total sales of the active cash session
    public function getTodaySalesTotalProperty()
    {
        if (empty($this->activeSessionInfo['id'])) {
            return 0;
        }
        return \App\Models\Sale::where('cash_session_id', $this->activeSessionInfo['id'])
            ->where('status', 'completed')
            ->sum('grand_total');
    }

    // Hitung jumlah item di cart per kategori DB (untuk badge di tab)
    public function getCategoryCountsProperty()
    {
        $counts = ['Semua' => 0];

        if (empty($this->cart)) {
            return $counts;
        }

        $variantIds = array_keys($this->cart);
        $variants = \App\Models\ProductVariant::with(['product:id,category'])
            ->select(['id', 'product_id'])
            ->whereIn('id', $variantIds)
            ->get();

        foreach ($variants as $variant) {
            $qty   = $this->cart[$variant->id]['qty'] ?? 0;
            $cat   = $variant->product->category ?? null;
            $counts['Semua'] += $qty;
            if ($cat) {
                $counts[$cat] = ($counts[$cat] ?? 0) + $qty;
            }
        }

        return $counts;
    }

    public function openProductSearch()
    {
        $this->showProductSearchModal = true;
    }

    public function closeProductSearch()
    {
        $this->showProductSearchModal = false;
        $this->search = '';
    }

    public function getCartVariantsProperty()
    {
        if (empty($this->cart)) {
            return collect();
        }
        return \App\Models\ProductVariant::whereIn('id', array_keys($this->cart))->get()->keyBy('id');
    }

    public function selectSalesRep($id, $name)
    {
        $this->selectedSalesRepId = $id;
        $this->selectedSalesRepName = $name;
        $this->salesSearch = '';
    }

    public function clearSalesRep()
    {
        $this->selectedSalesRepId = null;
        $this->selectedSalesRepName = '';
        $this->salesSearch = '';
    }

    public function getPreviewInvoiceNumberProperty()
    {
        if ($this->editingSaleId) {
            $sale = \App\Models\Sale::find($this->editingSaleId);
            if ($sale) {
                return $sale->invoice_number;
            }
        }
        return \App\Models\Sale::generateInvoiceNumber($this->saleCategory);
    }

    public function render()
    {
        $this->autoSaveDraft();
        return view('filament.pages.pos.pos')
            ->layout('layouts.pos');
    }
}
