<?php

namespace Tests\Feature\Actions\Procurement;

use App\Actions\Procurement\CreatePurchaseTransaction;
use App\Actions\Procurement\PostPurchaseTransaction;
use App\Actions\Procurement\ResetPurchaseTransactions;
use App\Actions\Supplier\ImportSupplierDebts;
use App\Filament\Resources\PurchaseTransactions\Pages\ListPurchaseTransactions;
use App\Helpers\AccountHelper;
use App\Helpers\BranchHelper;
use App\Models\Branch;
use App\Models\JournalEntry;
use App\Models\JournalItem;
use App\Models\Product;
use App\Models\ProductBranchStock;
use App\Models\ProductVariant;
use App\Models\PurchaseOrder;
use App\Models\PurchaseTransaction;
use App\Models\PurchaseTransactionDetail;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\SupplierPayment;
use App\Models\SupplierPaymentItem;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use RuntimeException;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class ResetPurchaseTransactionsTest extends TestCase
{
    use RefreshDatabase;

    private Branch $branch;

    private Branch $otherBranch;

    private User $authorizedUser;

    private Supplier $supplier;

    private ProductVariant $variant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->branch = Branch::create(['name' => 'Pusat', 'address' => 'Jl. Pusat', 'phone' => '081111', 'is_active' => true]);
        $this->otherBranch = Branch::create(['name' => 'Cabang 2', 'address' => 'Jl. Cabang 2', 'phone' => '082222', 'is_active' => true]);

        foreach (['bo.access', 'bo.purchase_transactions.view', 'bo.purchase_transactions.create', 'bo.purchase_transactions.delete'] as $perm) {
            Permission::findOrCreate($perm, 'web');
        }

        $role = Role::firstOrCreate(['name' => 'owner_reset_pt_test', 'guard_name' => 'web']);
        $role->syncPermissions([
            'bo.access',
            'bo.purchase_transactions.view',
            'bo.purchase_transactions.create',
            'bo.purchase_transactions.delete',
        ]);

        $this->authorizedUser = User::factory()->create();
        $this->authorizedUser->branches()->attach($this->branch->id);
        $this->authorizedUser->assignRole($role);

        $this->actingAs($this->authorizedUser);
        session(['pos_active_branch_id' => $this->branch->id]);

        $this->supplier = Supplier::create([
            'name' => 'PT Supplier Utama',
            'outstanding_debt' => 0,
        ]);

        $unit = Unit::firstOrCreate(['code' => 'pcs'], ['name' => 'Pcs', 'is_active' => true]);

        $product = Product::create([
            'name' => 'Gitar Akustik',
            'unit_id' => $unit->id,
            'type' => 'physical',
            'is_active' => true,
        ]);

        $this->variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'GTR-001',
            'cost_price' => 1000000,
            'hpp' => 1000000,
            'price' => 1500000,
            'is_active' => true,
        ]);

        AccountHelper::resolveAccountId('111401001', 'PERSEDIAAN BARANG DAGANG', 'asset');
        AccountHelper::resolveAccountId('211101001', 'HUTANG DAGANG', 'liability');
        AccountHelper::resolveAccountId('111101001', 'KAS', 'asset');
        AccountHelper::resolveAccountId('311101001', 'MODAL DISETOR', 'equity');
    }

    public function test_reset_deletes_active_branch_purchase_transactions_journals_stock_and_debt(): void
    {
        $po = PurchaseOrder::create([
            'supplier_id' => $this->supplier->id,
            'branch_id' => $this->branch->id,
            'po_number' => 'PO-TEST-001',
            'order_date' => '2026-04-01',
            'status' => 'approved',
            'total_amount' => 2000000,
            'grand_total' => 2000000,
        ]);

        // 1. Draft purchase transaction on active branch
        $draftPt = app(CreatePurchaseTransaction::class)->execute([
            'transaction_date' => '2026-04-02',
            'supplier_id' => $this->supplier->id,
            'branch_id' => $this->branch->id,
            'purchase_type' => 'Tunai',
            'items' => [
                [
                    'product_variant_id' => $this->variant->id,
                    'qty_received' => 1,
                    'price' => 1000000,
                ],
            ],
        ]);

        // 2. Posted Kredit purchase transaction linked to PO on active branch
        $postedPt = app(CreatePurchaseTransaction::class)->execute([
            'transaction_date' => '2026-04-03',
            'po_id' => $po->id,
            'supplier_id' => $this->supplier->id,
            'branch_id' => $this->branch->id,
            'purchase_type' => 'Kredit',
            'items' => [
                [
                    'product_variant_id' => $this->variant->id,
                    'qty_received' => 2,
                    'price' => 1000000,
                ],
            ],
        ]);
        app(PostPurchaseTransaction::class)->execute($postedPt);
        $po->update(['status' => 'closed']);

        // 3. Imported supplier debt (InitialDebt journal) on active branch
        $importAction = app(ImportSupplierDebts::class);
        $importAction->execute([
            [
                'no' => 1,
                'supplier' => $this->supplier->name,
                'tanggal' => '15-03-2026',
                'nota' => 'INV-IMP-001',
                'total_hutang' => 500000,
                'total_pembayaran' => 0,
                'sisa_hutang' => 500000,
            ],
        ], $this->branch->id);
        $importAction->recordInitialDebtJournal(
            totalValue: 500000,
            branchId: $this->branch->id,
            contraAccountId: null,
            userId: $this->authorizedUser->id,
            invoiceCount: 1,
        );

        // 4. Other branch data must remain untouched
        $otherBranchPt = app(CreatePurchaseTransaction::class)->execute([
            'transaction_date' => '2026-04-04',
            'supplier_id' => $this->supplier->id,
            'branch_id' => $this->otherBranch->id,
            'purchase_type' => 'Kredit',
            'items' => [
                [
                    'product_variant_id' => $this->variant->id,
                    'qty_received' => 1,
                    'price' => 1000000,
                ],
            ],
        ]);
        app(PostPurchaseTransaction::class)->execute($otherBranchPt);

        // Execute reset on active branch
        $result = app(ResetPurchaseTransactions::class)->execute($this->branch->id);

        $this->assertSame(3, $result['deleted_transactions']);
        $this->assertSame(2, $result['deleted_journals']); // 1 Purchase journal + 1 InitialDebt journal
        $this->assertSame(1, $result['deleted_stock_movements']);

        $this->assertSame(0, PurchaseTransaction::where('branch_id', $this->branch->id)->count());
        $this->assertSame(0, PurchaseTransactionDetail::whereIn('purchase_transaction_id', [$draftPt->id, $postedPt->id])->count());
        $this->assertSame(0, JournalEntry::where('branch_id', $this->branch->id)->whereIn('reference_type', ['Purchase', 'InitialDebt'])->count());
        $this->assertSame(0, JournalItem::whereHas('journalEntry', fn ($q) => $q->where('branch_id', $this->branch->id)->whereIn('reference_type', ['Purchase', 'InitialDebt']))->count());
        $this->assertSame(0, StockMovement::where('branch_id', $this->branch->id)->where('reference_type', 'Purchase')->count());

        // Stock on active branch returned to 0
        $activeStock = ProductBranchStock::where('product_variant_id', $this->variant->id)
            ->where('branch_id', $this->branch->id)
            ->first();
        $this->assertNotNull($activeStock);
        $this->assertSame(0, (int) $activeStock->stock);

        // PO reopened to approved
        $this->assertSame('approved', $po->fresh()->status);

        // Supplier debt still retains only the untouched other branch transaction (1,000,000)
        $this->assertEquals(1000000.00, (float) $this->supplier->fresh()->outstanding_debt);

        // Other branch PT and journal untouched
        $this->assertDatabaseHas('purchase_transactions', ['id' => $otherBranchPt->id]);
        $this->assertDatabaseHas('journal_entries', [
            'branch_id' => $this->otherBranch->id,
            'reference_type' => 'Purchase',
            'reference_id' => $otherBranchPt->id,
        ]);
    }

    public function test_reset_refuses_when_supplier_payment_items_exist(): void
    {
        $pt = app(CreatePurchaseTransaction::class)->execute([
            'transaction_date' => '2026-04-03',
            'supplier_id' => $this->supplier->id,
            'branch_id' => $this->branch->id,
            'purchase_type' => 'Kredit',
            'items' => [
                [
                    'product_variant_id' => $this->variant->id,
                    'qty_received' => 1,
                    'price' => 1000000,
                ],
            ],
        ]);
        app(PostPurchaseTransaction::class)->execute($pt);

        $cashAccountId = AccountHelper::resolveAccountId('111101001', 'KAS', 'asset');

        $payment = SupplierPayment::create([
            'payment_no' => 'SP-TEST-001',
            'supplier_id' => $this->supplier->id,
            'branch_id' => $this->branch->id,
            'payment_date' => '2026-04-05',
            'payment_method' => 'cash',
            'account_id' => $cashAccountId,
            'total_amount' => 500000,
            'status' => 'posted',
            'created_by' => $this->authorizedUser->id,
        ]);

        SupplierPaymentItem::create([
            'supplier_payment_id' => $payment->id,
            'purchase_transaction_id' => $pt->id,
            'amount_paid' => 500000,
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Pembayaran Hutang Supplier');

        app(ResetPurchaseTransactions::class)->execute($this->branch->id);
    }

    public function test_reset_requires_delete_permission(): void
    {
        $readOnlyRole = Role::firstOrCreate(['name' => 'viewer_reset_pt_test', 'guard_name' => 'web']);
        $readOnlyRole->syncPermissions(['bo.access', 'bo.purchase_transactions.view']);

        $viewer = User::factory()->create();
        $viewer->branches()->attach($this->branch->id);
        $viewer->assignRole($readOnlyRole);

        $this->actingAs($viewer);

        Livewire::test(ListPurchaseTransactions::class)
            ->assertActionHidden('reset_transactions');

        $this->expectException(HttpException::class);
        app(ResetPurchaseTransactions::class)->execute($this->branch->id);
    }

    public function test_list_page_hides_reset_header_action_even_when_authorized(): void
    {
        Livewire::test(ListPurchaseTransactions::class)
            ->assertActionHidden('reset_transactions');
    }
}
