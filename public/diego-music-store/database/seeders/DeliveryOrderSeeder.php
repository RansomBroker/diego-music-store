<?php

namespace Database\Seeders;

use App\Actions\DeliveryOrder\CreateDeliveryOrder;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;

class DeliveryOrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customer = Customer::first();
        if (!$customer) {
            $customer = Customer::create([
                'name' => 'General Customer',
                'phone' => '081234567890',
                'address' => 'Denpasar',
            ]);
        }

        $branch = Branch::first();
        $variant = ProductVariant::first();

        if (!$branch || !$variant) {
            return;
        }

        $createDoAction = app(CreateDeliveryOrder::class);

        $createDoAction->execute([
            'customer_id' => $customer->id,
            'branch_id' => $branch->id,
            'shipping_date' => now()->format('Y-m-d'),
            'shipping_cost' => 50000,
            'status' => 'draft',
            'notes' => 'Seeded draft customer Delivery Order (Surat Jalan)',
            'items' => [
                [
                    'product_variant_id' => $variant->id,
                    'quantity' => 2,
                ]
            ]
        ]);
    }
}
