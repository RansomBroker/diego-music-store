<?php

namespace App\Livewire\Pos;

use App\Actions\SalesQueue\CompleteSalesDuty;
use App\Actions\SalesQueue\InitializeDailySalesQueue;
use App\Actions\SalesQueue\StartSalesDuty;
use App\Actions\SalesQueue\ToggleSalesDutyStatus;
use App\Helpers\BranchHelper;
use App\Helpers\SalesQueueHelper;
use App\Models\CashSession;
use App\Models\SalesQueueMember;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class SalesQueueWidget extends Component
{
    public int $branchId = 0;
    public bool $isExpanded = false;
    public string $activeTab = 'queue'; // 'queue' or 'on_duty'
    public bool $showManageModal = false;

    public function mount(?int $branchId = null): void
    {
        $this->branchId = $branchId ?: (int) BranchHelper::getActiveBranchId();
        $this->initializeQueue();
    }

    public function initializeQueue(): void
    {
        if ($this->branchId > 0) {
            app(InitializeDailySalesQueue::class)->execute($this->branchId);
        }
    }

    public function toggleExpanded(): void
    {
        $this->isExpanded = !$this->isExpanded;
    }

    public function openWithTab(string $tab): void
    {
        if ($this->isExpanded && $this->activeTab === $tab) {
            $this->isExpanded = false;
        } else {
            $this->activeTab = $tab;
            $this->isExpanded = true;
        }
    }

    public function startDuty(int $memberId): void
    {
        $member = SalesQueueMember::with('user')->find($memberId);
        if (!$member) {
            return;
        }

        try {
            $activeSession = CashSession::where('branch_id', $this->branchId)
                ->where('status', 'open')
                ->first();

            app(StartSalesDuty::class)->execute($member, $activeSession?->id);

            Notification::make()
                ->title('Giliran Melayani Dimulai')
                ->body("Sales {$member->user->name} sekarang berstatus On Duty.")
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Gagal Memulai Giliran')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function completeDuty(int $memberId): void
    {
        $member = SalesQueueMember::with('user')->find($memberId);
        if (!$member) {
            return;
        }

        try {
            app(CompleteSalesDuty::class)->execute($member);

            Notification::make()
                ->title('Pelayanan Selesai')
                ->body("Sales {$member->user->name} telah kembali ke antrian belakang.")
                ->info()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Gagal Menyelesaikan')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function toggleOffDuty(int $memberId): void
    {
        $member = SalesQueueMember::with('user')->find($memberId);
        if (!$member) {
            return;
        }

        try {
            $updated = app(ToggleSalesDutyStatus::class)->execute($member);

            $statusText = $updated->status === 'off_duty' ? 'Istirahat / Off' : 'Kembali Siap di Antrian';
            Notification::make()
                ->title("Status {$member->user->name} Diperbarui")
                ->body("Sales {$member->user->name} sekarang {$statusText}.")
                ->info()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Gagal Mengubah Status')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function selectForTransaction(int $memberId): void
    {
        $member = SalesQueueMember::with('user')->find($memberId);
        if (!$member || !$member->user) {
            return;
        }

        $this->dispatch('pos-select-sales-rep', [
            'id' => $member->user->id,
            'name' => $member->user->name,
        ]);

        Notification::make()
            ->title('Sales Dipilih')
            ->body("{$member->user->name} terpilih sebagai Sales Representative transaksi aktif.")
            ->success()
            ->send();
    }

    public function syncRoster(): void
    {
        SalesQueueMember::where('branch_id', $this->branchId)
            ->where('date', now()->toDateString())
            ->where('status', 'off_duty')
            ->update(['status' => 'waiting']);

        $this->initializeQueue();

        Notification::make()
            ->title('Antrian Disinkronkan')
            ->body('Daftar sales cabang berhasil diperbarui.')
            ->success()
            ->send();
    }

    public function getOnDutyMembersProperty()
    {
        return SalesQueueHelper::getOnDutyMembers($this->branchId);
    }

    public function getWaitingMembersProperty()
    {
        return SalesQueueHelper::getWaitingMembers($this->branchId);
    }

    public function getNextSalesProperty(): ?SalesQueueMember
    {
        return SalesQueueHelper::getNextWaitingSales($this->branchId);
    }

    public function getOffDutyMembersProperty()
    {
        return SalesQueueMember::with('user')
            ->where('branch_id', $this->branchId)
            ->where('date', now()->toDateString())
            ->where('status', 'off_duty')
            ->get();
    }

    public function render()
    {
        return view('livewire.pos.sales-queue-widget', [
            'onDutyMembers' => $this->onDutyMembers,
            'waitingMembers' => $this->waitingMembers,
            'nextSales' => $this->nextSales,
            'offDutyMembers' => $this->offDutyMembers,
        ]);
    }
}
