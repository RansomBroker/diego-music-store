<?php

namespace App\Livewire\Traits\POS;

use App\Models\CashSession;
use App\Models\Customer;
use App\Models\PaymentMethod;
use App\Models\Sale;
use App\Models\SaleCategory;
use App\Models\PosHeldTransaction;
use App\Actions\Sales\CreatePOSSale;
use App\Actions\Sales\UpdatePOSSale;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

trait WithPaymentHandling
{
    public function getPaymentMethodsProperty()
    {
        return \App\Models\PaymentMethod::with('children')->get();
    }

    public function openPayment()
    {
        if (empty($this->cart)) {
            Notification::make()
                ->title('Keranjang Kosong')
                ->danger()
                ->send();
            return;
        }
        
        $this->selectedPaymentMethods = ['cash'];
        $this->amountCash = $this->grandTotal;
        $this->amountDebit = 0;
        $this->amountCredit = 0;
        $this->debitRef = '';
        $this->amountPaid = $this->grandTotal;
        
        $this->paymentAmounts = [
            'cash' => $this->grandTotal
        ];
        $this->paymentRefs = [];
        $this->notes = '';
        
        $this->showPaymentModal = true;
    }

    public function closePayment()
    {
        $this->showPaymentModal = false;
    }

    public function togglePaymentMethod($method)
    {
        if (in_array($method, $this->selectedPaymentMethods)) {
            if (count($this->selectedPaymentMethods) > 1) {
                $this->selectedPaymentMethods = array_values(array_diff($this->selectedPaymentMethods, [$method]));
                if ($method === 'cash') $this->amountCash = 0;
                if ($method === 'debit') {
                    $this->amountDebit = 0;
                    $this->debitRef = '';
                }
                if ($method === 'credit') $this->amountCredit = 0;
                
                unset($this->paymentAmounts[$method]);
                unset($this->paymentRefs[$method]);

                if (count($this->selectedPaymentMethods) === 1) {
                    $onlyMethod = $this->selectedPaymentMethods[0];
                    $this->paymentAmounts[$onlyMethod] = $this->grandTotal;
                    if ($onlyMethod === 'cash') $this->amountCash = $this->grandTotal;
                    if ($onlyMethod === 'debit') $this->amountDebit = $this->grandTotal;
                    if ($onlyMethod === 'credit') $this->amountCredit = $this->grandTotal;
                }
            }
        } else {
            $this->selectedPaymentMethods[] = $method;
            $existingSum = 0;
            foreach ($this->selectedPaymentMethods as $m) {
                if ($m !== $method) {
                    $existingSum += intval($this->paymentAmounts[$m] ?? ($m === 'cash' ? $this->amountCash : ($m === 'debit' ? $this->amountDebit : ($m === 'credit' ? $this->amountCredit : 0))));
                }
            }
            $remaining = max(0, $this->grandTotal - $existingSum);
            $this->paymentAmounts[$method] = $remaining;
            if ($method === 'cash') $this->amountCash = $remaining;
            if ($method === 'debit') $this->amountDebit = $remaining;
            if ($method === 'credit') $this->amountCredit = $remaining;
        }

        $this->distributePaymentAmounts();
    }

    public function distributePaymentAmounts()
    {
        $this->amountCash = intval(\App\Helpers\FormatHelper::parseRupiah($this->paymentAmounts['cash'] ?? $this->amountCash));
        $this->amountDebit = intval(\App\Helpers\FormatHelper::parseRupiah($this->paymentAmounts['debit'] ?? $this->amountDebit));
        $this->amountCredit = intval(\App\Helpers\FormatHelper::parseRupiah($this->paymentAmounts['credit'] ?? $this->amountCredit));
    }

    public function updated($property, $value)
    {
        if (str_starts_with($property, 'paymentAmounts.')) {
            $code = str_replace('paymentAmounts.', '', $property);
            $parsed = intval(\App\Helpers\FormatHelper::parseRupiah($value));
            if ($code === 'cash') $this->amountCash = $parsed;
            if ($code === 'debit') $this->amountDebit = $parsed;
            if ($code === 'credit') $this->amountCredit = $parsed;

            $this->distributePaymentAmounts();
            $this->autoBalancePayment($code, $parsed);
        }

        if ($property === 'amountCash') {
            $parsed = intval(\App\Helpers\FormatHelper::parseRupiah($value));
            $this->paymentAmounts['cash'] = $parsed;
            $this->distributePaymentAmounts();
            $this->autoBalancePayment('cash', $parsed);
        }
        if ($property === 'amountDebit') {
            $parsed = intval(\App\Helpers\FormatHelper::parseRupiah($value));
            $this->paymentAmounts['debit'] = $parsed;
            $this->distributePaymentAmounts();
            $this->autoBalancePayment('debit', $parsed);
        }
        if ($property === 'amountCredit') {
            $parsed = intval(\App\Helpers\FormatHelper::parseRupiah($value));
            $this->paymentAmounts['credit'] = $parsed;
            $this->distributePaymentAmounts();
            $this->autoBalancePayment('credit', $parsed);
        }
    }

    protected function autoBalancePayment($changedMethod, $parsedValue)
    {
        if (count($this->selectedPaymentMethods) === 2) {
            $otherMethods = array_values(array_filter($this->selectedPaymentMethods, fn($m) => $m !== $changedMethod));
            $otherMethod = $otherMethods[0];
            
            $remaining = max(0, $this->grandTotal - $parsedValue);
            
            $this->paymentAmounts[$otherMethod] = $remaining;
            if ($otherMethod === 'cash') $this->amountCash = $remaining;
            if ($otherMethod === 'debit') $this->amountDebit = $remaining;
            if ($otherMethod === 'credit') $this->amountCredit = $remaining;
            
            $this->distributePaymentAmounts();
        }
    }

    public function checkout(bool $sendWhatsApp = false)
    {
        $totalPaid = 0;
        foreach ($this->selectedPaymentMethods as $method) {
            $totalPaid += intval($this->paymentAmounts[$method] ?? ($method === 'cash' ? $this->amountCash : ($method === 'debit' ? $this->amountDebit : ($method === 'credit' ? $this->amountCredit : 0))));
        }

        $hasCredit = in_array('credit', $this->selectedPaymentMethods);

        if (!$hasCredit) {
            if (in_array('cash', $this->selectedPaymentMethods)) {
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
        } else {
            // Auto-fill or adjust credit amount with remaining balance if less than grand total
            $currentCredit = intval($this->paymentAmounts['credit'] ?? $this->amountCredit);
            if ($totalPaid < $this->grandTotal) {
                $deficit = $this->grandTotal - $totalPaid;
                $newCredit = $currentCredit + $deficit;
                $this->paymentAmounts['credit'] = $newCredit;
                $this->amountCredit = $newCredit;
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
            $cashPaid = in_array('cash', $this->selectedPaymentMethods) ? intval($this->paymentAmounts['cash'] ?? $this->amountCash) : 0;
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

            foreach ($this->selectedPaymentMethods as $method) {
                if ($method === 'cash') continue;
                
                $amount = intval($this->paymentAmounts[$method] ?? ($method === 'debit' ? $this->amountDebit : ($method === 'credit' ? $this->amountCredit : 0)));
                if ($amount > 0) {
                    $refValue = $this->paymentRefs[$method] ?? ($method === 'debit' ? $this->debitRef : null);
                    $subValue = $this->paymentSubMethods[$method] ?? null;
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
            foreach ($this->selectedPaymentMethods as $m) {
                $amount = intval($this->paymentAmounts[$m] ?? ($m === 'cash' ? $this->amountCash : ($m === 'debit' ? $this->amountDebit : ($m === 'credit' ? $this->amountCredit : 0))));
                if ($amount > 0) {
                    $dbMethod = PaymentMethod::where('code', $m)->first();
                    $baseName = $dbMethod ? $dbMethod->name : ($m === 'cash' ? 'Tunai' : ($m === 'debit' ? 'Debit Card' : ($m === 'credit' ? 'Piutang' : ucfirst($m))));
                    $subValue = $this->paymentSubMethods[$m] ?? null;
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
                    'notes' => $this->notes,
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
                    'notes' => $this->notes,
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
            if ($this->appliedVoucher) {
                $this->appliedVoucher->increment('used_count');
                $this->appliedVoucher = null;
                $this->voucherCodeInput = '';
                $this->voucherValidationMessage = '';
                $this->voucherIsValid = false;
            }

            $targetPhone = trim($this->customerPhone);

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
            
            // Reset Split Payment state
            $this->selectedPaymentMethods = ['cash'];
            $this->amountCash = 0;
            $this->amountDebit = 0;
            $this->amountCredit = 0;
            $this->debitRef = '';
            $this->paymentAmounts = [];
            $this->paymentRefs = [];

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
        $this->selectedPaymentMethods = ['cash'];
        $this->amountCash = 0;
        $this->amountDebit = 0;
        $this->amountCredit = 0;
        $this->debitRef = '';
        $this->paymentAmounts = [];
        $this->paymentRefs = [];
        $this->appliedVoucher = null;
        $this->voucherCodeInput = '';
        $this->voucherValidationMessage = '';
        $this->voucherIsValid = false;

        $this->currentDraftId = \Illuminate\Support\Str::uuid()->toString();
        $this->lastSavedDraftHash = null;
    }
}
