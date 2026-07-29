<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\Products\Pages\ListProducts;
use App\Models\Product;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProductResourceModalTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Unit $unit;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->actingAs($this->user);

        $this->unit = Unit::create([
            'name' => 'Pcs',
            'code' => 'PCS',
            'is_active' => true,
        ]);
    }

    public function test_it_can_render_list_products_page(): void
    {
        Livewire::test(ListProducts::class)
            ->assertSuccessful();
    }

    public function test_it_can_create_product_via_modal_action(): void
    {
        Livewire::test(ListProducts::class)
            ->callAction('create', data: [
                'name' => 'Efek Gitar Boss DS-1',
                'type' => 'physical',
                'unit_id' => $this->unit->id,
                'category' => 'Aksesoris',
                'brand' => 'Boss',
                'supplier_id' => 'Boss Supplier Corp',
                'discount_value' => 10,
                'discount_type' => 'percent',
                'tax_value' => 11,
                'tax_type' => 'percent',
                'minimum_stock' => 5,
                'is_active' => true,
                'has_variants' => false,
                'sku' => 'SKU-BOSS-DS1',
                'price' => 850000,
                'cost_price' => 600000,
                'hpp' => 600000,
            ])
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('products', [
            'name' => 'Efek Gitar Boss DS-1',
            'type' => 'physical',
            'category' => 'Aksesoris',
            'brand' => 'Boss',
            'minimum_stock' => 5,
        ]);

        $this->assertDatabaseHas('product_variants', [
            'sku' => 'SKU-BOSS-DS1',
            'discount_value' => 10.00,
            'discount_type' => 'percent',
            'tax_value' => 11.00,
            'tax_type' => 'percent',
        ]);

        $this->assertDatabaseHas('suppliers', [
            'name' => 'Boss Supplier Corp',
        ]);
    }

    public function test_it_can_edit_product_via_modal_action(): void
    {
        $product = Product::create([
            'name' => 'Amplifier Roland Cube',
            'type' => 'physical',
            'unit_id' => $this->unit->id,
            'is_active' => true,
        ]);

        $product->variants()->create([
            'sku' => 'SKU-RLD-CUBE',
            'price' => 2500000,
            'cost_price' => 1800000,
            'hpp' => 1800000,
            'is_active' => true,
        ]);

        Livewire::test(ListProducts::class)
            ->callTableAction('edit', $product, data: [
                'name' => 'Amplifier Roland Cube XL',
                'type' => 'physical',
                'unit_id' => $this->unit->id,
                'category' => 'Amplifier',
                'brand' => 'Roland',
                'supplier_id' => 'Roland Indonesia',
                'discount_value' => 50000,
                'discount_type' => 'fixed',
                'tax_value' => 10,
                'tax_type' => 'percent',
                'minimum_stock' => 10,
                'is_active' => true,
                'has_variants' => false,
                'sku' => 'SKU-RLD-CUBE-XL',
                'price' => 2800000,
                'cost_price' => 2000000,
                'hpp' => 2000000,
            ])
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Amplifier Roland Cube XL',
            'category' => 'Amplifier',
            'brand' => 'Roland',
            'minimum_stock' => 10,
        ]);

        $this->assertDatabaseHas('product_variants', [
            'product_id' => $product->id,
            'sku' => 'SKU-RLD-CUBE-XL',
            'discount_value' => 50000.00,
            'discount_type' => 'fixed',
            'tax_value' => 10.00,
            'tax_type' => 'percent',
        ]);

        $this->assertDatabaseHas('suppliers', [
            'name' => 'Roland Indonesia',
        ]);
    }

    public function test_it_filters_products_table_by_variant_display(): void
    {
        // 1. Single Product (1 variant, name null)
        $singleProduct = Product::create([
            'name' => 'Gitar Akustik Single',
            'type' => 'physical',
            'unit_id' => $this->unit->id,
            'is_active' => true,
        ]);
        $singleVariant = $singleProduct->variants()->create([
            'sku' => 'SKU-SINGLE-1',
            'name' => null,
            'price' => 1000000,
            'is_active' => true,
        ]);

        // 2. Multi-variant Product (3 variants: 1 parent null name, 2 child variants)
        $multiProduct = Product::create([
            'name' => 'Gitar Akustik Multi',
            'type' => 'physical',
            'unit_id' => $this->unit->id,
            'is_active' => true,
        ]);
        $parentVariant = $multiProduct->variants()->create([
            'sku' => 'SKU-MULTI-PARENT',
            'name' => null,
            'price' => 2000000,
            'is_active' => true,
        ]);
        $childVariantRed = $multiProduct->variants()->create([
            'sku' => 'SKU-MULTI-RED',
            'name' => 'Merah',
            'price' => 2100000,
            'is_active' => true,
        ]);
        $childVariantBlue = $multiProduct->variants()->create([
            'sku' => 'SKU-MULTI-BLUE',
            'name' => 'Biru',
            'price' => 2100000,
            'is_active' => true,
        ]);

        // Default filter 'hide_parent' should show singleVariant, childVariantRed, childVariantBlue, but hide parentVariant
        Livewire::test(ListProducts::class)
            ->assertCanSeeTableRecords([$singleVariant, $childVariantRed, $childVariantBlue])
            ->assertCanNotSeeTableRecords([$parentVariant]);

        // Filter 'all' should show all records including parentVariant
        Livewire::test(ListProducts::class)
            ->filterTable('variant_display', 'all')
            ->assertCanSeeTableRecords([$singleVariant, $childVariantRed, $childVariantBlue, $parentVariant]);

        // Filter 'only_parent' should show only parentVariant
        Livewire::test(ListProducts::class)
            ->filterTable('variant_display', 'only_parent')
            ->assertCanSeeTableRecords([$parentVariant])
            ->assertCanNotSeeTableRecords([$singleVariant, $childVariantRed, $childVariantBlue]);
    }
}
