<?php

namespace App\Actions\FocusProduct;

use App\Models\FocusProduct;
use DomainException;
use Illuminate\Support\Facades\DB;

class CreateManualFocusProduct
{
    /**
     * Create a manual focus product.
     *
     * @param array{
     *     branch_id: int,
     *     product_variant_id: int,
     *     reason: string,
     *     note?: ?string,
     *     active_until?: ?string,
     *     user_id?: ?int
     * } $data
     * @return FocusProduct
     * @throws DomainException
     */
    public function execute(array $data): FocusProduct
    {
        $branchId = (int) $data['branch_id'];
        $variantId = (int) $data['product_variant_id'];

        // Cek constraint: Hanya boleh 1 status ACTIVE per kombinasi cabang & varian produk
        $existingActive = FocusProduct::where('branch_id', $branchId)
            ->where('product_variant_id', $variantId)
            ->where('status', 'ACTIVE')
            ->first();

        if ($existingActive) {
            throw new DomainException("Produk ini sudah aktif sebagai Produk Fokus ({$existingActive->focus_number}) di cabang yang dipilih.");
        }

        return DB::transaction(function () use ($data, $branchId, $variantId) {
            return FocusProduct::create([
                'branch_id' => $branchId,
                'product_variant_id' => $variantId,
                'source' => 'MANUAL',
                'rule_id' => $data['rule_id'] ?? null,
                'recommendation_id' => null,
                'status' => 'ACTIVE',
                'reason' => $data['reason'],
                'note' => $data['note'] ?? null,
                'active_from' => now(),
                'active_until' => !empty($data['active_until']) ? $data['active_until'] : null,
                'focused_at' => now(),
                'focused_by' => $data['user_id'] ?? null,
            ]);
        });
    }
}
