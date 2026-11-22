<?php

namespace App\Actions\PaymentMethod;

use App\Models\PaymentMethod;
use Illuminate\Support\Facades\DB;
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
        return DB::transaction(function () use ($paymentMethod, $data) {
            $code = $data['code'] ?? $paymentMethod->code ?? Str::slug($data['name']);
            $parentId = array_key_exists('parent_id', $data)
                ? (!empty($data['parent_id']) ? (int) $data['parent_id'] : null)
                : $paymentMethod->parent_id;

            $isDefault = array_key_exists('is_default', $data)
                ? (bool) $data['is_default']
                : (bool) $paymentMethod->is_default;
            $isDefault = $parentId !== null && $isDefault;

            if ($isDefault) {
                PaymentMethod::query()
                    ->where('parent_id', $parentId)
                    ->whereKeyNot($paymentMethod->id)
                    ->where('is_default', true)
                    ->update(['is_default' => false]);
            }

            $paymentMethod->update([
                'name' => $data['name'] ?? $paymentMethod->name,
                'code' => $code,
                'parent_id' => $parentId,
                'account_id' => array_key_exists('account_id', $data) ? $data['account_id'] : $paymentMethod->account_id,
                'is_active' => array_key_exists('is_active', $data) ? $data['is_active'] : $paymentMethod->is_active,
                'is_default' => $isDefault,
            ]);

            return $paymentMethod;
        });
    }
}
