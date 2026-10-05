<?php

namespace App\Actions\StockTransfer;

use App\Models\StockTransfer;
use Exception;
use Illuminate\Support\Facades\DB;

class ApproveStockTransfer
{
    /**
     * Approve transfer from PENDING to APPROVED.
     */
    public function execute(StockTransfer $transfer): StockTransfer
    {
        if ($transfer->status !== 'PENDING') {
            throw new Exception("Hanya transfer dengan status PENDING yang dapat disetujui (approve).");
        }

        return DB::transaction(function () use ($transfer) {
            $transfer->update([
                'status' => 'APPROVED',
                'approved_at' => now(),
            ]);

            return $transfer;
        });
    }
}
