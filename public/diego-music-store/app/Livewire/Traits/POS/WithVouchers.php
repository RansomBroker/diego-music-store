<?php

namespace App\Livewire\Traits\POS;

use App\Models\Voucher;
use Filament\Notifications\Notification;

trait WithVouchers
{
    public function validateAndApplyVoucher(): void
    {
        $code = strtoupper(trim($this->voucherCodeInput));
        if (empty($code)) {
            $this->voucherValidationMessage = 'Masukkan kode voucher.';
            $this->voucherIsValid = false;
            $this->appliedVoucher = null;
            return;
        }

        $voucher = Voucher::where('code', $code)->first();
        if (!$voucher) {
            $this->voucherValidationMessage = 'Kode voucher tidak ditemukan.';
            $this->voucherIsValid = false;
            $this->appliedVoucher = null;
            return;
        }

        $subtotal = floatval($this->subtotal);
        $errorMessage = null;

        if (!$voucher->isValidForSubtotal($subtotal, $errorMessage)) {
            $this->voucherValidationMessage = $errorMessage;
            $this->voucherIsValid = false;
            $this->appliedVoucher = null;
            return;
        }

        $discountAmount = $voucher->calculateDiscountAmount($subtotal);
        $this->appliedVoucher = $voucher;
        $this->voucherIsValid = true;
        $this->voucherValidationMessage = "Voucher {$voucher->code} Valid! Diskon Rp " . number_format($discountAmount, 0, ',', '.');
        
        if (!in_array('voucher', $this->selectedPaymentMethods)) {
            $this->selectedPaymentMethods[] = 'voucher';
        }
        $this->paymentAmounts['voucher'] = $discountAmount;
        $this->paymentRefs['voucher'] = $voucher->code;

        $this->distributePaymentAmounts();

        Notification::make()
            ->title('Voucher Berhasil Diterapkan')
            ->body("Potongan voucher Rp " . number_format($discountAmount, 0, ',', '.') . " telah diterapkan.")
            ->success()
            ->send();
    }
}
