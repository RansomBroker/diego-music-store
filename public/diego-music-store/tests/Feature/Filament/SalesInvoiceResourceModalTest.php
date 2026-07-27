<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\SalesInvoices\Pages\ListSalesInvoices;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\SalesInvoice;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SalesInvoiceResourceModalTest extends TestCase
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

        $this->customer = Customer::create(['name' => 'PT Studio Sound', 'phone' => '081999888777']);
        $this->branch = Branch::create(['name' => 'Cabang Surabaya', 'store_name' => 'Store SBY', 'is_active' => true]);

        $unit = Unit::create(['name' => 'Unit', 'code' => 'UNT', 'is_active' => true]);
        $product = Product::create(['name' => 'Speaker Monitor Krk', 'type' => 'physical', 'unit_id' => $unit->id, 'is_active' => true]);
        $this->variant = ProductVariant::create(['product_id' => $product->id, 'sku' => 'SKU-KRK-05', 'price' => 3000000, 'cost_price' => 2000000, 'hpp' => 2000000, 'is_active' => true]);
    }

    public function test_it_can_render_list_sales_invoices_page(): void
    {
        Livewire::test(ListSalesInvoices::class)
            ->assertSuccessful();
    }

    public function test_it_can_create_sales_invoice_via_modal_action(): void
    {
        $test = Livewire::test(ListSalesInvoices::class)
            ->mountAction('create');

        $itemKey = array_key_first($test->get('mountedActions.0.data.items') ?? []);

        $test->setActionData([
                'customer_id' => $this->customer->id,
                'branch_id' => $this->branch->id,
                'invoice_number' => 'INV-TEST-001',
                'invoice_date' => now()->toDateString(),
                'payment_type' => 'Tunai',
                'status' => 'draft',
                'items' => [
                    ($itemKey ?: 'item-1') => [
                        'product_variant_id' => $this->variant->id,
                        'quantity' => 1,
                        'price' => 3000000,
                    ],
                ],
            ])
            ->callMountedAction()
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('sales_invoices', [
            'invoice_number' => 'INV-TEST-001',
            'customer_id' => $this->customer->id,
            'grand_total' => 3000000,
        ]);
    }

    public function test_it_can_render_sales_invoice_print_page(): void
    {
        $invoice = SalesInvoice::create([
            'customer_id' => $this->customer->id,
            'branch_id' => $this->branch->id,
            'invoice_number' => 'INV-PRINT-001',
            'invoice_date' => now()->toDateString(),
            'payment_type' => 'Tunai',
            'status' => 'posted',
            'grand_total' => 3000000,
        ]);

        $response = $this->get(route('backoffice.sales-invoices.print', $invoice));

        $response->assertStatus(200);
        $response->assertSee('FAKTUR PENJUALAN');
        $response->assertSee('INV-PRINT-001');
    }
}
