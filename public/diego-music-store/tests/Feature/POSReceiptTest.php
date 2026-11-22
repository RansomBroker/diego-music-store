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

        $response->assertRedirect(route('pos.login'));
    }

    public function test_authenticated_user_can_access_receipt_page(): void
    {
        $ownerRole = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'owner', 'guard_name' => 'web']);
        $user = User::factory()->create();
        $user->assignRole($ownerRole);
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

    public function test_receipt_displays_session_opener_as_cashier_and_sales_rep_at_bottom_after_payment_method(): void
    {
        $ownerRole = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'owner', 'guard_name' => 'web']);
        $sessionOpener = User::factory()->create(['name' => 'Kasir Sesi Pagi']);
        $otherViewer = User::factory()->create(['name' => 'Kasir Pengganti']);
        $otherViewer->assignRole($ownerRole);
        $salesUser = User::factory()->create(['name' => 'Budi Sales']);

        $branch = Branch::create([
            'name' => 'Cabang Pusat',
            'address' => 'Alamat Cabang',
            'phone' => '021',
            'is_active' => true,
        ]);
        $sessionOpener->branches()->attach($branch->id);
        $otherViewer->branches()->attach($branch->id);

        $session = \App\Models\CashSession::create([
            'branch_id' => $branch->id,
            'user_id' => $sessionOpener->id,
            'opened_at' => now(),
            'opening_cash' => 250000,
            'status' => 'open',
        ]);

        $product = Product::create([
            'name' => 'Keyboard Roland',
            'type' => 'physical',
            'is_active' => true,
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'RLD-KB-01',
            'name' => 'Black',
            'price' => 3500000,
            'cost_price' => 2500000,
            'hpp' => 2500000,
            'is_active' => true,
        ]);

        $sale = Sale::create([
            'branch_id' => $branch->id,
            'cash_session_id' => $session->id,
            'sales_rep_id' => $salesUser->id,
            'invoice_number' => 'INV-STR-0001',
            'invoice_date' => now()->toDateString(),
            'subtotal' => 3500000,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'grand_total' => 3500000,
            'payment_method' => 'Tunai',
            'status' => 'completed',
            'created_by' => $otherViewer->id,
        ]);

        SaleItem::create([
            'sale_id' => $sale->id,
            'product_variant_id' => $variant->id,
            'quantity' => 1,
            'unit_price' => 3500000,
            'discount_amount' => 0,
            'total_price' => 3500000,
        ]);

        $this->actingAs($otherViewer);
        $response = $this->get(route('pos.receipt', $sale->id));

        $response->assertStatus(200);
        $response->assertSeeInOrder([
            'Kasir:',
            'Kasir Sesi Pagi',
            'Metode & Rincian Bayar:',
            'TUNAI',
            'Sales:',
            'Budi Sales',
        ]);
        $response->assertDontSee('Kasir Pengganti');

        $pdfHtml = view('pos.receipt-pdf', [
            'sale' => $sale->fresh(['branch', 'customer', 'salesRep', 'creator', 'cashSession.user', 'items.variant.product', 'items.salesRep']),
            'setting' => null,
        ])->render();

        $this->assertStringContainsString('Kasir Sesi Pagi', $pdfHtml);
        $this->assertStringContainsString('Budi Sales', $pdfHtml);
        $this->assertLessThan(strpos($pdfHtml, 'Budi Sales'), strpos($pdfHtml, 'TUNAI'));
    }

    public function test_receipt_displays_change_for_cash_payments_and_omits_for_non_cash(): void
    {
        $ownerRole = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'owner', 'guard_name' => 'web']);
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'sales', 'guard_name' => 'web']);
        $cashier = User::factory()->create(['name' => 'Kasir Shift']);
        $cashier->assignRole($ownerRole);

        $branch = Branch::create([
            'name' => 'Cabang Pusat',
            'address' => 'Alamat Cabang',
            'phone' => '021',
            'is_active' => true,
        ]);
        $cashier->branches()->attach($branch->id);

        $session = \App\Models\CashSession::create([
            'branch_id' => $branch->id,
            'user_id' => $cashier->id,
            'opened_at' => now(),
            'opening_cash' => 250000,
            'status' => 'open',
        ]);

        $product = Product::create(['name' => 'Senar Gitar Elixir', 'type' => 'physical', 'is_active' => true]);
        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'ELX-01',
            'name' => '09-42',
            'price' => 200000,
            'cost_price' => 120000,
            'hpp' => 120000,
            'is_active' => true,
        ]);
        \App\Models\ProductBranchStock::create([
            'product_variant_id' => $variant->id,
            'branch_id' => $branch->id,
            'stock' => 10,
            'hpp' => 120000,
        ]);

        $this->actingAs($cashier);

        // Execute POS checkout with cash overpayment (200.000 total, paid 250.000 -> change 50.000)
        $pos = \Livewire\Livewire::test(\App\Livewire\POS::class)
            ->call('addToCart', $variant->id)
            ->call('executeCheckout', [
                'selectedPaymentMethods' => ['cash'],
                'paymentAmounts' => ['cash' => 250000],
                'amountCash' => 250000,
            ]);

        $pos->assertHasNoErrors();
        $cashSale = Sale::latest('id')->first();
        $this->assertSame(250000, $cashSale->paid_amount);
        $this->assertSame(50000, $cashSale->change_amount);

        $cashReceipt = $this->get(route('pos.receipt', $cashSale->id));
        $cashReceipt->assertStatus(200);
        $cashReceipt->assertSee('Dibayar:');
        $cashReceipt->assertSee('Rp 250.000');
        $cashReceipt->assertSee('Kembalian:');
        $cashReceipt->assertSee('Rp 50.000');

        $pdfHtml = view('pos.receipt-pdf', [
            'sale' => $cashSale->fresh(['branch', 'customer', 'salesRep', 'creator', 'cashSession.user', 'items.variant.product', 'items.salesRep']),
            'setting' => null,
        ])->render();
        $this->assertStringContainsString('Kembalian', $pdfHtml);
        $this->assertStringContainsString('Rp 50.000', $pdfHtml);

        // Non-cash payment should NOT show Kembalian
        $nonCashSale = Sale::create([
            'branch_id' => $branch->id,
            'cash_session_id' => $session->id,
            'sales_rep_id' => $cashier->id,
            'invoice_number' => 'INV-QRIS-0001',
            'invoice_date' => now()->toDateString(),
            'subtotal' => 200000,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'grand_total' => 200000,
            'paid_amount' => 200000,
            'change_amount' => 0,
            'payment_method' => 'QRIS',
            'status' => 'completed',
            'created_by' => $cashier->id,
        ]);

        $nonCashReceipt = $this->get(route('pos.receipt', $nonCashSale->id));
        $nonCashReceipt->assertStatus(200);
        $nonCashReceipt->assertDontSee('Kembalian:');
    }
}
