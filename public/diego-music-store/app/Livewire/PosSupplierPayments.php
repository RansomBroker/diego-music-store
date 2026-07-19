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
        $this->branch_id = Auth::user()->branches()->first()?->id;

        // Choose default asset account
        $defaultAccount = Account::where('classification', 'asset')
            ->where('is_header', false)
            ->where('code', 'like', '1-1%')
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
        $this->branch_id = Auth::user()->branches()->first()?->id;
        
        $defaultAccount = Account::where('classification', 'asset')
            ->where('is_header', false)
            ->where('code', 'like', '1-1%')
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
                    'grand_total' => $pt->grand_total,
                    'amount_due' => $remaining,
                    'amount_paid' => 0,
                ];
            }
        }
    }

    // ── Item Selection Handling ─────────────────────────────────────────
    public function toggleItemSelection(int $index): void
    {
        if (isset($this->items[$index])) {
            $item = $this->items[$index];
            if ($item['is_selected']) {
                $this->items[$index]['amount_paid'] = $item['amount_due'];
            } else {
                $this->items[$index]['amount_paid'] = 0;
            }
        }
    }

    public function updatedItems($value, $key): void
    {
        if (str_contains($key, '.amount_paid')) {
            $parts = explode('.', $key);
            $index = $parts[0];
            $amountPaid = intval($value ?: 0);
            $amountDue = $this->items[$index]['amount_due'];

            if ($amountPaid > $amountDue) {
                $amountPaid = $amountDue;
                $this->items[$index]['amount_paid'] = $amountDue;
            }

            if ($amountPaid > 0) {
                $this->items[$index]['is_selected'] = true;
            } else {
                $this->items[$index]['is_selected'] = false;
            }
        }
    }

    // ── Save Payment ───────────────────────────────────────────────────
    public function save(string $status = 'draft'): void
    {
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
            'account_id.required' => 'Akun Kas / Bank wajib dipilih.',
        ]);

        // Ensure at least one item is selected and has positive amount paid
        $selectedItems = collect($this->items)->filter(function ($item) {
            return ($item['is_selected'] ?? false) && intval($item['amount_paid'] ?? 0) > 0;
        });

        if ($selectedItems->isEmpty()) {
            Notification::make()
                ->title('Gagal Menyimpan')
                ->body('Paling sedikit satu invoice harus dipilih dan diisi jumlah pembayaran yang valid.')
                ->danger()
                ->send();
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

            Notification::make()
                ->title($status === 'posted' ? 'Pelunasan Hutang Berhasil Diposting' : 'Draft Pelunasan Hutang Berhasil Disimpan')
                ->success()
                ->send();

            $this->showCreateModal = false;
            $this->resetPage();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Gagal Menyimpan')
                ->body($e->getMessage())
                ->danger()
                ->send();
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

            Notification::make()
                ->title('Pelunasan Hutang Berhasil Diposting')
                ->success()
                ->send();

            $this->showPostConfirmation = false;
            $this->paymentIdToPost = null;
        } catch (\Exception $e) {
            Notification::make()
                ->title('Gagal Posting')
                ->body($e->getMessage())
                ->danger()
                ->send();
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

            Notification::make()
                ->title('Pelunasan Hutang Berhasil Dihapus')
                ->success()
                ->send();

            $this->showDeleteConfirmation = false;
            $this->paymentIdToDelete = null;
        } catch (\Exception $e) {
            Notification::make()
                ->title('Gagal Menghapus')
                ->body($e->getMessage())
                ->danger()
                ->send();
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
        $userBranchId = Auth::user()->branches()->first()?->id;
        $branch = $userBranchId ? Branch::find($userBranchId) : null;
        $selectedLogoUrl = ($branch && !empty($branch->logo_path))
            ? Storage::url($branch->logo_path)
            : null;

        // Query asset accounts for the select box
        $accounts = Account::where('classification', 'asset')
            ->where('is_header', false)
            ->where('code', 'like', '1-1%')
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
