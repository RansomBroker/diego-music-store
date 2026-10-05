<?php

namespace App\Actions\FocusProduct;

use App\Models\FocusProduct;

class ExpireFocusProducts
{
    /**
     * Mark all active focus products whose active_until has passed as EXPIRED.
     *
     * @return int Number of focus products expired
     */
    public function execute(): int
    {
        return FocusProduct::where('status', 'ACTIVE')
            ->whereNotNull('active_until')
            ->where('active_until', '<', now())
            ->update([
                'status' => 'EXPIRED',
            ]);
    }
}
