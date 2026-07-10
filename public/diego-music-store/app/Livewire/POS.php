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
    public $newCustomerPricingTierId = null;
    public $newCustomerIsLoyaltyMember = false;

    // Held transactions and reprint
    public $showHeldModal = false;
    public $lastSaleId = null;

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
    public $salesReps = [];
    public $selectedSalesRepId = null;
    public $invoiceDate = '';

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
        $this->salesReps = \App\Models\User::orderBy('name')->get();
        $this->selectedSalesRepId = Auth::id();
        $this->invoiceDate = now()->format('Y-m-d');

        $this->updateBranchDetails();
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
            'newCustomerPricingTierId' => 'nullable|exists:pricing_tiers,id',
        ], [
            'newCustomerName.required' => 'Nama pelanggan wajib diisi.',
            'newCustomerEmail.email' => 'Format email tidak valid.',
        ]);

        $customer = $createCustomerAction->execute([
            'name' => $this->newCustomerName,
            'phone' => $this->newCustomerPhone,
            'email' => $this->newCustomerEmail,
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
        
        // Check stock for physical products
        if ($variant->product->isPhysical()) {
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
        if ($variant->product->isPhysical() && $change > 0) {
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
        return \App\Models\Sale::generateInvoiceNumber();
    }

    public function getHeldTransactionsProperty()
    {
        return \App\Models\PosHeldTransaction::where('user_id', Auth::id())
            ->where('branch_id', $this->selectedBranchId)
            ->latest()
            ->get();
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
            }
        } else {
            $this->selectedPaymentMethods[] = $method;
        }

        $this->distributePaymentAmounts();
    }

    public function distributePaymentAmounts()
    {
        $total = $this->grandTotal;
        $count = count($this->selectedPaymentMethods);
        
        if ($count === 1) {
            $method = $this->selectedPaymentMethods[0];
            if ($method === 'cash') {
                $this->amountCash = $total;
                $this->amountDebit = 0;
                $this->amountCredit = 0;
            } elseif ($method === 'debit') {
                $this->amountCash = 0;
                $this->amountDebit = $total;
                $this->amountCredit = 0;
            } elseif ($method === 'credit') {
                $this->amountCash = 0;
                $this->amountDebit = 0;
                $this->amountCredit = $total;
            }
        } else {
            $assigned = 0;
            foreach ($this->selectedPaymentMethods as $method) {
                if ($method === 'cash') $assigned += intval($this->amountCash);
                if ($method === 'debit') $assigned += intval($this->amountDebit);
                if ($method === 'credit') $assigned += intval($this->amountCredit);
            }

            if ($assigned != $total) {
                if (in_array('cash', $this->selectedPaymentMethods)) {
                    $other = 0;
                    if (in_array('debit', $this->selectedPaymentMethods)) $other += intval($this->amountDebit);
                    if (in_array('credit', $this->selectedPaymentMethods)) $other += intval($this->amountCredit);
                    $this->amountCash = max(0, $total - $other);
                } else if (in_array('debit', $this->selectedPaymentMethods) && in_array('credit', $this->selectedPaymentMethods)) {
                    $this->amountDebit = max(0, $total - intval($this->amountCredit));
                }
            }
        }
    }

    public function checkout()
    {
        $totalPaid = 0;
        if (in_array('cash', $this->selectedPaymentMethods)) $totalPaid += intval($this->amountCash);
        if (in_array('debit', $this->selectedPaymentMethods)) $totalPaid += intval($this->amountDebit);
        if (in_array('credit', $this->selectedPaymentMethods)) $totalPaid += intval($this->amountCredit);

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
            if (in_array('cash', $this->selectedPaymentMethods)) {
                $change = max(0, $totalPaid - $this->grandTotal);
                $netCash = intval($this->amountCash) - $change;
                if ($netCash > 0) {
                    $paymentsData[] = [
                        'method' => 'cash',
                        'amount' => $netCash,
                        'ref' => null
                    ];
                }
            }
            if (in_array('debit', $this->selectedPaymentMethods) && $this->amountDebit > 0) {
                $paymentsData[] = [
                    'method' => 'debit',
                    'amount' => $this->amountDebit,
                    'ref' => $this->debitRef
                ];
            }
            if (in_array('credit', $this->selectedPaymentMethods) && $this->amountCredit > 0) {
                $paymentsData[] = [
                    'method' => 'credit',
                    'amount' => $this->amountCredit,
                    'ref' => null
                ];
            }

            // Compile human-readable payment method name
            $methodNames = [];
            foreach ($this->selectedPaymentMethods as $m) {
                if ($m === 'cash' && $this->amountCash > 0) $methodNames[] = 'Tunai';
                if ($m === 'debit' && $this->amountDebit > 0) $methodNames[] = 'Debit BCA';
                if ($m === 'credit' && $this->amountCredit > 0) $methodNames[] = 'Piutang';
            }
            $paymentMethodString = empty($methodNames) ? 'Tunai' : implode(' & ', $methodNames);

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
            ]);

            // Deduct points from database
            if ($pointsUsed > 0) {
                $customer = Customer::find($this->selectedCustomerId);
                if ($customer) {
                    $customer->decrement('loyalty_points', $pointsUsed);
                }
            }

            // Reset POS State
            $this->cart = [];
            $this->clearCustomer();
            $this->enableTax = false;
            $this->taxPercent = 11;
            $this->selectedSalesRepId = Auth::id();
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

            // Dispatch print event for thermal receipt printing
            $this->lastSaleId = $sale->id;
            $this->dispatch('print-receipt', saleId: $sale->id);

            Notification::make()
                ->title('Transaksi Sukses')
                ->body("Faktur {$sale->invoice_number} berhasil dicatat.")
                ->success()
                ->send();

        } catch (\Exception $e) {
            Notification::make()
                ->title('Gagal Checkout')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function clearCart()
    {
        $this->cart = [];
        $this->clearCustomer();
        $this->discountValue = 0;
        $this->discountType = 'fixed';
        $this->usePoints = false;

        Notification::make()
            ->title('Keranjang Direset')
            ->body('Seluruh item belanja dan data pelanggan telah dikosongkan.')
            ->info()
            ->send();
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

        $url = url('/pos/receipt-draft') . '?data=' . urlencode($data);
        $this->dispatch('open-draft-bill', url: $url);

        Notification::make()
            ->title('Mencetak Bill Sementara')
            ->body('Draft tagihan sedang dipersiapkan untuk dicetak.')
            ->info()
            ->send();
    }

    public function render()
    {
        return view('filament.pages.pos')
            ->layout('layouts.pos');
    }
}
