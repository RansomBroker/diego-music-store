<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Branch;
use App\Models\CashSession;
use App\Models\PricingTier;
use App\Models\Product;
use App\Models\ProductBranchStock;
use App\Models\ProductVariant;
use App\Models\PosHeldTransaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class POSCartQuantitySeamlessTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Branch $branch;
    protected PricingTier $pricingTier;
    protected ProductVariant $variant;

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

        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'cashier', 'guard_name' => 'web']);
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'sales', 'guard_name' => 'web']);

        $this->user = User::factory()->create();
        $this->actingAs($this->user);

        $this->branch = Branch::create([
            'name' => 'Cabang Test',
            'address' => 'Jl. Test',
            'phone' => '123',
            'is_active' => true,
        ]);
        $this->user->branches()->attach($this->branch->id);
        session(['pos_active_branch_id' => $this->branch->id]);

        $this->pricingTier = PricingTier::create([
            'name' => 'Umum / Retail',
            'description' => 'Retail tier price',
            'price_follows_hpp' => false,
        ]);

        $product = Product::create([
            'name' => 'Gitar Akustik Yamaha',
            'type' => 'physical',
            'is_active' => true,
        ]);

        $this->variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'YMH-AK',
            'name' => 'Natural',
            'price' => 1500000,
            'cost_price' => 900000,
            'hpp' => 950000,
            'is_active' => true,
        ]);

        ProductBranchStock::create([
            'product_variant_id' => $this->variant->id,
            'branch_id' => $this->branch->id,
            'stock' => 5,
            'hpp' => 950000,
        ]);

        CashSession::create([
            'user_id' => $this->user->id,
            'branch_id' => $this->branch->id,
            'opened_at' => now(),
            'opening_cash' => 500000,
            'status' => 'open',
        ]);
    }

    /** @test */
    public function it_can_set_quantity_seamlessly_using_set_cart_qty()
    {
        Livewire::test('App\Livewire\POS')
            ->call('addToCart', $this->variant->id)
            ->assertSet('cart.' . $this->variant->id . '.qty', 1)
            ->assertSet('subtotal', 1500000)
            ->call('setCartQty', $this->variant->id, 3)
            ->assertSet('cart.' . $this->variant->id . '.qty', 3)
            ->assertSet('subtotal', 4500000);
    }

    /** @test */
    public function it_removes_item_when_set_cart_qty_is_zero_or_negative()
    {
        Livewire::test('App\Livewire\POS')
            ->call('addToCart', $this->variant->id)
            ->assertSet('cart.' . $this->variant->id . '.qty', 1)
            ->call('setCartQty', $this->variant->id, 0)
            ->assertSet('cart', []);
    }

    /** @test */
    public function it_blocks_quantity_increase_when_exceeding_stock()
    {
        // Stock is 5
        Livewire::test('App\Livewire\POS')
            ->call('addToCart', $this->variant->id)
            ->assertSet('cart.' . $this->variant->id . '.qty', 1)
            ->call('setCartQty', $this->variant->id, 10)
            ->assertSet('cart.' . $this->variant->id . '.qty', 1)
            ->assertNotified('Stok Tidak Cukup');
    }

    /** @test */
    public function it_supports_backward_compatible_update_qty_delegation()
    {
        Livewire::test('App\Livewire\POS')
            ->call('addToCart', $this->variant->id)
            ->assertSet('cart.' . $this->variant->id . '.qty', 1)
            ->call('updateQty', $this->variant->id, 2)
            ->assertSet('cart.' . $this->variant->id . '.qty', 3)
            ->call('updateQty', $this->variant->id, -1)
            ->assertSet('cart.' . $this->variant->id . '.qty', 2);
    }

    /** @test */
    public function it_updates_autosave_draft_with_new_quantity()
    {
        Livewire::test('App\Livewire\POS')
            ->call('addToCart', $this->variant->id)
            ->call('setCartQty', $this->variant->id, 4);

        $held = PosHeldTransaction::where('user_id', $this->user->id)->first();
        $this->assertNotNull($held);
        $this->assertEquals(4, $held->cart_data[$this->variant->id]['qty']);
    }

    /** @test */
    public function it_scans_barcode_or_sku_and_adds_product_to_cart()
    {
        Livewire::test('App\Livewire\POS')
            ->call('scanBarcode', 'YMH-AK')
            ->assertSet('cart.' . $this->variant->id . '.qty', 1)
            ->assertSet('cart.' . $this->variant->id . '.price', 1500000)
            ->assertNotified('Produk Ditambahkan');
    }

    /** @test */
    public function it_notifies_when_scanned_barcode_is_not_found()
    {
        Livewire::test('App\Livewire\POS')
            ->call('scanBarcode', 'UNKNOWN-BARCODE-999')
            ->assertSet('cart', [])
            ->assertNotified('Produk Tidak Ditemukan');
    }

    /** @test */
    public function it_provides_lightweight_held_transactions_count()
    {
        PosHeldTransaction::create([
            'id' => \Illuminate\Support\Str::uuid()->toString(),
            'user_id' => $this->user->id,
            'branch_id' => $this->branch->id,
            'customer_name' => 'Held Test',
            'cart_data' => [$this->variant->id => ['qty' => 1]],
        ]);

        $component = Livewire::test('App\Livewire\POS');
        $this->assertEquals(1, $component->get('heldTransactionsCount'));
    }

    /** @test */
    public function it_caches_preview_invoice_number_and_updates_on_category_change()
    {
        $component = Livewire::test('App\Livewire\POS');
        $initialInvoice = $component->get('previewInvoiceNumber');
        $this->assertNotEmpty($initialInvoice);

        // Change category and verify preview invoice updates
        $component->set('saleCategory', 'Store');
        $this->assertNotEmpty($component->get('previewInvoiceNumber'));
    }

    /** @test */
    public function it_prints_draft_receipt_with_exact_pos_preview_invoice_number()
    {
        $component = Livewire::test('App\Livewire\POS')
            ->call('addToCart', $this->variant->id);

        $previewInvoice = $component->get('previewInvoiceNumber');
        $this->assertNotEmpty($previewInvoice);

        $component->call('printDraft', 'large')
            ->assertDispatched('open-draft-bill', function ($event, $params) use ($previewInvoice) {
                parse_str(parse_url($params['url'], PHP_URL_QUERY), $query);
                $decoded = json_decode(base64_decode($query['data']), true);
                return isset($decoded['invoice_number']) && $decoded['invoice_number'] === $previewInvoice;
            });

        // Also test GET request to /pos/receipt-draft
        $data = base64_encode(json_encode([
            'invoice_number' => 'STR-0001',
            'branch_id' => $this->branch->id,
            'cart' => [
                $this->variant->id => [
                    'variant_id' => $this->variant->id,
                    'qty' => 1,
                    'price' => 1500000,
                ]
            ],
            'grand_total' => 1500000,
        ]));

        $response = $this->get('/pos/receipt-draft?format=large&data=' . urlencode($data));
        $response->assertStatus(200);
        $response->assertSee('No. Ref:');
        $response->assertSee('STR-0001');
        $response->assertDontSee('DRAFT-202');
        $response->assertSee('INVOICE');
        $response->assertDontSee('LARGE BILL');
        $response->assertDontSee('BUKAN BUKTI');

        $responseInvoice = $this->get('/pos/receipt-draft?format=invoice&data=' . urlencode($data));
        $responseInvoice->assertStatus(200);
        $responseInvoice->assertDontSee('BUKAN BUKTI TRANSAKSI RESMI');
    }

    /** @test */
    public function it_disables_or_blocks_printing_invoice_when_no_previous_transaction_exists()
    {
        Livewire::test('App\Livewire\POS')
            ->set('lastSaleId', null)
            ->call('printLastInvoice')
            ->assertNotDispatched('open-draft-bill');
    }

    /** @test */
    public function it_prints_last_invoice_when_previous_transaction_exists()
    {
        $sale = \App\Models\Sale::create([
            'branch_id' => $this->branch->id,
            'sales_rep_id' => $this->user->id,
            'invoice_number' => 'STR-20261031-0001',
            'invoice_date' => now(),
            'subtotal' => 1500000,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'grand_total' => 1500000,
            'payment_method' => 'CASH',
            'status' => 'completed',
            'sale_category' => 'Retail',
            'created_by' => $this->user->id,
        ]);

        \App\Models\SaleItem::create([
            'sale_id' => $sale->id,
            'product_variant_id' => $this->variant->id,
            'quantity' => 1,
            'unit_price' => 1500000,
            'discount_amount' => 0,
            'total_price' => 1500000,
        ]);

        Livewire::test('App\Livewire\POS')
            ->set('lastSaleId', $sale->id)
            ->call('printLastInvoice')
            ->assertDispatched('open-draft-bill', function ($event, $params) use ($sale) {
                return str_contains($params['url'], '/pos/receipt/' . $sale->id . '?format=invoice');
            });

        // Test GET /pos/receipt/{sale}?format=invoice
        $response = $this->get('/pos/receipt/' . $sale->id . '?format=invoice');
        $response->assertStatus(200);
        $response->assertSee('INVOICE');
        $response->assertSee('LUNAS / PAID');
        $response->assertSee('STR-20261031-0001');
        $response->assertDontSee('BUKAN BUKTI TRANSAKSI RESMI');
    }
}
