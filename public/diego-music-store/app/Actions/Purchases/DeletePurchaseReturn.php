<?php

namespace App\Actions\Purchases;

use App\Models\PurchaseReturn;
use Illuminate\Support\Facades\DB;

class DeletePurchaseReturn
{
    /**
     * Delete a draft Purchase Return and its items.
     *
     * @param  PurchaseReturn  $purchaseReturn
     * @return bool
     */
    public function execute(PurchaseReturn $purchaseReturn): bool
    {
        return DB::transaction(function () use ($purchaseReturn) {
            if ($purchaseReturn->status === 'posted') {
                throw new \Exception('Tidak dapat menghapus data retur pembelian yang sudah berstatus Posted.');
            }

            $purchaseReturn->items()->delete();
            return (bool) $purchaseReturn->delete();
        });
    }
}
