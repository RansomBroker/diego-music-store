<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Branch;
use App\Models\CashSession;
use App\Models\CashTransaction;
use App\Models\Account;
use App\Actions\CashManagement\CreateCashTransaction;
use App\Helpers\PermissionHelper;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;
use Livewire\WithPagination;

class POSDailyCash extends Component
{
    use WithPagination;

    public $activeSession = null;
    public $branches = [];
    public $selectedBranchId = null;
    public int $perPage = 10;

    // Filter State
    public string $search = '';
    public string $fromDate = '';
    public string $toDate = '';
    public string $selectedType = 'all';
    public string $selectedAccountId = '';
    public string $selectedSessionId = 'all';

    public function updatingPerPage(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage($value): void
    {
        $this->perPage = (int) $value;
        $this->resetPage();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingSelectedType(): void
    {
        $this->resetPage();
    }

    public function updatingSelectedAccountId(): void
    {
        $this->resetPage();
    }

    public function updatingSelectedSessionId(): void
    {
        $this->resetPage();
    }

    public function updatedFromDate(): void
    {
        if ($this->selectedSessionId !== 'all') {
            $this->selectedSessionId = 'all';
        }
        $this->resetPage();
    }

    public function updatedToDate(): void
    {
        if ($this->selectedSessionId !== 'all') {
            $this->selectedSessionId = 'all';
        }
        $this->resetPage();
    }

    public function updatedSelectedBranchId(): void
    {
        $this->checkActiveSession();
        $this->selectedSessionId = $this->activeSession ? 'active' : 'all';
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->fromDate = now()->format('Y-m-d');
        $this->toDate = now()->format('Y-m-d');
        $this->selectedType = 'all';
        $this->selectedAccountId = '';
        $this->checkActiveSession();
        $this->selectedSessionId = $this->activeSession ? 'active' : 'all';
        $this->resetPage();
    }

    // Summary fields
    public $openingCash = 0;
    public $cashSales = 0;
    public $cashIn = 0;
    public $cashOut = 0;
    public $expectedCash = 0;

    // Modals visibility
    public $showInModal = false;
    public $showOutModal = false;
    public $showTransferModal = false;

    // Inflow Form State
    public $inAmount = 0;
    public $inSourceAccountId = '';
    public $inNotes = '';

    // Outflow Form State
    public $outAmount = 0;
    public $outDestinationAccountId = '';
    public $outNotes = '';

    // Transfer / Setor Kas Form State
    public $transferAmount = 0;
    public $transferSourceAccountId = '';
    public $transferDestinationAccountId = '';
    public $transferNotes = '';

    // Inflow sources option
    public $inflowSources = [];
    // Outflow destinations option
    public $outflowDestinations = [];
    // Transfer / Setor Kas accounts (Kas & Bank)
    public $transferAccounts = [];

    /**
     * Enforce this restriction on initial page loads and every Livewire request.
     */
    public function boot(): void
    {
        abort_unless(PermissionHelper::canAccessFrontofficeFeature(Auth::user(), 'fo.daily_cash.view'), 403);
    }

    private function resolveAuthorizedBranchId(): int
    {
        $allowedBranchIds = \App\Helpers\BranchHelper::getAllowedBranchesQuery()
            ->pluck('branches.id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $requestedBranchId = (int) ($this->selectedBranchId ?? 0);
        $branchId = $requestedBranchId && in_array($requestedBranchId, $allowedBranchIds, true)
            ? $requestedBranchId
            : \App\Helpers\BranchHelper::getActiveBranchId();

        abort_unless(in_array($branchId, $allowedBranchIds, true), 403);
        $this->selectedBranchId = $branchId;

        return $branchId;
    }

    public function mount()
    {
        $this->branches = \App\Helpers\BranchHelper::getAllowedBranchesQuery()->get();
        $this->selectedBranchId = \App\Helpers\BranchHelper::getActiveBranchId();
        $this->fromDate = now()->format('Y-m-d');
        $this->toDate = now()->format('Y-m-d');
        $this->checkActiveSession();
        $this->selectedSessionId = $this->activeSession ? 'active' : 'all';
        $this->loadAccounts();
    }

    protected function checkActiveSession()
    {
        $branchId = $this->resolveAuthorizedBranchId();
        $this->activeSession = null;
        $this->activeSession = CashSession::where('branch_id', $branchId)
            ->where('status', 'open')
            ->first();

        if ($this->activeSession) {
            $this->selectedBranchId = $this->activeSession->branch_id;
            $this->openingCash = $this->activeSession->opening_cash;
            $this->cashSales = \App\Helpers\SaleHelper::getSessionCashSalesSum($this->activeSession);

            $cashTotals = \App\Helpers\CashSessionHelper::getSessionCashTotals($this->activeSession);
            $this->cashIn = $cashTotals['cash_in'];
            $this->cashOut = $cashTotals['total_out'];

            $this->expectedCash = $this->openingCash + $this->cashSales + $this->cashIn - $this->cashOut;
        }
    }

    protected function loadAccounts()
    {
        // Load all active non-header Revenue & Equity accounts for Cash In
        $this->inflowSources = Account::query()
            ->where('is_header', false)
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereIn(\Illuminate\Support\Facades\DB::raw('LOWER(classification)'), ['revenue', 'equity'])
                    ->orWhere('code', 'like', '3%')
                    ->orWhere('code', 'like', '4%')
                    ->orWhere('code', 'like', '7%');
            })
            ->orderBy('code')
            ->get();

        // Load all active non-header Expense/Cost accounts for Cash Out (exclude revenue accounts like 621101002)
        $this->outflowDestinations = Account::query()
            ->where('is_header', false)
            ->where('is_active', true)
            ->whereRaw('LOWER(classification) != ?', ['revenue'])
            ->where(function ($q) {
                $q->whereIn(\Illuminate\Support\Facades\DB::raw('LOWER(classification)'), ['expense', 'cost'])
                    ->orWhere('code', 'like', '5%')
                    ->orWhere('code', 'like', '6%')
                    ->orWhere('code', 'like', '8%');
            })
            ->orderBy('code')
            ->get();

        $this->transferAccounts = \App\Helpers\AccountHelper::cashAndBankQuery()->get();

        // Auto-select sensible default options
        if ($this->inflowSources->isNotEmpty() && empty($this->inSourceAccountId)) {
            $defaultIn = $this->inflowSources->firstWhere('code', '311101003')
                ?? $this->inflowSources->firstWhere('code', '311101001')
                ?? $this->inflowSources->firstWhere('code', '411101001')
                ?? $this->inflowSources->first();
            $this->inSourceAccountId = $defaultIn?->id ?? '';
        }
        if ($this->outflowDestinations->isNotEmpty() && empty($this->outDestinationAccountId)) {
            $defaultOut = $this->outflowDestinations->firstWhere('code', '611101014')
                ?? $this->outflowDestinations->firstWhere('code', '611101001')
                ?? $this->outflowDestinations->first();
            $this->outDestinationAccountId = $defaultOut?->id ?? '';
        }
        $this->applyDefaultTransferAccounts();
    }

    protected function applyDefaultTransferAccounts(): void
    {
        $accounts = collect($this->transferAccounts);
        if ($accounts->isEmpty()) {
            return;
        }

        // Default sumber: KAS Kecil / Laci Kasir (111101002); Default tujuan: KAS Besar / Owner (111101001)
        $kasKecil = $accounts->firstWhere('code', '111101002') ?? $accounts->first();
        $kasBesar = $accounts->firstWhere('code', '111101001')
            ?? $accounts->first(fn ($acc) => $acc->id !== $kasKecil?->id);

        $this->transferSourceAccountId = $kasKecil?->id ?? '';
        $this->transferDestinationAccountId = $kasBesar?->id ?? '';
    }

    public function openInModal()
    {
        abort_unless(PermissionHelper::can(Auth::user(), 'fo.daily_cash.create'), 403);
        $this->inAmount = '';
        $this->inNotes = '';
        $this->showInModal = true;
    }

    public function updated($propertyName)
    {
        if (in_array($propertyName, ['inAmount', 'outAmount', 'transferAmount'])) {
            $this->$propertyName = \App\Helpers\FormatHelper::parseRupiah($this->$propertyName);
        }
    }

    public function openOutModal()
    {
        abort_unless(PermissionHelper::can(Auth::user(), 'fo.daily_cash.create'), 403);
        $this->outAmount = '';
        $this->outNotes = '';
        $this->showOutModal = true;
    }

    public function openTransferModal()
    {
        abort_unless(PermissionHelper::can(Auth::user(), 'fo.daily_cash.create'), 403);
        $this->loadAccounts();
        $this->transferAmount = '';
        $this->transferNotes = '';
        $this->showTransferModal = true;
    }

    public function saveTransfer()
    {
        abort_unless(PermissionHelper::can(Auth::user(), 'fo.daily_cash.create'), 403);
        $branchId = $this->resolveAuthorizedBranchId();
        $this->checkActiveSession();
        abort_unless($this->activeSession && (int) $this->activeSession->branch_id === $branchId, 403);
        $this->loadAccounts(); // Enforce locked default accounts (KAS Kecil -> KAS Besar)

        $this->validate([
            'transferAmount' => 'required|integer|min:1',
            'transferSourceAccountId' => 'required|exists:accounts,id',
            'transferDestinationAccountId' => 'required|exists:accounts,id|different:transferSourceAccountId',
            'transferNotes' => 'nullable|string|max:255',
        ], [
            'transferAmount.required' => 'Nominal setor wajib diisi.',
            'transferAmount.integer' => 'Nominal setor harus berupa angka.',
            'transferAmount.min' => 'Nominal setor harus lebih dari 0.',
            'transferSourceAccountId.required' => 'Akun kas asal wajib dipilih.',
            'transferDestinationAccountId.required' => 'Akun tujuan setor wajib dipilih.',
            'transferDestinationAccountId.different' => 'Akun tujuan setor harus berbeda dari akun asal.',
        ]);

        try {
            if ($this->expectedCash < $this->transferAmount) {
                throw new \Exception('Kas di laci tidak mencukupi untuk melakukan setor kas ini.');
            }

            $destAccount = Account::find($this->transferDestinationAccountId);
            $notes = trim((string) $this->transferNotes);
            if ($notes === '') {
                $notes = 'Setor kas ke ' . ($destAccount?->name ?? 'Kas Tujuan');
            }

            app(CreateCashTransaction::class)->execute([
                'branch_id' => $branchId,
                'cash_session_id' => $this->activeSession->id,
                'type' => 'transfer',
                'transaction_date' => now()->toDateString(),
                'source_account_id' => $this->transferSourceAccountId,
                'destination_account_id' => $this->transferDestinationAccountId,
                'amount' => $this->transferAmount,
                'notes' => $notes,
                'status' => 'posted',
            ]);

            Notification::make()
                ->title('Setor Kas Berhasil Dicatat')
                ->success()
                ->send();

            $this->dispatch('toast', [
                'type' => 'success',
                'message' => 'Setor Kas Berhasil Dicatat',
            ]);

            $this->showTransferModal = false;
            $this->checkActiveSession();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Gagal Mencatat Setor Kas')
                ->body($e->getMessage())
                ->danger()
                ->send();

            $this->dispatch('toast', [
                'type' => 'error',
                'message' => 'Gagal Mencatat Setor Kas: ' . $e->getMessage(),
            ]);
        }
    }

    public function saveInflow()
    {
        abort_unless(PermissionHelper::can(Auth::user(), 'fo.daily_cash.create'), 403);
        $branchId = $this->resolveAuthorizedBranchId();
        $this->checkActiveSession();
        abort_unless($this->activeSession && (int) $this->activeSession->branch_id === $branchId, 403);

        $this->validate([
            'inAmount' => 'required|integer|min:1',
            'inSourceAccountId' => 'required|exists:accounts,id',
            'inNotes' => 'required|string|max:255',
        ], [
            'inAmount.required' => 'Nominal wajib diisi.',
            'inAmount.integer' => 'Nominal harus berupa angka.',
            'inAmount.min' => 'Nominal harus lebih dari 0.',
            'inSourceAccountId.required' => 'Sumber kas wajib dipilih.',
            'inNotes.required' => 'Catatan wajib diisi.',
        ]);

        try {
            $cashAccount = \App\Helpers\CashSessionHelper::resolveDrawerCashAccount();
            if (!$cashAccount) {
                throw new \Exception('Akun Kas Laci (111101002 / 111101001) tidak ditemukan.');
            }

            app(CreateCashTransaction::class)->execute([
                'branch_id' => $branchId,
                'cash_session_id' => $this->activeSession->id,
                'type' => 'in',
                'transaction_date' => now()->toDateString(),
                'source_account_id' => $this->inSourceAccountId,
                'destination_account_id' => $cashAccount->id,
                'amount' => $this->inAmount,
                'notes' => $this->inNotes,
                'status' => 'posted',
            ]);

            Notification::make()
                ->title('Kas Masuk Berhasil Dicatat')
                ->success()
                ->send();

            $this->dispatch('toast', [
                'type' => 'success',
                'message' => 'Kas Masuk Berhasil Dicatat',
            ]);

            $this->showInModal = false;
            $this->checkActiveSession();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Gagal Mencatat Kas Masuk')
                ->body($e->getMessage())
                ->danger()
                ->send();

            $this->dispatch('toast', [
                'type' => 'error',
                'message' => 'Gagal Mencatat Kas Masuk: ' . $e->getMessage(),
            ]);
        }
    }

    public function saveOutflow()
    {
        abort_unless(PermissionHelper::can(Auth::user(), 'fo.daily_cash.create'), 403);
        $branchId = $this->resolveAuthorizedBranchId();
        $this->checkActiveSession();
        abort_unless($this->activeSession && (int) $this->activeSession->branch_id === $branchId, 403);

        $this->validate([
            'outAmount' => 'required|integer|min:1',
            'outDestinationAccountId' => 'required|exists:accounts,id',
            'outNotes' => 'required|string|max:255',
        ], [
            'outAmount.required' => 'Nominal wajib diisi.',
            'outAmount.integer' => 'Nominal harus berupa angka.',
            'outAmount.min' => 'Nominal harus lebih dari 0.',
            'outDestinationAccountId.required' => 'Kategori beban wajib dipilih.',
            'outNotes.required' => 'Catatan wajib diisi.',
        ]);

        try {
            $cashAccount = \App\Helpers\CashSessionHelper::resolveDrawerCashAccount();
            if (!$cashAccount) {
                throw new \Exception('Akun Kas Laci (111101002 / 111101001) tidak ditemukan.');
            }

            // Check if drawer has enough cash
            if ($this->expectedCash < $this->outAmount) {
                throw new \Exception('Kas di laci tidak mencukupi untuk melakukan transaksi kas keluar ini.');
            }

            app(CreateCashTransaction::class)->execute([
                'branch_id' => $this->selectedBranchId,
                'cash_session_id' => $this->activeSession->id,
                'type' => 'out',
                'transaction_date' => now()->toDateString(),
                'source_account_id' => $cashAccount->id,
                'destination_account_id' => $this->outDestinationAccountId,
                'amount' => $this->outAmount,
                'notes' => $this->outNotes,
                'status' => 'posted',
            ]);

            Notification::make()
                ->title('Kas Keluar Berhasil Dicatat')
                ->success()
                ->send();

            $this->dispatch('toast', [
                'type' => 'success',
                'message' => 'Kas Keluar Berhasil Dicatat',
            ]);

            $this->showOutModal = false;
            $this->checkActiveSession();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Gagal Mencatat Kas Keluar')
                ->body($e->getMessage())
                ->danger()
                ->send();

            $this->dispatch('toast', [
                'type' => 'error',
                'message' => 'Gagal Mencatat Kas Keluar: ' . $e->getMessage(),
            ]);
        }
    }

    public function render()
    {
        $branchId = $this->resolveAuthorizedBranchId();
        $this->checkActiveSession();

        $baseQuery = CashTransaction::query()
            ->with(['sourceAccount', 'destinationAccount', 'cashSession'])
            ->where('branch_id', $branchId)
            ->when($this->selectedSessionId === 'active', function ($query) {
                if ($this->activeSession) {
                    $query->where('cash_session_id', $this->activeSession->id);
                } else {
                    $query->whereRaw('1 = 0');
                }
            })
            ->when($this->selectedSessionId !== 'all' && $this->selectedSessionId !== 'active' && is_numeric($this->selectedSessionId), function ($query) {
                $query->where('cash_session_id', (int) $this->selectedSessionId);
            })
            ->when($this->selectedSessionId === 'all' && $this->fromDate !== '', function ($query) {
                $query->whereDate('transaction_date', '>=', $this->fromDate);
            })
            ->when($this->selectedSessionId === 'all' && $this->toDate !== '', function ($query) {
                $query->whereDate('transaction_date', '<=', $this->toDate);
            })
            ->when(in_array($this->selectedType, ['in', 'out', 'transfer'], true), function ($query) {
                $query->where('type', $this->selectedType);
            })
            ->when($this->selectedAccountId !== '' && is_numeric($this->selectedAccountId), function ($query) {
                $accountId = (int) $this->selectedAccountId;
                $query->where(function ($q) use ($accountId) {
                    $q->where(function ($inQ) use ($accountId) {
                        $inQ->where('type', 'in')->where('source_account_id', $accountId);
                    })->orWhere(function ($outQ) use ($accountId) {
                        $outQ->where('type', 'out')->where('destination_account_id', $accountId);
                    })->orWhere(function ($trQ) use ($accountId) {
                        $trQ->where('type', 'transfer')
                            ->where(function ($sub) use ($accountId) {
                                $sub->where('source_account_id', $accountId)
                                    ->orWhere('destination_account_id', $accountId);
                            });
                    });
                });
            })
            ->when(trim($this->search) !== '', function ($query) {
                $term = trim($this->search);
                $query->where(function ($q) use ($term) {
                    $q->where('transaction_no', 'like', "%{$term}%")
                        ->orWhere('notes', 'like', "%{$term}%");
                });
            });

        $filteredCount = (clone $baseQuery)->count();
        $filteredIn = (int) (clone $baseQuery)->where('type', 'in')->sum('amount');
        $filteredOut = (int) (clone $baseQuery)->where('type', 'out')->sum('amount');
        $filteredTransfer = (int) (clone $baseQuery)->where('type', 'transfer')->sum('amount');
        $filteredNet = $filteredIn - $filteredOut - $filteredTransfer;

        $transactions = (clone $baseQuery)
            ->orderByDesc('transaction_date')
            ->orderByDesc('created_at')
            ->paginate($this->perPage > 0 ? $this->perPage : 1000);

        $availableSessions = CashSession::query()
            ->where('branch_id', $branchId)
            ->orderByDesc('opened_at')
            ->limit(30)
            ->get();

        $usedAccountIds = CashTransaction::query()
            ->where('branch_id', $branchId)
            ->get(['type', 'source_account_id', 'destination_account_id'])
            ->toBase()
            ->map(fn ($tx) => $tx->type === 'in' ? $tx->source_account_id : $tx->destination_account_id)
            ->filter()
            ->merge(collect($this->inflowSources)->pluck('id'))
            ->merge(collect($this->outflowDestinations)->pluck('id'))
            ->unique()
            ->values();

        $filterAccounts = Account::query()
            ->whereIn('id', $usedAccountIds)
            ->orderBy('code')
            ->get();

        $branch = Branch::find($branchId);
        $selectedLogoUrl = ($branch && !empty($branch->logo_path) && trim($branch->logo_path) !== '') ? \Illuminate\Support\Facades\Storage::url($branch->logo_path) : null;

        return view('livewire.pos-daily-cash', [
            'transactions' => $transactions,
            'availableSessions' => $availableSessions,
            'filterAccounts' => $filterAccounts,
            'filteredSummary' => [
                'count' => $filteredCount,
                'in' => $filteredIn,
                'out' => $filteredOut,
                'transfer' => $filteredTransfer,
                'net' => $filteredNet,
            ],
            'selectedLogoUrl' => $selectedLogoUrl,
        ])->layout('layouts.pos');
    }
}
