<?php

namespace Database\Seeders;

use App\Actions\DeliveryOrder\CreateDeliveryOrder;
use App\Models\Branch;
use App\Models\ProductVariant;
use App\Models\PurchaseOrder;
use Illuminate\Database\Seeder;

class DeliveryOrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $po = PurchaseOrder::where('status', 'approved')->first();
        $branch = Branch::first();

        if (!$po || !$branch) {
            return;
        }

        // Find the variant used in the PO if possible, otherwise use first
        $poItem = $po->items->first();
        $variantId = $poItem ? $poItem->product_variant_id : ProductVariant::first()?->id;
        $qtyOrdered = $poItem ? $poItem->quantity : 5;

        if (!$variantId) {
            return;
        }

        $createDoAction = app(CreateDeliveryOrder::class);

        $createDoAction->execute([
            'purchase_order_id' => $po->id,
            'branch_id' => $branch->id,
            'received_date' => now()->format('Y-m-d'),
            'shipping_cost' => 50000,
            'status' => 'draft',
            'notes' => 'Seeded draft Delivery Order',
            'items' => [
                [
                    'product_variant_id' => $variantId,
                    'quantity_ordered' => $qtyOrdered,
                    'quantity_received' => $qtyOrdered,
                ]
            ]
        ]);
    }
}
