<?php

namespace App\Actions\StockTransfer;

use App\Models\StockTransfer;
use Exception;
use Illuminate\Support\Facades\DB;

class SubmitStockTransfer
{
    /**
     * Submit transfer from DRAFT to PENDING.
     */
    public function execute(StockTransfer $transfer): StockTransfer
    {
        if ($transfer->status !== 'DRAFT') {
            throw new Exception("Hanya transfer dengan status DRAFT yang dapat diajukan.");
        }

        if ($transfer->items()->count() === 0) {
            throw new Exception("Transfer harus memiliki minimal satu barang sebelum diajukan.");
        }

        return DB::transaction(function () use ($transfer) {
            $transfer->update([
                'status' => 'PENDING',
                'submitted_at' => now(),
            ]);

            return $transfer;
        });
    }
}
