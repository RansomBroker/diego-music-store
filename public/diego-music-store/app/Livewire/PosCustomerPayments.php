<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Account;
use App\Models\Branch;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Filament\Notifications\Notification;

class PosCustomerPayments extends Component
{
    use WithPagination;

    // ── Table & Filter State ───────────────────────────────────────────
    public string $search = '';
    public string $statusFilter = ''; // '', 'draft', 'posted'
    public string $sortField = 'payment_date';
    public string $sortDirection = 'desc';
    public int $perPage = 15;

    // ── Create Form State ──────────────────────────────────────────────
    public bool $showCreateModal = false;
    public ?int $customer_id = null;
    public ?int $branch_id = null;
    public string $payment_date = '';
    public ?int $account_id = null;
    public string $payment_method = 'Tunai';
    public string $payment_reference = '';
    public string $notes = '';
    public array $items = [];

    // ── Detail View State ──────────────────────────────────────────────
    public bool $showDetailModal = false;
    public ?\App\Models\Sale $selectedSale = null;
    public array $settlementHistory = [];

    // ── Query string bindings for filters ───────────────────────────────
    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'sortField' => ['except' => 'payment_date'],
        'sortDirection' => ['except' => 'desc'],
    ];

    // ── Lifecycle & Helpers ──────────────────────────────────────────────
    /**
     * Enforce this restriction on initial page loads and every Livewire request.
     */
    public function boot(): void
    {
        abort_if(Auth::user()?->hasRole(['sales', 'Sales']), 403);
    }

    public function mount(): void
    {
        abort_if(Auth::user()?->hasRole(['sales', 'Sales']), 403);

        $this->payment_date = now()->format('Y-m-d');
        $this->branch_id = \App\Helpers\BranchHelper::getActiveBranchId();

        $defaultAccount = Account::where('classification', 'asset')
            ->when($this->branch_id, fn ($q) => $q->where('branch_id', $this->branch_id))
            ->where('is_header', false)
            ->first();
        if ($defaultAccount) {
            $this->account_id = $defaultAccount->id;
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

    public function updatingPerPage(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage($value): void
    {
        $this->perPage = (int) $value;
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

    // ── Open Create Form Modal ───────────────────────────────────────────
    public function openCreate(): void
    {
        $this->reset([
            'customer_id', 'payment_reference', 'notes', 'items'
        ]);
        $this->payment_date = now()->format('Y-m-d');
        $this->branch_id = \App\Helpers\BranchHelper::getActiveBranchId();

        $paymentMethodsData = \App\Models\PaymentMethod::where('is_active', true)->orderBy('name')->get();
        if ($paymentMethodsData->isNotEmpty()) {
            $firstPm = $paymentMethodsData->first();
            $this->payment_method = $firstPm->name;
            $effectiveAccount = $firstPm->getEffectiveAccountId();
            if ($effectiveAccount) {
                $this->account_id = $effectiveAccount;
            }
        } else {
            $this->payment_method = 'Tunai';
        }

        $this->showCreateModal = true;
    }

    public function updatedPaymentMethod($value): void
    {
        if (!$value) {
            return;
        }

        $pm = \App\Models\PaymentMethod::where('name', $value)
            ->orWhere('code', $value)
            ->first();

        if ($pm) {
            $effectiveAccountId = $pm->getEffectiveAccountId();
            if ($effectiveAccountId) {
                $this->account_id = $effectiveAccountId;
            }
        }
    }

    // ── Load Customer Unpaid Sales Invoices ────────────────────────────
    public function updatedCustomerId($value): void
    {
        if (!$value) {
            $this->items = [];
            return;
        }

        $activeBranchId = \App\Helpers\BranchHelper::getActiveBranchId();
        if (!$activeBranchId) {
            $this->items = [];
            return;
        }

        $unpaidSales = \App\Models\Sale::query()
            ->where('branch_id', $activeBranchId)
            ->where('customer_id', $value)
            ->where(function ($q) {
                $q->where('payment_method', 'like', '%piutang%')
                  
                  ->orWhere('status', '!=', 'completed');
            })
            ->get();

        $this->items = [];
        foreach ($unpaidSales as $sale) {
            $due = $sale->getPiutangAmount();
            if ($due <= 0) {
                continue;
            }
            $this->items[] = [
                'is_selected' => false,
                'sale_id' => $sale->id,
                'invoice_number' => $sale->invoice_number,
                'transaction_date' => $sale->invoice_date->format('Y-m-d'),
                'grand_total' => floatval($sale->grand_total),
                'amount_due' => $due,
                'amount_paid' => 0,
            ];
        }
    }

    public function toggleItemSelection(int $index): void
    {
        if (isset($this->items[$index])) {
            if (!empty($this->items[$index]['is_selected'])) {
                if (floatval($this->items[$index]['amount_paid'] ?? 0) <= 0) {
                    $this->items[$index]['amount_paid'] = $this->items[$index]['amount_due'];
                }
            } else {
                $this->items[$index]['amount_paid'] = 0;
            }
        }
    }

    public function updated($property, $value): void
    {
        if (str_contains($property, 'items.') && str_contains($property, '.amount_paid')) {
            preg_match('/items\.(\d+)\.amount_paid/', $property, $matches);
            if (isset($matches[1])) {
                $index = (int) $matches[1];
                $amountPaid = \App\Helpers\FormatHelper::parseRupiah($value);
                $this->items[$index]['amount_paid'] = $amountPaid;
                $amountDue = floatval($this->items[$index]['amount_due'] ?? 0);

                if ($amountPaid > $amountDue) {
                    $amountPaid = $amountDue;
                    $this->items[$index]['amount_paid'] = $amountDue;
                }

                $this->items[$index]['is_selected'] = ($amountPaid > 0);
            }
        }
    }

    // ── Save Payment ───────────────────────────────────────────────────
    public function save(string $status = 'posted'): void
    {
        $activeBranchId = \App\Helpers\BranchHelper::getActiveBranchId();
        abort_unless($activeBranchId, 403);
        // Never trust a public Livewire branch_id supplied by the browser.
        $this->branch_id = $activeBranchId;

        if ($this->payment_method) {
            $pm = \App\Models\PaymentMethod::where('name', $this->payment_method)
                ->orWhere('code', $this->payment_method)
                ->first();
            if ($pm) {
                $effectiveAcc = $pm->getEffectiveAccountId();
                if ($effectiveAcc) {
                    $this->account_id = $effectiveAcc;
                }
            }
        }

        if (!$this->account_id) {
            $defaultAccount = Account::where('classification', 'asset')
                ->where('is_header', false)
                ->first();
            if ($defaultAccount) {
                $this->account_id = $defaultAccount->id;
            }
        }

        $this->validate([
            'customer_id' => 'required|exists:customers,id',
            'branch_id' => 'required|exists:branches,id',
            'payment_date' => 'required|date',
            'account_id' => 'required|exists:accounts,id',
            'payment_method' => 'required|string',
            'payment_reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:500',
        ], [
            'customer_id.required' => 'Pelanggan wajib dipilih.',
            'customer_id.exists' => 'Pelanggan tidak valid.',
            'account_id.required' => 'Akun Kas / Bank tidak ditemukan untuk metode pembayaran ini.',
            'account_id.exists' => 'Akun Kas / Bank tidak valid.',
            'branch_id.required' => 'Cabang wajib ditentukan.',
        ]);

        $selectedItems = collect($this->items)->filter(function ($item) {
            return ($item['is_selected'] ?? false) && floatval($item['amount_paid'] ?? 0) > 0;
        })->unique(fn ($item) => $item['sale_id'] ?? null);

        if ($selectedItems->isEmpty()) {
            $msg = 'Paling sedikit satu transaksi piutang harus dipilih dan diisi jumlah pembayaran yang lebih dari 0.';
            Notification::make()->title('Gagal Menyimpan')->body($msg)->danger()->send();
            $this->dispatch('toast', type: 'danger', title: 'Gagal Menyimpan', body: $msg);
            return;
        }

        try {
            \Illuminate\Support\Facades\DB::transaction(function () use ($selectedItems, $activeBranchId) {
                $settleAction = app(\App\Actions\Sales\SettleCustomerReceivable::class);
                foreach ($selectedItems as $item) {
                    $sale = \App\Models\Sale::query()
                        ->where('branch_id', $activeBranchId)
                        ->where('customer_id', $this->customer_id)
                        ->find($item['sale_id'] ?? null);
                    if (!$sale) {
                        throw new \Illuminate\Auth\Access\AuthorizationException('Transaksi tidak berada pada cabang atau pelanggan yang dipilih.');
                    }

                    $paidAmount = floatval($item['amount_paid'] ?? 0);
                    if ($paidAmount <= 0) {
                        continue;
                    }

                    $settleAction->execute($sale, [
                        'amount' => $paidAmount,
                        'account_id' => $this->account_id,
                        'payment_method' => $this->payment_method,
                        'payment_date' => $this->payment_date,
                        'payment_reference' => $this->payment_reference,
                        'user_id' => \Illuminate\Support\Facades\Auth::id(),
                    ]);
                }
            });

            $title = 'Pelunasan Piutang Berhasil Diposting';
            Notification::make()->title($title)->success()->send();
            $this->dispatch('toast', type: 'success', title: $title);

            $this->showCreateModal = false;
            $this->resetPage();
        } catch (\Throwable $e) {
            Notification::make()->title('Gagal Menyimpan')->body($e->getMessage())->danger()->send();
            $this->dispatch('toast', type: 'danger', title: 'Gagal Menyimpan', body: $e->getMessage());
        }
    }

    // ── Show Detail & Settlement History Modal ──────────────────────────
    public function showDetails(int $id): void
    {
        $sale = \App\Models\Sale::with(['customer', 'branch', 'salesRep', 'items.variant.product'])
            ->where('branch_id', \App\Helpers\BranchHelper::getActiveBranchId())
            ->find($id);
        if (!$sale) {
            return;
        }

        $this->selectedSale = $sale;

        // Fetch all journal entries for this Sale (settlement transactions)
        $journalEntries = \App\Models\JournalEntry::with(['items.account', 'user'])
            ->where('reference_type', 'Sales')
            ->where('reference_id', $sale->id)
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        $piutangAccount = \App\Helpers\AccountHelper::findByCode('111301001') ?? \App\Models\Account::where('code', '1-1200')->first();

        $history = [];
        foreach ($journalEntries as $je) {
            $piutangCredit = $je->items->first(fn($item) => $piutangAccount && $item->account_id === $piutangAccount->id && floatval($item->credit) > 0);
            $kasDebit = $je->items->first(fn($item) => floatval($item->debit) > 0);

            if ($piutangCredit || $kasDebit) {
                $amount = $piutangCredit ? floatval($piutangCredit->credit) : floatval($kasDebit?->debit ?? 0);
                $accountName = $kasDebit?->account?->name ?? 'Kas / Bank';

                $history[] = [
                    'id' => $je->id,
                    'entry_number' => $je->entry_no ?? ('JE-' . str_pad($je->id, 5, '0', STR_PAD_LEFT)),
                    'date' => $je->date ? \Carbon\Carbon::parse($je->date)->format('d/m/Y') : '-',
                    'account_name' => $accountName,
                    'description' => $je->description ?? 'Pelunasan Piutang',
                    'user_name' => $je->user?->name ?? $je->creator?->name ?? 'System/Kasir',
                    'amount' => $amount,
                ];
            }
        }

        $this->settlementHistory = $history;
        $this->showDetailModal = true;
    }

    public function closeDetails(): void
    {
        $this->showDetailModal = false;
        $this->selectedSale = null;
        $this->settlementHistory = [];
    }

    // ── Render ───────────────────────────────────────────────────────────
    public function render()
    {
        $activeBranchId = \App\Helpers\BranchHelper::getActiveBranchId();
        $salesPiutang = \App\Models\Sale::with(['customer', 'branch'])
            ->where('branch_id', $activeBranchId)
            ->when($this->statusFilter, function ($q) {
                if ($this->statusFilter === 'completed') {
                    $q->where('status', 'completed');
                } elseif ($this->statusFilter === 'unpaid' || $this->statusFilter === 'draft') {
                    $q->where('status', '!=', 'completed');
                }
            })
            ->when($this->search, function ($q) {
                $q->where(function ($searchQuery) {
                    $searchQuery->where('invoice_number', 'like', "%{$this->search}%")
                        ->orWhereHas('customer', fn ($customerQuery) => $customerQuery->where('name', 'like', "%{$this->search}%"));
                });
            })
            ->latest()
            ->paginate($this->perPage > 0 ? $this->perPage : 1000);

        $branch = $activeBranchId ? Branch::find($activeBranchId) : null;
        $selectedLogoUrl = ($branch && !empty($branch->logo_path))
            ? Storage::url($branch->logo_path)
            : null;

        $accounts = Account::where('classification', 'asset')
            ->when($activeBranchId, fn ($q) => $q->where('branch_id', $activeBranchId))
            ->where('is_header', false)
            ->orderBy('code')
            ->get();

        $paymentMethodsData = \App\Models\PaymentMethod::where('is_active', true)->orderBy('name')->get();

        return view('livewire.pos-customer-payments', [
            'payments' => $salesPiutang,
            'customers' => \App\Models\Customer::query()
                ->whereIn('id', \App\Models\Sale::query()->where('branch_id', $activeBranchId)->whereNotNull('customer_id')->distinct()->pluck('customer_id'))
                ->orderBy('name')->get(),
            'accounts' => $accounts,
            'paymentMethodsData' => $paymentMethodsData,
            'selectedLogoUrl' => $selectedLogoUrl,
        ])->layout('layouts.pos', ['title' => 'Pelunasan Piutang — POS']);
    }
}
