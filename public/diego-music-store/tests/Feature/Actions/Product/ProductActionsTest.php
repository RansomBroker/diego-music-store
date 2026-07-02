<?php

namespace Tests\Feature\Actions\Product;

use App\Actions\Product\CreateProduct;
use App\Actions\Product\UpdateProduct;
use App\Actions\Product\DuplicateProduct;
use App\Models\Branch;
use App\Models\PricingTier;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductTierPrice;
use App\Models\ProductBranchStock;
use App\Models\ProductBundle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductActionsTest extends TestCase
{
    use RefreshDatabase;

    private Branch $branchPusat;
    private Branch $branchKuta;
    private PricingTier $tierRetail;
    private PricingTier $tierGrosir;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed default branches
        $this->branchPusat = Branch::create([
            'name' => 'Cabang Pusat (Back Office)',
            'address' => 'Jl. Bypass Ngurah Rai No. 123, Denpasar, Bali',
            'phone' => '081234567890',
            'is_active' => true,
        ]);

        $this->branchKuta = Branch::create([
            'name' => 'Cabang Kuta',
            'address' => 'Jl. Raya Kuta No. 45, Badung, Bali',
            'phone' => '081234567891',
            'is_active' => true,
        ]);

        // Seed default pricing tiers
        $this->tierRetail = PricingTier::create([
            'name' => 'Umum / Retail',
            'description' => 'Harga retail standar',
        ]);

        $this->tierGrosir = PricingTier::create([
            'name' => 'Reseller / Grosir',
            'description' => 'Harga grosir untuk reseller',
        ]);
    }

    public function test_it_can_create_physical_product_with_variants_and_tier_prices(): void
    {
        $data = [
            'name' => 'Gitar Akustik Yamaha FS800',
            'type' => 'physical',
            'description' => 'Gitar akustik berkualitas tinggi.',
            'image_path' => null,
            'is_active' => true,
            'has_variants' => true,
            'variants' => [
                [
                    'name' => 'Natural',
                    'sku' => 'SKU-YMHFSNAT',
                    'barcode' => '8991234567891',
                    'price' => 3200000,
                    'cost_price' => 2000000,
                    'hpp' => 2100000,
                    'tier_prices' => [
                        $this->tierGrosir->id => 3000000,
                    ],
                ],
                [
                    'name' => 'Sunburst',
                    'sku' => 'SKU-YMHFSBST',
                    'barcode' => '8991234567892',
                    'price' => 3300000,
                    'cost_price' => 2100000,
                    'hpp' => 2200000,
                    'tier_prices' => [
                        $this->tierGrosir->id => 3100000,
                    ],
                ],
            ],
        ];

        /** @var CreateProduct $action */
        $action = app(CreateProduct::class);
        $product = $action->execute($data);

        $this->assertInstanceOf(Product::class, $product);
        $this->assertEquals('Gitar Akustik Yamaha FS800', $product->name);
        $this->assertTrue($product->isPhysical());

        // Assert 2 variants created
        $this->assertCount(2, $product->variants);

        $naturalVariant = $product->variants->where('name', 'Natural')->first();
        $this->assertNotNull($naturalVariant);
        $this->assertEquals('SKU-YMHFSNAT', $naturalVariant->sku);
        $this->assertEquals(3200000, $naturalVariant->price);
        $this->assertEquals(2100000, $naturalVariant->hpp);

        // Assert tier prices
        $naturalTierPrice = ProductTierPrice::where('product_variant_id', $naturalVariant->id)
            ->where('pricing_tier_id', $this->tierGrosir->id)
            ->first();
        $this->assertNotNull($naturalTierPrice);
        $this->assertEquals(3000000, $naturalTierPrice->price);
    }

    public function test_it_can_create_physical_product_without_variants(): void
    {
        $data = [
            'name' => 'Gitar Akustik Baso',
            'type' => 'physical',
            'description' => 'Gitar biasa.',
            'image_path' => null,
            'is_active' => true,
            'has_variants' => false,
            'sku' => 'SKU-BASO123',
            'barcode' => '8991234560000',
            'price' => 1500000,
            'cost_price' => 1000000,
            'hpp' => 1050000,
            'tier_prices' => [
                $this->tierGrosir->id => 1400000,
            ],
        ];

        /** @var CreateProduct $action */
        $action = app(CreateProduct::class);
        $product = $action->execute($data);

        $this->assertInstanceOf(Product::class, $product);
        $this->assertCount(1, $product->variants);

        $defaultVariant = $product->variants->first();
        $this->assertNull($defaultVariant->name);
        $this->assertEquals('SKU-BASO123', $defaultVariant->sku);
        $this->assertEquals(1050000, $defaultVariant->hpp);
    }

    public function test_it_can_create_service_product(): void
    {
        $data = [
            'name' => 'Stem Gitar',
            'type' => 'service',
            'description' => 'Jasa stem.',
            'image_path' => null,
            'is_active' => true,
            'sku' => 'SKU-SERVSTEM',
            'barcode' => '8999876543210',
            'price' => 50000,
            'cost_price' => 0,
            'hpp' => 0,
            'tier_prices' => [
                $this->tierGrosir->id => 45000,
            ],
        ];

        /** @var CreateProduct $action */
        $action = app(CreateProduct::class);
        $product = $action->execute($data);

        $this->assertInstanceOf(Product::class, $product);
        $this->assertTrue($product->isService());
        $this->assertCount(1, $product->variants);

        $defaultVariant = $product->variants->first();
        $this->assertEquals('SKU-SERVSTEM', $defaultVariant->sku);
        $this->assertEquals(0, $defaultVariant->hpp);

        // Service has no stocks
        $this->assertCount(0, ProductBranchStock::where('product_variant_id', $defaultVariant->id)->get());
    }

    public function test_it_can_create_bundle_product(): void
    {
        // 1. Create a physical product first
        $guitar = Product::create(['name' => 'Gitar', 'type' => 'physical']);
        $guitarVariant = ProductVariant::create([
            'product_id' => $guitar->id,
            'sku' => 'SKU-GTR',
            'price' => 1000000,
            'cost_price' => 700000,
            'hpp' => 700000,
            'is_active' => true,
        ]);

        $data = [
            'name' => 'Paket Gitar Lengkap',
            'type' => 'bundle',
            'description' => 'Paket lengkap.',
            'image_path' => null,
            'is_active' => true,
            'sku' => 'SKU-BNDL01',
            'barcode' => '8999999999999',
            'price' => 1200000,
            'cost_price' => 700000,
            'hpp' => 700000,
            'bundle_items' => [
                [
                    'child_variant_id' => $guitarVariant->id,
                    'quantity' => 1,
                ]
            ],
        ];

        /** @var CreateProduct $action */
        $action = app(CreateProduct::class);
        $product = $action->execute($data);

        $this->assertInstanceOf(Product::class, $product);
        $this->assertTrue($product->isBundle());

        $defaultVariant = $product->variants->first();
        $this->assertCount(1, $defaultVariant->bundleItems);
        $this->assertEquals($guitarVariant->id, $defaultVariant->bundleItems->first()->child_variant_id);
    }

    public function test_it_can_update_product_and_sync_variants(): void
    {
        // Create initial product without variants
        $product = Product::create(['name' => 'Gitar A', 'type' => 'physical']);
        $initialVariant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'SKU-GITA',
            'price' => 1000000,
            'cost_price' => 700000,
            'hpp' => 700000,
            'is_active' => true,
        ]);

        $updateData = [
            'name' => 'Gitar A Updated',
            'type' => 'physical',
            'description' => 'Updated desc.',
            'image_path' => null,
            'is_active' => true,
            'has_variants' => true,
            'variants' => [
                [
                    'id' => null, // new variant
                    'name' => 'Red',
                    'sku' => 'SKU-GITA-RED',
                    'barcode' => '8990000000001',
                    'price' => 1100000,
                    'cost_price' => 750000,
                    'hpp' => 750000,
                    'tier_prices' => [],
                ]
            ]
        ];

        /** @var UpdateProduct $action */
        $action = app(UpdateProduct::class);
        $updatedProduct = $action->execute($product, $updateData);

        $this->assertEquals('Gitar A' . ' Updated', $updatedProduct->name);
        
        // Assert initial variant is deleted and the new one is created
        $this->assertCount(1, $updatedProduct->variants);
        $newVariant = $updatedProduct->variants->first();
        $this->assertEquals('Red', $newVariant->name);
        $this->assertEquals('SKU-GITA-RED', $newVariant->sku);

        $this->assertFalse(ProductVariant::where('id', $initialVariant->id)->exists());
    }

    public function test_it_can_duplicate_product(): void
    {
        // 1. Create a product with variant and tier price
        $product = Product::create([
            'name' => 'Original Product',
            'type' => 'physical',
            'is_active' => true,
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'SKU-ORIGINAL',
            'barcode' => '1234567890',
            'price' => 500000,
            'cost_price' => 300000,
            'hpp' => 310000,
            'is_active' => true,
        ]);

        ProductTierPrice::create([
            'product_variant_id' => $variant->id,
            'pricing_tier_id' => $this->tierGrosir->id,
            'price' => 450000,
        ]);

        // 2. Duplicate the product
        /** @var DuplicateProduct $action */
        $action = app(DuplicateProduct::class);
        $duplicatedProduct = $action->execute($product);

        // 3. Assertions
        $this->assertNotEquals($product->id, $duplicatedProduct->id);
        $this->assertEquals('Original Product - Copy', $duplicatedProduct->name);

        $this->assertCount(1, $duplicatedProduct->variants);
        $duplicatedVariant = $duplicatedProduct->variants->first();

        $this->assertNotEquals($variant->id, $duplicatedVariant->id);
        $this->assertNotEquals('SKU-ORIGINAL', $duplicatedVariant->sku);
        $this->assertNotEquals('1234567890', $duplicatedVariant->barcode);
        $this->assertEquals(500000, $duplicatedVariant->price);
        $this->assertEquals(310000, $duplicatedVariant->hpp);

        // Assert tier price was copied
        $copiedTierPrice = ProductTierPrice::where('product_variant_id', $duplicatedVariant->id)
            ->where('pricing_tier_id', $this->tierGrosir->id)
            ->first();
        $this->assertNotNull($copiedTierPrice);
        $this->assertEquals(450000, $copiedTierPrice->price);
    }
}
