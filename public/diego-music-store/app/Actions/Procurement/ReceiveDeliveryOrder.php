<?php

namespace App\Actions\Procurement;

use App\Models\DeliveryOrder;
use App\Models\ProductBranchStock;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ReceiveDeliveryOrder
{
    /**
     * Execute the action to mark a Delivery Order as received and update stock/HPP.
     *
     * @param  DeliveryOrder  $deliveryOrder
     * @return DeliveryOrder
     */
    public function execute(DeliveryOrder $deliveryOrder): DeliveryOrder
    {
        return DB::transaction(function () use ($deliveryOrder) {
            // Check if already received
            if ($deliveryOrder->status === 'received') {
                throw new InvalidArgumentException('Delivery Order ini sudah diterima.');
            }

            // Calculate total quantity received across all items
            $totalQtyReceived = $deliveryOrder->items->sum('quantity_received');
            
            // Calculate shipping cost per unit
            $shippingCostPerUnit = 0;
            if ($totalQtyReceived > 0 && $deliveryOrder->shipping_cost > 0) {
                $shippingCostPerUnit = (int) ($deliveryOrder->shipping_cost / $totalQtyReceived);
            }

            // Load the associated Purchase Order items for price reference
            $poItems = $deliveryOrder->purchaseOrder->items->keyBy('product_variant_id');

            // Process each item
            foreach ($deliveryOrder->items as $item) {
                $variantId = $item->product_variant_id;
                $branchId = $deliveryOrder->branch_id;
                $qtyReceived = $item->quantity_received;

                // 1. Get or create the branch stock record
                $branchStock = ProductBranchStock::firstOrCreate(
                    [
                        'product_variant_id' => $variantId,
                        'branch_id' => $branchId,
                    ],
                    [
                        'stock' => 0,
                        'hpp' => 0,
                    ]
                );

                $stockLama = $branchStock->stock;
                
                // If current branch hpp is 0, fall back to variant cost_price or price
                $hppLama = $branchStock->hpp;
                if ($hppLama <= 0) {
                    $variant = $item->productVariant;
                    $hppLama = $variant->cost_price > 0 ? $variant->cost_price : ($variant->price > 0 ? $variant->price : 0);
                }

                // Get purchase price from PO or fallback to variant cost_price
                $poItem = $poItems->get($variantId);
                $hargaBeli = $poItem ? $poItem->price : $item->productVariant->cost_price;

                // 2. Compute the new HPP using Weighted Average Formula
                $newHpp = $hppLama;
                $totalStockBaru = $stockLama + $qtyReceived;
                if ($totalStockBaru > 0) {
                    $totalCostLama = $stockLama * $hppLama;
                    $totalCostBaru = $qtyReceived * ($hargaBeli + $shippingCostPerUnit);
                    $newHpp = (int) (($totalCostLama + $totalCostBaru) / $totalStockBaru);
                }

                // 3. Update stock and HPP
                $branchStock->update([
                    'stock' => $stockLama + $qtyReceived,
                    'hpp' => $newHpp,
                ]);
            }

            // Update DO status and save
            $deliveryOrder->update([
                'status' => 'received',
            ]);

            return $deliveryOrder;
        });
    }
}
