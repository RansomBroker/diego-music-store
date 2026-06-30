<?php

namespace App\Actions\DeliveryOrder;

use App\Models\DeliveryOrder;
use App\Models\ProductBranchStock;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;

class ProcessDeliveryOrderShipped
{
    /**
     * Execute the action to process a shipped/delivered Delivery Order.
     *
     * @param  DeliveryOrder  $deliveryOrder
     * @return void
     */
    public function execute(DeliveryOrder $deliveryOrder): void
    {
        DB::transaction(function () use ($deliveryOrder) {
            foreach ($deliveryOrder->items as $item) {
                // Find or create product branch stock record
                $branchStock = ProductBranchStock::firstOrCreate([
                    'product_variant_id' => $item->product_variant_id,
                    'branch_id' => $deliveryOrder->branch_id,
                ], [
                    'stock' => 0,
                    'hpp' => 0,
                ]);

                $stockLama = $branchStock->stock;
                $qty = $item->quantity;

                // Update stock (decrease because we are shipping out to customer)
                $branchStock->update([
                    'stock' => $stockLama - $qty,
                ]);

                // Record stock movement
                StockMovement::create([
                    'product_variant_id' => $item->product_variant_id,
                    'branch_id' => $deliveryOrder->branch_id,
                    'type' => 'out',
                    'quantity' => $qty,
                    'reference_type' => 'DO',
                    'reference_id' => $deliveryOrder->id,
                ]);
            }
        });
    }
}
