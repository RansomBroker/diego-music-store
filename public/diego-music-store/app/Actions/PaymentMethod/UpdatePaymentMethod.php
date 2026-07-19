<?php

namespace App\Actions\PaymentMethod;

use App\Models\PaymentMethod;
use Illuminate\Support\Str;

class UpdatePaymentMethod
{
    /**
     * Update an existing payment method.
     *
     * @param  PaymentMethod  $paymentMethod
     * @param  array  $data
     * @return PaymentMethod
     */
    public function execute(PaymentMethod $paymentMethod, array $data): PaymentMethod
    {
        $code = $data['code'] ?? Str::slug($data['name']);

        $paymentMethod->update([
            'name' => $data['name'],
            'code' => $code,
            'account_id' => $data['account_id'] ?? null,
            'is_active' => $data['is_active'] ?? true,
        ]);

        return $paymentMethod;
    }
}
