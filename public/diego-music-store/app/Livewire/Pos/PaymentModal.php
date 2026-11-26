<?php

namespace App\Livewire\Pos;

use Livewire\Component;
use App\Models\PaymentMethod;
use App\Models\Voucher;
use Filament\Notifications\Notification;

class PaymentModal extends Component
{
    // Props passed from parent
    public $grandTotal = 0;
    public $subtotal = 0;
    public $discountAmount = 0;
    public $discountType = 'percent';
    public $discountValue = 0;
    public $taxAmount = 0;
    public $pointDiscountAmount = 0;
    public $usePoints = false;
    public $customerPhone = '';
    
    // Modal state
    public $show = true; // Managed by parent's @if, so always true when mounted

    // Payment State
    public $notes = '';
    public $selectedPaymentMethods = ['cash'];
    public $amountCash = 0;
    public $amountDebit = 0;
    public $amountCredit = 0;
    public $debitRef = '';
    public array $paymentAmounts = [];
    public array $paymentRefs = [];
    public array $paymentSubMethods = [];

    // Voucher State
    public string $voucherCodeInput = '';
    public ?Voucher $appliedVoucher = null;
    public string $voucherValidationMessage = '';
    public bool $voucherIsValid = false;

    public function mount()
    {
        $this->amountCash = $this->grandTotal;
        $this->paymentAmounts = [
            'cash' => $this->grandTotal
        ];

        foreach ($this->selectedPaymentMethods as $methodCode) {
            $this->applyDefaultSubMethod($methodCode);
        }
    }

    public function getPaymentMethodsProperty()
    {
        return PaymentMethod::with([
            'children' => fn ($q) => $q->where('is_active', true)->orderByDesc('is_default')->orderBy('id'),
            'parent',
        ])->where('is_active', true)->get();
    }

    protected function applyDefaultSubMethod(string $methodCode): void
    {
        if (! empty($this->paymentSubMethods[$methodCode])) {
            return;
        }

        $parent = $this->paymentMethods->firstWhere('code', $methodCode);
        if (! $parent || $parent->children->isEmpty()) {
            return;
        }

        $defaultChild = $parent->children->firstWhere('is_default', true);
        if ($defaultChild) {
            $this->paymentSubMethods[$methodCode] = $defaultChild->name;
        }
    }

    protected function getNonVoucherSelectedMethods(): array
    {
        return array_values(array_filter(
            $this->selectedPaymentMethods,
            fn ($m) => ! str_contains(strtolower((string) $m), 'voucher')
        ));
    }

    protected function clearMethodState(string $method): void
    {
        if ($method === 'cash') {
            $this->amountCash = 0;
        }
        if ($method === 'debit') {
            $this->amountDebit = 0;
            $this->debitRef = '';
        }
        if ($method === 'credit') {
            $this->amountCredit = 0;
        }

        $this->paymentAmounts[$method] = 0;
        $this->paymentRefs[$method] = '';
        $this->paymentSubMethods[$method] = '';
    }

    protected function setMethodAmount(string $method, int $amount): void
    {
        $this->paymentAmounts[$method] = $amount;
        if ($method === 'cash') {
            $this->amountCash = $amount;
        }
        if ($method === 'debit') {
            $this->amountDebit = $amount;
        }
        if ($method === 'credit') {
            $this->amountCredit = $amount;
        }
    }

    protected function getMethodAmount(string $method): int
    {
        return intval($this->paymentAmounts[$method] ?? ($method === 'cash' ? $this->amountCash : ($method === 'debit' ? $this->amountDebit : ($method === 'credit' ? $this->amountCredit : 0))));
    }

    protected function rebuildSelectedMethods(array $nonVoucherMethods): void
    {
        $voucherMethods = array_values(array_filter(
            $this->selectedPaymentMethods,
            fn ($m) => str_contains(strtolower((string) $m), 'voucher')
        ));

        $this->selectedPaymentMethods = array_values(array_merge($nonVoucherMethods, $voucherMethods));
    }

    public function selectPrimaryPaymentMethod(string $method): void
    {
        $nonVoucher = $this->getNonVoucherSelectedMethods();
        $currentPrimary = $nonVoucher[0] ?? null;
        $currentSecondary = $nonVoucher[1] ?? null;

        if ($method === $currentPrimary || $method === $currentSecondary) {
            return;
        }

        $carriedAmount = $currentPrimary
            ? $this->getMethodAmount($currentPrimary)
            : max(0, (int) $this->grandTotal - (int) ($this->paymentAmounts['voucher'] ?? 0));

        if ($currentPrimary) {
            $this->clearMethodState($currentPrimary);
        }

        $nonVoucher[0] = $method;
        $this->rebuildSelectedMethods($nonVoucher);

        if (count($nonVoucher) === 1) {
            $carriedAmount = max(0, (int) $this->grandTotal - (int) ($this->paymentAmounts['voucher'] ?? 0));
        }

        $this->setMethodAmount($method, $carriedAmount);
        $this->applyDefaultSubMethod($method);
        $this->distributePaymentAmounts();
    }

    public function addSecondaryPaymentMethod(?string $method = null): void
    {
        $nonVoucher = $this->getNonVoucherSelectedMethods();

        if (count($nonVoucher) >= 2) {
            Notification::make()
                ->title('Batas Metode Pembayaran')
                ->body('Anda hanya dapat memilih maksimal 2 metode pembayaran.')
                ->warning()
                ->send();
            return;
        }

        if ($method === null) {
            $parentMethods = $this->paymentMethods->filter(
                fn ($m) => empty($m->parent_id) && ! str_contains(strtolower((string) $m->code), 'voucher')
            );

            $candidate = $parentMethods->first(fn ($m) => ! in_array($m->code, $nonVoucher, true));
            if ($candidate) {
                $method = $candidate->code;
            } else {
                $fallbacks = ['debit', 'cash', 'credit'];
                $method = collect($fallbacks)->first(fn ($c) => ! in_array($c, $nonVoucher, true));
            }
        }

        if (! $method || in_array($method, $nonVoucher, true)) {
            return;
        }

        $existingSum = 0;
        foreach ($this->selectedPaymentMethods as $m) {
            $existingSum += $this->getMethodAmount($m);
        }

        $nonVoucher[] = $method;
        $this->rebuildSelectedMethods($nonVoucher);

        $remaining = max(0, (int) $this->grandTotal - $existingSum);
        $this->setMethodAmount($method, $remaining);
        $this->applyDefaultSubMethod($method);
        $this->distributePaymentAmounts();
    }

    public function selectSecondaryPaymentMethod(string $method): void
    {
        $nonVoucher = $this->getNonVoucherSelectedMethods();
        if (count($nonVoucher) < 2) {
            $this->addSecondaryPaymentMethod($method);
            return;
        }

        $currentPrimary = $nonVoucher[0];
        $currentSecondary = $nonVoucher[1];

        if ($method === $currentPrimary || $method === $currentSecondary) {
            return;
        }

        $carriedAmount = $this->getMethodAmount($currentSecondary);
        $this->clearMethodState($currentSecondary);

        $nonVoucher[1] = $method;
        $this->rebuildSelectedMethods($nonVoucher);

        $this->setMethodAmount($method, $carriedAmount);
        $this->applyDefaultSubMethod($method);
        $this->distributePaymentAmounts();
    }

    public function removeSecondaryPaymentMethod(?string $method = null): void
    {
        $nonVoucher = $this->getNonVoucherSelectedMethods();
        if (count($nonVoucher) < 2) {
            return;
        }

        $target = ($method && in_array($method, $nonVoucher, true)) ? $method : $nonVoucher[1];
        $this->clearMethodState($target);

        $remaining = array_values(array_diff($nonVoucher, [$target]));
        $primary = $remaining[0] ?? 'cash';
        $this->rebuildSelectedMethods([$primary]);

        $fullAmount = max(0, (int) $this->grandTotal - (int) ($this->paymentAmounts['voucher'] ?? 0));
        $this->setMethodAmount($primary, $fullAmount);
        $this->distributePaymentAmounts();
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
                unset($this->paymentSubMethods[$method]);

                if (count($this->selectedPaymentMethods) === 1) {
                    $onlyMethod = $this->selectedPaymentMethods[0];
                    $this->paymentAmounts[$onlyMethod] = $this->grandTotal;
                    if ($onlyMethod === 'cash') $this->amountCash = $this->grandTotal;
                    if ($onlyMethod === 'debit') $this->amountDebit = $this->grandTotal;
                    if ($onlyMethod === 'credit') $this->amountCredit = $this->grandTotal;
                }
            }
        } else {
            $nonVoucherMethodsCount = count(array_filter($this->selectedPaymentMethods, fn($m) => $m !== 'voucher'));
            if ($nonVoucherMethodsCount >= 2) {
                Notification::make()
                    ->title('Batas Metode Pembayaran')
                    ->body('Anda hanya dapat memilih maksimal 2 metode pembayaran.')
                    ->warning()
                    ->send();
                return;
            }

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
            $this->applyDefaultSubMethod($method);
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

    public function closePayment()
    {
        $this->dispatch('close-payment-modal');
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

        $activeAmounts = [];
        $activeRefs = [];
        $activeSubMethods = [];
        foreach ($this->selectedPaymentMethods as $method) {
            $activeAmounts[$method] = $this->getMethodAmount($method);
            if (! empty($this->paymentRefs[$method])) {
                $activeRefs[$method] = $this->paymentRefs[$method];
            }
            if (! empty($this->paymentSubMethods[$method])) {
                $activeSubMethods[$method] = $this->paymentSubMethods[$method];
            }
        }

        // Package payment data
        $paymentData = [
            'selectedPaymentMethods' => $this->selectedPaymentMethods,
            'paymentAmounts' => $activeAmounts,
            'amountCash' => $this->amountCash,
            'amountDebit' => $this->amountDebit,
            'amountCredit' => $this->amountCredit,
            'debitRef' => $this->debitRef,
            'paymentRefs' => $activeRefs,
            'paymentSubMethods' => $activeSubMethods,
            'notes' => $this->notes,
            'customerPhone' => $this->customerPhone,
            'appliedVoucherId' => $this->appliedVoucher ? $this->appliedVoucher->id : null,
        ];

        $this->dispatch('execute-checkout', paymentData: $paymentData, sendWhatsApp: $sendWhatsApp);
    }

    public function render()
    {
        return view('livewire.pos.payment-modal');
    }
}
