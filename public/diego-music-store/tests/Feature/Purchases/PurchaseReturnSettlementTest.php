<?php

namespace Tests\Feature\Purchases;

use App\Actions\Purchases\CreatePurchaseReturn;
use App\Models\Account;
use App\Models\Branch;
use App\Models\JournalEntry;
use App\Models\JournalItem;
use App\Models\Product;
use App\Models\ProductBranchStock;
use App\Models\ProductVariant;
use App\Models\PurchaseTransaction;
use App\Models\PurchaseTransactionDetail;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseReturnSettlementTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Branch $branch;
    protected Supplier $supplier;
    protected Account $bankAccount;

    protected function setUp(): void
    {
        parent::setUp();

        $this->branch = Branch::create(['name' => 'Cabang Utama', 'store_name' => 'Diego Music Store']);
        $this->supplier = Supplier::create(['name' => 'PT Yamaha Musik Indonesia']);
        $this->user = User::factory()->create();
        $this->user->branches()->attach($this->branch->id);
        $this->actingAs($this->user);

        $this->bankAccount = Account::create([
            'code' => '1-1110',
            'name' => 'Bank BCA Operasional',
            'classification' => 'asset',
            'is_active' => true,
            'is_header' => false,
        ]);
    }

    private function createPurchaseSetup(string $purchaseType = 'Kredit', int $qty = 5, int $price = 10000000): array
    {
        $product = Product::create([
            'name' => 'Gitar Akustik Yamaha FSX800C',
            'type' => 'physical',
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'YMH-FSX800C',
            'price' => $price * 1.3,
            'cost_price' => $price,
        ]);

        ProductBranchStock::create([
            'branch_id' => $this->branch->id,
            'product_variant_id' => $variant->id,
            'stock' => $qty,
            'hpp' => $price,
        ]);

        $grandTotal = $qty * $price;

        $pt = PurchaseTransaction::create([
            'transaction_no' => 'PT-' . uniqid(),
            'transaction_date' => now()->toDateString(),
            'supplier_id' => $this->supplier->id,
            'branch_id' => $this->branch->id,
            'purchase_type' => $purchaseType,
            'subtotal' => $grandTotal,
            'grand_total' => $grandTotal,
            'status' => 'posted',
        ]);

        $detail = PurchaseTransactionDetail::create([
            'purchase_transaction_id' => $pt->id,
            'product_variant_id' => $variant->id,
            'qty_po' => $qty,
            'qty_received' => $qty,
            'price' => $price,
            'subtotal' => $grandTotal,
        ]);

        return [$pt, $detail, $variant];
    }

    /** @test */
    public function it_processes_invoice_deduction_and_reduces_unpaid_ap_debt()
    {
        [$pt, $detail, $variant] = $this->createPurchaseSetup('Kredit', 3, 10000000); // 30.000.000 total

        $this->assertEquals(30000000, $pt->getRemainingUnpaidAmount());

        // Return 1 unit (10.000.000) using invoice_deduction
        $purchaseReturn = app(CreatePurchaseReturn::class)->execute([
            'purchase_transaction_id' => $pt->id,
            'reason'                  => 'Retur cacat bodi ke supplier (potong tagihan faktur)',
            'return_type'             => 'invoice_deduction',
            'status'                  => 'posted',
            'items'                   => [
                [
                    'purchase_transaction_detail_id' => $detail->id,
                    'quantity'                       => 1,
                ]
            ]
        ]);

        $this->assertEquals('invoice_deduction', $purchaseReturn->return_type);
        $this->assertEquals('posted', $purchaseReturn->status);
        $this->assertEquals(10000000, $purchaseReturn->total_amount);

        // Check stock reduced from 3 to 2
        $this->assertDatabaseHas('product_branch_stocks', [
            'branch_id' => $this->branch->id,
            'product_variant_id' => $variant->id,
            'stock' => 2,
        ]);

        // Remaining unpaid balance should now be 20.000.000
        $this->assertEquals(20000000, $pt->fresh()->getRemainingUnpaidAmount());

        // Verify Journal: Debit Hutang Dagang (2-1000) 10.000.000, Credit Persediaan (1-1300) 10.000.000
        $journal = JournalEntry::where('reference_type', 'PurchaseReturn')
            ->where('reference_id', $purchaseReturn->id)
            ->first();

        $this->assertNotNull($journal);
        $this->assertEquals('posted', $journal->status);

        $hutangItem = JournalItem::where('journal_entry_id', $journal->id)->where('debit', 10000000)->first();
        $this->assertNotNull($hutangItem);
        $this->assertEquals('2-1000', $hutangItem->account->code);

        $persediaanItem = JournalItem::where('journal_entry_id', $journal->id)->where('credit', 10000000)->first();
        $this->assertNotNull($persediaanItem);
        $this->assertEquals('1-1300', $persediaanItem->account->code);
    }

    /** @test */
    public function it_blocks_invoice_deduction_on_tunai_cash_purchases()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Metode Penyesuaian Faktur hanya berlaku untuk transaksi pembelian Kredit');

        [$pt, $detail] = $this->createPurchaseSetup('Tunai', 2, 5000000);

        app(CreatePurchaseReturn::class)->execute([
            'purchase_transaction_id' => $pt->id,
            'return_type'             => 'invoice_deduction',
            'items'                   => [
                [
                    'purchase_transaction_detail_id' => $detail->id,
                    'quantity'                       => 1,
                ]
            ]
        ]);
    }

    /** @test */
    public function it_processes_refund_return_to_chosen_cash_or_bank_account()
    {
        [$pt, $detail, $variant] = $this->createPurchaseSetup('Tunai', 2, 5000000); // 10.000.000 total

        $purchaseReturn = app(CreatePurchaseReturn::class)->execute([
            'purchase_transaction_id' => $pt->id,
            'reason'                  => 'Refund langsung via transfer Bank BCA',
            'return_type'             => 'refund',
            'refund_account_id'       => $this->bankAccount->id,
            'status'                  => 'posted',
            'items'                   => [
                [
                    'purchase_transaction_detail_id' => $detail->id,
                    'quantity'                       => 1,
                ]
            ]
        ]);

        $this->assertEquals('refund', $purchaseReturn->return_type);
        $this->assertEquals($this->bankAccount->id, $purchaseReturn->refund_account_id);
        $this->assertEquals(5000000, $purchaseReturn->total_amount);

        // Verify Journal: Debit Bank BCA (1-1110) 5.000.000, Credit Persediaan (1-1300) 5.000.000
        $journal = JournalEntry::where('reference_type', 'PurchaseReturn')
            ->where('reference_id', $purchaseReturn->id)
            ->first();

        $this->assertNotNull($journal);

        $bankItem = JournalItem::where('journal_entry_id', $journal->id)->where('account_id', $this->bankAccount->id)->first();
        $this->assertNotNull($bankItem);
        $this->assertEquals(5000000, $bankItem->debit);

        $persediaanItem = JournalItem::where('journal_entry_id', $journal->id)->where('credit', 5000000)->first();
        $this->assertNotNull($persediaanItem);
        $this->assertEquals('1-1300', $persediaanItem->account->code);
    }

    /** @test */
    public function it_processes_replacement_tukar_guling_return()
    {
        [$pt, $detail, $variant] = $this->createPurchaseSetup('Tunai', 5, 2000000);

        $purchaseReturn = app(CreatePurchaseReturn::class)->execute([
            'purchase_transaction_id' => $pt->id,
            'reason'                  => 'Tukar guling unit cacat dengan unit baru',
            'return_type'             => 'replacement',
            'replacement_status'      => 'received',
            'status'                  => 'posted',
            'items'                   => [
                [
                    'purchase_transaction_detail_id' => $detail->id,
                    'quantity'                       => 2,
                ]
            ]
        ]);

        $this->assertEquals('replacement', $purchaseReturn->return_type);
        $this->assertEquals('received', $purchaseReturn->replacement_status);

        // Net physical stock stays at 5 because 2 went out and 2 came back in
        $branchStock = ProductBranchStock::where('branch_id', $this->branch->id)
            ->where('product_variant_id', $variant->id)
            ->first();
        $this->assertEquals(5, $branchStock->stock);

        // Both stock movements exist: 'out' (return) and 'in' (replacement)
        $outMovement = StockMovement::where('reference_type', 'PurchaseReturn')
            ->where('reference_id', $purchaseReturn->id)
            ->where('type', 'out')
            ->first();
        $this->assertNotNull($outMovement);
        $this->assertEquals(2, $outMovement->quantity);

        $inMovement = StockMovement::where('reference_type', 'PurchaseReturnReplacement')
            ->where('reference_id', $purchaseReturn->id)
            ->where('type', 'in')
            ->first();
        $this->assertNotNull($inMovement);
        $this->assertEquals(2, $inMovement->quantity);

        // Tukar guling should NOT create financial journal entries
        $journal = JournalEntry::where('reference_type', 'PurchaseReturn')
            ->where('reference_id', $purchaseReturn->id)
            ->first();
        $this->assertNull($journal);
    }

    /** @test */
    public function it_processes_supplier_credit_deposit_return()
    {
        [$pt, $detail, $variant] = $this->createPurchaseSetup('Kredit', 4, 3000000); // 12.000.000 total

        $purchaseReturn = app(CreatePurchaseReturn::class)->execute([
            'purchase_transaction_id' => $pt->id,
            'reason'                  => 'Retur dialokasikan sebagai Deposit / Nota Kredit Supplier',
            'return_type'             => 'supplier_credit',
            'status'                  => 'posted',
            'items'                   => [
                [
                    'purchase_transaction_detail_id' => $detail->id,
                    'quantity'                       => 1,
                ]
            ]
        ]);

        $this->assertEquals('supplier_credit', $purchaseReturn->return_type);
        $this->assertEquals(3000000, $purchaseReturn->total_amount);

        // Verify Journal: Debit Uang Muka Pembelian/Deposit Supplier (1-1400) 3.000.000, Credit Persediaan (1-1300) 3.000.000
        $journal = JournalEntry::where('reference_type', 'PurchaseReturn')
            ->where('reference_id', $purchaseReturn->id)
            ->first();

        $this->assertNotNull($journal);

        $depositItem = JournalItem::where('journal_entry_id', $journal->id)->where('debit', 3000000)->first();
        $this->assertNotNull($depositItem);
        $this->assertEquals('1-1400', $depositItem->account->code);

        $persediaanItem = JournalItem::where('journal_entry_id', $journal->id)->where('credit', 3000000)->first();
        $this->assertNotNull($persediaanItem);
        $this->assertEquals('1-1300', $persediaanItem->account->code);
    }

    /** @test */
    public function it_can_create_purchase_return_via_filament_modal_action()
    {
        [$pt, $detail, $variant] = $this->createPurchaseSetup('Kredit', 5, 2000000);

        \Livewire\Livewire::test(\App\Filament\Resources\PurchaseReturns\Pages\ListPurchaseReturns::class)
            ->callAction('create_return', data: [
                'purchase_transaction_id' => $pt->id,
                'return_type'             => 'invoice_deduction',
                'reason'                  => 'Retur dari modal action',
                'status'                  => 'posted',
                'return_items'            => [
                    $detail->id => 2,
                ],
            ])
            ->assertHasNoActionErrors();

        $this->assertDatabaseHas('purchase_returns', [
            'purchase_transaction_id' => $pt->id,
            'total_amount'            => 4000000,
            'return_type'             => 'invoice_deduction',
            'status'                  => 'posted',
        ]);

        $this->assertDatabaseHas('purchase_return_items', [
            'purchase_transaction_detail_id' => $detail->id,
            'quantity'                       => 2,
            'total_price'                    => 4000000,
        ]);
    }

    /** @test */
    public function it_can_update_a_draft_purchase_return()
    {
        [$pt, $detail, $variant] = $this->createPurchaseSetup('Kredit', 5, 1000000);

        // Create draft return with 1 item
        $purchaseReturn = app(CreatePurchaseReturn::class)->execute([
            'purchase_transaction_id' => $pt->id,
            'reason'                  => 'Draft awal 1 unit',
            'return_type'             => 'invoice_deduction',
            'status'                  => 'draft',
            'items'                   => [
                [
                    'purchase_transaction_detail_id' => $detail->id,
                    'quantity'                       => 1,
                ]
            ]
        ]);

        $this->assertEquals('draft', $purchaseReturn->status);
        $this->assertEquals(1000000, $purchaseReturn->total_amount);

        // Update draft return to 3 units
        $updated = app(\App\Actions\Purchases\UpdatePurchaseReturn::class)->execute($purchaseReturn, [
            'reason'       => 'Revisi draft menjadi 3 unit',
            'return_type'  => 'invoice_deduction',
            'status'       => 'draft',
            'return_items' => [
                $detail->id => 3,
            ],
        ]);

        $this->assertEquals(3000000, $updated->total_amount);
        $this->assertEquals('Revisi draft menjadi 3 unit', $updated->reason);
        $this->assertDatabaseHas('purchase_return_items', [
            'purchase_return_id'             => $updated->id,
            'purchase_transaction_detail_id' => $detail->id,
            'quantity'                       => 3,
            'total_price'                    => 3000000,
        ]);
    }

    /** @test */
    public function it_cannot_update_or_delete_a_posted_purchase_return()
    {
        [$pt, $detail, $variant] = $this->createPurchaseSetup('Tunai', 3, 2000000);

        // Create posted return
        $purchaseReturn = app(CreatePurchaseReturn::class)->execute([
            'purchase_transaction_id' => $pt->id,
            'reason'                  => 'Posted return',
            'return_type'             => 'refund',
            'refund_account_id'       => $this->bankAccount->id,
            'status'                  => 'posted',
            'items'                   => [
                [
                    'purchase_transaction_detail_id' => $detail->id,
                    'quantity'                       => 1,
                ]
            ]
        ]);

        $this->assertEquals('posted', $purchaseReturn->status);

        // Expect exception when trying to update
        try {
            app(\App\Actions\Purchases\UpdatePurchaseReturn::class)->execute($purchaseReturn, [
                'reason' => 'Should fail',
            ]);
            $this->fail('Expected exception when updating posted return');
        } catch (\Exception $e) {
            $this->assertStringContainsString('Tidak dapat mengubah', $e->getMessage());
        }

        // Expect exception when trying to delete
        try {
            app(\App\Actions\Purchases\DeletePurchaseReturn::class)->execute($purchaseReturn);
            $this->fail('Expected exception when deleting posted return');
        } catch (\Exception $e) {
            $this->assertStringContainsString('Tidak dapat menghapus', $e->getMessage());
        }
    }

    /** @test */
    public function it_can_delete_a_draft_purchase_return()
    {
        [$pt, $detail, $variant] = $this->createPurchaseSetup('Kredit', 4, 1500000);

        $purchaseReturn = app(CreatePurchaseReturn::class)->execute([
            'purchase_transaction_id' => $pt->id,
            'reason'                  => 'Draft retur yang akan dihapus',
            'return_type'             => 'invoice_deduction',
            'status'                  => 'draft',
            'items'                   => [
                [
                    'purchase_transaction_detail_id' => $detail->id,
                    'quantity'                       => 2,
                ]
            ]
        ]);

        $returnId = $purchaseReturn->id;

        $deleted = app(\App\Actions\Purchases\DeletePurchaseReturn::class)->execute($purchaseReturn);
        $this->assertTrue($deleted);

        $this->assertDatabaseMissing('purchase_returns', ['id' => $returnId]);
        $this->assertDatabaseMissing('purchase_return_items', ['purchase_return_id' => $returnId]);
    }

    /** @test */
    public function it_can_edit_and_delete_draft_purchase_return_via_table_actions()
    {
        [$pt, $detail, $variant] = $this->createPurchaseSetup('Kredit', 5, 1000000);

        $purchaseReturn = app(CreatePurchaseReturn::class)->execute([
            'purchase_transaction_id' => $pt->id,
            'reason'                  => 'Awal draft',
            'return_type'             => 'invoice_deduction',
            'status'                  => 'draft',
            'items'                   => [
                [
                    'purchase_transaction_detail_id' => $detail->id,
                    'quantity'                       => 1,
                ]
            ]
        ]);

        // Test Edit action on table
        \Livewire\Livewire::test(\App\Filament\Resources\PurchaseReturns\Pages\ListPurchaseReturns::class)
            ->callTableAction('edit', $purchaseReturn, data: [
                'purchase_transaction_id' => $pt->id,
                'return_type'             => 'invoice_deduction',
                'reason'                  => 'Diedit via table action',
                'status'                  => 'draft',
                'return_items'            => [
                    $detail->id => 2,
                ],
            ])
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('purchase_returns', [
            'id'           => $purchaseReturn->id,
            'reason'       => 'Diedit via table action',
            'total_amount' => 2000000,
        ]);

        // Test Delete action on table
        \Livewire\Livewire::test(\App\Filament\Resources\PurchaseReturns\Pages\ListPurchaseReturns::class)
            ->callTableAction('delete', $purchaseReturn)
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseMissing('purchase_returns', [
            'id' => $purchaseReturn->id,
        ]);
    }

    /** @test */
    public function it_can_open_detail_modal_on_table()
    {
        [$pt, $detail, $variant] = $this->createPurchaseSetup('Kredit', 3, 2000000);

        $purchaseReturn = app(CreatePurchaseReturn::class)->execute([
            'purchase_transaction_id' => $pt->id,
            'reason'                  => 'Detail modal test',
            'return_type'             => 'invoice_deduction',
            'status'                  => 'posted',
            'items'                   => [
                [
                    'purchase_transaction_detail_id' => $detail->id,
                    'quantity'                       => 1,
                ]
            ]
        ]);

        // Verify that ViewAction exists on the table
        \Livewire\Livewire::test(\App\Filament\Resources\PurchaseReturns\Pages\ListPurchaseReturns::class)
            ->assertTableActionExists('view');

        // Verify that the detail modal blade renders all details cleanly
        $view = $this->view('filament.components.purchase-return-detail', [
            'record' => $purchaseReturn->loadMissing(['items.productVariant.product', 'purchaseTransaction', 'supplier', 'branch', 'refundAccount', 'creator']),
        ]);

        $view->assertSee($purchaseReturn->return_no)
            ->assertSee('Potong Faktur Tempo')
            ->assertSee($variant->product->name)
            ->assertSee('Rincian Barang yang Diretur ke Supplier');
    }
}
