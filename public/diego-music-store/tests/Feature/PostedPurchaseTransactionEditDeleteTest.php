<?php

namespace Tests\Feature;

use App\Actions\Procurement\CreatePurchaseTransaction;
use App\Actions\Procurement\DeletePurchaseTransaction;
use App\Actions\Procurement\PostPurchaseTransaction;
use App\Actions\Procurement\UpdatePurchaseTransaction;
use App\Filament\Resources\PurchaseTransactions\PurchaseTransactionResource;
use App\Models\Branch;
use App\Models\JournalEntry;
use App\Models\JournalItem;
use App\Models\Product;
use App\Models\ProductBranchStock;
use App\Models\ProductVariant;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\PurchaseReturn;
use App\Models\PurchaseTransaction;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\SupplierPayment;
use App\Models\SupplierPaymentItem;
use App\Models\Unit;
use App\Models\User;
use Database\Seeders\AccountSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class PostedPurchaseTransactionEditDeleteTest extends TestCase
{
    use RefreshDatabase;

    private Branch $branch;
    private Supplier $supplier;
    private ProductVariant $variant;
    private Unit $unit;
    private User $owner;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleAndPermissionSeeder::class);
        $this->seed(AccountSeeder::class);

        $this->branch = Branch::create([
            'name' => 'Cabang Utama',
            'code' => 'CBG-PT-01',
            'is_active' => true,
        ]);

        $this->supplier = Supplier::create([
            'name' => 'Supplier Gitar Test',
            'code' => 'SUP-PT-01',
            'outstanding_debt' => 0,
            'is_active' => true,
        ]);

        $this->unit = Unit::create([
            'name' => 'Pcs',
            'code' => 'PCS',
            'conversion_factor' => 1,
            'is_active' => true,
        ]);

        $product = Product::create([
            'name' => 'Gitar Akustik Test',
            'category' => 'Gitar',
            'type' => 'physical',
            'unit_id' => $this->unit->id,
            'is_active' => true,
        ]);

        $this->variant = ProductVariant::create([
            'product_id' => $product->id,
            'name' => 'Standard',
            'sku' => 'GTR-TEST-01',
            'price' => 1500000,
            'cost_price' => 1000000,
            'hpp' => 1000000,
            'is_active' => true,
        ]);

        $this->owner = User::factory()->create(['is_active' => true]);
        $this->owner->assignRole('owner');
        $this->owner->branches()->attach($this->branch->id);
    }

    private function createPostedPurchase(array $overrides = []): PurchaseTransaction
    {
        $payload = array_merge([
            'transaction_date' => now()->format('Y-m-d'),
            'supplier_id' => $this->supplier->id,
            'branch_id' => $this->branch->id,
            'warehouse_id' => $this->branch->id,
            'purchase_type' => PurchaseTransaction::TYPE_KREDIT,
            'due_date' => now()->addDays(14)->format('Y-m-d'),
            'status' => 'posted',
            'shipping_borne_by' => 'supplier',
            'shipping_cost' => 0,
            'items' => [
                [
                    'product_variant_id' => $this->variant->id,
                    'qty_received' => 5,
                    'qty_bonus' => 0,
                    'unit_id' => $this->unit->id,
                    'price' => 1000000,
                    'discount_value' => 0,
                    'tax_rate' => 0,
                ],
            ],
        ], $overrides);

        return app(CreatePurchaseTransaction::class)->execute($payload);
    }

    public function test_rbac_separates_draft_and_posted_edit_delete_permissions(): void
    {
        $this->actingAs($this->owner);

        $draftPt = $this->createPostedPurchase(['status' => 'draft']);
        $postedPt = $this->createPostedPurchase(['status' => 'posted']);

        // Role hanya punya izin edit & delete draft (tanpa edit_posted & delete_posted)
        $draftOnlyRole = Role::firstOrCreate(['name' => 'staff_purchasing_draft', 'guard_name' => 'web']);
        $draftOnlyRole->syncPermissions([
            'bo.access',
            'bo.purchase_transactions.view',
            'bo.purchase_transactions.edit',
            'bo.purchase_transactions.delete',
        ]);

        $draftUser = User::factory()->create(['is_active' => true]);
        $draftUser->assignRole($draftOnlyRole);
        $draftUser->branches()->attach($this->branch->id);

        $this->actingAs($draftUser);
        $this->assertTrue(PurchaseTransactionResource::canEdit($draftPt));
        $this->assertTrue(PurchaseTransactionResource::canDelete($draftPt));
        $this->assertFalse(PurchaseTransactionResource::canEdit($postedPt));
        $this->assertFalse(PurchaseTransactionResource::canDelete($postedPt));

        // Mencoba update/delete posted via action harus ditolak 403
        try {
            app(UpdatePurchaseTransaction::class)->execute($postedPt, [
                'transaction_date' => now()->format('Y-m-d'),
                'supplier_id' => $this->supplier->id,
                'branch_id' => $this->branch->id,
                'warehouse_id' => $this->branch->id,
                'purchase_type' => PurchaseTransaction::TYPE_KREDIT,
                'items' => [[
                    'product_variant_id' => $this->variant->id,
                    'qty_received' => 2,
                    'unit_id' => $this->unit->id,
                    'price' => 1000000,
                ]],
            ]);
            $this->fail('Expected 403 HttpException when non-permitted user edits posted transaction.');
        } catch (HttpException $e) {
            $this->assertEquals(403, $e->getStatusCode());
        }

        try {
            app(DeletePurchaseTransaction::class)->execute($postedPt);
            $this->fail('Expected 403 HttpException when non-permitted user deletes posted transaction.');
        } catch (HttpException $e) {
            $this->assertEquals(403, $e->getStatusCode());
        }

        // Role dengan izin edit_posted & delete_posted dapat mengedit dan menghapus transaksi posted
        $postedRole = Role::firstOrCreate(['name' => 'supervisor_purchasing', 'guard_name' => 'web']);
        $postedRole->syncPermissions([
            'bo.access',
            'bo.purchase_transactions.view',
            'bo.purchase_transactions.edit_posted',
            'bo.purchase_transactions.delete_posted',
        ]);

        $supervisor = User::factory()->create(['is_active' => true]);
        $supervisor->assignRole($postedRole);
        $supervisor->branches()->attach($this->branch->id);

        $this->actingAs($supervisor);
        $this->assertTrue(PurchaseTransactionResource::canEdit($postedPt));
        $this->assertTrue(PurchaseTransactionResource::canDelete($postedPt));
    }

    public function test_editing_posted_purchase_rolls_back_and_reposts_stock_debt_and_journal(): void
    {
        $this->actingAs($this->owner);

        $pt = $this->createPostedPurchase();
        $oldJournalId = $pt->journal_entry_id;

        $this->assertEquals(5, ProductBranchStock::where('product_variant_id', $this->variant->id)->where('branch_id', $this->branch->id)->value('stock'));
        $this->assertEquals(5000000, (int) $this->supplier->fresh()->outstanding_debt);
        $this->assertNotNull($oldJournalId);

        // Edit transaksi posted: ubah qty dari 5 -> 3 dan harga 1.000.000 -> 1.200.000 (total 3.600.000)
        $updated = app(UpdatePurchaseTransaction::class)->execute($pt, [
            'transaction_date' => now()->format('Y-m-d'),
            'supplier_id' => $this->supplier->id,
            'branch_id' => $this->branch->id,
            'warehouse_id' => $this->branch->id,
            'purchase_type' => PurchaseTransaction::TYPE_KREDIT,
            'due_date' => now()->addDays(30)->format('Y-m-d'),
            'shipping_borne_by' => 'supplier',
            'shipping_cost' => 0,
            'items' => [
                [
                    'product_variant_id' => $this->variant->id,
                    'qty_received' => 3,
                    'qty_bonus' => 0,
                    'unit_id' => $this->unit->id,
                    'price' => 1200000,
                    'discount_value' => 0,
                    'tax_rate' => 0,
                ],
            ],
        ]);

        $this->assertEquals('posted', $updated->status);
        $this->assertEquals(3600000, $updated->grand_total);
        $this->assertEquals(3, ProductBranchStock::where('product_variant_id', $this->variant->id)->where('branch_id', $this->branch->id)->value('stock'));
        $this->assertEquals(3600000, (int) $this->supplier->fresh()->outstanding_debt);

        // Jurnal lama terhapus, jurnal baru balance 3.600.000
        $this->assertNull(JournalEntry::find($oldJournalId));
        $this->assertNotNull($updated->journal_entry_id);
        $this->assertEquals(1, JournalEntry::where('reference_type', 'Purchase')->where('reference_id', $updated->id)->count());
        $this->assertEquals(3600000, (int) JournalItem::where('journal_entry_id', $updated->journal_entry_id)->sum('debit'));
        $this->assertEquals(3600000, (int) JournalItem::where('journal_entry_id', $updated->journal_entry_id)->sum('credit'));

        // StockMovement hanya 1 (hasil repost) dengan qty 3
        $movements = StockMovement::where('reference_type', 'Purchase')->where('reference_id', $updated->id)->get();
        $this->assertCount(1, $movements);
        $this->assertEquals(3, $movements->first()->quantity);
    }

    public function test_deleting_posted_purchase_rolls_back_stock_debt_journal_and_reopens_po(): void
    {
        $this->actingAs($this->owner);

        $po = PurchaseOrder::create([
            'po_number' => 'PO-TEST-001',
            'supplier_id' => $this->supplier->id,
            'branch_id' => $this->branch->id,
            'order_date' => now()->format('Y-m-d'),
            'status' => 'approved',
            'subtotal' => 5000000,
            'grand_total' => 5000000,
            'created_by' => $this->owner->id,
        ]);

        PurchaseOrderItem::create([
            'purchase_order_id' => $po->id,
            'product_variant_id' => $this->variant->id,
            'unit_id' => $this->unit->id,
            'quantity' => 5,
            'price' => 1000000,
            'subtotal' => 5000000,
        ]);

        $pt = $this->createPostedPurchase(['po_id' => $po->id]);
        $journalId = $pt->journal_entry_id;

        $this->assertEquals('closed', $po->fresh()->status);
        $this->assertEquals(5, ProductBranchStock::where('product_variant_id', $this->variant->id)->where('branch_id', $this->branch->id)->value('stock'));
        $this->assertEquals(5000000, (int) $this->supplier->fresh()->outstanding_debt);

        app(DeletePurchaseTransaction::class)->execute($pt);

        $this->assertNull(PurchaseTransaction::find($pt->id));
        $this->assertEquals(0, ProductBranchStock::where('product_variant_id', $this->variant->id)->where('branch_id', $this->branch->id)->value('stock'));
        $this->assertEquals(0, (int) $this->supplier->fresh()->outstanding_debt);
        $this->assertNull(JournalEntry::find($journalId));
        $this->assertEquals(0, StockMovement::where('reference_type', 'Purchase')->where('reference_id', $pt->id)->count());
        $this->assertEquals('approved', $po->fresh()->status);
    }

    public function test_cannot_edit_or_delete_posted_purchase_with_existing_payment_or_return(): void
    {
        $this->actingAs($this->owner);

        $ptWithPayment = $this->createPostedPurchase();
        $payment = SupplierPayment::create([
            'payment_no' => 'SP-TEST-001',
            'supplier_id' => $this->supplier->id,
            'branch_id' => $this->branch->id,
            'account_id' => \App\Models\Account::first()->id,
            'payment_method' => 'Tunai',
            'payment_date' => now()->format('Y-m-d'),
            'total_amount' => 1000000,
            'status' => 'posted',
            'created_by' => $this->owner->id,
        ]);
        SupplierPaymentItem::create([
            'supplier_payment_id' => $payment->id,
            'purchase_transaction_id' => $ptWithPayment->id,
            'amount_paid' => 1000000,
        ]);

        $this->expectException(InvalidArgumentException::class);
        app(DeletePurchaseTransaction::class)->execute($ptWithPayment);
    }
}
