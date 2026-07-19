<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Branch;
use App\Models\CashSession;
use App\Actions\CashSession\OpenCashSession;
use App\Actions\CashSession\CloseCashSession;
use App\Actions\CashSession\ReopenCashSession;
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
    public $notes = '';
    public $branches = [];

    // Close Session Form State
    public $actualCash = 0;
    public $closingNotes = '';
    public $expectedCash = 0;
    public $cashSales = 0;

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

    public function mount()
    {
        $this->branches = Branch::where('is_active', true)->get();
        
        // Find if user belongs to branches and set default branch
        $userBranchId = Auth::user()->branches()->first()?->id;
        $this->selectedBranchId = $userBranchId ?? ($this->branches->first()?->id ?? null);

        $this->checkActiveSession();
    }

    protected function checkActiveSession()
    {
        // Find if user has any active (open) session
        $this->activeSession = CashSession::where('user_id', Auth::id())
            ->where('status', 'open')
            ->first();

        if ($this->activeSession) {
            $this->selectedBranchId = $this->activeSession->branch_id;
            
            $this->cashSales = \App\Helpers\SaleHelper::getSessionCashSalesSum($this->activeSession);
            $cashIn = $this->activeSession->cashTransactions()
                ->where('type', 'in')
                ->where('status', 'posted')
                ->sum('amount');
            $cashOut = $this->activeSession->cashTransactions()
                ->where('type', 'out')
                ->where('status', 'posted')
                ->sum('amount');

            $this->expectedCash = $this->activeSession->opening_cash + $this->cashSales + $cashIn - $cashOut;
            $this->actualCash = $this->expectedCash;
        } else {
            // Default opening cash state
            $this->openingCash = 500000; // standard default Rp 500.000 modal
        }
    }

    public function selectOpeningPreset($amount)
    {
        $this->openingCash = $amount;
    }

    public function selectActualPreset($amount)
    {
        $this->actualCash = $amount;
    }

    public function openSession()
    {
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

        // Check if user has supervisor authority (owner or admin role)
        if (!$supervisor->hasAnyRole(['owner', 'admin'])) {
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
        $session = CashSession::find($sessionId);
        if (!$session) {
            return;
        }

        try {
            // Check latest session
            $latestSession = CashSession::where('user_id', $session->user_id)
                ->orderBy('id', 'desc')
                ->first();

            if ($latestSession && $latestSession->id !== $session->id) {
                throw new \Exception('Hanya sesi kasir terakhir yang dapat dibuka kembali.');
            }

            // Check if there is already an active session
            $activeSession = CashSession::where('user_id', $session->user_id)
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
        try {
            $session = CashSession::findOrFail($this->sessionToReopenId);
            
            app(ReopenCashSession::class)->execute($session);

            Notification::make()
                ->title('Sesi Kasir Dibuka Kembali')
                ->body('Sesi kasir berhasil dibuka kembali.')
                ->success()
                ->send();

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
            $closedBy = $supervisorId ?? Auth::id();

            $sessionToPrint = app(CloseCashSession::class)->execute($this->activeSession, [
                'actual_cash' => $this->actualCash,
                'notes' => $this->closingNotes,
                'closed_by_user_id' => $closedBy,
            ]);

            Notification::make()
                ->title('Sesi Kasir Ditutup')
                ->body('Sesi kasir berhasil ditutup.')
                ->success()
                ->send();

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
        $this->selectedSessionId = $sessionId;

        // Load sales from the session
        $sales = \App\Models\Sale::where('cash_session_id', $sessionId)
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
        // Fetch closed sessions for history
        $history = CashSession::with(['branch', 'user', 'closedBy'])
            ->where('user_id', Auth::id())
            ->orderBy('id', 'desc')
            ->limit(10)
            ->get();

        // Get logo of selected branch
        $branch = Branch::find($this->selectedBranchId);
        $selectedLogoUrl = ($branch && !empty($branch->logo_path) && trim($branch->logo_path) !== '') ? \Illuminate\Support\Facades\Storage::url($branch->logo_path) : null;

        return view('livewire.pos-cash-session', [
            'history' => $history,
            'selectedLogoUrl' => $selectedLogoUrl,
        ])->layout('layouts.pos');
    }
}
