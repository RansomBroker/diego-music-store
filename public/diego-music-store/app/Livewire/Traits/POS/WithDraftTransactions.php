<?php

namespace App\Livewire\Traits\POS;

use App\Models\PosHeldTransaction;
use App\Models\Customer;
use Filament\Notifications\Notification;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

trait WithDraftTransactions
{
    private function resolveHeldTransactionBranchId(): int
    {
        if (!empty($this->selectedBranchId)) {
            return (int) $this->selectedBranchId;
        }

        $allowedBranchIds = \App\Helpers\BranchHelper::getAllowedBranchesQuery()
            ->pluck('branches.id')->map(fn ($id) => (int) $id)->all();
        $branchId = \App\Helpers\BranchHelper::getActiveBranchId();
        if (!empty($allowedBranchIds) && !in_array($branchId, $allowedBranchIds, true)) {
            $branchId = $allowedBranchIds[0];
        }
        $this->selectedBranchId = (int) $branchId;
        return (int) $branchId;
    }

    public function getHeldTransactionsCountProperty(): int
    {
        return PosHeldTransaction::where('user_id', Auth::id())
            ->where('branch_id', $this->resolveHeldTransactionBranchId())
            ->whereNotNull('cart_data')
            ->count();
    }

    public function getHeldTransactionsProperty()
    {
        if (!$this->showHeldModal) {
            return collect();
        }

        return PosHeldTransaction::where('user_id', Auth::id())
            ->where('branch_id', $this->resolveHeldTransactionBranchId())
            ->whereNotNull('cart_data')
            ->orderBy('updated_at', 'desc')
            ->get();
    }

    public function openHeldTransactionsModal()
    {
        $this->showHeldModal = true;
        $this->isHeldModalMandatory = false;
    }

    public function holdTransaction()
    {
        abort_if(Auth::user()?->hasRole(['sales', 'Sales']), 403);
        if (empty($this->cart) && !$this->selectedCustomerId) {
            Notification::make()
                ->title('Keranjang Kosong')
                ->body('Tidak ada transaksi untuk ditunda.')
                ->warning()
                ->send();
            return;
        }

        $this->startNewTransaction();

        Notification::make()
            ->title('Transaksi Disimpan')
            ->body('Transaksi berhasil disimpan sebagai draft dan layar telah dikosongkan.')
            ->success()
            ->send();
    }

    public function restoreHeldTransaction($holdId)
    {
        abort_if(Auth::user()?->hasRole(['sales', 'Sales']), 403);
        $held = PosHeldTransaction::query()
            ->where('user_id', Auth::id())
            ->where('branch_id', $this->resolveHeldTransactionBranchId())
            ->find($holdId);
        if (!$held) {
            Notification::make()
                ->title('Transaksi Tidak Ditemukan')
                ->body('Transaksi tunda tidak dapat ditemukan atau sudah dihapus.')
                ->danger()
                ->send();
            return;
        }

        $this->cart = $held->cart_data;
        $this->selectedCustomerId = $held->customer_id;
        $this->selectedCustomerName = $held->customer_name ?? 'Umum / Walk-in';
        if ($held->customer_id) {
            $heldCust = Customer::find($held->customer_id);
            $this->customerPhone = $heldCust?->phone ?? '';
        } else {
            $this->customerPhone = '';
        }
        $this->isLoyaltyMember = $held->is_loyalty;
        $this->selectedPricingTierId = $held->pricing_tier_id;
        $this->usePoints = $held->use_points;
        $this->discountValue = $held->discount_value;
        $this->discountType = $held->discount_type ?? 'percent';

        $this->currentDraftId = $held->id;
        $this->showHeldModal = false;

        Notification::make()
            ->title('Transaksi Dimuat')
            ->body('Transaksi tunda berhasil dimuat kembali ke keranjang.')
            ->success()
            ->send();
    }

    public function deleteHeldTransaction($holdId)
    {
        abort_if(Auth::user()?->hasRole(['sales', 'Sales']), 403);
        $held = PosHeldTransaction::find($holdId);
        if ($held) {
            $held->delete();
        }

        Notification::make()
            ->title('Transaksi Dihapus')
            ->body('Transaksi tunda berhasil dihapus.')
            ->info()
            ->send();
    }

    public function reprintLastReceipt()
    {
        if (!$this->lastSaleId) {
            Notification::make()
                ->title('Tidak Ada Transaksi')
                ->body('Belum ada transaksi yang diselesaikan dalam sesi ini.')
                ->warning()
                ->send();
            return;
        }

        $this->dispatch('print-receipt', saleId: $this->lastSaleId);

        Notification::make()
            ->title('Mencetak Ulang Struk')
            ->body('Permintaan cetak ulang struk berhasil dikirim.')
            ->success()
            ->send();
    }

    public function printLastInvoice()
    {
        if (!$this->lastSaleId) {
            Notification::make()
                ->title('Tidak Ada Transaksi Terakhir')
                ->body('Belum ada transaksi yang diselesaikan dalam sesi ini.')
                ->warning()
                ->send();
            return;
        }

        $url = url('/pos/receipt/' . $this->lastSaleId . '?format=invoice');
        $this->dispatch('open-draft-bill', url: $url);

        Notification::make()
            ->title('Mencetak Invoice Terakhir')
            ->body('Invoice transaksi sebelumnya sedang dipersiapkan untuk dicetak.')
            ->success()
            ->send();
    }

    public function reprintLastInvoice()
    {
        $this->printLastInvoice();
    }

    public function printBill()
    {
        $this->printDraft('bill');
    }

    public function printDraft($format = 'bill')
    {
        $this->resolveHeldTransactionBranchId();
        if (empty($this->cart)) {
            Notification::make()
                ->title('Keranjang Kosong')
                ->body('Tidak ada item untuk dicetak.')
                ->warning()
                ->send();
            return;
        }

        $data = base64_encode(json_encode([
            'invoice_number' => $this->previewInvoiceNumber ?: $this->getPreviewInvoiceNumberProperty(),
            'branch_id' => $this->selectedBranchId,
            'customer_id' => $this->selectedCustomerId,
            'customer_name' => $this->selectedCustomerName,
            'cart' => $this->cart,
            'discount_amount' => $this->discountAmount,
            'tax_amount' => $this->taxAmount,
            'grand_total' => $this->grandTotal,
            'use_points' => $this->usePoints,
            'point_discount_amount' => $this->pointDiscountAmount,
        ]));

        $url = url('/pos/receipt-draft') . '?format=' . $format . '&data=' . urlencode($data);
        $this->dispatch('open-draft-bill', url: $url);

        $titles = [
            'bill' => 'Mencetak Bill Sementara',
            'large' => 'Mencetak Invoice',
            'penawaran' => 'Mencetak Penawaran',
            'tagihan' => 'Mencetak Tagihan',
        ];

        Notification::make()
            ->title($titles[$format] ?? 'Mencetak Bill')
            ->body('Draft sedang dipersiapkan untuk dicetak.')
            ->info()
            ->send();
    }

    public function autoSaveDraft()
    {
        if (empty($this->cart) && !$this->selectedCustomerId) {
            if ($this->currentDraftId) {
                PosHeldTransaction::query()
                    ->where('id', $this->currentDraftId)
                    ->where('user_id', Auth::id())
                    ->where('branch_id', $this->resolveHeldTransactionBranchId())
                    ->delete();
                $this->currentDraftId = null;
                $this->lastSavedDraftHash = null;
            }
            return;
        }

        if (!$this->currentDraftId) {
            $this->currentDraftId = Str::uuid()->toString();
        }

        $currentStateHash = md5(json_encode([
            $this->cart,
            $this->selectedCustomerId,
            $this->discountValue,
            $this->discountType,
            $this->usePoints,
            $this->selectedPricingTierId,
        ]));

        if ($this->lastSavedDraftHash === $currentStateHash) {
            return;
        }
        $this->lastSavedDraftHash = $currentStateHash;

        $branchId = $this->resolveHeldTransactionBranchId();
        $held = PosHeldTransaction::query()
            ->where('id', $this->currentDraftId)
            ->where('user_id', Auth::id())
            ->where('branch_id', $branchId)
            ->first();

        if (!$held) {
            $held = new PosHeldTransaction();
            $held->id = Str::uuid()->toString();
            $this->currentDraftId = $held->id;
        }

        $held->fill([
            'branch_id' => $branchId,
            'user_id' => Auth::id(),
            'customer_id' => $this->selectedCustomerId,
            'customer_name' => $this->selectedCustomerName,
            'cart_data' => $this->cart,
            'discount_amount' => $this->discountAmount,
            'discount_type' => $this->discountType,
            'discount_value' => $this->discountValue,
            'use_points' => $this->usePoints,
            'is_loyalty' => $this->isLoyaltyMember,
            'pricing_tier_id' => $this->selectedPricingTierId,
        ]);
        $held->save();
    }
}
