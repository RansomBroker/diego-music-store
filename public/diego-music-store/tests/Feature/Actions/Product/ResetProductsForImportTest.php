<?php

namespace Tests\Feature\Actions\Product;

use App\Actions\Procurement\CreatePurchaseTransaction;
use App\Actions\Procurement\PostPurchaseTransaction;
use App\Actions\Product\ImportProducts;
use App\Actions\Product\ResetProductsForImport;
use App\Actions\Supplier\ImportSupplierDebts;
use App\Helpers\AccountHelper;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\JournalEntry;
use App\Models\JournalItem;
use App\Models\Product;
use App\Models\ProductBranchStock;
use App\Models\ProductVariant;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\PurchaseTransaction;
use App\Models\PurchaseTransactionDetail;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\StockMovement;
use App\Models\StockOpname;
use App\Models\StockOpnameItem;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class ResetProductsForImportTest extends TestCase
{
    use RefreshDatabase;

    protected Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();

        $this->branch = Branch::create([
            'name' => 'Cabang Pusat Reset Test',
            'address' => 'Jl. Gajah Mada',
            'phone' => '0561-123456',
            'is_active' => true,
        ]);

        Unit::firstOrCreate(['code' => 'pcs'], ['name' => 'Pieces', 'is_active' => true]);

        AccountHelper::resolveAccountId('111401001', 'PERSEDIAAN BARANG DAGANG', 'asset');
        AccountHelper::resolveAccountId('211101001', 'HUTANG DAGANG', 'liability');
        AccountHelper::resolveAccountId('111101001', 'KAS', 'asset');
        AccountHelper::resolveAccountId('311101001', 'MODAL DISETOR', 'equity');
    }

    public function test_resets_products_variants_stocks_and_initial_stock_journals(): void
    {
        $csvContent = <<<'CSV'
NO,KODE BARANG,NAMA STOK,KATEGORI BARANG,Harga Beli,Netto,Disc,HET,HARGA JUAL CASH,JLH.STOK
1,OLD-001,PRODUK LAMA,GUITAR,100000,100000,0,150000,150000,4 Pcs
CSV;
        $tempFile = tempnam(sys_get_temp_dir(), 'reset_test_') . '.csv';
        file_put_contents($tempFile, $csvContent);

        try {
            app(ImportProducts::class)->execute($tempFile, $this->branch->id);

            $this->assertEquals(1, Product::count());
            $this->assertEquals(1, ProductVariant::count());
            $this->assertEquals(1, ProductBranchStock::count());
            $this->assertEquals(1, StockMovement::count());
            $this->assertEquals(1, JournalEntry::where('reference_type', 'InitialStock')->count());

            $result = app(ResetProductsForImport::class)->execute();

            $this->assertEquals(1, $result['deleted_products']);
            $this->assertEquals(1, $result['deleted_variants']);
            $this->assertEquals(1, $result['deleted_journals']);
            $this->assertEquals(0, Product::count());
            $this->assertEquals(0, ProductVariant::count());
            $this->assertEquals(0, ProductBranchStock::count());
            $this->assertEquals(0, StockMovement::count());
            $this->assertEquals(0, JournalEntry::where('reference_type', 'InitialStock')->count());
        } finally {
            @unlink($tempFile);
        }
    }

    public function test_resets_sales_procurement_operational_movements_and_journals_while_preserving_initial_debt(): void
    {
        $user = User::factory()->create();
        $unit = Unit::where('code', 'pcs')->firstOrFail();

        $supplier = Supplier::create([
            'name' => 'PT Musik Sentosa',
            'outstanding_debt' => 0,
        ]);

        $customer = Customer::create([
            'name' => 'Pelanggan Kredit',
            'outstanding_debt' => 500000,
        ]);

        $product = Product::create([
            'name' => 'Gitar Terjual',
            'unit_id' => $unit->id,
            'type' => 'physical',
            'is_active' => true,
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'SOLD-001',
            'price' => 500000,
            'cost_price' => 300000,
            'hpp' => 300000,
            'is_active' => true,
        ]);

        // 1. Purchase Order & Posted Kredit Purchase Transaction
        $po = PurchaseOrder::create([
            'supplier_id' => $supplier->id,
            'branch_id' => $this->branch->id,
            'po_number' => 'PO-RST-001',
            'order_date' => '2026-04-01',
            'status' => 'closed',
            'total_amount' => 600000,
            'grand_total' => 600000,
        ]);
        PurchaseOrderItem::create([
            'purchase_order_id' => $po->id,
            'product_variant_id' => $variant->id,
            'quantity' => 2,
            'price' => 300000,
            'subtotal' => 600000,
        ]);

        $pt = app(CreatePurchaseTransaction::class)->execute([
            'transaction_date' => '2026-04-02',
            'po_id' => $po->id,
            'supplier_id' => $supplier->id,
            'branch_id' => $this->branch->id,
            'purchase_type' => 'Kredit',
            'items' => [
                [
                    'product_variant_id' => $variant->id,
                    'qty_received' => 2,
                    'price' => 300000,
                ],
            ],
        ]);
        app(PostPurchaseTransaction::class)->execute($pt);

        // 2. Imported Supplier Debt (InitialDebt - has NO product details, should be preserved)
        $importDebt = app(ImportSupplierDebts::class);
        $importDebt->execute([
            [
                'no' => 1,
                'supplier' => $supplier->name,
                'tanggal' => '15-03-2026',
                'nota' => 'DEBT-KEEP-001',
                'total_hutang' => 400000,
                'total_pembayaran' => 0,
                'sisa_hutang' => 400000,
            ],
        ], $this->branch->id);
        $importDebt->recordInitialDebtJournal(
            totalValue: 400000,
            branchId: $this->branch->id,
            contraAccountId: null,
            userId: $user->id,
            invoiceCount: 1,
        );

        // 3. POS Sale (credit) + Operational StockMovement + Sales Journal
        $sale = Sale::create([
            'invoice_number' => 'INV-LOCK-001',
            'invoice_date' => now()->format('Y-m-d'),
            'branch_id' => $this->branch->id,
            'customer_id' => $customer->id,
            'created_by' => $user->id,
            'sales_rep_id' => $user->id,
            'payment_method' => 'credit',
            'subtotal' => 500000,
            'grand_total' => 500000,
            'status' => 'pending',
        ]);

        SaleItem::create([
            'sale_id' => $sale->id,
            'product_variant_id' => $variant->id,
            'quantity' => 1,
            'unit_price' => 500000,
            'total_price' => 500000,
        ]);

        StockMovement::create([
            'product_variant_id' => $variant->id,
            'branch_id' => $this->branch->id,
            'type' => 'out',
            'quantity' => 1,
            'unit_cost' => 300000,
            'hpp' => 300000,
            'reference_type' => 'POS',
            'reference_id' => $sale->id,
        ]);

        $salesJournal = JournalEntry::create([
            'branch_id' => $this->branch->id,
            'entry_no' => 'JV-POS-RESET-001',
            'date' => now()->toDateString(),
            'description' => 'POS Sale Test',
            'reference_type' => 'Sales',
            'reference_id' => $sale->id,
            'status' => 'posted',
            'created_by' => $user->id,
            'posted_at' => now(),
            'posted_by' => $user->id,
        ]);
        JournalItem::create([
            'journal_entry_id' => $salesJournal->id,
            'account_id' => AccountHelper::resolveAccountId('111301001', 'PIUTANG DAGANG', 'asset'),
            'debit' => 500000,
            'credit' => 0,
        ]);

        // 4. Stock Opname + item
        $opname = StockOpname::create([
            'opname_no' => 'SO-TEST-001',
            'opname_date' => now()->toDateString(),
            'branch_id' => $this->branch->id,
            'status' => 'completed',
            'created_by' => $user->id,
        ]);
        StockOpnameItem::create([
            'stock_opname_id' => $opname->id,
            'product_variant_id' => $variant->id,
            'system_qty' => 1,
            'physical_qty' => 1,
            'difference' => 0,
            'cost_price' => 300000,
        ]);

        // Execute reset
        $result = app(ResetProductsForImport::class)->execute();

        $this->assertEquals(1, $result['deleted_products']);
        $this->assertEquals(1, $result['deleted_variants']);
        $this->assertEquals(1, $result['deleted_sales']);
        $this->assertEquals(2, $result['deleted_stock_movements']); // 1 Purchase + 1 POS
        $this->assertEquals(2, $result['deleted_journals']); // 1 Purchase + 1 Sales

        $this->assertEquals(0, Product::count());
        $this->assertEquals(0, ProductVariant::count());
        $this->assertEquals(0, Sale::count());
        $this->assertEquals(0, SaleItem::count());
        $this->assertEquals(0, PurchaseOrder::count());
        $this->assertEquals(0, PurchaseOrderItem::count());
        $this->assertEquals(0, PurchaseTransactionDetail::count());
        $this->assertEquals(0, StockOpname::count());
        $this->assertEquals(0, StockOpnameItem::count());
        $this->assertEquals(0, StockMovement::count());

        // Customer credit debt from deleted sale reverted (500,000 -> 0)
        $this->assertEquals(0.0, (float) $customer->fresh()->outstanding_debt);

        // Supplier debt from deleted product purchase reverted, InitialDebt preserved (400,000)
        $this->assertEquals(400000.0, (float) $supplier->fresh()->outstanding_debt);
        $this->assertDatabaseHas('purchase_transactions', ['invoice_number' => 'DEBT-KEEP-001']);
        $this->assertDatabaseHas('journal_entries', ['reference_type' => 'InitialDebt', 'branch_id' => $this->branch->id]);
    }

    public function test_enforces_delete_permission_when_authenticated(): void
    {
        Permission::findOrCreate('bo.products.delete', 'web');
        $staffRole = Role::firstOrCreate(['name' => 'staff_no_delete', 'guard_name' => 'web']);
        $staffRole->syncPermissions([]);

        $user = User::factory()->create();
        $user->assignRole($staffRole);
        $this->actingAs($user);

        $this->expectException(HttpException::class);
        app(ResetProductsForImport::class)->execute();
    }
}
