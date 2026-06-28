<?php

namespace App\Actions\Procurement;

use App\Models\DeliveryOrder;
use App\Models\DeliveryOrderItem;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class UpdateDeliveryOrder
{
    /**
     * Execute the action to update a Delivery Order.
     *
     * @param  DeliveryOrder  $do
     * @param  array<string, mixed>  $data
     * @return DeliveryOrder
     */
    public function execute(DeliveryOrder $do, array $data): DeliveryOrder
    {
        return DB::transaction(function () use ($do, $data) {
            // Guard clause to prevent editing already received DOs
            if ($do->status === 'received') {
                throw new InvalidArgumentException('Delivery Order yang sudah diterima tidak dapat diubah.');
            }

            // Update main details
            $do->update([
                'purchase_order_id' => $data['purchase_order_id'],
                'branch_id' => $data['branch_id'],
                'do_number' => $data['do_number'],
                'received_date' => $data['received_date'],
                'shipping_cost' => $data['shipping_cost'] ?? 0,
                'notes' => $data['notes'] ?? null,
            ]);

            // Sync items (delete existing ones and recreate)
            $do->items()->delete();

            $items = $data['items'] ?? [];
            foreach ($items as $item) {
                DeliveryOrderItem::create([
                    'delivery_order_id' => $do->id,
                    'product_variant_id' => $item['product_variant_id'],
                    'quantity_ordered' => $item['quantity_ordered'],
                    'quantity_received' => $item['quantity_received'] ?? 0,
                ]);
            }

            // Trigger reception if the new status is received
            if (($data['status'] ?? 'draft') === 'received') {
                app(ReceiveDeliveryOrder::class)->execute($do);
            }

            return $do;
        });
    }
}
