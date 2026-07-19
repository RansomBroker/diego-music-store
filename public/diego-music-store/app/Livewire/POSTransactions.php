<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Sale;
use App\Models\Branch;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;

class POSTransactions extends Component
{
    use WithPagination;

    // ── Search & Filter State ─────────────────────────────────────────────
    public string $search = '';
    public ?int $selectedBranchId = null;
    public string $selectedStatus = 'all';
    public string $selectedPaymentMethod = 'all';
    public string $fromDate = '';
    public string $toDate = '';

    // ── Table Sorting State ───────────────────────────────────────────────
    public string $sortField = 'created_at';
    public string $sortDirection = 'desc';
    public int $perPage = 15;

    // ── Details Modal State ───────────────────────────────────────────────
    public bool $showDetailsModal = false;
    public ?Sale $selectedSale = null;

    // ── Return Modal State ────────────────────────────────────────────────
    public bool $showReturnModal = false;
    public ?Sale $returnSale = null;
    public array $returnItems = [];
    public string $returnReason = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'selectedBranchId' => ['except' => null],
        'selectedStatus' => ['except' => 'all'],
        'selectedPaymentMethod' => ['except' => 'all'],
        'fromDate' => ['except' => ''],
        'toDate' => ['except' => ''],
    ];

    public function mount(): void
    {
        // Default date range is today
        $this->fromDate = now()->format('Y-m-d');
        $this->toDate = now()->format('Y-m-d');
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingSelectedBranchId(): void
    {
        $this->resetPage();
    }

    public function updatingSelectedStatus(): void
    {
        $this->resetPage();
    }

    public function updatingSelectedPaymentMethod(): void
    {
        $this->resetPage();
    }

    public function updatingFromDate(): void
    {
        $this->resetPage();
    }

    public function updatingToDate(): void
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

    public function resetFilters(): void
    {
        $this->search = '';
        $this->selectedBranchId = null;
        $this->selectedStatus = 'all';
        $this->selectedPaymentMethod = 'all';
        $this->fromDate = now()->format('Y-m-d');
        $this->toDate = now()->format('Y-m-d');
        $this->resetPage();
    }

    public function showDetails(int $saleId): void
    {
        $this->selectedSale = Sale::with(['items.variant.product', 'customer', 'salesRep', 'branch'])
            ->findOrFail($saleId);
        $this->showDetailsModal = true;
    }

    public function closeDetails(): void
    {
        $this->showDetailsModal = false;
        $this->selectedSale = null;
    }

    public function printReceipt(int $saleId): void
    {
        $this->dispatch('print-receipt', saleId: $saleId);
    }

    public function startReturn(int $saleId): void
    {
        $sale = Sale::with(['items.variant.product'])->findOrFail($saleId);

        if ($sale->status === 'cancelled') {
            Notification::make()
                ->title('Gagal')
                ->body('Tidak dapat melakukan retur pada transaksi yang sudah dibatalkan.')
                ->danger()
                ->send();
            return;
        }

        $this->returnSale = $sale;
        $this->returnReason = '';
        $this->returnItems = [];

        foreach ($sale->items as $item) {
            $maxReturn = $item->available_qty_for_return;
            
            // Skip items that have already been fully returned
            if ($maxReturn <= 0) {
                continue;
            }

            $this->returnItems[$item->id] = [
                'name' => $item->variant->product->name . ' (' . $item->variant->name . ')',
                'qty' => 0, // start with 0 return quantity
                'max' => $maxReturn,
                'price' => $item->unit_price,
                'refund_per_unit' => intval(round($item->total_price / $item->quantity)),
                'returned_qty' => $item->returned_qty,
                'original_qty' => $item->quantity,
            ];
        }

        if (empty($this->returnItems)) {
            Notification::make()
                ->title('Informasi')
                ->body('Semua barang dalam transaksi ini sudah diretur.')
                ->info()
                ->send();
            return;
        }

        $this->showReturnModal = true;
    }

    public function cancelReturn(): void
    {
        $this->showReturnModal = false;
        $this->returnSale = null;
        $this->returnItems = [];
        $this->returnReason = '';
    }

    public function processReturn(): void
    {
        // Check active cash session
        $activeSession = \App\Models\CashSession::where('user_id', Auth::id())
            ->where('status', 'open')
            ->first();

        if (!$activeSession) {
            Notification::make()
                ->title('Gagal')
                ->body('Sesi kasir aktif tidak ditemukan. Silakan buka kasir terlebih dahulu.')
                ->danger()
                ->send();
            return;
        }

        $returnPayload = [];
        foreach ($this->returnItems as $itemId => $item) {
            $qty = intval($item['qty'] ?? 0);
            if ($qty < 0) {
                Notification::make()
                    ->title('Validasi Gagal')
                    ->body('Jumlah retur tidak boleh negatif.')
                    ->danger()
                    ->send();
                return;
            }
            if ($qty > $item['max']) {
                Notification::make()
                    ->title('Validasi Gagal')
                    ->body("Jumlah retur untuk {$item['name']} melebihi sisa barang yang dapat diretur ({$item['max']}).")
                    ->danger()
                    ->send();
                return;
            }

            if ($qty > 0) {
                $returnPayload[] = [
                    'sale_item_id' => $itemId,
                    'quantity' => $qty,
                ];
            }
        }

        if (empty($returnPayload)) {
            Notification::make()
                ->title('Validasi Gagal')
                ->body('Pilih minimal satu barang dengan jumlah retur lebih besar dari 0.')
                ->danger()
                ->send();
            return;
        }

        try {
            $action = new \App\Actions\Sales\CreateSalesReturn();
            $action->execute([
                'sale_id' => $this->returnSale->id,
                'cash_session_id' => $activeSession->id,
                'reason' => $this->returnReason,
                'items' => $returnPayload,
            ]);

            Notification::make()
                ->title('Sukses')
                ->body('Retur penjualan berhasil diproses.')
                ->success()
                ->send();

            $this->cancelReturn();
            $this->resetPage();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function render()
    {
        // Load branches for filter dropdown
        $branches = Branch::where('is_active', true)->orderBy('name')->get();

        // Get logo of selected branch
        $userBranchId = Auth::user()->branches()->first()?->id;
        $activeBranch = $userBranchId ? Branch::find($userBranchId) : null;
        $selectedLogoUrl = ($activeBranch && !empty($activeBranch->logo_path))
            ? \Illuminate\Support\Facades\Storage::url($activeBranch->logo_path)
            : null;

        // Query sales
        $sales = Sale::with(['customer', 'salesRep', 'branch'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('invoice_number', 'like', "%{$this->search}%")
                      ->orWhereHas('customer', function ($c) {
                          $c->where('name', 'like', "%{$this->search}%");
                      })
                      ->orWhereHas('salesRep', function ($u) {
                          $u->where('name', 'like', "%{$this->search}%");
                      });
                });
            })
            ->when($this->selectedBranchId, function ($query) {
                $query->where('branch_id', $this->selectedBranchId);
            })
            ->when($this->selectedStatus !== 'all', function ($query) {
                $query->where('status', $this->selectedStatus);
            })
            ->when($this->selectedPaymentMethod !== 'all', function ($query) {
                $query->where('payment_method', 'like', "%{$this->selectedPaymentMethod}%");
            })
            ->when($this->fromDate, function ($query) {
                $query->whereDate('invoice_date', '>=', $this->fromDate);
            })
            ->when($this->toDate, function ($query) {
                $query->whereDate('invoice_date', '<=', $this->toDate);
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.pos-transactions', [
            'sales' => $sales,
            'branches' => $branches,
            'selectedLogoUrl' => $selectedLogoUrl,
        ])->layout('layouts.pos', ['title' => 'Daftar Transaksi — POS']);
    }
}
