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

    // Create Customer Modal State
    public $showCreateCustomerModal = false;
    public $newCustomerName = '';
    public $newCustomerPhone = '';
    public $newCustomerEmail = '';
    public $newCustomerPricingTierId = null;
    public $newCustomerIsLoyaltyMember = false;

    // Payment state
    public $paymentMethod = 'cash';
    public $discountAmount = 0;
    public $enableTax = true;
    public $taxPercent = 11;
    public $amountPaid = 0;
    public $showPaymentModal = false;

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
        if (strlen($this->customerSearch) < 2) {
            return [];
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
        $this->isLoyaltyMember = false; // Disable loyalty member features for now
        $this->customerSearch = ''; // Clear search
        $this->discountAmount = 0;

        // Automatically set pricing tier if registered for this customer
        $customer = Customer::find($id);
        if ($customer && $customer->pricing_tier_id) {
            $this->selectedPricingTierId = $customer->pricing_tier_id;
        } else {
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
        $this->discountAmount = 0;

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
        $this->newCustomerIsLoyaltyMember = false;
        
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
            'is_loyalty_member' => $this->newCustomerIsLoyaltyMember,
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

    public function getTaxAmountProperty()
    {
        if (!$this->enableTax) {
            return 0;
        }
        return intval(($this->subtotal - $this->discountAmount) * ($this->taxPercent / 100));
    }

    public function getGrandTotalProperty()
    {
        return $this->subtotal - $this->discountAmount + $this->taxAmount;
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
        
        $this->amountPaid = $this->grandTotal;
        $this->showPaymentModal = true;
    }

    public function closePayment()
    {
        $this->showPaymentModal = false;
    }

    public function setPaymentMethod($method)
    {
        $this->paymentMethod = $method;
    }

    public function checkout()
    {
        if ($this->amountPaid < $this->grandTotal && $this->paymentMethod !== 'credit') {
            Notification::make()
                ->title('Jumlah Bayar Kurang')
                ->body('Uang yang dibayarkan kurang dari total tagihan.')
                ->danger()
                ->send();
            return;
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

            $sale = app(CreatePOSSale::class)->execute([
                'branch_id' => $this->selectedBranchId,
                'cash_session_id' => $activeSession->id,
                'customer_id' => $this->selectedCustomerId,
                'sales_rep_id' => $this->selectedSalesRepId,
                'invoice_date' => $this->invoiceDate,
                'payment_method' => $this->paymentMethod,
                'discount_amount' => $this->discountAmount,
                'tax_amount' => $this->taxAmount,
                'items' => $itemsData,
            ]);

            // Reset POS State
            $this->cart = [];
            $this->clearCustomer();
            $this->enableTax = true;
            $this->taxPercent = 11;
            $this->selectedSalesRepId = Auth::id();
            $this->invoiceDate = now()->format('Y-m-d');
            $this->showPaymentModal = false;
            $this->amountPaid = 0;

            // Dispatch print event for thermal receipt printing
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

    public function render()
    {
        return view('filament.pages.pos')
            ->layout('layouts.pos');
    }
}
