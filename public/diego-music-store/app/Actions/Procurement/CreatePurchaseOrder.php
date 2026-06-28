<?php

namespace App\Actions\Procurement;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
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
            
            // Calculate total amount
            $totalAmount = 0;
            foreach ($items as $item) {
                $totalAmount += intval($item['quantity'] ?? 0) * intval($item['price'] ?? 0);
            }

            // Create PO
            $po = PurchaseOrder::create([
                'supplier_id' => $data['supplier_id'],
                'po_number' => $data['po_number'],
                'order_date' => $data['order_date'],
                'status' => $data['status'] ?? 'draft',
                'total_amount' => $totalAmount,
                'notes' => $data['notes'] ?? null,
            ]);

            // Create PO Items
            foreach ($items as $item) {
                PurchaseOrderItem::create([
                    'purchase_order_id' => $po->id,
                    'product_variant_id' => $item['product_variant_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                ]);
            }

            return $po;
        });
    }
}
