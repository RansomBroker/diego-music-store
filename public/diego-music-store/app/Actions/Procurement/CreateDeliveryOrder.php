<?php

namespace App\Actions\Procurement;

use App\Models\DeliveryOrder;
use App\Models\DeliveryOrderItem;
use Illuminate\Support\Facades\DB;

class CreateDeliveryOrder
{
    /**
     * Execute the action to create a Delivery Order.
     *
     * @param  array<string, mixed>  $data
     * @return DeliveryOrder
     */
    public function execute(array $data): DeliveryOrder
    {
        return DB::transaction(function () use ($data) {
            // Create Delivery Order in draft status first
            $do = DeliveryOrder::create([
                'purchase_order_id' => $data['purchase_order_id'],
                'branch_id' => $data['branch_id'],
                'do_number' => $data['do_number'],
                'received_date' => $data['received_date'],
                'status' => 'draft',
                'shipping_cost' => $data['shipping_cost'] ?? 0,
                'notes' => $data['notes'] ?? null,
            ]);

            // Save items
            $items = $data['items'] ?? [];
            foreach ($items as $item) {
                DeliveryOrderItem::create([
                    'delivery_order_id' => $do->id,
                    'product_variant_id' => $item['product_variant_id'],
                    'quantity_ordered' => $item['quantity_ordered'],
                    'quantity_received' => $item['quantity_received'] ?? 0,
                ]);
            }

            // If the status is specified as received, execute the ReceiveDeliveryOrder action
            if (($data['status'] ?? 'draft') === 'received') {
                app(ReceiveDeliveryOrder::class)->execute($do);
            }

            return $do;
        });
    }
}
