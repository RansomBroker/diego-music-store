<?php

namespace App\Actions\InventoryMutation;

use App\Models\InventoryMutation;
use App\Models\ProductBranchStock;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ProcessInventoryMutationTransit
{
    /**
     * Execute the transition to set a mutation to Transit.
     * Deducts stock from sending branch and records out stock movement.
     *
     * @param  InventoryMutation  $mutation
     * @return void
     */
    public function execute(InventoryMutation $mutation): void
    {
        DB::transaction(function () use ($mutation) {
            // Guard clause: status must be draft
            if ($mutation->status !== 'draft') {
                throw new InvalidArgumentException('Hanya mutasi dengan status draft yang dapat di-transit.');
            }

            // Verify each item's stock in the sending branch
            foreach ($mutation->items as $item) {
                $senderStock = ProductBranchStock::where([
                    'branch_id' => $mutation->sender_branch_id,
                    'product_variant_id' => $item->product_variant_id,
                ])->first();

                $availableStock = $senderStock ? $senderStock->stock : 0;

                if ($availableStock < $item->quantity) {
                    $variantName = $item->productVariant->name ?? 'Default';
                    $productName = $item->productVariant->product->name ?? 'Produk';
                    $fullName = "{$productName} ({$variantName})";

                    throw new InvalidArgumentException("Stok tidak mencukupi di cabang pengirim untuk: {$fullName}. Tersedia: {$availableStock}, Diminta: {$item->quantity}");
                }

                // Decrement stock in sender branch
                $senderStock->decrement('stock', $item->quantity);

                // Create StockMovement out record
                StockMovement::create([
                    'product_variant_id' => $item->product_variant_id,
                    'branch_id' => $mutation->sender_branch_id,
                    'type' => 'out',
                    'quantity' => $item->quantity,
                    'unit_cost' => $senderStock->hpp,
                    'hpp' => $senderStock->hpp,
                    'reference_type' => 'Mutation',
                    'reference_id' => $mutation->id,
                ]);
            }

            // Update mutation status
            $mutation->update([
                'status' => 'transit',
            ]);
        });
    }
}
