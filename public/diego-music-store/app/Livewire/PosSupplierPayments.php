<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\SupplierPayment;
use App\Models\Supplier;
use App\Models\Account;
use App\Models\Branch;
use App\Models\PurchaseTransaction;
use App\Actions\SupplierPayment\CreateSupplierPayment;
use App\Actions\SupplierPayment\ProcessSupplierPaymentComplete;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Filament\Notifications\Notification;

class PosSupplierPayments extends Component
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
    public ?int $supplier_id = null;
    public ?int $branch_id = null;
    public string $payment_date = '';
    public ?int $account_id = null;
    public string $payment_method = 'Bank Transfer';
    public string $payment_reference = '';
    public string $notes = '';
    public array $items = [];

    // ── Detail View State ──────────────────────────────────────────────
    public bool $showDetailModal = false;
    public ?int $detailPaymentId = null;
    public ?SupplierPayment $detailPayment = null;

    // ── Confirmation Modal State ────────────────────────────────────────
    public bool $showPostConfirmation = false;
    public ?int $paymentIdToPost = null;

    public bool $showDeleteConfirmation = false;
    public ?int $paymentIdToDelete = null;

    // ── Query string bindings for filters ───────────────────────────────
    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'sortField' => ['except' => 'payment_date'],
        'sortDirection' => ['except' => 'desc'],
    ];

    // ── Lifecycle & Helpers ──────────────────────────────────────────────
    public function mount(): void
    {
        $this->payment_date = now()->format('Y-m-d');
        $this->branch_id = Auth::user()?->branches()->first()?->id
            ?? Auth::user()?->branch_id
            ?? Branch::first()?->id;

        // Choose default asset account
        $defaultAccount = Account::where('classification', 'asset')
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
            'supplier_id', 'payment_reference', 'notes', 'items'
        ]);
        $this->payment_date = now()->format('Y-m-d');
        $this->branch_id = Auth::user()?->branches()->first()?->id
            ?? Auth::user()?->branch_id
            ?? Branch::first()?->id;

        $defaultAccount = Account::where('classification', 'asset')
            ->where('is_header', false)
            ->first();
        if ($defaultAccount) {
            $this->account_id = $defaultAccount->id;
        }

        $this->payment_method = 'Bank Transfer';
        $this->showCreateModal = true;
    }

    // ── Load Supplier Invoices ──────────────────────────────────────────
    public function updatedSupplierId($value): void
    {
        if (!$value) {
            $this->items = [];
            return;
        }

        $unpaidTransactions = PurchaseTransaction::query()
            ->where('supplier_id', $value)
            ->where('purchase_type', 'Kredit')
            ->where('status', 'posted')
            ->get();

        $this->items = [];
        foreach ($unpaidTransactions as $pt) {
            $remaining = $pt->getRemainingUnpaidAmount();
            if ($remaining > 0) {
                $this->items[] = [
                    'is_selected' => false,
                    'purchase_transaction_id' => $pt->id,
                    'transaction_no' => $pt->transaction_no,
                    'invoice_number' => $pt->invoice_number,
                    'transaction_date' => $pt->transaction_date->format('Y-m-d'),
                    'due_date' => $pt->due_date?->format('Y-m-d'),
                    'grand_total' => floatval($pt->grand_total),
                    'amount_due' => floatval($remaining),
                    'amount_paid' => 0,
                ];
            }
        }
    }

    // ── Item Selection & Amount Change Handling (Livewire 3 compatibility) ───
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
                $amountPaid = floatval($value ?: 0);
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
    public function save(string $status = 'draft'): void
    {
        if (!$this->branch_id) {
            $this->branch_id = Auth::user()?->branches()->first()?->id
                ?? Auth::user()?->branch_id
                ?? Branch::first()?->id;
        }

        $this->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'branch_id' => 'required|exists:branches,id',
            'payment_date' => 'required|date',
            'account_id' => 'required|exists:accounts,id',
            'payment_method' => 'required|in:Cash,Bank Transfer,Giro,Cheque',
            'payment_reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:500',
        ], [
            'supplier_id.required' => 'Supplier wajib dipilih.',
            'supplier_id.exists' => 'Supplier tidak valid.',
            'account_id.required' => 'Akun Kas / Bank wajib dipilih.',
            'account_id.exists' => 'Akun Kas / Bank tidak valid.',
            'branch_id.required' => 'Cabang wajib ditentukan.',
        ]);

        // Ensure at least one item is selected and has positive amount paid
        $selectedItems = collect($this->items)->filter(function ($item) {
            return ($item['is_selected'] ?? false) && floatval($item['amount_paid'] ?? 0) > 0;
        });

        if ($selectedItems->isEmpty()) {
            $msg = 'Paling sedikit satu invoice harus dipilih dan diisi jumlah pembayaran yang lebih dari 0.';
            Notification::make()->title('Gagal Menyimpan')->body($msg)->danger()->send();
            $this->dispatch('toast', type: 'danger', title: 'Gagal Menyimpan', body: $msg);
            return;
        }

        $data = [
            'payment_no' => SupplierPayment::generatePaymentNo(),
            'payment_date' => $this->payment_date,
            'supplier_id' => $this->supplier_id,
            'branch_id' => $this->branch_id,
            'account_id' => $this->account_id,
            'payment_method' => $this->payment_method,
            'payment_reference' => $this->payment_reference ?: null,
            'notes' => $this->notes ?: null,
            'status' => $status,
            'items' => $this->items,
        ];

        try {
            app(CreateSupplierPayment::class)->execute($data);

            $title = $status === 'posted' ? 'Pelunasan Hutang Berhasil Diposting' : 'Draft Pelunasan Hutang Berhasil Disimpan';
            Notification::make()->title($title)->success()->send();
            $this->dispatch('toast', type: 'success', title: $title);

            $this->showCreateModal = false;
            $this->resetPage();
        } catch (\Throwable $e) {
            Notification::make()->title('Gagal Menyimpan')->body($e->getMessage())->danger()->send();
            $this->dispatch('toast', type: 'danger', title: 'Gagal Menyimpan', body: $e->getMessage());
        }
    }

    // ── Open Detail Modal ───────────────────────────────────────────────
    public function showDetails(int $id): void
    {
        $this->detailPayment = SupplierPayment::with(['supplier', 'branch', 'account', 'creator', 'items.purchaseTransaction'])->findOrFail($id);
        $this->showDetailModal = true;
    }

    // ── Post Draft Payment ──────────────────────────────────────────────
    public function confirmPost(int $id): void
    {
        $this->paymentIdToPost = $id;
        $this->showPostConfirmation = true;
    }

    public function postPayment(): void
    {
        if (!$this->paymentIdToPost) {
            return;
        }

        try {
            $payment = SupplierPayment::findOrFail($this->paymentIdToPost);
            app(ProcessSupplierPaymentComplete::class)->execute($payment);

            Notification::make()->title('Pelunasan Hutang Berhasil Diposting')->success()->send();
            $this->dispatch('toast', type: 'success', title: 'Pelunasan Hutang Berhasil Diposting');

            $this->showPostConfirmation = false;
            $this->paymentIdToPost = null;
        } catch (\Throwable $e) {
            Notification::make()->title('Gagal Posting')->body($e->getMessage())->danger()->send();
            $this->dispatch('toast', type: 'danger', title: 'Gagal Posting', body: $e->getMessage());
        }
    }

    // ── Delete/Cancel Draft Payment ──────────────────────────────────────
    public function confirmDelete(int $id): void
    {
        $this->paymentIdToDelete = $id;
        $this->showDeleteConfirmation = true;
    }

    public function deletePayment(): void
    {
        if (!$this->paymentIdToDelete) {
            return;
        }

        try {
            $payment = SupplierPayment::findOrFail($this->paymentIdToDelete);
            if ($payment->status !== 'draft') {
                throw new \Exception('Hanya pelunasan berstatus draft yang dapat dihapus.');
            }

            $payment->items()->delete();
            $payment->delete();

            Notification::make()->title('Pelunasan Hutang Berhasil Dihapus')->success()->send();
            $this->dispatch('toast', type: 'success', title: 'Pelunasan Hutang Berhasil Dihapus');

            $this->showDeleteConfirmation = false;
            $this->paymentIdToDelete = null;
        } catch (\Throwable $e) {
            Notification::make()->title('Gagal Menghapus')->body($e->getMessage())->danger()->send();
            $this->dispatch('toast', type: 'danger', title: 'Gagal Menghapus', body: $e->getMessage());
        }
    }

    // ── Render ───────────────────────────────────────────────────────────
    public function render()
    {
        $payments = SupplierPayment::with(['supplier', 'account', 'creator'])
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
            ->when($this->search, function ($q) {
                $q->where('payment_no', 'like', "%{$this->search}%")
                  ->orWhereHas('supplier', fn ($sq) => $sq->where('name', 'like', "%{$this->search}%"));
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        // Current Branch Logo for Sidebar
        $userBranchId = Auth::user()?->branches()->first()?->id ?? Branch::first()?->id;
        $branch = $userBranchId ? Branch::find($userBranchId) : null;
        $selectedLogoUrl = ($branch && !empty($branch->logo_path))
            ? Storage::url($branch->logo_path)
            : null;

        // Query asset accounts for the select box
        $accounts = Account::where('classification', 'asset')
            ->where('is_header', false)
            ->orderBy('code')
            ->get();

        return view('livewire.pos-supplier-payments', [
            'payments' => $payments,
            'suppliers' => Supplier::orderBy('name')->get(),
            'accounts' => $accounts,
            'selectedLogoUrl' => $selectedLogoUrl,
        ])->layout('layouts.pos', ['title' => 'Pelunasan Hutang — POS']);
    }
}
