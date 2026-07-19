<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Branch;
use App\Models\CashSession;
use App\Models\CashTransaction;
use App\Models\Account;
use App\Actions\CashManagement\CreateCashTransaction;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;
use Livewire\WithPagination;

class POSDailyCash extends Component
{
    use WithPagination;

    public $activeSession = null;
    public $branches = [];
    public $selectedBranchId = null;

    // Summary fields
    public $openingCash = 0;
    public $cashSales = 0;
    public $cashIn = 0;
    public $cashOut = 0;
    public $expectedCash = 0;

    // Modals visibility
    public $showInModal = false;
    public $showOutModal = false;

    // Inflow Form State
    public $inAmount = 0;
    public $inSourceAccountId = '';
    public $inNotes = '';

    // Outflow Form State
    public $outAmount = 0;
    public $outDestinationAccountId = '';
    public $outNotes = '';

    // Inflow sources option
    public $inflowSources = [];
    // Outflow destinations option
    public $outflowDestinations = [];

    public function mount()
    {
        $this->branches = Branch::where('is_active', true)->get();
        $this->checkActiveSession();
        $this->loadAccounts();
    }

    protected function checkActiveSession()
    {
        $this->activeSession = CashSession::where('user_id', Auth::id())
            ->where('status', 'open')
            ->first();

        if ($this->activeSession) {
            $this->selectedBranchId = $this->activeSession->branch_id;
            $this->openingCash = $this->activeSession->opening_cash;
            $this->cashSales = \App\Helpers\SaleHelper::getSessionCashSalesSum($this->activeSession);
            
            $this->cashIn = $this->activeSession->cashTransactions()
                ->where('type', 'in')
                ->where('status', 'posted')
                ->sum('amount');
                
            $this->cashOut = $this->activeSession->cashTransactions()
                ->where('type', 'out')
                ->where('status', 'posted')
                ->sum('amount');

            $this->expectedCash = $this->openingCash + $this->cashSales + $this->cashIn - $this->cashOut;
        }
    }

    protected function loadAccounts()
    {
        // Load standard accounts
        $this->inflowSources = Account::whereIn('code', ['3-1000', '4-1000'])->get(); // Modal Pemilik or Revenue
        $this->outflowDestinations = Account::where('code', '6-1000')->get(); // Beban Operasional & Gaji
        
        // Auto-select first options
        if ($this->inflowSources->isNotEmpty()) {
            $this->inSourceAccountId = $this->inflowSources->first()->id;
        }
        if ($this->outflowDestinations->isNotEmpty()) {
            $this->outDestinationAccountId = $this->outflowDestinations->first()->id;
        }
    }

    public function openInModal()
    {
        $this->inAmount = '';
        $this->inNotes = '';
        $this->showInModal = true;
    }

    public function openOutModal()
    {
        $this->outAmount = '';
        $this->outNotes = '';
        $this->showOutModal = true;
    }

    public function saveInflow()
    {
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
            $cashAccount = Account::where('code', '1-1000')->first();
            if (!$cashAccount) {
                throw new \Exception('Akun Kas Utama (1-1000) tidak ditemukan.');
            }

            app(CreateCashTransaction::class)->execute([
                'branch_id' => $this->selectedBranchId,
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

            $this->showInModal = false;
            $this->checkActiveSession();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Gagal Mencatat Kas Masuk')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function saveOutflow()
    {
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
            $cashAccount = Account::where('code', '1-1000')->first();
            if (!$cashAccount) {
                throw new \Exception('Akun Kas Utama (1-1000) tidak ditemukan.');
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

            $this->showOutModal = false;
            $this->checkActiveSession();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Gagal Mencatat Kas Keluar')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function render()
    {
        $transactions = [];
        if ($this->activeSession) {
            $transactions = CashTransaction::where('cash_session_id', $this->activeSession->id)
                ->orderBy('created_at', 'desc')
                ->paginate(10);
        }

        $branch = Branch::find($this->selectedBranchId);
        $selectedLogoUrl = ($branch && !empty($branch->logo_path) && trim($branch->logo_path) !== '') ? \Illuminate\Support\Facades\Storage::url($branch->logo_path) : null;

        return view('livewire.pos-daily-cash', [
            'transactions' => $transactions,
            'selectedLogoUrl' => $selectedLogoUrl,
        ])->layout('layouts.pos');
    }
}
