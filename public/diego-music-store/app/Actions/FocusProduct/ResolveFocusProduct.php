<?php

namespace App\Actions\FocusProduct;

use App\Models\FocusProduct;
use DomainException;

class ResolveFocusProduct
{
    /**
     * Mark a focus product as RESOLVED.
     *
     * @param int $focusProductId
     * @param string $resolutionNote
     * @param int|null $userId
     * @return FocusProduct
     * @throws DomainException
     */
    public function execute(int $focusProductId, string $resolutionNote, ?int $userId = null): FocusProduct
    {
        $focusProduct = FocusProduct::findOrFail($focusProductId);

        if ($focusProduct->status === 'RESOLVED') {
            throw new DomainException("Produk Fokus ({$focusProduct->focus_number}) sudah ditandai selesai sebelumnya.");
        }

        $focusProduct->update([
            'status' => 'RESOLVED',
            'resolved_at' => now(),
            'resolved_by' => $userId,
            'resolution_note' => $resolutionNote,
        ]);

        return $focusProduct;
    }
}
