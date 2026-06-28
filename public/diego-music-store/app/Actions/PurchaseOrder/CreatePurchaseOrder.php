<?php

namespace App\Actions\PurchaseOrder;

use App\Models\PurchaseOrder;
use Illuminate\Support\Facades\DB;

class CreatePurchaseOrder
{
    /**
     * Execute the action to create a Purchase Order.
     *
     * @param  array<string, mixed>  $data
     * @return PurchaseOrder
     */
    public function execute(array $data): PurchaseOrder
    {
        return DB::transaction(function () use ($data) {
            $items = $data['items'] ?? [];
            unset($data['items']);

            // Create PO
            $purchaseOrder = PurchaseOrder::create($data);

            // Create items and calculate total amount
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
