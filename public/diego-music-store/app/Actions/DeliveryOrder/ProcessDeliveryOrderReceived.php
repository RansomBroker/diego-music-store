<?php

namespace App\Actions\DeliveryOrder;

use App\Models\DeliveryOrder;
use App\Models\DeliveryOrderItem;
use App\Models\ProductBranchStock;
use App\Models\PurchaseOrderItem;
use Illuminate\Support\Facades\DB;

class ProcessDeliveryOrderReceived
{
    /**
     * Execute the action to process a received Delivery Order.
     *
     * @param  DeliveryOrder  $deliveryOrder
     * @return void
     */
    public function execute(DeliveryOrder $deliveryOrder): void
    {
        DB::transaction(function () use ($deliveryOrder) {
            $totalQtyReceived = $deliveryOrder->items->sum('quantity_received');
            $shippingCostPerUnit = $totalQtyReceived > 0 
                ? (int) floor($deliveryOrder->shipping_cost / $totalQtyReceived) 
                : 0;

            foreach ($deliveryOrder->items as $item) {
                // Get the purchase price from PO
                $poItem = PurchaseOrderItem::where('purchase_order_id', $deliveryOrder->purchase_order_id)
                    ->where('product_variant_id', $item->product_variant_id)
                    ->first();
                
                $purchasePrice = $poItem ? $poItem->price : 0;
                $unitCost = $purchasePrice + $shippingCostPerUnit;

                // Find or create product branch stock record
                $branchStock = ProductBranchStock::firstOrCreate([
                    'product_variant_id' => $item->product_variant_id,
                    'branch_id' => $deliveryOrder->branch_id,
                ], [
                    'stock' => 0,
                    'hpp' => 0,
                ]);

                $stockLama = $branchStock->stock;
                $hppLama = $branchStock->hpp;
                $qtyBaru = $item->quantity_received;

                // Calculate Weighted Average HPP per Branch
                if ($stockLama + $qtyBaru > 0) {
                    $newHpp = (($stockLama * $hppLama) + ($qtyBaru * $unitCost)) / ($stockLama + $qtyBaru);
                    $newHpp = (int) round($newHpp);
                } else {
                    $newHpp = $unitCost;
                }

                // Update stock and HPP
                $branchStock->update([
                    'stock' => $stockLama + $qtyBaru,
                    'hpp' => $newHpp,
                ]);
            }

            // Update Purchase Order status based on fulfillment
            $po = $deliveryOrder->purchaseOrder;
            if ($po) {
                $allPoReceived = true;
                foreach ($po->items as $poItem) {
                    $totalReceivedForVariant = DeliveryOrderItem::whereHas('deliveryOrder', function ($query) use ($po) {
                        $query->where('purchase_order_id', $po->id)->where('status', 'received');
                    })
                    ->where('product_variant_id', $poItem->product_variant_id)
                    ->sum('quantity_received');

                    if ($totalReceivedForVariant < $poItem->quantity) {
                        $allPoReceived = false;
                        break;
                    }
                }

                $po->update([
                    'status' => $allPoReceived ? 'closed' : 'approved',
                ]);
            }
        });
    }
}
