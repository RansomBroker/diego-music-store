<?php

namespace App\Actions\PaymentMethod;

use App\Models\PaymentMethod;
use Illuminate\Support\Str;

class CreatePaymentMethod
{
    /**
     * Create a new payment method.
     *
     * @param  array  $data
     * @return PaymentMethod
     */
    public function execute(array $data): PaymentMethod
    {
        $code = $data['code'] ?? Str::slug($data['name']);
        
        return PaymentMethod::create([
            'name' => $data['name'],
            'code' => $code,
            'account_id' => $data['account_id'] ?? null,
            'is_active' => $data['is_active'] ?? true,
        ]);
    }
}
