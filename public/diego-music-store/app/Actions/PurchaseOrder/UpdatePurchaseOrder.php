<?php

namespace App\Actions\PurchaseOrder;

use App\Models\PurchaseOrder;
use Illuminate\Support\Facades\DB;

class UpdatePurchaseOrder
{
    /**
     * Execute the action to update a Purchase Order.
     *
     * @param  PurchaseOrder  $purchaseOrder
     * @param  array<string, mixed>  $data
     * @return PurchaseOrder
     */
    public function execute(PurchaseOrder $purchaseOrder, array $data): PurchaseOrder
    {
        return DB::transaction(function () use ($purchaseOrder, $data) {
            $items = $data['items'] ?? [];
            unset($data['items']);

            // Update PO header
            $purchaseOrder->update($data);

            // Recreate items and calculate total amount
            $purchaseOrder->items()->delete();
            $totalAmount = 0;
            foreach ($items as $item) {
                $subtotal = $item['quantity'] * $item['price'];
                $totalAmount += $subtotal;

                $purchaseOrder->items()->create([
                    'product_variant_id' => $item['product_variant_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                ]);
            }

            // Update total amount on the PO header
            $purchaseOrder->update([
                'total_amount' => $totalAmount,
            ]);

            return $purchaseOrder;
        });
    }
}
