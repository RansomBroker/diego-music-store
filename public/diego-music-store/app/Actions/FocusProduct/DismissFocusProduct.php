<?php

namespace App\Actions\FocusProduct;

use App\Models\FocusProduct;
use DomainException;

class DismissFocusProduct
{
    /**
     * Dismiss an active or expired focus product.
     *
     * @param int $focusProductId
     * @param string|null $dismissalNote
     * @param int|null $userId
     * @return FocusProduct
     * @throws DomainException
     */
    public function execute(int $focusProductId, ?string $dismissalNote = null, ?int $userId = null): FocusProduct
    {
        $focusProduct = FocusProduct::findOrFail($focusProductId);

        if ($focusProduct->status === 'DISMISSED') {
            throw new DomainException("Produk Fokus ({$focusProduct->focus_number}) sudah diabaikan sebelumnya.");
        }

        $focusProduct->update([
            'status' => 'DISMISSED',
            'dismissed_at' => now(),
            'dismissed_by' => $userId,
            'dismissal_note' => $dismissalNote,
        ]);

        return $focusProduct;
    }
}
