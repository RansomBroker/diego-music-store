<?php

namespace App\Actions\DeliveryOrder;

use App\Models\DeliveryOrder;
use Illuminate\Support\Facades\DB;

class UpdateDeliveryOrder
{
    /**
     * Execute the action to update a Delivery Order.
     *
     * @param  DeliveryOrder  $deliveryOrder
     * @param  array<string, mixed>  $data
     * @return DeliveryOrder
     */
    public function execute(DeliveryOrder $deliveryOrder, array $data): DeliveryOrder
    {
        return DB::transaction(function () use ($deliveryOrder, $data) {
            $items = $data['items'] ?? [];
            unset($data['items']);

            // Update DO header
            $deliveryOrder->update($data);

            // Recreate items
            $deliveryOrder->items()->delete();
            foreach ($items as $item) {
                $deliveryOrder->items()->create([
                    'product_variant_id' => $item['product_variant_id'],
                    'quantity' => $item['quantity'],
                ]);
            }

            // If DO status is shipped or delivered, process stock updates
            if (in_array($deliveryOrder->status, ['shipped', 'delivered'])) {
                app(ProcessDeliveryOrderShipped::class)->execute($deliveryOrder);
            }

            return $deliveryOrder;
        });
    }
}
