<?php

namespace App\Actions\PaymentMethod;

use App\Models\PaymentMethod;
use Illuminate\Support\Facades\DB;
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
        return DB::transaction(function () use ($data) {
            $code = $data['code'] ?? Str::slug($data['name']);
            $parentId = !empty($data['parent_id']) ? (int) $data['parent_id'] : null;
            $isDefault = $parentId !== null && (bool) ($data['is_default'] ?? false);

            if ($isDefault) {
                PaymentMethod::query()
                    ->where('parent_id', $parentId)
                    ->where('is_default', true)
                    ->update(['is_default' => false]);
            }

            return PaymentMethod::create([
                'name' => $data['name'],
                'code' => $code,
                'parent_id' => $parentId,
                'account_id' => $data['account_id'] ?? null,
                'is_active' => $data['is_active'] ?? true,
                'is_default' => $isDefault,
            ]);
        });
    }
}
