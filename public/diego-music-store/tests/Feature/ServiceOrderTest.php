<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\ServiceOrder;
use App\Models\User;
use App\Actions\Service\CreateServiceOrderFromSale;
use App\Actions\Service\UpdateServiceOrderStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceOrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_automatic_service_order_creation_from_pos_sale()
    {
        $branch = Branch::create(['name' => 'Cabang Utama', 'store_name' => 'Diego Store']);
        $user = User::create([
            'name'     => 'Teknisi Test',
            'username' => 'teknisitest',
            'email'    => 'teknisi@test.com',
            'password' => bcrypt('password'),
        ]);
        $customer = Customer::create(['name' => 'Budi Santoso', 'phone' => '081234567890']);

        // Create product marked as service
        $product = Product::create([
            'name'     => 'Jasa Setup & Tuning Gitar',
            'type'     => 'service',
            'category' => 'Jasa',
        ]);
        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'name'       => 'Standard',
            'price'      => 150000,
            'sku'        => 'SVC-SET-01',
        ]);

        $sale = Sale::create([
            'branch_id'      => $branch->id,
            'customer_id'    => $customer->id,
            'sales_rep_id'   => $user->id,
            'invoice_number' => 'INV-TEST-001',
            'invoice_date'   => now()->toDateString(),
            'subtotal'       => 150000,
            'grand_total'    => 150000,
            'payment_method' => 'cash',
            'status'         => 'completed',
            'created_by'     => $user->id,
        ]);

        SaleItem::create([
            'sale_id'            => $sale->id,
            'product_variant_id' => $variant->id,
            'quantity'           => 1,
            'unit_price'         => 150000,
            'total_price'        => 150000,
            'notes'              => 'Gitar Fender Akustik - Senar 3 putus',
        ]);

        $orders = CreateServiceOrderFromSale::execute($sale);

        $this->assertCount(1, $orders);
        $this->assertDatabaseHas('service_orders', [
            'sale_id'       => $sale->id,
            'customer_name' => 'Budi Santoso',
            'status'        => 'received',
            'estimated_cost'=> 150000,
        ]);

        $order = $orders[0];
        $this->assertStringStartsWith('SVC-', $order->ticket_code);
    }

    public function test_update_service_order_status_and_additional_charges()
    {
        $branch = Branch::create(['name' => 'Cabang 2', 'store_name' => 'Diego Store 2']);
        $user = User::create([
            'name'     => 'Teknisi 2',
            'username' => 'teknisi2',
            'email'    => 'teknisi2@test.com',
            'password' => bcrypt('password'),
        ]);

        $order = ServiceOrder::create([
            'ticket_code'    => 'SVC-20260802-0001',
            'branch_id'      => $branch->id,
            'customer_name'  => 'Agus',
            'device_name'    => 'Keyboard Yamaha PSR',
            'status'         => 'received',
            'estimated_cost' => 200000,
            'created_by'     => $user->id,
        ]);

        $updated = UpdateServiceOrderStatus::execute($order, [
            'status'             => 'in_progress',
            'technician_id'      => $user->id,
            'serial_number'      => 'SN-PSR-9921',
            'notes'              => 'Sedang ganti tuts titi nada C3',
            'additional_charges' => [
                ['name' => 'Tuts Rubber Key PSR', 'amount' => 50000],
            ],
        ]);

        $this->assertEquals('in_progress', $updated->status);
        $this->assertEquals(250000, $updated->total_cost);
        $this->assertEquals('SN-PSR-9921', $updated->serial_number);
    }

    public function test_public_service_tracking_page_accessible()
    {
        $branch = Branch::create(['name' => 'Cabang Musik', 'store_name' => 'Diego Music']);
        $user = User::create([
            'name'     => 'User Test 3',
            'username' => 'user3',
            'email'    => 'user3@test.com',
            'password' => bcrypt('password'),
        ]);

        $order = ServiceOrder::create([
            'ticket_code'    => 'SVC-TRACK-999',
            'branch_id'      => $branch->id,
            'customer_name'  => 'Pelanggan Test',
            'customer_phone' => '081299998888',
            'device_name'    => 'Amplifier Marshall',
            'status'         => 'diagnosing',
            'estimated_cost' => 300000,
            'created_by'     => $user->id,
        ]);

        $response = $this->get('/track-service/' . $order->ticket_code);
        $response->assertStatus(200);
        $response->assertSee('SVC-TRACK-999');
        $response->assertSee('Amplifier Marshall');
        $response->assertSee('Diagnosa');
    }
}
