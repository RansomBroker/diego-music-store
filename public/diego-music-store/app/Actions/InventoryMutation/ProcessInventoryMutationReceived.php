<?php

namespace App\Actions\InventoryMutation;

use App\Models\InventoryMutation;
use App\Models\ProductBranchStock;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ProcessInventoryMutationReceived
{
    /**
     * Execute the transition to set a mutation to Received.
     * Adds stock to receiving branch and records in stock movement.
     *
     * @param  InventoryMutation  $mutation
     * @return void
     */
    public function execute(InventoryMutation $mutation): void
    {
        DB::transaction(function () use ($mutation) {
            // Guard clause: status must be transit
            if ($mutation->status !== 'transit') {
                throw new InvalidArgumentException('Hanya mutasi dengan status transit yang dapat diterima.');
            }

            // Process each item
            foreach ($mutation->items as $item) {
                // Get the HPP from the sending branch to initialize receiving branch if needed
                $senderStock = ProductBranchStock::where([
                    'branch_id' => $mutation->sender_branch_id,
                    'product_variant_id' => $item->product_variant_id,
                ])->first();

                $senderHpp = $senderStock ? $senderStock->hpp : 0;

                // Find or create product branch stock record for the receiver branch
                $receiverStock = ProductBranchStock::firstOrCreate([
                    'branch_id' => $mutation->receiver_branch_id,
                    'product_variant_id' => $item->product_variant_id,
                ], [
                    'stock' => 0,
                    'hpp' => $senderHpp ?: ($item->productVariant->hpp ?? 0),
                ]);

                $oldStock = $receiverStock->stock;
                $oldHpp = $receiverStock->hpp;
                $newStockQty = $oldStock + $item->quantity;

                if ($newStockQty > 0) {
                    $newHpp = (int) round((($oldStock * $oldHpp) + ($item->quantity * $senderHpp)) / $newStockQty);
                } else {
                    $newHpp = $senderHpp;
                }

                // Update stock and HPP in receiver branch
                $receiverStock->update([
                    'stock' => $newStockQty,
                    'hpp' => $newHpp,
                ]);

                // Create StockMovement in record
                StockMovement::create([
                    'product_variant_id' => $item->product_variant_id,
                    'branch_id' => $mutation->receiver_branch_id,
                    'type' => 'in',
                    'quantity' => $item->quantity,
                    'unit_cost' => $senderHpp,
                    'hpp' => $newHpp,
                    'reference_type' => 'Mutation',
                    'reference_id' => $mutation->id,
                ]);
            }

            // Update mutation status
            $mutation->update([
                'status' => 'received',
            ]);
        });
    }
}
