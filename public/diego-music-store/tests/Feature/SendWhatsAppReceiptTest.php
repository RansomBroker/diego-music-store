<?php

namespace Tests\Feature;

use App\Actions\Notification\SendWhatsAppReceipt;
use App\Livewire\POS;
use App\Models\Branch;
use App\Models\CashSession;
use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductBranchStock;
use App\Models\ProductVariant;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SendWhatsAppReceiptTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Branch $branch;
    protected Customer $customer;
    protected ProductVariant $variant;
    protected CashSession $cashSession;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'cashier', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'sales', 'guard_name' => 'web']);
        $this->user = User::factory()->create();
        $this->user->assignRole('cashier');

        $this->branch = Branch::create([
            'name' => 'Cabang Utama',
            'store_name' => 'Diego Music Store',
            'phone' => '081234567890',
            'address' => 'Jl. Merdeka No. 10',
            'fonnte_token' => 'mock-fonnte-token-xyz',
            'fonnte_whatsapp_number' => '081234567890',
            'is_whatsapp_enabled' => true,
            'is_active' => true,
        ]);

        $this->user->branches()->attach($this->branch->id);

        $this->customer = Customer::create([
            'name' => 'Budi Santoso',
            'phone' => '085712345678',
            'email' => 'budi@example.com',
            'is_loyalty_member' => true,
            'loyalty_points' => 100,
        ]);

        $product = Product::create([
            'name' => 'Gitar Akustik Yamaha F310',
            'type' => 'single',
            'is_active' => true,
        ]);

        $this->variant = ProductVariant::create([
            'product_id' => $product->id,
            'name' => 'Natural',
            'sku' => 'GTR-YAM-F310-NAT',
            'price' => 1500000,
            'cost_price' => 1100000,
        ]);

        // Branch stock
        ProductBranchStock::create([
            'branch_id' => $this->branch->id,
            'product_variant_id' => $this->variant->id,
            'stock' => 10,
        ]);

        $this->cashSession = CashSession::create([
            'user_id' => $this->user->id,
            'branch_id' => $this->branch->id,
            'opening_balance' => 500000,
            'status' => 'open',
            'opened_at' => now(),
        ]);
    }

    public function test_it_sends_whatsapp_receipt_successfully_via_action(): void
    {
        Http::fake([
            'https://api.fonnte.com/send' => Http::response([
                'status' => true,
                'target' => ['6285712345678'],
                'message' => 'Message sent successfully',
            ], 200),
        ]);

        $sale = Sale::create([
            'branch_id' => $this->branch->id,
            'customer_id' => $this->customer->id,
            'sales_rep_id' => $this->user->id,
            'created_by' => $this->user->id,
            'invoice_number' => 'INV-20260922-0001',
            'invoice_date' => now(),
            'subtotal' => 1500000,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'grand_total' => 1500000,
            'payment_method' => 'cash',
            'status' => 'completed',
        ]);

        SaleItem::create([
            'sale_id' => $sale->id,
            'product_variant_id' => $this->variant->id,
            'quantity' => 1,
            'unit_price' => 1500000,
            'discount_amount' => 0,
            'total_price' => 1500000,
        ]);

        $action = new SendWhatsAppReceipt();
        $result = $action->execute($sale);

        $this->assertTrue($result['success']);

        Http::assertSent(function ($request) {
            $data = collect($request->data());
            $target = $data->firstWhere('name', 'target')['contents'] ?? null;
            $filename = $data->firstWhere('name', 'filename')['contents'] ?? null;
            $message = $data->firstWhere('name', 'message')['contents'] ?? null;

            return $request->url() === 'https://api.fonnte.com/send'
                && $target === '085712345678'
                && $request->isMultipart()
                && $request->hasFile('file')
                && str_contains($filename ?? '', 'Struk-INV-20260922-0001.pdf')
                && str_contains($message ?? '', 'STRUK PEMBELIAN');
        });
    }

    public function test_it_fails_when_branch_whatsapp_is_disabled(): void
    {
        $this->branch->update(['is_whatsapp_enabled' => false]);

        $sale = Sale::create([
            'branch_id' => $this->branch->id,
            'customer_id' => $this->customer->id,
            'sales_rep_id' => $this->user->id,
            'created_by' => $this->user->id,
            'invoice_number' => 'INV-20260922-0002',
            'invoice_date' => now(),
            'subtotal' => 1500000,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'grand_total' => 1500000,
            'payment_method' => 'cash',
            'status' => 'completed',
        ]);

        $action = new SendWhatsAppReceipt();
        $result = $action->execute($sale);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('dinonaktifkan', $result['message']);
    }

    public function test_it_fails_when_target_phone_is_missing(): void
    {
        $sale = Sale::create([
            'branch_id' => $this->branch->id,
            'customer_id' => null,
            'sales_rep_id' => $this->user->id,
            'created_by' => $this->user->id,
            'invoice_number' => 'INV-20260922-0003',
            'invoice_date' => now(),
            'subtotal' => 1500000,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'grand_total' => 1500000,
            'payment_method' => 'cash',
            'status' => 'completed',
        ]);

        $action = new SendWhatsAppReceipt();
        $result = $action->execute($sale, '');

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('tidak ditemukan', $result['message']);
    }

    public function test_pos_checkout_sends_whatsapp_when_send_whatsapp_is_true(): void
    {
        Http::fake([
            'https://api.fonnte.com/send' => Http::response([
                'status' => true,
                'target' => ['6285712345678'],
                'message' => 'Receipt sent successfully',
            ], 200),
        ]);

        $this->session(['pos_active_branch_id' => $this->branch->id]);

        Livewire::actingAs($this->user)
            ->test(POS::class)
            ->set('selectedBranchId', $this->branch->id)
            ->call('addToCart', $this->variant->id)
            ->call('selectCustomer', $this->customer->id, $this->customer->name, true)
            ->assertSet('customerPhone', '085712345678')
            ->set('amountCash', 1500000)
            ->set('selectedPaymentMethods', ['cash'])
            ->call('checkout', true)
            ->assertDispatched('print-receipt');

        Http::assertSent(function ($request) {
            $data = collect($request->data());
            $target = $data->firstWhere('name', 'target')['contents'] ?? null;

            return $request->url() === 'https://api.fonnte.com/send'
                && $target === '085712345678'
                && $request->isMultipart()
                && $request->hasFile('file');
        });
    }

    public function test_pos_checkout_only_prints_receipt_when_send_whatsapp_is_false(): void
    {
        Http::fake();

        $this->session(['pos_active_branch_id' => $this->branch->id]);

        Livewire::actingAs($this->user)
            ->test(POS::class)
            ->set('selectedBranchId', $this->branch->id)
            ->call('addToCart', $this->variant->id)
            ->call('selectCustomer', $this->customer->id, $this->customer->name, true)
            ->set('amountCash', 1500000)
            ->set('selectedPaymentMethods', ['cash'])
            ->call('checkout', false)
            ->assertDispatched('print-receipt');

        Http::assertNothingSent();
    }
}
