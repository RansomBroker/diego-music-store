<?php

namespace App\Actions\DeliveryOrder;

use App\Models\DeliveryOrder;
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
            $items = $data['items'] ?? [];
            unset($data['items']);

            $doNumber = $data['do_number'] ?? null;
            if (empty($doNumber)) {
                $doNumber = DeliveryOrder::generateDoNumber();
            }
            $data['do_number'] = $doNumber;

            // Create DO
            $deliveryOrder = DeliveryOrder::create($data);

            // Create items
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
