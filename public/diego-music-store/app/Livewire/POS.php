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

class POS extends Component
{
    // Livewire states
    public $search = '';
    public $activeCategory = 'Semua';
    public $cart = [];
    
    // Customer search & selection
    public $customerSearch = '';
    public $selectedCustomerId = null;
    public $selectedCustomerName = 'Umum / Walk-in';
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
    public $lastSaleId = null;
    public $editingSaleId = null;

    // Product search modal
    public $showProductSearchModal = false;

    // Payment state
    public $paymentMethod = 'cash';
    public $discountValue = 0;
    public $discountType = 'fixed';
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
        // Enforce active cashier session check
        $activeSession = \App\Models\CashSession::where('user_id', Auth::id())
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
            'id' => $activeSession->id,
            'opened_at' => $activeSession->opened_at->format('d M Y H:i'),
            'opening_cash' => $activeSession->opening_cash,
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
                        $this->isLoyaltyMember = (bool)$sale->customer->is_loyalty_member;
                        $this->customerPoints = (int)$sale->customer->loyalty_points;
                    } else {
                        $this->selectedCustomerId = null;
                        $this->selectedCustomerName = 'Umum / Walk-in';
                        $this->isLoyaltyMember = false;
                        $this->customerPoints = 0;
                    }
                    
                    $this->selectedSalesRepId = $sale->sales_rep_id;
                    $this->selectedSalesRepName = $sale->salesRep->name ?? '';
                    $this->saleCategory = $sale->sale_category;
                    $this->discountValue = $sale->discount_amount;
                    $this->discountType = 'fixed';
                    $this->enableTax = $sale->tax_amount > 0;
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
                            'discount_type' => 'fixed',
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
        }
    }

    public function updatedSelectedBranchId($value)
    {
        // Enforce active session branch lock
        $activeSession = \App\Models\CashSession::where('user_id', Auth::id())
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

    // Load products filtered by category and search
    public function getProductsProperty()
    {
        $query = ProductVariant::with(['product', 'branchStocks'])
            ->where('is_active', true)
            ->whereHas('product', function ($q) {
                $q->where('is_active', true);
            });

        // Search filter (SKU, name, barcode)
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

        $allVariants = $query->get();

        // Categorize logically
        return $allVariants->filter(function ($variant) {
            $cat = $this->getCategoryOfVariant($variant);
            if ($this->activeCategory === 'Semua') {
                return true;
            }
            return $cat === $this->activeCategory;
        });
    }

    private function getCategoryOfVariant($variant): string
    {
        if ($variant->product->isService()) {
            return 'Jasa Reparasi';
        }
        
        $name = strtolower($variant->product->name . ' ' . $variant->name);
        
        if (str_contains($name, 'gitar') || str_contains($name, 'guitar') || str_contains($name, 'bass')) {
            return 'Gitar & Bass';
        }
        if (str_contains($name, 'keyboard') || str_contains($name, 'piano') || str_contains($name, 'organ')) {
            return 'Keyboard & Piano';
        }
        if (str_contains($name, 'drum') || str_contains($name, 'stick') || str_contains($name, 'perkusi')) {
            return 'Drum & Perkusi';
        }
        if (str_contains($name, 'senar') || str_contains($name, 'kabel') || str_contains($name, 'jack') || str_contains($name, 'aksesoris')) {
            return 'Aksesoris';
        }
        
        return 'Aksesoris'; // Fallback
    }

    // Get matching customers for live search dropdown
    public function getCustomersProperty()
    {
        if (empty($this->customerSearch)) {
            return Customer::orderBy('name')->limit(5)->get();
        }

        $search = '%' . $this->customerSearch . '%';
        return Customer::where('name', 'like', $search)
            ->orWhere('phone', 'like', $search)
            ->limit(5)
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

    // Get count of cart items per category
    public function getCategoryCountsProperty()
    {
        $counts = [
            'Semua' => 0,
            'Gitar & Bass' => 0,
            'Keyboard & Piano' => 0,
            'Drum & Perkusi' => 0,
            'Aksesoris' => 0,
            'Jasa Reparasi' => 0,
        ];
        
        if (empty($this->cart)) {
            return $counts;
        }
        
        $variantIds = array_keys($this->cart);
        $variants = \App\Models\ProductVariant::with('product')->whereIn('id', $variantIds)->get();
        
        foreach ($variants as $variant) {
            $qty = $this->cart[$variant->id]['qty'] ?? 0;
            $cat = $this->getCategoryOfVariant($variant);
            if (isset($counts[$cat])) {
                $counts[$cat] += $qty;
            }
            $counts['Semua'] += $qty;
        }
        
        return $counts;
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

    public function selectCustomer($id, $name, $isLoyalty)
    {
        $this->selectedCustomerId = $id;
        $this->selectedCustomerName = $name;
        $this->isLoyaltyMember = $isLoyalty;
        $this->customerSearch = ''; // Clear search
        $this->discountValue = 0;
        $this->discountType = 'fixed';
        $this->usePoints = false;

        // Automatically set pricing tier if registered for this customer
        $customer = Customer::find($id);
        if ($customer) {
            $this->customerPoints = $customer->loyalty_points;
            if ($customer->pricing_tier_id) {
                $this->selectedPricingTierId = $customer->pricing_tier_id;
            } else {
                // Fallback to default retail tier
                $defaultTier = \App\Models\PricingTier::where('name', 'like', '%retail%')
                    ->orWhere('name', 'like', '%umum%')
                    ->first() ?? \App\Models\PricingTier::first();
                $this->selectedPricingTierId = $defaultTier ? $defaultTier->id : null;
            }
        } else {
            $this->customerPoints = 0;
            // Fallback to default retail tier
            $defaultTier = \App\Models\PricingTier::where('name', 'like', '%retail%')
                ->orWhere('name', 'like', '%umum%')
                ->first() ?? \App\Models\PricingTier::first();
            $this->selectedPricingTierId = $defaultTier ? $defaultTier->id : null;
        }
        $this->updatedSelectedPricingTierId($this->selectedPricingTierId);
    }

    public function clearCustomer()
    {
        $this->selectedCustomerId = null;
        $this->selectedCustomerName = 'Umum / Walk-in';
        $this->isLoyaltyMember = false;
        $this->discountValue = 0;
        $this->discountType = 'fixed';
        $this->customerPoints = 0;
        $this->usePoints = false;

        // Fallback to default retail tier
        $defaultTier = \App\Models\PricingTier::where('name', 'like', '%retail%')
            ->orWhere('name', 'like', '%umum%')
            ->first() ?? \App\Models\PricingTier::first();
        $this->selectedPricingTierId = $defaultTier ? $defaultTier->id : null;
        $this->updatedSelectedPricingTierId($this->selectedPricingTierId);
    }

    public function openCreateCustomerModal()
    {
        $this->newCustomerName = $this->customerSearch;
        $this->newCustomerPhone = '';
        $this->newCustomerEmail = '';
        $this->newCustomerAddress = '';
        
        $defaultTier = \App\Models\PricingTier::where('name', 'like', '%retail%')
            ->orWhere('name', 'like', '%umum%')
            ->first() ?? \App\Models\PricingTier::first();
        $this->newCustomerPricingTierId = $defaultTier ? $defaultTier->id : null;
        $this->newCustomerIsLoyaltyMember = true;
        
        $this->showCreateCustomerModal = true;
    }

    public function createCustomer(CreateCustomer $createCustomerAction)
    {
        $this->validate([
            'newCustomerName' => 'required|string|max:255',
            'newCustomerPhone' => 'nullable|string|max:255',
            'newCustomerEmail' => 'nullable|email|max:255',
            'newCustomerAddress' => 'nullable|string|max:1000',
            'newCustomerPricingTierId' => 'nullable|exists:pricing_tiers,id',
        ], [
            'newCustomerName.required' => 'Nama pelanggan wajib diisi.',
            'newCustomerEmail.email' => 'Format email tidak valid.',
        ]);

        $customer = $createCustomerAction->execute([
            'name' => $this->newCustomerName,
            'phone' => $this->newCustomerPhone,
            'email' => $this->newCustomerEmail,
            'address' => $this->newCustomerAddress,
            'pricing_tier_id' => $this->newCustomerPricingTierId,
            'is_loyalty_member' => true,
            'loyalty_points' => 0,
        ]);

        Notification::make()
            ->title('Pelanggan Berhasil Didaftarkan')
            ->body("Pelanggan {$customer->name} telah berhasil disimpan dan terpilih.")
            ->success()
            ->send();

        $this->selectCustomer($customer->id, $customer->name, $customer->is_loyalty_member);
        $this->showCreateCustomerModal = false;
    }

    public function setCategory($category)
    {
        $this->activeCategory = $category;
    }

    // Cart actions
    public function addToCart($variantId)
    {
        $variant = ProductVariant::with('product')->findOrFail($variantId);
        
        // Check stock for physical products and bundles
        if ($variant->product->isPhysical() || $variant->product->isBundle()) {
            $stock = $variant->stockForBranch($this->selectedBranchId);
            $currentInCart = $this->cart[$variantId]['qty'] ?? 0;
            if ($stock <= $currentInCart) {
                Notification::make()
                    ->title('Stok Tidak Cukup')
                    ->body("Stok untuk {$variant->product->name} ({$variant->name}) tersisa {$stock} pcs.")
                    ->warning()
                    ->send();
                return;
            }
        }

        if (isset($this->cart[$variantId])) {
            $this->cart[$variantId]['qty']++;
        } else {
            $name = $variant->product->name;
            if ($variant->name) {
                $name .= ' (' . $variant->name . ')';
            }
            $tierId = $this->selectedPricingTierId;
            if ($tierId === 'custom') {
                $defaultTier = \App\Models\PricingTier::where('name', 'like', '%retail%')->first() ?? \App\Models\PricingTier::first();
                $tierId = $defaultTier ? $defaultTier->id : null;
            }

            $this->cart[$variantId] = [
                'variant_id' => $variant->id,
                'name' => $name,
                'price' => $variant->priceForTier($tierId),
                'qty' => 1,
                'type' => $variant->product->type,
                'emoji' => $variant->product->isService() ? '🛠️' : ($variant->product->isBundle() ? '📦' : '🎸'),
                'notes' => '',
                'discount_value' => 0,
                'discount_type' => 'fixed',
                'discount_amount' => 0,
                'pricing_tier_id' => $tierId,
            ];
        }
    }

    public function updateQty($variantId, $change)
    {
        if (!isset($this->cart[$variantId])) {
            return;
        }

        $newQty = $this->cart[$variantId]['qty'] + $change;

        if ($newQty <= 0) {
            unset($this->cart[$variantId]);
            return;
        }

        // Check stock
        $variant = ProductVariant::findOrFail($variantId);
        if (($variant->product->isPhysical() || $variant->product->isBundle()) && $change > 0) {
            $stock = $variant->stockForBranch($this->selectedBranchId);
            if ($stock < $newQty) {
                Notification::make()
                    ->title('Stok Tidak Cukup')
                    ->body("Stok untuk {$variant->product->name} ({$variant->name}) tersisa {$stock} pcs.")
                    ->warning()
                    ->send();
                return;
            }
        }

        $this->cart[$variantId]['qty'] = $newQty;
        $this->recalculateItemDiscountAmount($variantId);
    }

    public function updateItemNote($variantId, $note)
    {
        if (isset($this->cart[$variantId])) {
            $this->cart[$variantId]['notes'] = $note;
        }
    }

    public function updateItemPricingTier($variantId, $tierId)
    {
        if (isset($this->cart[$variantId])) {
            $variant = ProductVariant::find($variantId);
            if ($variant) {
                $this->cart[$variantId]['pricing_tier_id'] = $tierId;
                $this->cart[$variantId]['price'] = $variant->priceForTier($tierId);
                $this->recalculateItemDiscountAmount($variantId);
                $this->selectedPricingTierId = 'custom';
            }
        }
    }

    public function updateItemDiscountValue($variantId, $value)
    {
        if (isset($this->cart[$variantId])) {
            $this->cart[$variantId]['discount_value'] = max(0, intval($value));
            $this->recalculateItemDiscountAmount($variantId);
        }
    }

    public function toggleItemDiscountType($variantId)
    {
        if (isset($this->cart[$variantId])) {
            $currentType = $this->cart[$variantId]['discount_type'] ?? 'fixed';
            $this->cart[$variantId]['discount_type'] = $currentType === 'fixed' ? 'percent' : 'fixed';
            $this->recalculateItemDiscountAmount($variantId);
        }
    }

    protected function recalculateItemDiscountAmount($variantId)
    {
        if (isset($this->cart[$variantId])) {
            $item = &$this->cart[$variantId];
            $value = intval($item['discount_value'] ?? 0);
            $type = $item['discount_type'] ?? 'fixed';
            $price = intval($item['price'] ?? 0);
            $qty = intval($item['qty'] ?? 1);

            if ($type === 'percent') {
                $item['discount_amount'] = intval(($price * $qty) * ($value / 100));
            } else {
                $item['discount_amount'] = $value;
            }
        }
    }

    // Calculations helper
    public function getSubtotalProperty()
    {
        $sum = 0;
        foreach ($this->cart as $item) {
            $itemDiscount = intval($item['discount_amount'] ?? 0);
            $sum += ($item['price'] * $item['qty']) - $itemDiscount;
        }
        return $sum;
    }

    public function getDiscountAmountProperty()
    {
        if ($this->discountType === 'percent') {
            return min($this->subtotal, intval($this->subtotal * ($this->discountValue / 100)));
        }
        return min($this->subtotal, intval($this->discountValue));
    }

    public function toggleGlobalDiscountType()
    {
        $this->discountType = $this->discountType === 'fixed' ? 'percent' : 'fixed';
    }

    public function getPreviewInvoiceNumberProperty()
    {
        if ($this->editingSaleId) {
            $sale = \App\Models\Sale::find($this->editingSaleId);
            if ($sale) {
                return $sale->invoice_number;
            }
        }
        return \App\Models\Sale::generateInvoiceNumber();
    }

    public function getHeldTransactionsProperty()
    {
        return \App\Models\PosHeldTransaction::where('user_id', Auth::id())
            ->where('branch_id', $this->selectedBranchId)
            ->latest()
            ->get();
    }

    public function getPaymentMethodsProperty()
    {
        return \App\Models\PaymentMethod::where('is_active', true)->get();
    }

    public function getTaxAmountProperty()
    {
        if (!$this->enableTax) {
            return 0;
        }
        return intval(($this->subtotal - $this->discountAmount) * ($this->taxPercent / 100));
    }

    public function getGrandTotalProperty()
    {
        $baseTotal = $this->subtotal - $this->discountAmount + $this->taxAmount;
        return max(0, $baseTotal - $this->pointDiscountAmount);
    }

    public function getPointDiscountAmountProperty()
    {
        if (!$this->usePoints || !$this->selectedCustomerId || $this->customerPoints <= 0) {
            return 0;
        }
        $maxDiscount = $this->subtotal - $this->discountAmount + $this->taxAmount;
        $pointsValue = $this->customerPoints * self::POINT_VALUATION;
        return min($pointsValue, $maxDiscount);
    }

    // Checkout / Payment operations
    public function openPayment()
    {
        if (empty($this->cart)) {
            Notification::make()
                ->title('Keranjang Kosong')
                ->danger()
                ->send();
            return;
        }
        
        $this->selectedPaymentMethods = ['cash'];
        $this->amountCash = $this->grandTotal;
        $this->amountDebit = 0;
        $this->amountCredit = 0;
        $this->debitRef = '';
        $this->amountPaid = $this->grandTotal;
        
        $this->paymentAmounts = [
            'cash' => $this->grandTotal
        ];
        $this->paymentRefs = [];
        
        $this->showPaymentModal = true;
    }

    public function closePayment()
    {
        $this->showPaymentModal = false;
    }

    public function togglePaymentMethod($method)
    {
        if (in_array($method, $this->selectedPaymentMethods)) {
            if (count($this->selectedPaymentMethods) > 1) {
                $this->selectedPaymentMethods = array_values(array_diff($this->selectedPaymentMethods, [$method]));
                if ($method === 'cash') $this->amountCash = 0;
                if ($method === 'debit') {
                    $this->amountDebit = 0;
                    $this->debitRef = '';
                }
                if ($method === 'credit') $this->amountCredit = 0;
                
                unset($this->paymentAmounts[$method]);
                unset($this->paymentRefs[$method]);
            }
        } else {
            $this->selectedPaymentMethods[] = $method;
            $this->paymentAmounts[$method] = 0;
        }

        $this->distributePaymentAmounts();
    }

    public function distributePaymentAmounts()
    {
        $total = $this->grandTotal;
        $count = count($this->selectedPaymentMethods);
        
        if ($count === 1) {
            $method = $this->selectedPaymentMethods[0];
            $this->paymentAmounts = [$method => $total];
            
            // Sync compatibility vars
            $this->amountCash = ($method === 'cash') ? $total : 0;
            $this->amountDebit = ($method === 'debit') ? $total : 0;
            $this->amountCredit = ($method === 'credit') ? $total : 0;
        } else {
            $assigned = 0;
            foreach ($this->selectedPaymentMethods as $method) {
                $assigned += intval($this->paymentAmounts[$method] ?? 0);
            }

            if ($assigned != $total) {
                if (in_array('cash', $this->selectedPaymentMethods)) {
                    $other = 0;
                    foreach ($this->selectedPaymentMethods as $method) {
                        if ($method !== 'cash') {
                            $other += intval($this->paymentAmounts[$method] ?? 0);
                        }
                    }
                    $this->paymentAmounts['cash'] = max(0, $total - $other);
                    $this->amountCash = $this->paymentAmounts['cash'];
                } else {
                    $firstMethod = $this->selectedPaymentMethods[0];
                    $other = 0;
                    foreach ($this->selectedPaymentMethods as $method) {
                        if ($method !== $firstMethod) {
                            $other += intval($this->paymentAmounts[$method] ?? 0);
                        }
                    }
                    $this->paymentAmounts[$firstMethod] = max(0, $total - $other);
                    
                    if ($firstMethod === 'cash') $this->amountCash = $this->paymentAmounts[$firstMethod];
                    if ($firstMethod === 'debit') $this->amountDebit = $this->paymentAmounts[$firstMethod];
                    if ($firstMethod === 'credit') $this->amountCredit = $this->paymentAmounts[$firstMethod];
                }
            }
        }
    }

    public function updated($property, $value)
    {
        if (str_starts_with($property, 'paymentAmounts.')) {
            $code = str_replace('paymentAmounts.', '', $property);
            if ($code === 'cash') $this->amountCash = intval($value);
            if ($code === 'debit') $this->amountDebit = intval($value);
            if ($code === 'credit') $this->amountCredit = intval($value);

            $this->distributePaymentAmounts();
        }

        if (str_starts_with($property, 'paymentRefs.')) {
            $code = str_replace('paymentRefs.', '', $property);
            if ($code === 'debit') $this->debitRef = $value;
        }

        if ($property === 'amountCash') {
            $this->paymentAmounts['cash'] = intval($value);
            $this->distributePaymentAmounts();
        }
        if ($property === 'amountDebit') {
            $this->paymentAmounts['debit'] = intval($value);
            $this->distributePaymentAmounts();
        }
        if ($property === 'amountCredit') {
            $this->paymentAmounts['credit'] = intval($value);
            $this->distributePaymentAmounts();
        }
        if ($property === 'debitRef') {
            $this->paymentRefs['debit'] = $value;
        }
    }

    public function checkout()
    {
        $totalPaid = 0;
        foreach ($this->selectedPaymentMethods as $method) {
            $totalPaid += intval($this->paymentAmounts[$method] ?? ($method === 'cash' ? $this->amountCash : ($method === 'debit' ? $this->amountDebit : ($method === 'credit' ? $this->amountCredit : 0))));
        }

        if (in_array('cash', $this->selectedPaymentMethods)) {
            if ($totalPaid < $this->grandTotal) {
                Notification::make()
                    ->title('Jumlah Bayar Kurang')
                    ->body('Total pembayaran kurang dari total tagihan.')
                    ->danger()
                    ->send();
                return;
            }
        } else {
            if ($totalPaid != $this->grandTotal) {
                Notification::make()
                    ->title('Jumlah Bayar Tidak Pas')
                    ->body('Pembayaran non-tunai harus pas dengan total tagihan: ' . number_format($this->grandTotal, 0, ',', '.'))
                    ->danger()
                    ->send();
                return;
            }
        }

        try {
            $activeSession = \App\Models\CashSession::where('user_id', Auth::id())
                ->where('status', 'open')
                ->first();

            if (!$activeSession) {
                throw new \Exception('Sesi kasir aktif tidak ditemukan. Silakan buka sesi terlebih dahulu.');
            }

            $itemsData = [];
            foreach ($this->cart as $c) {
                $itemsData[] = [
                    'variant_id' => $c['variant_id'],
                    'qty' => $c['qty'],
                    'price' => $c['price'],
                    'discount_amount' => $c['discount_amount'] ?? 0,
                    'notes' => $c['notes'] ?? null,
                ];
            }

            // Calculate points to deduct and points discount to apply
            $pointsUsed = 0;
            $pointDiscount = 0;
            if ($this->usePoints && $this->selectedCustomerId && $this->customerPoints > 0) {
                $pointDiscount = $this->pointDiscountAmount;
                $pointsUsed = ceil($pointDiscount / self::POINT_VALUATION);
            }

            // Compile payments data for split payment support
            $paymentsData = [];
            $change = 0;
            $cashPaid = in_array('cash', $this->selectedPaymentMethods) ? intval($this->paymentAmounts['cash'] ?? $this->amountCash) : 0;
            if ($cashPaid > 0) {
                $change = max(0, $totalPaid - $this->grandTotal);
                $netCash = $cashPaid - $change;
                if ($netCash > 0) {
                    $paymentsData[] = [
                        'method' => 'cash',
                        'amount' => $netCash,
                        'ref' => null
                    ];
                }
            }

            foreach ($this->selectedPaymentMethods as $method) {
                if ($method === 'cash') continue;
                
                $amount = intval($this->paymentAmounts[$method] ?? ($method === 'debit' ? $this->amountDebit : ($method === 'credit' ? $this->amountCredit : 0)));
                if ($amount > 0) {
                    $paymentsData[] = [
                        'method' => $method,
                        'amount' => $amount,
                        'ref' => $this->paymentRefs[$method] ?? ($method === 'debit' ? $this->debitRef : null)
                    ];
                }
            }

            // Compile human-readable payment method name
            $methodNames = [];
            foreach ($this->selectedPaymentMethods as $m) {
                $amount = intval($this->paymentAmounts[$m] ?? ($m === 'cash' ? $this->amountCash : ($m === 'debit' ? $this->amountDebit : ($m === 'credit' ? $this->amountCredit : 0))));
                if ($amount > 0) {
                    $dbMethod = \App\Models\PaymentMethod::where('code', $m)->first();
                    $methodNames[] = $dbMethod ? $dbMethod->name : ($m === 'cash' ? 'Tunai' : ($m === 'debit' ? 'Debit BCA' : ($m === 'credit' ? 'Piutang' : ucfirst($m))));
                }
            }
            $paymentMethodString = empty($methodNames) ? 'Tunai' : implode(' & ', $methodNames);

            $isEditing = !empty($this->editingSaleId);

            if ($isEditing) {
                $editingSale = \App\Models\Sale::findOrFail($this->editingSaleId);
                $sale = app(\App\Actions\Sales\UpdatePOSSale::class)->execute($editingSale, [
                    'customer_id' => $this->selectedCustomerId,
                    'sales_rep_id' => $this->selectedSalesRepId,
                    'invoice_date' => $this->invoiceDate,
                    'payment_method' => $paymentMethodString,
                    'payments' => $paymentsData,
                    'discount_amount' => $this->discountAmount + $pointDiscount,
                    'tax_amount' => $this->taxAmount,
                    'items' => $itemsData,
                    'sale_category' => $this->saleCategory,
                ]);
            } else {
                $sale = app(CreatePOSSale::class)->execute([
                    'branch_id' => $this->selectedBranchId,
                    'cash_session_id' => $activeSession->id,
                    'customer_id' => $this->selectedCustomerId,
                    'sales_rep_id' => $this->selectedSalesRepId,
                    'invoice_date' => $this->invoiceDate,
                    'payment_method' => $paymentMethodString,
                    'payments' => $paymentsData,
                    'discount_amount' => $this->discountAmount + $pointDiscount,
                    'tax_amount' => $this->taxAmount,
                    'items' => $itemsData,
                    'sale_category' => $this->saleCategory,
                ]);
            }

            // Deduct points from database
            if ($pointsUsed > 0) {
                $customer = Customer::find($this->selectedCustomerId);
                if ($customer) {
                    $customer->decrement('loyalty_points', $pointsUsed);
                }
            }

            // Reset POS State
            $this->editingSaleId = null;
            $this->cart = [];
            $this->clearCustomer();
            $this->enableTax = false;
            $this->taxPercent = 11;
            $this->selectedSalesRepId = Auth::id();
            $this->selectedSalesRepName = Auth::user() ? Auth::user()->name : '';
            $this->salesSearch = '';
            $defaultCategory = \App\Models\SaleCategory::first();
            $this->saleCategory = $defaultCategory ? $defaultCategory->name : 'Store';
            $this->invoiceDate = now()->format('Y-m-d');
            $this->showPaymentModal = false;
            $this->amountPaid = 0;
            $this->usePoints = false;
            
            // Reset Split Payment state
            $this->selectedPaymentMethods = ['cash'];
            $this->amountCash = 0;
            $this->amountDebit = 0;
            $this->amountCredit = 0;
            $this->debitRef = '';
            $this->paymentAmounts = [];
            $this->paymentRefs = [];

            // Dispatch print event for thermal receipt printing
            $this->lastSaleId = $sale->id;
            $this->dispatch('print-receipt', saleId: $sale->id);

            Notification::make()
                ->title($isEditing ? 'Transaksi Diperbarui' : 'Transaksi Sukses')
                ->body($isEditing ? "Faktur {$sale->invoice_number} berhasil diperbarui." : "Faktur {$sale->invoice_number} berhasil dicatat.")
                ->success()
                ->send();

            if ($isEditing) {
                return redirect()->to('/pos/transactions');
            }

        } catch (\Exception $e) {
            Notification::make()
                ->title('Gagal Checkout')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function cancelEdit()
    {
        $this->editingSaleId = null;
        $this->cart = [];
        $this->clearCustomer();
        $this->discountValue = 0;
        $this->enableTax = false;
        
        Notification::make()
            ->title('Edit Transaksi Dibatalkan')
            ->body('Keranjang belanja telah dikosongkan.')
            ->info()
            ->send();
            
        return redirect()->to('/pos');
    }

    public function clearCart()
    {
        $this->cart = [];
        $this->clearCustomer();
        $this->discountValue = 0;
        $this->discountType = 'fixed';
        $this->usePoints = false;
        $this->selectedSalesRepId = Auth::id();
        $this->selectedSalesRepName = Auth::user() ? Auth::user()->name : '';
        $this->salesSearch = '';
        $defaultCategory = \App\Models\SaleCategory::first();
        $this->saleCategory = $defaultCategory ? $defaultCategory->name : 'Store';

        Notification::make()
            ->title('Keranjang Direset')
            ->body('Seluruh item belanja dan data pelanggan telah dikosongkan.')
            ->info()
            ->send();
    }

    public function openProductSearch()
    {
        $this->showProductSearchModal = true;
    }

    public function closeProductSearch()
    {
        $this->showProductSearchModal = false;
    }

    public function openHeldTransactionsModal()
    {
        $this->showHeldModal = true;
    }

    public function holdTransaction()
    {
        if (empty($this->cart)) {
            Notification::make()
                ->title('Keranjang Kosong')
                ->body('Tidak ada transaksi untuk ditunda.')
                ->warning()
                ->send();
            return;
        }

        \App\Models\PosHeldTransaction::create([
            'id' => \Illuminate\Support\Str::uuid()->toString(),
            'branch_id' => $this->selectedBranchId,
            'user_id' => Auth::id(),
            'customer_id' => $this->selectedCustomerId,
            'customer_name' => $this->selectedCustomerName,
            'cart_data' => $this->cart,
            'discount_amount' => $this->discountAmount,
            'discount_type' => $this->discountType,
            'discount_value' => $this->discountValue,
            'use_points' => $this->usePoints,
            'is_loyalty' => $this->isLoyaltyMember,
            'pricing_tier_id' => $this->selectedPricingTierId,
        ]);

        // Clear current cart/state
        $this->cart = [];
        $this->clearCustomer();
        $this->discountValue = 0;
        $this->discountType = 'fixed';
        $this->usePoints = false;
        $this->showHeldModal = false;

        Notification::make()
            ->title('Transaksi Ditunda')
            ->body('Transaksi berhasil disimpan ke daftar tunda.')
            ->success()
            ->send();
    }

    public function restoreHeldTransaction($holdId)
    {
        $held = \App\Models\PosHeldTransaction::find($holdId);
        if (!$held) {
            Notification::make()
                ->title('Transaksi Tidak Ditemukan')
                ->body('Transaksi tunda tidak dapat ditemukan atau sudah dihapus.')
                ->danger()
                ->send();
            return;
        }

        $this->cart = $held->cart_data;
        $this->selectedCustomerId = $held->customer_id;
        $this->selectedCustomerName = $held->customer_name ?? 'Umum / Walk-in';
        $this->isLoyaltyMember = $held->is_loyalty;
        $this->selectedPricingTierId = $held->pricing_tier_id;
        $this->usePoints = $held->use_points;
        $this->discountValue = $held->discount_value;
        $this->discountType = $held->discount_type;

        $held->delete();
        $this->showHeldModal = false;

        Notification::make()
            ->title('Transaksi Dimuat')
            ->body('Transaksi tunda berhasil dimuat kembali ke keranjang.')
            ->success()
            ->send();
    }

    public function deleteHeldTransaction($holdId)
    {
        $held = \App\Models\PosHeldTransaction::find($holdId);
        if ($held) {
            $held->delete();
        }

        Notification::make()
            ->title('Transaksi Dihapus')
            ->body('Transaksi tunda berhasil dihapus.')
            ->info()
            ->send();
    }

    public function reprintLastReceipt()
    {
        if (!$this->lastSaleId) {
            Notification::make()
                ->title('Tidak Ada Transaksi')
                ->body('Belum ada transaksi yang diselesaikan dalam sesi ini.')
                ->warning()
                ->send();
            return;
        }

        $this->dispatch('print-receipt', saleId: $this->lastSaleId);

        Notification::make()
            ->title('Mencetak Ulang Struk')
            ->body('Permintaan cetak ulang struk berhasil dikirim.')
            ->success()
            ->send();
    }

    public function printBill()
    {
        $this->printDraft('bill');
    }

    public function printDraft($format = 'bill')
    {
        if (empty($this->cart)) {
            Notification::make()
                ->title('Keranjang Kosong')
                ->body('Tidak ada item untuk dicetak.')
                ->warning()
                ->send();
            return;
        }

        $data = base64_encode(json_encode([
            'branch_id' => $this->selectedBranchId,
            'customer_id' => $this->selectedCustomerId,
            'customer_name' => $this->selectedCustomerName,
            'cart' => $this->cart,
            'discount_amount' => $this->discountAmount,
            'tax_amount' => $this->taxAmount,
            'grand_total' => $this->grandTotal,
            'use_points' => $this->usePoints,
            'point_discount_amount' => $this->pointDiscountAmount,
        ]));

        $url = url('/pos/receipt-draft') . '?format=' . $format . '&data=' . urlencode($data);
        $this->dispatch('open-draft-bill', url: $url);

        $titles = [
            'bill' => 'Mencetak Bill Sementara',
            'large' => 'Mencetak Large Bill',
            'penawaran' => 'Mencetak Penawaran',
            'tagihan' => 'Mencetak Tagihan',
        ];

        Notification::make()
            ->title($titles[$format] ?? 'Mencetak Bill')
            ->body('Draft sedang dipersiapkan untuk dicetak.')
            ->info()
            ->send();
    }

    public function render()
    {
        return view('filament.pages.pos.pos')
            ->layout('layouts.pos');
    }
}
