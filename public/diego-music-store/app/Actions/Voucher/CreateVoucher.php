<?php

namespace App\Actions\Voucher;

use App\Models\Voucher;
use Illuminate\Support\Str;

class CreateVoucher
{
    /**
     * Create a new voucher.
     *
     * @param  array  $data
     * @return Voucher
     */
    public function execute(array $data): Voucher
    {
        $code = strtoupper(trim($data['code'] ?? Str::random(8)));

        return Voucher::create([
            'code' => $code,
            'name' => $data['name'],
            'type' => $data['type'] ?? 'fixed',
            'value' => floatval($data['value'] ?? 0),
            'min_spend' => floatval($data['min_spend'] ?? 0),
            'valid_until' => !empty($data['valid_until']) ? $data['valid_until'] : null,
            'max_uses' => !empty($data['max_uses']) ? (int)$data['max_uses'] : null,
            'used_count' => 0,
            'is_active' => $data['is_active'] ?? true,
        ]);
    }
}
