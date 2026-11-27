<?php

namespace Tests\Feature;

use App\Actions\Procurement\CreatePurchaseTransaction;
use App\Models\Branch;
use App\Models\PricingTier;
use App\Models\Product;
use App\Models\ProductBranchStock;
use App\Models\ProductVariant;
use App\Models\PurchaseTransaction;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use Database\Seeders\AccountSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseHppAfterShippingSyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_posting_purchase_with_shipping_syncs_hpp_after_shipping_to_product_variant_for_any_branch_name(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);
        $this->seed(AccountSeeder::class);

        // Nama cabang tanpa kata "Pusat" atau "Back Office" (seperti di cPanel)
        $branch = Branch::create([
            'name' => 'DIEGO MUSIC STORE PONTIANAK',
            'code' => 'DMS-P',
            'is_active' => true,
        ]);

        $owner = User::factory()->create(['is_active' => true]);
        $owner->assignRole('owner');
        $owner->branches()->attach($branch->id);
        $this->actingAs($owner);

        $supplier = Supplier::create([
            'name' => 'Supplier NUX',
            'code' => 'SUP-NUX',
            'is_active' => true,
        ]);

        $unit = Unit::create([
            'name' => 'Pcs',
            'code' => 'PCS',
            'conversion_factor' => 1,
            'is_active' => true,
        ]);

        $hppTier = PricingTier::create([
            'name' => 'Harga HPP',
            'price_follows_hpp' => true,
            'censor_prices' => true,
        ]);

        $prod1 = Product::create(['name' => 'NUX DM210', 'category' => 'Drum', 'type' => 'physical', 'unit_id' => $unit->id, 'is_active' => true]);
        $var1 = ProductVariant::create(['product_id' => $prod1->id, 'name' => 'Std', 'sku' => 'DMS-P-001', 'price' => 8000000, 'cost_price' => 6000000, 'hpp' => 6000000, 'is_active' => true]);

        $prod2 = Product::create(['name' => 'NPK-200 WH', 'category' => 'Piano', 'type' => 'physical', 'unit_id' => $unit->id, 'is_active' => true]);
        $var2 = ProductVariant::create(['product_id' => $prod2->id, 'name' => 'Std', 'sku' => 'DMS-P-002', 'price' => 6000000, 'cost_price' => 4550000, 'hpp' => 4550000, 'is_active' => true]);

        app(CreatePurchaseTransaction::class)->execute([
            'transaction_date' => now()->format('Y-m-d'),
            'supplier_id' => $supplier->id,
            'branch_id' => $branch->id,
            'warehouse_id' => $branch->id,
            'purchase_type' => PurchaseTransaction::TYPE_TUNAI,
            'status' => 'posted',
            'shipping_borne_by' => 'third_party',
            'shipping_carrier_name' => 'Ekspedisi Cargo',
            'shipping_cost' => 1000000,
            'items' => [
                [
                    'product_variant_id' => $var1->id,
                    'qty_received' => 5,
                    'qty_bonus' => 0,
                    'unit_id' => $unit->id,
                    'price' => 6000000, // 30.000.000 (75% dari 40.000.000) -> ongkir 750.000 / 5 = 150.000 => HPP 6.150.000
                    'update_cost_price' => true,
                    'discount_value' => 0,
                    'tax_rate' => 0,
                ],
                [
                    'product_variant_id' => $var2->id,
                    'qty_received' => 2,
                    'qty_bonus' => 0,
                    'unit_id' => $unit->id,
                    'price' => 5000000, // 10.000.000 (25% dari 40.000.000) -> ongkir 250.000 / 2 = 125.000 => HPP 5.125.000
                    'update_cost_price' => false,
                    'discount_value' => 0,
                    'tax_rate' => 0,
                ],
            ],
        ]);

        $var1->refresh();
        $var2->refresh();

        $bs1 = ProductBranchStock::where('product_variant_id', $var1->id)->where('branch_id', $branch->id)->first();
        $bs2 = ProductBranchStock::where('product_variant_id', $var2->id)->where('branch_id', $branch->id)->first();

        // Varian 1 (update_cost_price = true): cost_price = 6.000.000, hpp = 6.150.000 (setelah ongkir), tier ikut 6.150.000
        $this->assertEquals(6000000, $var1->cost_price);
        $this->assertEquals(6150000, $bs1->hpp);
        $this->assertEquals(6150000, $var1->hpp);
        $this->assertEquals(6150000, $var1->priceForTier($hppTier->id));

        // Varian 2 (update_cost_price = false): cost_price tetap 4.550.000, hpp = 5.125.000 (setelah ongkir), tier ikut 5.125.000
        $this->assertEquals(4550000, $var2->cost_price);
        $this->assertEquals(5125000, $bs2->hpp);
        $this->assertEquals(5125000, $var2->hpp);
        $this->assertEquals(5125000, $var2->priceForTier($hppTier->id));
    }
}

