<?php

namespace App\Actions\Voucher;

use App\Models\Voucher;

class UpdateVoucher
{
    /**
     * Update an existing voucher.
     *
     * @param  Voucher  $voucher
     * @param  array  $data
     * @return Voucher
     */
    public function execute(Voucher $voucher, array $data): Voucher
    {
        $code = strtoupper(trim($data['code'] ?? $voucher->code));

        $voucher->update([
            'code' => $code,
            'name' => $data['name'],
            'type' => $data['type'] ?? 'fixed',
            'value' => floatval($data['value'] ?? 0),
            'min_spend' => floatval($data['min_spend'] ?? 0),
            'valid_until' => !empty($data['valid_until']) ? $data['valid_until'] : null,
            'max_uses' => !empty($data['max_uses']) ? (int)$data['max_uses'] : null,
            'is_active' => $data['is_active'] ?? true,
        ]);

        return $voucher;
    }
}
