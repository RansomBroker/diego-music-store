<?php

namespace App\Actions\FocusProduct;

use App\Models\FocusProduct;
use App\Models\FocusProductRecommendation;
use DomainException;
use Illuminate\Support\Facades\DB;

class AcceptFocusRecommendation
{
    /**
     * Accept a focus recommendation and create an ACTIVE FocusProduct.
     *
     * @param int $recommendationId
     * @param string|null $activeUntil
     * @param string|null $note
     * @param int|null $userId
     * @return FocusProduct
     * @throws DomainException
     */
    public function execute(
        int $recommendationId,
        ?string $activeUntil = null,
        ?string $note = null,
        ?int $userId = null
    ): FocusProduct {
        $recommendation = FocusProductRecommendation::findOrFail($recommendationId);

        // Validasi jika sudah ada focus ACTIVE untuk cabang & variant ini
        $existingActive = FocusProduct::where('branch_id', $recommendation->branch_id)
            ->where('product_variant_id', $recommendation->product_variant_id)
            ->where('status', 'ACTIVE')
            ->first();

        if ($existingActive) {
            // Tandai rekomendasi sebagai ACCEPTED jika belum
            $recommendation->update(['status' => 'ACCEPTED']);
            throw new DomainException("Produk ini sudah aktif sebagai Produk Fokus ({$existingActive->focus_number}) di cabang terkait.");
        }

        return DB::transaction(function () use ($recommendation, $activeUntil, $note, $userId) {
            $recommendation->update([
                'status' => 'ACCEPTED',
            ]);

            return FocusProduct::create([
                'branch_id' => $recommendation->branch_id,
                'product_variant_id' => $recommendation->product_variant_id,
                'source' => 'RULE',
                'rule_id' => $recommendation->rule_id,
                'recommendation_id' => $recommendation->id,
                'status' => 'ACTIVE',
                'reason' => $recommendation->reason,
                'note' => $note,
                'active_from' => now(),
                'active_until' => $activeUntil,
                'focused_at' => now(),
                'focused_by' => $userId,
            ]);
        });
    }
}
