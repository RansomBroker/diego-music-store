<?php

namespace App\Actions\InventoryMutation;

use App\Models\InventoryMutation;
use App\Models\ProductBranchStock;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;

class DeleteInventoryMutation
{
    /**
     * Execute the action to delete an Inventory Mutation and rollback any stock changes.
     *
     * @param InventoryMutation $mutation
     * @return void
     */
    public function execute(InventoryMutation $mutation): void
    {
        DB::transaction(function () use ($mutation) {
            // 1. Rollback receiver stock if status was 'received'
            if ($mutation->status === 'received') {
                foreach ($mutation->items as $item) {
                    $receiverStock = ProductBranchStock::where([
                        'branch_id' => $mutation->receiver_branch_id,
                        'product_variant_id' => $item->product_variant_id,
                    ])->first();

                    if ($receiverStock) {
                        $receiverStock->decrement('stock', $item->quantity);
                    }
                }
            }

            // 2. Rollback sender stock if status was 'transit' or 'received'
            if (in_array($mutation->status, ['transit', 'received'])) {
                foreach ($mutation->items as $item) {
                    $senderStock = ProductBranchStock::where([
                        'branch_id' => $mutation->sender_branch_id,
                        'product_variant_id' => $item->product_variant_id,
                    ])->first();

                    if ($senderStock) {
                        $senderStock->increment('stock', $item->quantity);
                    }
                }

                // Delete related stock movements
                StockMovement::where('reference_type', 'Mutation')
                    ->where('reference_id', $mutation->id)
                    ->delete();
            }

            // 3. Delete mutation items and mutation record
            $mutation->items()->delete();
            $mutation->delete();
        });
    }
}
