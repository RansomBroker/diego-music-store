<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Branch;
use App\Models\CashSession;
use App\Actions\CashSession\OpenCashSession;
use App\Actions\CashSession\CloseCashSession;
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
            
            // Calculate current expected cash (opening cash + completed cash sales)
            $this->cashSales = $this->activeSession->sales()
                ->where('status', 'completed')
                ->where('payment_method', 'cash')
                ->sum('grand_total');

            $this->expectedCash = $this->activeSession->opening_cash + $this->cashSales;
            $this->actualCash = $this->expectedCash; // default to expected cash
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

        // Close the session with supervisor ID as close_by
        $this->closeSession($supervisor->id);
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
