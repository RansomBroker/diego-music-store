<?php

namespace App\Livewire\Traits\POS;

use App\Models\CashSession;
use App\Models\Customer;
use App\Models\PaymentMethod;
use App\Models\Sale;
use App\Models\SaleCategory;
use App\Models\PosHeldTransaction;
use App\Models\Voucher;
use App\Actions\Sales\CreatePOSSale;
use App\Actions\Sales\UpdatePOSSale;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\On;

trait WithPaymentHandling
{
    public function openPayment()
    {
        if (empty($this->cart)) {
            Notification::make()
                ->title('Keranjang Kosong')
                ->danger()
                ->send();
            return;
        }
        
        $this->showPaymentModal = true;
    }

    #[On('close-payment-modal')]
    public function closePayment()
    {
        $this->showPaymentModal = false;
    }

    #[On('execute-checkout')]
    public function executeCheckout(array $paymentData, bool $sendWhatsApp = false)
    {
        $selectedPaymentMethods = $paymentData['selectedPaymentMethods'] ?? ['cash'];
        $paymentAmounts = $paymentData['paymentAmounts'] ?? [];
        $amountCash = $paymentData['amountCash'] ?? 0;
        $amountDebit = $paymentData['amountDebit'] ?? 0;
        $amountCredit = $paymentData['amountCredit'] ?? 0;
        $debitRef = $paymentData['debitRef'] ?? '';
        $paymentRefs = $paymentData['paymentRefs'] ?? [];
        $paymentSubMethods = $paymentData['paymentSubMethods'] ?? [];
        $notes = $paymentData['notes'] ?? '';
        $customerPhone = $paymentData['customerPhone'] ?? '';
        $appliedVoucherId = $paymentData['appliedVoucherId'] ?? null;

        $totalPaid = 0;
        foreach ($selectedPaymentMethods as $method) {
            $totalPaid += intval($paymentAmounts[$method] ?? ($method === 'cash' ? $amountCash : ($method === 'debit' ? $amountDebit : ($method === 'credit' ? $amountCredit : 0))));
        }

        $hasCredit = in_array('credit', $selectedPaymentMethods);

        if (!$hasCredit) {
            if (in_array('cash', $selectedPaymentMethods)) {
                if ($totalPaid < $this->grandTotal) {
                    Notification::make()
                        ->title('Jumlah Bayar Kurang')
                        ->body('Total pembayaran kurang dari total tagihan.')
                        ->danger()
                        ->send();
                    return;
                }
            } else {
                if ($totalPaid != $this->grandTotal) {
                    Notification::make()
                        ->title('Jumlah Bayar Tidak Pas')
                        ->body('Pembayaran non-tunai harus pas dengan total tagihan: ' . number_format($this->grandTotal, 0, ',', '.'))
                        ->danger()
                        ->send();
                    return;
                }
            }
        }

        try {
            $activeSession = CashSession::where('branch_id', $this->selectedBranchId)
                ->where('status', 'open')
                ->first();

            if (!$activeSession) {
                throw new \Exception('Sesi kasir aktif tidak ditemukan. Silakan buka sesi terlebih dahulu.');
            }

            $itemsData = [];
            foreach ($this->cart as $c) {
                $itemsData[] = [
                    'variant_id' => $c['variant_id'],
                    'qty' => $c['qty'],
                    'price' => $c['price'],
                    'discount_amount' => $c['discount_amount'] ?? 0,
                    'notes' => $c['notes'] ?? null,
                ];
            }

            // Calculate points to deduct and points discount to apply
            $pointsUsed = 0;
            $pointDiscount = 0;
            if ($this->usePoints && $this->selectedCustomerId && $this->customerPoints > 0) {
                $pointDiscount = $this->pointDiscountAmount;
                $pointsUsed = ceil($pointDiscount / self::POINT_VALUATION);
            }

            // Compile payments data for split payment support
            $paymentsData = [];
            $change = 0;
            $cashPaid = in_array('cash', $selectedPaymentMethods) ? intval($paymentAmounts['cash'] ?? $amountCash) : 0;
            if ($cashPaid > 0) {
                $change = max(0, $totalPaid - $this->grandTotal);
                $netCash = $cashPaid - $change;
                if ($netCash > 0) {
                    $paymentsData[] = [
                        'method' => 'cash',
                        'amount' => $netCash,
                        'ref' => null
                    ];
                }
            }

            foreach ($selectedPaymentMethods as $method) {
                if ($method === 'cash') continue;
                
                $amount = intval($paymentAmounts[$method] ?? ($method === 'debit' ? $amountDebit : ($method === 'credit' ? $amountCredit : 0)));
                if ($amount > 0) {
                    $refValue = $paymentRefs[$method] ?? ($method === 'debit' ? $debitRef : null);
                    $subValue = $paymentSubMethods[$method] ?? null;
                    $combinedRef = trim(($subValue ? "Bank: {$subValue}" : '') . ($refValue ? ($subValue ? ' | ' : '') . "Ref: {$refValue}" : ''));

                    $paymentsData[] = [
                        'method' => $method,
                        'amount' => $amount,
                        'ref' => $combinedRef ?: null
                    ];
                }
            }

            // Compile human-readable payment method name
            $methodNames = [];
            foreach ($selectedPaymentMethods as $m) {
                $amount = intval($paymentAmounts[$m] ?? ($m === 'cash' ? $amountCash : ($m === 'debit' ? $amountDebit : ($m === 'credit' ? $amountCredit : 0))));
                if ($amount > 0) {
                    $dbMethod = PaymentMethod::where('code', $m)->first();
                    $baseName = $dbMethod ? $dbMethod->name : ($m === 'cash' ? 'Tunai' : ($m === 'debit' ? 'Debit Card' : ($m === 'credit' ? 'Piutang' : ucfirst($m))));
                    $subValue = $paymentSubMethods[$m] ?? null;
                    if (!empty($subValue)) {
                        $baseName .= " ({$subValue})";
                    }
                    $methodNames[] = $baseName;
                }
            }
            $paymentMethodString = empty($methodNames) ? 'Tunai' : implode(' & ', $methodNames);

            $isEditing = !empty($this->editingSaleId);

            if ($isEditing) {
                $editingSale = Sale::findOrFail($this->editingSaleId);
                $sale = app(UpdatePOSSale::class)->execute($editingSale, [
                    'customer_id' => $this->selectedCustomerId,
                    'sales_rep_id' => $this->selectedSalesRepId,
                    'invoice_date' => $this->invoiceDate,
                    'payment_method' => $paymentMethodString,
                    'payments' => $paymentsData,
                    'discount_amount' => $this->discountAmount + $pointDiscount,
                    'tax_amount' => $this->taxAmount,
                    'items' => $itemsData,
                    'sale_category' => $this->saleCategory,
                    'notes' => $notes,
                ]);
            } else {
                $sale = app(CreatePOSSale::class)->execute([
                    'branch_id' => $this->selectedBranchId,
                    'cash_session_id' => $activeSession->id,
                    'customer_id' => $this->selectedCustomerId,
                    'sales_rep_id' => $this->selectedSalesRepId,
                    'invoice_date' => $this->invoiceDate,
                    'payment_method' => $paymentMethodString,
                    'payments' => $paymentsData,
                    'discount_amount' => $this->discountAmount + $pointDiscount,
                    'tax_amount' => $this->taxAmount,
                    'items' => $itemsData,
                    'sale_category' => $this->saleCategory,
                    'notes' => $notes,
                ]);
            }

            // Deduct points from database
            if ($pointsUsed > 0) {
                $customer = Customer::find($this->selectedCustomerId);
                if ($customer) {
                    $customer->decrement('loyalty_points', $pointsUsed);
                }
            }

            // Increment used count for applied voucher
            if ($appliedVoucherId) {
                $voucher = Voucher::find($appliedVoucherId);
                if ($voucher) {
                    $voucher->increment('used_count');
                }
            }

            $targetPhone = trim($customerPhone);

            // Reset POS State
            if ($this->currentDraftId) {
                PosHeldTransaction::where('id', $this->currentDraftId)->delete();
                $this->currentDraftId = null;
                $this->lastSavedDraftHash = null;
            }
            $this->editingSaleId = null;
            $this->cart = [];
            $this->clearCustomer();
            $this->enableTax = false;
            $this->taxPercent = 11;
            $this->selectedSalesRepId = Auth::id();
            $this->selectedSalesRepName = Auth::user() ? Auth::user()->name : '';
            $this->salesSearch = '';
            $defaultCategory = SaleCategory::first();
            $this->saleCategory = $defaultCategory ? $defaultCategory->name : 'Store';
            $this->invoiceDate = now()->format('Y-m-d');
            $this->showPaymentModal = false;
            $this->amountPaid = 0;
            $this->usePoints = false;
            
            // Dispatch print event for thermal receipt printing
            $this->lastSaleId = $sale->id;
            $this->dispatch('print-receipt', saleId: $sale->id);

            // Send WhatsApp receipt if requested and phone number is available
            if ($sendWhatsApp && !empty($targetPhone)) {
                $waResult = app(\App\Actions\Notification\SendWhatsAppReceipt::class)->execute($sale, $targetPhone);
                if ($waResult['success']) {
                    Notification::make()
                        ->title('Struk WhatsApp Terkirim')
                        ->body("Bukti struk {$sale->invoice_number} berhasil dikirim ke WhatsApp {$targetPhone}.")
                        ->success()
                        ->send();
                } else {
                    Notification::make()
                        ->title('Gagal Kirim WhatsApp')
                        ->body("Transaksi selesai & struk dicetak, tapi WhatsApp gagal: {$waResult['message']}")
                        ->warning()
                        ->send();
                }
            }

            Notification::make()
                ->title($isEditing ? 'Transaksi Diperbarui' : 'Transaksi Sukses')
                ->body($isEditing ? "Faktur {$sale->invoice_number} berhasil diperbarui." : "Faktur {$sale->invoice_number} berhasil dicatat.")
                ->success()
                ->send();

            if ($isEditing) {
                return redirect()->to('/pos/transactions');
            }

        } catch (\Exception $e) {
            Log::error('POS Checkout Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            Notification::make()
                ->title('Gagal Checkout')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function cancelEdit()
    {
        $this->editingSaleId = null;
        $this->cart = [];
        $this->clearCustomer();
        $this->discountValue = 0;
        $this->enableTax = false;
        
        Notification::make()
            ->title('Edit Transaksi Dibatalkan')
            ->body('Keranjang belanja telah dikosongkan.')
            ->info()
            ->send();
            
        return redirect()->to('/pos');
    }

    public function startNewTransaction()
    {
        $this->showHeldModal = false;
        $this->editingSaleId = null;
        $this->clearCart();
        $this->invoiceDate = now()->format('Y-m-d');
        $this->enableTax = false;
        $this->taxPercent = 11;
        $this->usePoints = false;

        $this->currentDraftId = \Illuminate\Support\Str::uuid()->toString();
        $this->lastSavedDraftHash = null;
    }
}
