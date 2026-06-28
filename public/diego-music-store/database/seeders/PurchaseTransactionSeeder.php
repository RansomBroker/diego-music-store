<?php

namespace Database\Seeders;

use App\Models\Supplier;
use App\Models\Branch;
use App\Models\ProductVariant;
use App\Models\PurchaseOrder;
use App\Models\PurchaseTransaction;
use App\Actions\Procurement\CreatePurchaseTransaction;
use Illuminate\Database\Seeder;

class PurchaseTransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $supplier1 = Supplier::where('name', 'Borneo Music Supplier')->first();
        $supplier2 = Supplier::where('name', 'Symphony Khatulistiwa')->first();
        $branch = Branch::first();

        if (!$supplier1 || !$branch) {
            return;
        }

        // Find variants
        $variant1 = ProductVariant::where('sku', 'SKU-YMHFSNAT')->first();
        $variant2 = ProductVariant::where('sku', 'SKU-YMHFSBST')->first();

        if (!$variant1) {
            $variant1 = ProductVariant::first();
        }

        $createPtAction = app(CreatePurchaseTransaction::class);

        // Fetch the seeded approved PO
        $po = PurchaseOrder::where('status', 'approved')->first();

        // 1. Create a Draft Purchase Transaction referencing the approved PO
        if ($po) {
            $createPtAction->execute([
                'transaction_date' => now()->format('Y-m-d'),
                'po_id' => $po->id,
                'supplier_id' => $po->supplier_id,
                'branch_id' => $po->branch_id,
                'warehouse_id' => $po->branch_id,
                'purchase_type' => 'Kredit',
                'invoice_number' => 'INV-SUP-8899',
                'delivery_note_number' => 'SJ-SUP-8899',
                'invoice_date' => now()->format('Y-m-d'),
                'due_date' => now()->addDays(30)->format('Y-m-d'),
                'discount' => 10000,
                'shipping_cost' => 50000,
                'other_cost' => 0,
                'pph_amount' => 0,
                'status' => 'draft', // DRAF
                'items' => [
                    [
                        'product_variant_id' => $variant1->id,
                        'qty_po' => 5,
                        'qty_received' => 4, // Partial receive
                        'unit_id' => $variant1->product->unit_id,
                        'price' => $variant1->cost_price > 0 ? $variant1->cost_price : 2000000,
                        'discount' => 0,
                        'tax_rate' => 11,
                    ],
                ],
            ]);
        }

        // 2. Create a Posted Direct Purchase Transaction (no PO reference)
        $createPtAction->execute([
            'transaction_date' => now()->subDays(1)->format('Y-m-d'),
            'po_id' => null,
            'supplier_id' => $supplier2->id,
            'branch_id' => $branch->id,
            'warehouse_id' => $branch->id,
            'purchase_type' => 'Tunai',
            'invoice_number' => 'INV-DIR-1122',
            'delivery_note_number' => 'SJ-DIR-1122',
            'invoice_date' => now()->subDays(1)->format('Y-m-d'),
            'due_date' => null,
            'discount' => 0,
            'shipping_cost' => 20000,
            'other_cost' => 5000,
            'pph_amount' => 0,
            'status' => 'posted', // POSTED (Triggers stock increase and HPP average calculation)
            'items' => [
                [
                    'product_variant_id' => $variant1->id,
                    'qty_po' => null,
                    'qty_received' => 2,
                    'unit_id' => $variant1->product->unit_id,
                    'price' => $variant1->cost_price > 0 ? $variant1->cost_price : 2000000,
                    'discount' => 5000,
                    'tax_rate' => 11,
                ],
            ],
        ]);
    }
}
