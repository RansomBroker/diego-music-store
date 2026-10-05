<?php

namespace App\Actions\FocusProduct;

use App\Models\FocusProductRecommendation;

class DismissRecommendation
{
    /**
     * Dismiss a focus product recommendation.
     *
     * @param int $recommendationId
     * @param int|null $userId
     * @return FocusProductRecommendation
     */
    public function execute(int $recommendationId, ?int $userId = null): FocusProductRecommendation
    {
        $recommendation = FocusProductRecommendation::findOrFail($recommendationId);

        $recommendation->update([
            'status' => 'DISMISSED',
            'dismissed_at' => now(),
            'dismissed_by' => $userId,
        ]);

        return $recommendation;
    }
}
