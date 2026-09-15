<?php

namespace App\Livewire;

use App\Actions\CustomerDeposit\CancelCustomerDeposit;
use App\Actions\CustomerDeposit\CreateCustomerDeposit;
use App\Actions\CustomerDeposit\SettleCustomerDeposit;
use App\Actions\CustomerDeposit\UpdateCustomerDeposit;
use App\Models\Account;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\CustomerDeposit;
use App\Models\PaymentMethod;
use App\Models\Product;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithPagination;

class PosCustomerDeposits extends Component
{
    use WithPagination;

    // Filters
    public string $search = '';
    public string $statusFilter = '';
    public string $dateFrom = '';
    public string $dateTo = '';
    public string $sortField = 'created_at';
    public string $sortDirection = 'desc';
    public int $perPage = 15;

    // Form Modal State (Create / Edit)
    public bool $showFormModal = false;
    public bool $isEditMode = false;
    public ?int $editingDepositId = null;

    // Form fields
    public ?int $customer_id = null;
    public string $customer_search = '';
    public bool $showCustomerDropdown = false;
    public string $customer_name = '';
    public string $product_type = 'existing'; // 'existing' | 'manual'
    public ?int $product_id = null;
    public string $product_search = '';
    public bool $showProductDropdown = false;
    public string $product_name = '';
    public float|int|string $price = 0;
    public int|string $qty = 1;
    public float|int|string $total_amount = 0;
    public float|int|string $deposit_amount = 0;
    public float|int|string $remaining_amount = 0;
    public ?int $account_id = null;
    public string $payment_method = 'Tunai';
    public ?string $payment_reference = null;
    public string $deposit_date = '';
    public ?string $notes = null;

    // Pelunasan Modal State
    public bool $showSettleModal = false;
    public ?int $settlingDepositId = null;
    public ?CustomerDeposit $settlingDeposit = null;
    public ?int $settlement_account_id = null;
    public string $settlement_payment_method = 'Tunai';
    public ?string $settlement_reference = null;
    public ?string $settlement_notes = null;

    // Store Logo
    public ?string $selectedLogoUrl = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'page' => ['except' => 1],
    ];

    public function mount(): void
    {
        $this->deposit_date = now()->format('Y-m-d');
        $this->loadDefaultAccount();

        // Load branch logo
        $userBranchId = session('pos_active_branch_id')
            ?? Auth::user()?->branches()->first()?->id
            ?? Auth::user()?->branch_id
            ?? Branch::first()?->id;

        $branch = $userBranchId ? Branch::find($userBranchId) : null;
        $this->selectedLogoUrl = ($branch && !empty($branch->logo_path) && trim($branch->logo_path) !== '')
            ? Storage::url($branch->logo_path)
            : null;
    }

    private function loadDefaultAccount(): void
    {
        $defaultAccount = Account::where('classification', 'asset')
            ->where('is_header', false)
            ->where(function ($q) {
                $q->where('code', '1-1000')->orWhere('name', 'like', '%kas%');
            })
            ->first();

        if ($defaultAccount) {
            $this->account_id = $defaultAccount->id;
            $this->settlement_account_id = $defaultAccount->id;
        }
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatingDateFrom(): void
    {
        $this->resetPage();
    }

    public function updatingDateTo(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'statusFilter', 'dateFrom', 'dateTo']);
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'desc';
        }
    }

    public function updatedPrice(): void
    {
        $this->recalculate();
    }

    public function updatedQty(): void
    {
        $this->recalculate();
    }

    public function updatedDepositAmount($value = null): void
    {
        $this->recalculate();
        if ($this->total_amount > 0 && floatval($this->deposit_amount) > $this->total_amount) {
            $this->addError('deposit_amount', 'Nilai deposit tidak boleh melebihi total harga pesanan (Rp ' . number_format($this->total_amount, 0, ',', '.') . ').');
        } else {
            $this->resetErrorBag('deposit_amount');
        }
    }

    public function recalculate(): void
    {
        $cleanPrice = floatval(preg_replace('/[^\d.]/', '', (string) ($this->price ?? 0)));
        $cleanQty = max(1, intval($this->qty ?? 1));
        $this->price = $cleanPrice;
        $this->qty = $cleanQty;
        $this->total_amount = round($cleanPrice * $cleanQty, 2);

        $cleanDeposit = floatval(preg_replace('/[^\d.]/', '', (string) ($this->deposit_amount ?? 0)));
        $this->deposit_amount = $cleanDeposit;

        $this->remaining_amount = max(0, round($this->total_amount - $cleanDeposit, 2));
    }

    public function updatedCustomerSearch($value): void
    {
        $this->showCustomerDropdown = true;
        if ($this->customer_id && $value !== $this->customer_name) {
            $this->customer_id = null;
        }
    }

    public function clearSelectedCustomer(): void
    {
        $this->customer_id = null;
        $this->customer_name = '';
        $this->customer_search = '';
        $this->showCustomerDropdown = true;
    }

    public function selectCustomer(int $customerId): void
    {
        $customer = Customer::find($customerId);
        if ($customer) {
            $this->customer_id = $customer->id;
            $this->customer_name = $customer->name;
            $this->customer_search = $customer->name;
            $this->showCustomerDropdown = false;
        }
    }

    public function updatedProductType($value): void
    {
        $this->showProductDropdown = false;
        if ($value === 'manual') {
            $this->product_id = null;
            $this->product_search = '';
        }
    }

    public function updatedProductSearch($value): void
    {
        $this->showProductDropdown = true;
        if ($this->product_id && $value !== $this->product_name) {
            $this->product_id = null;
        }
    }

    public function clearSelectedProduct(): void
    {
        $this->product_id = null;
        $this->product_name = '';
        $this->product_search = '';
        $this->showProductDropdown = true;
        $this->price = 0;
        $this->recalculate();
    }

    public function selectProduct(int $productId): void
    {
        $product = Product::with('variants')->find($productId);
        if ($product) {
            $this->product_id = $product->id;
            $this->product_name = $product->name;
            $this->product_search = $product->name;
            $this->showProductDropdown = false;

            // Pick price from default variant if available
            $variant = $product->default_variant;
            if ($variant && $variant->price > 0) {
                $this->price = floatval($variant->price);
            }
            $this->recalculate();
        }
    }

    public function updatedPaymentMethod($value): void
    {
        if (!$value) return;
        $pm = PaymentMethod::where('name', $value)->orWhere('code', $value)->first();
        if ($pm && $pm->getEffectiveAccountId()) {
            $this->account_id = $pm->getEffectiveAccountId();
        }
    }

    public function updatedSettlementPaymentMethod($value): void
    {
        if (!$value) return;
        $pm = PaymentMethod::where('name', $value)->orWhere('code', $value)->first();
        if ($pm && $pm->getEffectiveAccountId()) {
            $this->settlement_account_id = $pm->getEffectiveAccountId();
        }
    }

    // ── Form Modal Handlers ───────────────────────────────────────────────────
    public function openCreateModal(): void
    {
        $this->reset([
            'editingDepositId', 'isEditMode', 'customer_id', 'customer_search', 'customer_name', 'showCustomerDropdown',
            'product_type', 'product_id', 'product_search', 'product_name',
            'price', 'qty', 'total_amount', 'deposit_amount', 'remaining_amount',
            'payment_reference', 'notes', 'showProductDropdown'
        ]);

        $this->product_type = 'existing';
        $this->qty = 1;
        $this->deposit_date = now()->format('Y-m-d');
        $this->payment_method = 'Tunai';
        $this->loadDefaultAccount();

        $this->showFormModal = true;
    }

    public function openEditModal(int $id): void
    {
        $deposit = CustomerDeposit::findOrFail($id);

        if (!$deposit->isPending()) {
            Notification::make()->title('Perhatian')->body('Hanya deposit berstatus pending yang dapat diubah.')->warning()->send();
            return;
        }

        $this->isEditMode = true;
        $this->editingDepositId = $deposit->id;
        $this->customer_id = $deposit->customer_id;
        $this->customer_name = $deposit->customer?->name ?? '';
        $this->customer_search = $deposit->customer?->name ?? '';
        $this->showCustomerDropdown = false;
        $this->product_type = $deposit->product_type;
        $this->product_id = $deposit->product_id;
        $this->product_name = $deposit->product_name;
        $this->product_search = $deposit->product_name;
        $this->price = floatval($deposit->price);
        $this->qty = intval($deposit->qty);
        $this->total_amount = floatval($deposit->total_amount);
        $this->deposit_amount = floatval($deposit->deposit_amount);
        $this->remaining_amount = floatval($deposit->remaining_amount);
        $this->account_id = $deposit->account_id;
        $this->payment_method = $deposit->payment_method;
        $this->payment_reference = $deposit->payment_reference;
        $this->deposit_date = $deposit->deposit_date->format('Y-m-d');
        $this->notes = $deposit->notes;

        $this->showFormModal = true;
    }

    public function saveDeposit(
        CreateCustomerDeposit $createAction,
        UpdateCustomerDeposit $updateAction
    ): void {
        $this->validate([
            'customer_id' => 'required|exists:customers,id',
            'product_name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'qty' => 'required|integer|min:1',
            'deposit_amount' => 'required|numeric|min:0',
            'deposit_date' => 'required|date',
        ], [
            'customer_id.required' => 'Pelanggan wajib dipilih.',
            'product_name.required' => 'Nama produk/barang pesanan wajib diisi.',
            'price.required' => 'Harga satuan wajib diisi.',
            'qty.min' => 'Jumlah minimal 1.',
        ]);

        $this->recalculate();

        if ($this->total_amount > 0 && floatval($this->deposit_amount) > $this->total_amount) {
            $this->addError('deposit_amount', 'Nilai deposit tidak boleh melebihi total harga pesanan (Rp ' . number_format($this->total_amount, 0, ',', '.') . ').');
            return;
        }

        try {
            // Automatically assign Penitipan Dana account (2-1200)
            $penitipanDanaAcc = Account::where('code', '2-1200')->first();
            $accountId = $penitipanDanaAcc?->id ?? $this->account_id;

            $data = [
                'customer_id' => $this->customer_id,
                'deposit_date' => $this->deposit_date,
                'product_type' => $this->product_type,
                'product_id' => $this->product_type === 'existing' ? $this->product_id : null,
                'product_name' => $this->product_name,
                'price' => $this->price,
                'qty' => $this->qty,
                'deposit_amount' => $this->deposit_amount,
                'account_id' => $accountId,
                'payment_method' => $this->payment_method ?: 'Tunai',
                'payment_reference' => $this->payment_reference,
                'notes' => $this->notes,
            ];

            if ($this->isEditMode && $this->editingDepositId) {
                $deposit = CustomerDeposit::findOrFail($this->editingDepositId);
                $updateAction->execute($deposit, $data, Auth::user());
                $msg = "Deposit {$deposit->deposit_number} berhasil diperbarui.";
            } else {
                $deposit = $createAction->execute($data, Auth::user());
                $msg = "Deposit {$deposit->deposit_number} berhasil dicatat & masuk ke Penitipan Dana.";
            }

            Notification::make()->title('Berhasil')->body($msg)->success()->send();
            $this->dispatch('toast', type: 'success', title: 'Berhasil', body: $msg);

            $this->showFormModal = false;
        } catch (\Throwable $e) {
            Notification::make()->title('Gagal Menyimpan')->body($e->getMessage())->danger()->send();
            $this->dispatch('toast', type: 'danger', title: 'Gagal', body: $e->getMessage());
        }
    }

    // ── Pelunasan Handlers ────────────────────────────────────────────────────
    public function openSettleModal(int $id): void
    {
        $deposit = CustomerDeposit::with('customer', 'product')->findOrFail($id);

        if (!$deposit->isPending()) {
            Notification::make()->title('Perhatian')->body('Hanya deposit berstatus pending yang dapat dilunasi.')->warning()->send();
            return;
        }

        $this->settlingDepositId = $deposit->id;
        $this->settlingDeposit = $deposit;
        $this->settlement_payment_method = 'Tunai';
        $this->settlement_reference = null;
        $this->settlement_notes = null;
        $this->loadDefaultAccount();

        $this->showSettleModal = true;
    }

    public function processSettlement(SettleCustomerDeposit $settleAction): void
    {
        if (!$this->settlingDepositId) return;

        try {
            $deposit = CustomerDeposit::findOrFail($this->settlingDepositId);

            // Automatically resolve default Kas account if not explicitly set
            $defaultKas = Account::where('classification', 'asset')
                ->where('is_header', false)
                ->where(function ($q) {
                    $q->where('code', '1-1000')->orWhere('name', 'like', '%kas%');
                })
                ->first();
            $settlementAccountId = $this->settlement_account_id ?? $defaultKas?->id;

            $settleAction->execute($deposit, [
                'settlement_account_id' => $settlementAccountId,
                'settlement_payment_method' => $this->settlement_payment_method ?: 'Tunai',
                'settlement_reference' => $this->settlement_reference,
                'settlement_notes' => $this->settlement_notes,
            ], Auth::user());

            $msg = "Pelunasan deposit {$deposit->deposit_number} berhasil! Dana dari Penitipan Dana telah dialihkan ke Pendapatan Penjualan.";
            Notification::make()->title('Pelunasan Berhasil')->body($msg)->success()->send();
            $this->dispatch('toast', type: 'success', title: 'Pelunasan Berhasil', body: $msg);

            $this->showSettleModal = false;
            $this->settlingDeposit = null;
            $this->settlingDepositId = null;
        } catch (\Throwable $e) {
            Notification::make()->title('Gagal Pelunasan')->body($e->getMessage())->danger()->send();
            $this->dispatch('toast', type: 'danger', title: 'Gagal Pelunasan', body: $e->getMessage());
        }
    }

    // ── Cancel / Delete Handler ───────────────────────────────────────────────
    public function cancelDeposit(int $id, CancelCustomerDeposit $cancelAction): void
    {
        try {
            $deposit = CustomerDeposit::findOrFail($id);
            $cancelAction->execute($deposit, 'Dibatalkan oleh kasir POS', Auth::user());

            $msg = "Deposit {$deposit->deposit_number} berhasil dibatalkan.";
            Notification::make()->title('Deposit Dibatalkan')->body($msg)->info()->send();
            $this->dispatch('toast', type: 'info', title: 'Dibatalkan', body: $msg);
        } catch (\Throwable $e) {
            Notification::make()->title('Gagal Membatalkan')->body($e->getMessage())->danger()->send();
            $this->dispatch('toast', type: 'danger', title: 'Gagal Membatalkan', body: $e->getMessage());
        }
    }

    public function render()
    {
        $query = CustomerDeposit::with(['customer', 'product', 'account', 'user'])
            ->when($this->search, function ($q) {
                $term = '%' . trim($this->search) . '%';
                $q->where(function ($sub) use ($term) {
                    $sub->where('deposit_number', 'like', $term)
                        ->orWhere('product_name', 'like', $term)
                        ->orWhereHas('customer', fn($c) => $c->where('name', 'like', $term)->orWhere('phone', 'like', $term));
                });
            })
            ->when($this->statusFilter, function ($q) {
                $q->where('status', $this->statusFilter);
            })
            ->when($this->dateFrom, fn($q) => $q->whereDate('deposit_date', '>=', $this->dateFrom))
            ->when($this->dateTo, fn($q) => $q->whereDate('deposit_date', '<=', $this->dateTo))
            ->orderBy($this->sortField, $this->sortDirection);

        $deposits = $this->perPage > 0 ? $query->paginate($this->perPage) : $query->paginate(1000);

        // Reference data
        $paymentMethods = PaymentMethod::where('is_active', true)->orderBy('name')->get();
        $cashAccounts = Account::where('classification', 'asset')
            ->where('is_header', false)
            ->where(function ($q) {
                $q->where('code', 'like', '1-1%')
                  ->orWhere('name', 'like', '%kas%')
                  ->orWhere('name', 'like', '%bank%');
            })
            ->orderBy('code')
            ->get();

        // Catalog products for search dropdown: filtered if search term provided, otherwise initial 15 products
        $catalogProducts = [];
        if ($this->product_type === 'existing') {
            $productQuery = Product::with('variants')->where('is_active', true);
            if (strlen(trim($this->product_search)) >= 1) {
                $term = '%' . trim($this->product_search) . '%';
                $productQuery->where(function ($q) use ($term) {
                    $q->where('name', 'like', $term)
                      ->orWhereHas('variants', fn($v) => $v->where('sku', 'like', $term)->orWhere('barcode', 'like', $term));
                });
            }
            $catalogProducts = $productQuery->orderBy('name')->limit(15)->get();
        }

        // Customer search dropdown: filtered if search term provided, otherwise initial 15 customers
        $customerQuery = Customer::query();
        if (strlen(trim($this->customer_search)) >= 1) {
            $term = '%' . trim($this->customer_search) . '%';
            $customerQuery->where(function ($q) use ($term) {
                $q->where('name', 'like', $term)
                  ->orWhere('phone', 'like', $term);
            });
        }
        $searchedCustomers = $customerQuery->orderBy('name')->limit(15)->get();

        return view('livewire.pos-customer-deposits', [
            'deposits' => $deposits,
            'customers' => $searchedCustomers,
            'searchedCustomers' => $searchedCustomers,
            'paymentMethods' => $paymentMethods,
            'cashAccounts' => $cashAccounts,
            'catalogProducts' => $catalogProducts,
        ])->layout('layouts.pos', ['title' => 'Deposit Pelanggan — POS']);
    }
}
