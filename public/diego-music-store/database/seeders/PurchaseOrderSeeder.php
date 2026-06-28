<?php

namespace Database\Seeders;

use App\Models\Supplier;
use App\Models\ProductVariant;
use App\Models\PurchaseOrder;
use App\Actions\Procurement\CreatePurchaseOrder;
use Illuminate\Database\Seeder;

class PurchaseOrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $supplier1 = Supplier::where('name', 'Borneo Music Supplier')->first();
        $supplier2 = Supplier::where('name', 'Symphony Khatulistiwa')->first();

        // Fallbacks if suppliers don't exist yet (though they are seeded in DatabaseSeeder)
        if (!$supplier1) {
            $supplier1 = Supplier::create([
                'name' => 'Borneo Music Supplier',
                'contact_person' => 'Ahmad',
                'phone' => '0811567890',
                'email' => 'info@borneomusic.com',
                'address' => 'Jl. Imam Bonjol No. 88, Pontianak, Kalimantan Barat',
                'bank_name' => 'Bank Kalbar',
                'bank_account_number' => '1012345678',
                'bank_account_name' => 'PT Borneo Music Supplier',
            ]);
        }

        if (!$supplier2) {
            $supplier2 = Supplier::create([
                'name' => 'Symphony Khatulistiwa',
                'contact_person' => 'Dewi',
                'phone' => '081345678901',
                'email' => 'symphony.khatulistiwa@gmail.com',
                'address' => 'Jl. Teuku Umar No. 12, Pontianak, Kalimantan Barat',
                'bank_name' => 'BCA',
                'bank_account_number' => '0291234567',
                'bank_account_name' => 'Dewi Lestari',
            ]);
        }

        // Get some variants for PO items
        $variant1 = ProductVariant::where('sku', 'SKU-YMHFSNAT')->first();
        $variant2 = ProductVariant::where('sku', 'SKU-YMHFSBST')->first();

        if (!$variant1 || !$variant2) {
            // Get any variants as fallback
            $variants = ProductVariant::take(2)->get();
            $variant1 = $variants->first();
            $variant2 = $variants->last();
        }

        if (!$variant1) {
            return; // Cannot seed POs without variants
        }

        $branchId = \App\Models\Branch::first()?->id;

        $createPoAction = app(CreatePurchaseOrder::class);

        // 1. PO Draft (Borneo Music Supplier) - Item level tax & discount
        $createPoAction->execute([
            'supplier_id' => $supplier1->id,
            'branch_id' => $branchId,
            'po_number' => 'PO-' . now()->format('Ymd') . '-0001',
            'order_date' => now()->subDays(5)->format('Y-m-d'),
            'status' => 'draft',
            'currency' => 'IDR',
            'payment_term' => 'COD',
            'tax_mode' => 'ITEM',
            'discount_amount' => 50000, // global discount
            'other_cost' => 150000, // shipping
            'notes' => 'Pemesanan draf dengan diskon item dan PPN 11% per item.',
            'items' => [
                [
                    'product_variant_id' => $variant1->id,
                    'quantity' => 10,
                    'price' => $variant1->cost_price > 0 ? $variant1->cost_price : 2000000,
                    'discount_amount' => 20000, // item discount
                    'tax_rate' => 11, // 11% tax
                    'notes' => 'Gitar warna natural.',
                ],
            ],
        ]);

        // 2. PO Approved (Symphony Khatulistiwa) - Global tax
        if ($variant2) {
            $createPoAction->execute([
                'supplier_id' => $supplier2->id,
                'branch_id' => $branchId,
                'po_number' => 'PO-' . now()->format('Ymd') . '-0002',
                'order_date' => now()->subDays(3)->format('Y-m-d'),
                'status' => 'approved',
                'currency' => 'IDR',
                'payment_term' => '30 Hari',
                'tax_mode' => 'GLOBAL',
                'tax_rate' => 11, // global PPN 11%
                'discount_amount' => 0,
                'other_cost' => 100000, // shipping
                'notes' => 'Pemesanan disetujui dengan PPN global 11%.',
                'items' => [
                    [
                        'product_variant_id' => $variant1->id,
                        'quantity' => 5,
                        'price' => $variant1->cost_price > 0 ? $variant1->cost_price : 2000000,
                        'discount_amount' => 0,
                        'tax_rate' => 11,
                        'notes' => 'Natural',
                    ],
                    [
                        'product_variant_id' => $variant2->id,
                        'quantity' => 5,
                        'price' => $variant2->cost_price > 0 ? $variant2->cost_price : 2100000,
                        'discount_amount' => 0,
                        'tax_rate' => 11,
                        'notes' => 'Sunburst',
                    ],
                ],
            ]);
        }
    }
}
