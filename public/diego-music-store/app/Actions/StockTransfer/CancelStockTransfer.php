<?php

namespace App\Actions\StockTransfer;

use App\Models\StockTransfer;
use Exception;
use Illuminate\Support\Facades\DB;

class CancelStockTransfer
{
    /**
     * Cancel transfer from DRAFT or PENDING to CANCELLED.
     */
    public function execute(StockTransfer $transfer): StockTransfer
    {
        if (!in_array($transfer->status, ['DRAFT', 'PENDING'])) {
            throw new Exception("Hanya transfer berstatus DRAFT atau PENDING yang dapat dibatalkan.");
        }

        return DB::transaction(function () use ($transfer) {
            $transfer->update([
                'status' => 'CANCELLED',
                'cancelled_at' => now(),
            ]);

            return $transfer;
        });
    }
}
