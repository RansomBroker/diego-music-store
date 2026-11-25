<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Branch;
use App\Models\CashSession;
use App\Actions\CashSession\OpenCashSession;
use App\Actions\CashSession\CloseCashSession;
use App\Actions\CashSession\ReopenCashSession;
use App\Helpers\PermissionHelper;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Filament\Notifications\Notification;

class POSCashSession extends Component
{
    // Active tab state
    public $activeTab = 'sesi'; // 'sesi' or 'riwayat'

    // Active session (if any)
    public $activeSession = null;

    // Open Session Form State
    public $selectedBranchId = null;
    public $openingCash = 0;
    public ?int $lastClosedActualCash = null;
    public ?int $lastClosedSessionId = null;
    public $notes = '';
    public $branches = [];

    // Close Session Form State
    public $actualCash = 0;
    public $closingNotes = '';
    public $expectedCash = 0;
    public $cashSales = 0;
    public int $cashIn = 0;
    public int $cashOut = 0;
    public int $cashTransferOut = 0;
    public int $cashTransferCount = 0;
    public string $lastTransferLabel = '';

    // Setor / Transfer Kas Modal State (langsung dari halaman Tutup Sesi)
    public bool $showTransferModal = false;
    public $transferAmount = 0;
    public $transferSourceAccountId = '';
    public $transferDestinationAccountId = '';
    public string $transferNotes = '';
    public $transferAccounts = [];

    // Supervisor confirmation state for cash discrepancy
    public $showSupervisorModal = false;
    public $supervisorEmail = '';
    public $supervisorPassword = '';

    // State untuk detail transaksi sesi kasir
    public bool $showTransactionsModal = false;
    public ?int $selectedSessionId = null;
    public $selectedSessionTransactions = [];
    public array $selectedSessionSummary = [];

    // Otorisasi & pembukaan kembali sesi kasir
    public string $supervisorAction = 'close'; // 'close' or 'reopen'
    public ?int $sessionToReopenId = null;

    public function boot(): void
    {
        abort_unless(PermissionHelper::canAccessFrontofficeFeature(Auth::user(), 'fo.cash_session.view'), 403);
    }

    public function mount()
    {
        $this->branches = \App\Helpers\BranchHelper::getAllowedBranchesQuery()->get();
        $this->selectedBranchId = \App\Helpers\BranchHelper::getActiveBranchId();

        $this->checkActiveSession();
    }

    public function updatedSelectedBranchId($value)
    {
        if ($value) {
            session(['pos_active_branch_id' => $value]);
            $this->checkActiveSession();
        }
    }

    protected function checkActiveSession()
    {
        $activeBranchId = \App\Helpers\BranchHelper::getActiveBranchId();
        $this->selectedBranchId = $activeBranchId;

        // Find if branch has active (open) session
        $this->activeSession = CashSession::with('user')
            ->where('branch_id', $activeBranchId)
            ->where('status', 'open')
            ->first();

        if ($this->activeSession) {
            $this->cashSales = \App\Helpers\SaleHelper::getSessionCashSalesSum($this->activeSession);
            $cashTotals = \App\Helpers\CashSessionHelper::getSessionCashTotals($this->activeSession);
            $this->cashIn = $cashTotals['cash_in'];
            $this->cashOut = $cashTotals['cash_out'];
            $this->cashTransferOut = $cashTotals['cash_transfer_out'];

            $transfers = $this->activeSession->cashTransactions()
                ->with(['sourceAccount', 'destinationAccount'])
                ->where('type', 'transfer')
                ->where('status', 'posted')
                ->latest('id')
                ->get();
            $this->cashTransferCount = $transfers->count();
            $latestTransfer = $transfers->first();
            $this->lastTransferLabel = $latestTransfer
                ? trim(($latestTransfer->sourceAccount?->name ?? 'Kas') . ' → ' . ($latestTransfer->destinationAccount?->name ?? 'Kas Kecil'))
                : '';

            $this->expectedCash = $this->activeSession->opening_cash + $this->cashSales + $cashTotals['cash_in'] - $cashTotals['total_out'];
            $this->actualCash = $this->expectedCash;
        } else {
            $lastClosed = CashSession::query()
                ->where('branch_id', $activeBranchId)
                ->where('status', 'closed')
                ->whereNotNull('actual_cash')
                ->orderByDesc('closed_at')
                ->orderByDesc('id')
                ->first();

            if ($lastClosed) {
                $this->lastClosedActualCash = (int) $lastClosed->actual_cash;
                $this->lastClosedSessionId = (int) $lastClosed->id;
                $this->openingCash = $this->lastClosedActualCash;
            } else {
                $this->lastClosedActualCash = null;
                $this->lastClosedSessionId = null;
                $this->openingCash = 500000; // standard default Rp 500.000 modal
            }
            $this->cashIn = 0;
            $this->cashOut = 0;
            $this->cashTransferOut = 0;
            $this->cashTransferCount = 0;
            $this->lastTransferLabel = '';
        }
    }

    protected function loadTransferAccounts(): void
    {
        $this->transferAccounts = \App\Helpers\AccountHelper::cashAndBankQuery()->get();

        $accounts = collect($this->transferAccounts);
        if ($accounts->isNotEmpty()) {
            // Default sumber: KAS Kecil / Laci Kasir (111101002); Default tujuan: KAS Besar / Owner (111101001)
            $kasKecil = $accounts->firstWhere('code', '111101002') ?? $accounts->first();
            $kasBesar = $accounts->firstWhere('code', '111101001')
                ?? $accounts->first(fn ($acc) => $acc->id !== $kasKecil?->id);

            $this->transferSourceAccountId = $kasKecil?->id ?? '';
            $this->transferDestinationAccountId = $kasBesar?->id ?? '';
        }
    }

    public function openTransferModal(): void
    {
        abort_unless(PermissionHelper::can(Auth::user(), ['fo.daily_cash.create', 'fo.cash_session.close']), 403);
        $this->checkActiveSession();
        if (! $this->activeSession) {
            return;
        }

        $this->loadTransferAccounts();
        $this->transferAmount = '';
        $this->transferNotes = '';
        $this->showTransferModal = true;
    }

    public function saveTransfer(): void
    {
        abort_unless(PermissionHelper::can(Auth::user(), ['fo.daily_cash.create', 'fo.cash_session.close']), 403);
        $this->checkActiveSession();
        if (! $this->activeSession) {
            return;
        }
        $this->loadTransferAccounts(); // Enforce locked default accounts (KAS Kecil -> KAS Besar)

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

            $destAccount = \App\Models\Account::find($this->transferDestinationAccountId);
            $notes = trim((string) $this->transferNotes);
            if ($notes === '') {
                $notes = 'Setor kas ke ' . ($destAccount?->name ?? 'Kas Kecil');
            }

            app(\App\Actions\CashManagement\CreateCashTransaction::class)->execute([
                'branch_id' => $this->activeSession->branch_id,
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
                ->body('Ekspektasi kas laci telah diperbarui otomatis.')
                ->success()
                ->send();

            $this->dispatch('toast', [
                'type' => 'success',
                'message' => 'Setor Kas Berhasil Dicatat',
            ]);

            $this->showTransferModal = false;
            $this->checkActiveSession();
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Gagal Mencatat Setor Kas')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function selectOpeningPreset($amount)
    {
        $this->openingCash = $amount;
    }

    public function updated($propertyName)
    {
        if (in_array($propertyName, ['openingCash', 'actualCash', 'transferAmount'])) {
            $this->$propertyName = \App\Helpers\FormatHelper::parseRupiah($this->$propertyName);
        }
    }

    public function selectActualPreset($amount)
    {
        $this->actualCash = $amount;
    }

    public function openSession()
    {
        abort_unless(PermissionHelper::can(Auth::user(), 'fo.cash_session.open'), 403);
        $this->validate([
            'selectedBranchId' => 'required|exists:branches,id',
            'openingCash' => 'required|integer|min:0',
        ], [
            'selectedBranchId.required' => 'Cabang wajib dipilih.',
            'openingCash.required' => 'Modal awal wajib diisi.',
            'openingCash.integer' => 'Modal awal harus berupa angka.',
            'openingCash.min' => 'Modal awal tidak boleh negatif.',
        ]);

        try {
            app(OpenCashSession::class)->execute([
                'user_id' => Auth::id(),
                'branch_id' => $this->selectedBranchId,
                'opening_cash' => $this->openingCash,
                'notes' => $this->notes,
            ]);

            Notification::make()
                ->title('Sesi Kasir Dibuka')
                ->body('Sesi kasir berhasil dibuka. Anda sekarang dapat mengakses POS.')
                ->success()
                ->send();

            return redirect()->to('/pos');
        } catch (\Exception $e) {
            Notification::make()
                ->title('Gagal Membuka Sesi')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function confirmCloseSession()
    {
        abort_unless(PermissionHelper::can(Auth::user(), 'fo.cash_session.close'), 403);
        $this->validate([
            'actualCash' => 'required|integer|min:0',
        ], [
            'actualCash.required' => 'Jumlah kas fisik wajib diisi.',
            'actualCash.integer' => 'Kas fisik harus berupa angka.',
            'actualCash.min' => 'Kas fisik tidak boleh negatif.',
        ]);

        $discrepancy = $this->actualCash - $this->expectedCash;

        if ($discrepancy !== 0) {
            // Require supervisor validation
            $this->supervisorAction = 'close';
            $this->supervisorEmail = '';
            $this->supervisorPassword = '';
            $this->showSupervisorModal = true;
        } else {
            $this->closeSession();
        }
    }

    public function authorizeAndClose()
    {
        $requiredPerm = $this->supervisorAction === 'reopen' ? 'fo.cash_session.reopen' : 'fo.cash_session.close';
        abort_unless(PermissionHelper::can(Auth::user(), $requiredPerm), 403);

        $this->validate([
            'supervisorEmail' => 'required|email',
            'supervisorPassword' => 'required',
        ], [
            'supervisorEmail.required' => 'Email supervisor wajib diisi.',
            'supervisorEmail.email' => 'Format email tidak valid.',
            'supervisorPassword.required' => 'Password supervisor wajib diisi.',
        ]);

        // Attempt to authenticate supervisor
        $supervisor = \App\Models\User::where('email', $this->supervisorEmail)->first();

        if (!$supervisor || !Hash::check($this->supervisorPassword, $supervisor->password)) {
            $this->addError('supervisorEmail', 'Kredensial supervisor salah.');
            return;
        }

        // Check if supervisor holds permission to authorize close/reopen
        if (!PermissionHelper::can($supervisor, ['fo.cash_session.close', 'fo.cash_session.reopen'])) {
            $this->addError('supervisorEmail', 'User ini tidak memiliki wewenang supervisor/admin.');
            return;
        }

        if ($this->supervisorAction === 'reopen') {
            $this->reopenSession();
        } else {
            // Close the session with supervisor ID as close_by
            $this->closeSession($supervisor->id);
        }
    }

    public function requestReopenSession(int $sessionId)
    {
        abort_unless(PermissionHelper::can(Auth::user(), 'fo.cash_session.reopen'), 403);
        $session = CashSession::query()
            ->where('branch_id', \App\Helpers\BranchHelper::getActiveBranchId())
            ->find($sessionId);
        if (!$session) {
            return;
        }

        try {
            // Check latest session
            $latestSession = CashSession::where('branch_id', $session->branch_id)
                ->orderBy('id', 'desc')
                ->first();

            if ($latestSession && $latestSession->id !== $session->id) {
                throw new \Exception('Hanya sesi kasir terakhir yang dapat dibuka kembali.');
            }

            // Check if there is already an active session
            $activeSession = CashSession::where('branch_id', $session->branch_id)
                ->where('status', 'open')
                ->first();

            if ($activeSession) {
                throw new \Exception('Anda sudah memiliki sesi kasir lain yang sedang aktif.');
            }

            $this->sessionToReopenId = $sessionId;
            $this->supervisorAction = 'reopen';
            $this->supervisorEmail = '';
            $this->supervisorPassword = '';
            $this->showSupervisorModal = true;
        } catch (\Exception $e) {
            Notification::make()
                ->title('Gagal Memproses')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    protected function reopenSession()
    {
        abort_unless(PermissionHelper::can(Auth::user(), 'fo.cash_session.reopen'), 403);
        try {
            $session = CashSession::query()
                ->where('branch_id', \App\Helpers\BranchHelper::getActiveBranchId())
                ->findOrFail($this->sessionToReopenId);
            
            app(ReopenCashSession::class)->execute($session);

            Notification::make()
                ->title('Sesi Kasir Dibuka Kembali')
                ->body('Sesi kasir berhasil dibuka kembali.')
                ->success()
                ->send();

            $this->dispatch('toast', [
                'type' => 'success',
                'message' => 'Sesi kasir berhasil dibuka kembali oleh supervisor.',
            ]);

            $this->showSupervisorModal = false;
            $this->activeTab = 'sesi';
            $this->checkActiveSession();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Gagal Membuka Kembali Sesi')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    protected function closeSession($supervisorId = null)
    {
        try {
            $sessionToPrint = app(CloseCashSession::class)->execute($this->activeSession, [
                'actual_cash' => $this->actualCash,
                'notes' => $this->closingNotes,
            ]);

            Notification::make()
                ->title('Sesi Kasir Ditutup')
                ->body('Sesi kasir berhasil ditutup.')
                ->success()
                ->send();

            $this->dispatch('toast', [
                'type' => 'success',
                'message' => 'Sesi kasir berhasil ditutup dan Z-Report diterbitkan.',
            ]);

            $this->activeSession = null;
            $this->showSupervisorModal = false;
            $this->checkActiveSession();

            // Open Z-Report in new tab to print
            $this->dispatch('print-z-report', ['url' => route('pos.z-report', $sessionToPrint->id)]);
        } catch (\Exception $e) {
            Notification::make()
                ->title('Gagal Menutup Sesi')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function showTransactions(int $sessionId): void
    {
        $session = CashSession::query()
            ->where('branch_id', \App\Helpers\BranchHelper::getActiveBranchId())
            ->findOrFail($sessionId);
        $user = Auth::user();
        $isManagement = PermissionHelper::can($user, ['fo.cash_session.close', 'fo.cash_session.reopen']);
        abort_unless($isManagement || (int) $session->user_id === (int) Auth::id(), 403);
        $this->selectedSessionId = $session->id;

        // Load sales from the authorized session
        $sales = \App\Models\Sale::where('cash_session_id', $session->id)
            ->with('customer')
            ->orderBy('created_at', 'desc')
            ->get();

        $this->selectedSessionTransactions = $sales;

        // Calculate summaries
        $cashTotal = 0;
        $nonCashTotal = 0;
        $totalSales = 0;

        foreach ($sales as $sale) {
            if ($sale->status !== 'completed') continue;
            $cashAmt = \App\Helpers\SaleHelper::getCashAmount($sale);
            $cashTotal += $cashAmt;
            $nonCashTotal += max(0, $sale->grand_total - $cashAmt);
            $totalSales += $sale->grand_total;
        }

        $this->selectedSessionSummary = [
            'cash_total' => $cashTotal,
            'non_cash_total' => $nonCashTotal,
            'total_sales' => $totalSales,
            'transaction_count' => $sales->count(),
        ];

        $this->showTransactionsModal = true;
    }

    public function render()
    {
        $activeBranchId = \App\Helpers\BranchHelper::getActiveBranchId();

        // Fetch closed sessions for history scoped to active branch
        $history = CashSession::with(['branch', 'user', 'closedBy'])
            ->where('branch_id', $activeBranchId)
            ->orderBy('id', 'desc')
            ->limit(10)
            ->get();

        // Get logo of selected branch
        $branch = Branch::find($activeBranchId);
        $selectedLogoUrl = ($branch && !empty($branch->logo_path) && trim($branch->logo_path) !== '') ? \Illuminate\Support\Facades\Storage::url($branch->logo_path) : null;

        return view('livewire.pos-cash-session', [
            'history' => $history,
            'selectedLogoUrl' => $selectedLogoUrl,
        ])->layout('layouts.pos');
    }
}
