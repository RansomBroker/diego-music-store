<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Sale;
use App\Models\User;
use App\Models\Account;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\SaleItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class POSReceiptTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed basic accounts needed
        Account::firstOrCreate(['code' => '1-1000'], ['name' => 'Kas Utama', 'classification' => 'Asset', 'is_active' => true]);
        Account::firstOrCreate(['code' => '1-1200'], ['name' => 'Piutang Dagang', 'classification' => 'Asset', 'is_active' => true]);
        Account::firstOrCreate(['code' => '1-1110'], ['name' => 'Bank BCA', 'classification' => 'Asset', 'is_active' => true]);
        Account::firstOrCreate(['code' => '1-1300'], ['name' => 'Persediaan Barang', 'classification' => 'Asset', 'is_active' => true]);
        Account::firstOrCreate(['code' => '4-1000'], ['name' => 'Pendapatan Penjualan', 'classification' => 'Revenue', 'is_active' => true]);
        Account::firstOrCreate(['code' => '5-1000'], ['name' => 'Harga Pokok Penjualan', 'classification' => 'Expense', 'is_active' => true]);
    }

    public function test_unauthenticated_user_cannot_access_receipt_page(): void
    {
        $branch = Branch::create([
            'name' => 'Cabang Pusat',
            'address' => 'Alamat Cabang',
            'phone' => '021',
            'is_active' => true,
        ]);

        $user = User::factory()->create();

        $sale = Sale::create([
            'branch_id' => $branch->id,
            'sales_rep_id' => $user->id,
            'invoice_number' => 'INV-20260704-0001',
            'invoice_date' => now()->toDateString(),
            'subtotal' => 100000,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'grand_total' => 100000,
            'payment_method' => 'cash',
            'status' => 'completed',
            'created_by' => $user->id,
        ]);

        $response = $this->get(route('pos.receipt', $sale->id));

        $response->assertStatus(403);
    }

    public function test_authenticated_user_can_access_receipt_page(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $branch = Branch::create([
            'name' => 'Cabang Pusat',
            'address' => 'Alamat Cabang',
            'phone' => '021',
            'is_active' => true,
        ]);

        $product = Product::create([
            'name' => 'Gitar Akustik Yamaha',
            'type' => 'physical',
            'is_active' => true,
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'YMH-AK',
            'name' => 'Natural',
            'price' => 2000000,
            'cost_price' => 1200000,
            'hpp' => 1250000,
            'is_active' => true,
        ]);

        $sale = Sale::create([
            'branch_id' => $branch->id,
            'sales_rep_id' => $user->id,
            'invoice_number' => 'INV-20260704-0001',
            'invoice_date' => now()->toDateString(),
            'subtotal' => 2000000,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'grand_total' => 2000000,
            'payment_method' => 'cash',
            'status' => 'completed',
            'created_by' => $user->id,
        ]);

        SaleItem::create([
            'sale_id' => $sale->id,
            'product_variant_id' => $variant->id,
            'quantity' => 1,
            'unit_price' => 2000000,
            'discount_amount' => 0,
            'total_price' => 2000000,
        ]);

        $response = $this->get(route('pos.receipt', $sale->id));

        $response->assertStatus(200);
        $response->assertSee('Struk Pembayaran');
        $response->assertSee($sale->invoice_number);
        $response->assertSee('Gitar Akustik Yamaha');
    }
}
