<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\SalesQuotations\Pages\ListSalesQuotations;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\SalesQuotation;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SalesQuotationResourceModalTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Customer $customer;
    private Branch $branch;
    private ProductVariant $variant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->actingAs($this->user);

        $this->customer = Customer::create(['name' => 'PT Musik Indonesia', 'phone' => '08123456789']);
        $this->branch = Branch::create(['name' => 'Cabang Jakarta', 'store_name' => 'Store JKT', 'is_active' => true]);

        $unit = Unit::create(['name' => 'Unit', 'code' => 'UNT', 'is_active' => true]);
        $product = Product::create(['name' => 'Keyboard Roland', 'type' => 'physical', 'unit_id' => $unit->id, 'is_active' => true]);
        $this->variant = ProductVariant::create(['product_id' => $product->id, 'sku' => 'SKU-RLD-01', 'price' => 5000000, 'cost_price' => 3500000, 'hpp' => 3500000, 'is_active' => true]);
    }

    public function test_it_can_render_list_sales_quotations_page(): void
    {
        Livewire::test(ListSalesQuotations::class)
            ->assertSuccessful();
    }

    public function test_it_can_create_sales_quotation_via_modal_action(): void
    {
        Livewire::test(ListSalesQuotations::class)
            ->callAction('create', data: [
                'customer_id' => $this->customer->id,
                'branch_id' => $this->branch->id,
                'quotation_number' => 'SQ-TEST-001',
                'quotation_date' => now()->toDateString(),
                'status' => 'draft',
                'notes' => 'Penawaran Spesial',
                'items' => [
                    'item-1' => [
                        'product_variant_id' => $this->variant->id,
                        'quantity' => 2,
                        'price' => 5000000,
                    ]
                ],
            ])
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('sales_quotations', [
            'quotation_number' => 'SQ-TEST-001',
            'customer_id' => $this->customer->id,
            'grand_total' => 10000000,
        ]);
    }
}
