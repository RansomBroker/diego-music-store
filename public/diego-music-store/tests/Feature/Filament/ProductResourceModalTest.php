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
}
