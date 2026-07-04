<?php

namespace Tests\Feature\Actions;

use App\Actions\Sales\CreatePOSSale;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductBranchStock;
use App\Models\StockMovement;
use App\Models\JournalEntry;
use App\Models\JournalItem;
use App\Models\Account;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class POSActionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create standard COA
        Account::firstOrCreate(['code' => '1-1000'], ['name' => 'Kas Utama', 'classification' => 'Asset', 'is_active' => true]);
        Account::firstOrCreate(['code' => '1-1200'], ['name' => 'Piutang Dagang', 'classification' => 'Asset', 'is_active' => true]);
        Account::firstOrCreate(['code' => '1-1110'], ['name' => 'Bank BCA', 'classification' => 'Asset', 'is_active' => true]);
        Account::firstOrCreate(['code' => '1-1300'], ['name' => 'Persediaan Barang', 'classification' => 'Asset', 'is_active' => true]);
        Account::firstOrCreate(['code' => '4-1000'], ['name' => 'Pendapatan Penjualan', 'classification' => 'Revenue', 'is_active' => true]);
        Account::firstOrCreate(['code' => '5-1000'], ['name' => 'Harga Pokok Penjualan', 'classification' => 'Expense', 'is_active' => true]);
    }

    public function test_it_can_process_pos_checkout_successfully(): void
    {
        // 1. Arrange
        $user = User::factory()->create();
        $this->actingAs($user);

        $branch = Branch::create([
            'name' => 'Cabang Test',
            'address' => 'Jl. Test',
            'phone' => '123',
            'is_active' => true,
        ]);

        $customer = Customer::create([
            'name' => 'Budi',
            'phone' => '0812',
            'is_loyalty_member' => true,
            'loyalty_points' => 10,
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

        // Seed stock of 5
        ProductBranchStock::create([
            'product_variant_id' => $variant->id,
            'branch_id' => $branch->id,
            'stock' => 5,
            'hpp' => 1250000,
        ]);

        $data = [
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'payment_method' => 'cash',
            'discount_amount' => 50000, // Factur discount
            'tax_amount' => 0,
            'items' => [
                [
                    'variant_id' => $variant->id,
                    'qty' => 2,
                    'price' => 2000000,
                    'discount_amount' => 10000, // item discount
                ]
            ]
        ];

        // 2. Act
        $action = new CreatePOSSale();
        $sale = $action->execute($data);

        // 3. Assert
        $this->assertDatabaseHas('sales', [
            'id' => $sale->id,
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'subtotal' => 3980000, // (2,000,000 * 2) - 10,000 discount = 3,980,000
            'discount_amount' => 50000,
            'grand_total' => 3930000, // 3,980,000 - 50,000 = 3,930,000
            'status' => 'completed',
        ]);

        $this->assertDatabaseHas('sale_items', [
            'sale_id' => $sale->id,
            'product_variant_id' => $variant->id,
            'quantity' => 2,
            'unit_price' => 2000000,
            'discount_amount' => 10000,
            'total_price' => 3980000,
        ]);

        // Check stock depleted to 3 (5 - 2)
        $branchStock = ProductBranchStock::where([
            'product_variant_id' => $variant->id,
            'branch_id' => $branch->id,
        ])->first();
        $this->assertEquals(3, $branchStock->stock);

        // Check stock movement logged
        $this->assertDatabaseHas('stock_movements', [
            'product_variant_id' => $variant->id,
            'branch_id' => $branch->id,
            'type' => 'out',
            'quantity' => 2,
            'reference_type' => 'POS',
            'reference_id' => $sale->id,
        ]);

        // Check Journal Entries
        $journal = JournalEntry::where([
            'reference_type' => 'Sales',
            'reference_id' => $sale->id,
        ])->first();
        $this->assertNotNull($journal);
        $this->assertEquals('posted', $journal->status);

        // Check Journal Items
        // Debit Kas Utama = 3,930,000
        $this->assertDatabaseHas('journal_items', [
            'journal_entry_id' => $journal->id,
            'account_id' => Account::where('code', '1-1000')->first()->id,
            'debit' => 3930000,
            'credit' => 0,
        ]);

        // Credit Pendapatan Penjualan = 3,930,000
        $this->assertDatabaseHas('journal_items', [
            'journal_entry_id' => $journal->id,
            'account_id' => Account::where('code', '4-1000')->first()->id,
            'debit' => 0,
            'credit' => 3930000,
        ]);

        // Debit COGS = 2,500,000 (1,250,000 HPP * 2)
        $this->assertDatabaseHas('journal_items', [
            'journal_entry_id' => $journal->id,
            'account_id' => Account::where('code', '5-1000')->first()->id,
            'debit' => 2500000,
            'credit' => 0,
        ]);

        // Credit Persediaan = 2,500,000
        $this->assertDatabaseHas('journal_items', [
            'journal_entry_id' => $journal->id,
            'account_id' => Account::where('code', '1-1300')->first()->id,
            'debit' => 0,
            'credit' => 2500000,
        ]);
    }
}
