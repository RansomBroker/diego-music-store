<?php

namespace Tests\Feature;

use App\Actions\Branch\EnsureBranchCoaAccounts;
use App\Actions\StockTransfer\ApproveStockTransfer;
use App\Actions\StockTransfer\CancelStockTransfer;
use App\Actions\StockTransfer\CompleteStockTransfer;
use App\Actions\StockTransfer\SubmitStockTransfer;
use App\Models\Branch;
use App\Models\JournalEntry;
use App\Models\Product;
use App\Models\ProductBranchStock;
use App\Models\ProductVariant;
use App\Models\StockMovement;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use App\Models\Unit;
use App\Models\User;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StockTransferTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Branch $fromBranch;
    protected Branch $toBranch;
    protected ProductVariant $variantGuitar;
    protected ProductVariant $variantBass;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();

        $role = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $this->user = User::factory()->create(['is_active' => true]);
        $this->user->assignRole($role);
        $this->actingAs($this->user);

        // 1. Setup Branches
        $this->fromBranch = Branch::create([
            'name' => 'Pontianak',
            'address' => 'Jl. Gajah Mada No. 1, Pontianak',
            'phone' => '0561-111111',
            'is_active' => true,
        ]);

        $this->toBranch = Branch::create([
            'name' => 'Singkawang',
            'address' => 'Jl. Diponegoro No. 2, Singkawang',
            'phone' => '0562-222222',
            'is_active' => true,
        ]);

        $this->user->branches()->attach([$this->fromBranch->id, $this->toBranch->id]);

        // 2. Provision COA Accounts for both branches
        EnsureBranchCoaAccounts::execute($this->fromBranch);
        EnsureBranchCoaAccounts::execute($this->toBranch);
        $this->fromBranch->refresh();
        $this->toBranch->refresh();

        // 3. Setup Units & Products
        $unit = Unit::create(['name' => 'Pieces', 'code' => 'pcs']);

        $product1 = Product::create([
            'name' => 'Gitar Akustik Yamaha',
            'type' => 'physical',
            'unit_id' => $unit->id,
            'is_active' => true,
        ]);

        $this->variantGuitar = ProductVariant::create([
            'product_id' => $product1->id,
            'sku' => 'GTR-YMH-01',
            'barcode' => '8990011223344',
            'name' => 'Natural Wood',
            'price' => 2500000,
            'cost_price' => 2000000,
            'hpp' => 2000000,
            'is_active' => true,
        ]);

        $product2 = Product::create([
            'name' => 'Bass Elektrik Fender',
            'type' => 'physical',
            'unit_id' => $unit->id,
            'is_active' => true,
        ]);

        $this->variantBass = ProductVariant::create([
            'product_id' => $product2->id,
            'sku' => 'BAS-FND-01',
            'barcode' => '8990055667788',
            'name' => 'Sunburst',
            'price' => 4000000,
            'cost_price' => 3000000,
            'hpp' => 3000000,
            'is_active' => true,
        ]);

        // 4. Setup Initial Stocks at fromBranch (Pontianak)
        ProductBranchStock::create([
            'branch_id' => $this->fromBranch->id,
            'product_variant_id' => $this->variantGuitar->id,
            'stock' => 10,
            'hpp' => 2000000,
        ]);

        ProductBranchStock::create([
            'branch_id' => $this->fromBranch->id,
            'product_variant_id' => $this->variantBass->id,
            'stock' => 5,
            'hpp' => 3000000,
        ]);
    }

    #[Test]
    public function it_can_create_a_stock_transfer_in_draft_status()
    {
        $transfer = StockTransfer::create([
            'transfer_number' => StockTransfer::generateTransferNumber(),
            'from_branch_id' => $this->fromBranch->id,
            'to_branch_id' => $this->toBranch->id,
            'transfer_date' => now()->toDateString(),
            'description' => 'Pemindahan stok display',
            'status' => 'DRAFT',
            'total_qty' => 3,
            'total_cost' => 7000000,
        ]);

        StockTransferItem::create([
            'stock_transfer_id' => $transfer->id,
            'product_variant_id' => $this->variantGuitar->id,
            'qty' => 2,
            'unit_cost' => 2000000,
            'total_cost' => 4000000,
        ]);

        StockTransferItem::create([
            'stock_transfer_id' => $transfer->id,
            'product_variant_id' => $this->variantBass->id,
            'qty' => 1,
            'unit_cost' => 3000000,
            'total_cost' => 3000000,
        ]);

        $this->assertEquals('DRAFT', $transfer->status);
        $this->assertCount(2, $transfer->items);
        $this->assertEquals(3, $transfer->total_qty);
        $this->assertEquals(7000000, $transfer->total_cost);

        // Belum mempengaruhi stok fisik
        $guitarStock = ProductBranchStock::where('branch_id', $this->fromBranch->id)
            ->where('product_variant_id', $this->variantGuitar->id)
            ->value('stock');
        $this->assertEquals(10, $guitarStock);

        // Belum ada jurnal
        $this->assertNull($transfer->journal_entry_id);
    }

    #[Test]
    public function it_can_submit_and_approve_a_stock_transfer()
    {
        $transfer = StockTransfer::create([
            'transfer_number' => StockTransfer::generateTransferNumber(),
            'from_branch_id' => $this->fromBranch->id,
            'to_branch_id' => $this->toBranch->id,
            'transfer_date' => now()->toDateString(),
            'status' => 'DRAFT',
            'total_qty' => 2,
            'total_cost' => 4000000,
        ]);

        StockTransferItem::create([
            'stock_transfer_id' => $transfer->id,
            'product_variant_id' => $this->variantGuitar->id,
            'qty' => 2,
            'unit_cost' => 2000000,
            'total_cost' => 4000000,
        ]);

        // 1. Submit (DRAFT -> PENDING)
        app(SubmitStockTransfer::class)->execute($transfer);
        $transfer->refresh();

        $this->assertEquals('PENDING', $transfer->status);
        $this->assertNotNull($transfer->submitted_at);

        // 2. Approve (PENDING -> APPROVED)
        app(ApproveStockTransfer::class)->execute($transfer);
        $transfer->refresh();

        $this->assertEquals('APPROVED', $transfer->status);
        $this->assertNotNull($transfer->approved_at);
    }

    #[Test]
    public function it_can_cancel_a_transfer_from_draft_or_pending()
    {
        $transfer = StockTransfer::create([
            'transfer_number' => StockTransfer::generateTransferNumber(),
            'from_branch_id' => $this->fromBranch->id,
            'to_branch_id' => $this->toBranch->id,
            'transfer_date' => now()->toDateString(),
            'status' => 'DRAFT',
            'total_qty' => 1,
            'total_cost' => 2000000,
        ]);

        StockTransferItem::create([
            'stock_transfer_id' => $transfer->id,
            'product_variant_id' => $this->variantGuitar->id,
            'qty' => 1,
            'unit_cost' => 2000000,
            'total_cost' => 2000000,
        ]);

        // Cancel from DRAFT
        app(CancelStockTransfer::class)->execute($transfer);
        $transfer->refresh();

        $this->assertEquals('CANCELLED', $transfer->status);
        $this->assertNotNull($transfer->cancelled_at);

        // Cancel should fail if already cancelled
        $this->expectException(Exception::class);
        app(CancelStockTransfer::class)->execute($transfer);
    }

    #[Test]
    public function it_completes_transfer_moves_stock_and_posts_4_sided_interbranch_journal()
    {
        $transfer = StockTransfer::create([
            'transfer_number' => 'TRF-202610-00001',
            'from_branch_id' => $this->fromBranch->id,
            'to_branch_id' => $this->toBranch->id,
            'transfer_date' => '2026-10-04',
            'status' => 'APPROVED',
            'total_qty' => 3,
            'total_cost' => 7000000,
        ]);

        StockTransferItem::create([
            'stock_transfer_id' => $transfer->id,
            'product_variant_id' => $this->variantGuitar->id,
            'qty' => 2,
            'unit_cost' => 2000000,
            'total_cost' => 4000000,
        ]);

        StockTransferItem::create([
            'stock_transfer_id' => $transfer->id,
            'product_variant_id' => $this->variantBass->id,
            'qty' => 1,
            'unit_cost' => 3000000,
            'total_cost' => 3000000,
        ]);

        // Eksekusi CompleteStockTransfer Action
        app(CompleteStockTransfer::class)->execute($transfer);
        $transfer->refresh();

        // 1. Verifikasi Status Transfer
        $this->assertEquals('COMPLETED', $transfer->status);
        $this->assertNotNull($transfer->completed_at);
        $this->assertNotNull($transfer->journal_entry_id);

        // 2. Verifikasi Pemindahan Fisik Stok
        // Cabang Asal berkurang: Gitar 10 - 2 = 8, Bass 5 - 1 = 4
        $guitarFromStock = ProductBranchStock::where('branch_id', $this->fromBranch->id)
            ->where('product_variant_id', $this->variantGuitar->id)
            ->value('stock');
        $bassFromStock = ProductBranchStock::where('branch_id', $this->fromBranch->id)
            ->where('product_variant_id', $this->variantBass->id)
            ->value('stock');
        $this->assertEquals(8, $guitarFromStock);
        $this->assertEquals(4, $bassFromStock);

        // Cabang Tujuan bertambah: Gitar 0 + 2 = 2, Bass 0 + 1 = 1
        $guitarToStock = ProductBranchStock::where('branch_id', $this->toBranch->id)
            ->where('product_variant_id', $this->variantGuitar->id)
            ->value('stock');
        $bassToStock = ProductBranchStock::where('branch_id', $this->toBranch->id)
            ->where('product_variant_id', $this->variantBass->id)
            ->value('stock');
        $this->assertEquals(2, $guitarToStock);
        $this->assertEquals(1, $bassToStock);

        // 3. Verifikasi Mutasi Stok (Stock Movement)
        $movements = StockMovement::where('reference_type', StockTransfer::class)
            ->where('reference_id', $transfer->id)
            ->get();
        $this->assertCount(4, $movements); // 2 out + 2 in
        $this->assertCount(2, $movements->where('type', 'out')->where('branch_id', $this->fromBranch->id));
        $this->assertCount(2, $movements->where('type', 'in')->where('branch_id', $this->toBranch->id));

        // 4. Verifikasi Jurnal Umum Akuntansi (4 Sisi)
        $journal = JournalEntry::with('items')->find($transfer->journal_entry_id);
        $this->assertNotNull($journal);
        $this->assertEquals('posted', $journal->status);
        $this->assertCount(4, $journal->items);

        // Sisi Pontianak:
        // DR Piutang Antar Cabang (14110...) Rp7.000.000
        $recItem = $journal->items->firstWhere('account_id', $this->fromBranch->interbranch_receivable_account_id);
        $this->assertNotNull($recItem);
        $this->assertEquals(7000000, $recItem->debit);
        $this->assertEquals(0, $recItem->credit);

        // CR Persediaan Barang Dagang - Pontianak Rp7.000.000
        $invFromItem = $journal->items->firstWhere('account_id', $this->fromBranch->inventory_account_id);
        $this->assertNotNull($invFromItem);
        $this->assertEquals(0, $invFromItem->debit);
        $this->assertEquals(7000000, $invFromItem->credit);

        // Sisi Singkawang:
        // DR Persediaan Barang Dagang - Singkawang Rp7.000.000
        $invToItem = $journal->items->firstWhere('account_id', $this->toBranch->inventory_account_id);
        $this->assertNotNull($invToItem);
        $this->assertEquals(7000000, $invToItem->debit);
        $this->assertEquals(0, $invToItem->credit);

        // CR Hutang Antar Cabang (21110...) Rp7.000.000
        $payItem = $journal->items->firstWhere('account_id', $this->toBranch->interbranch_payable_account_id);
        $this->assertNotNull($payItem);
        $this->assertEquals(0, $payItem->debit);
        $this->assertEquals(7000000, $payItem->credit);

        // Total Debit == Total Credit (Balance)
        $this->assertEquals(14000000, $journal->items->sum('debit'));
        $this->assertEquals(14000000, $journal->items->sum('credit'));
    }

    #[Test]
    public function it_throws_exception_if_branch_coa_is_incomplete()
    {
        $branchNoCoa = Branch::create([
            'name' => 'Cabang Baru Tanpa COA',
            'is_active' => true,
        ]);

        $transfer = StockTransfer::create([
            'transfer_number' => 'TRF-TEST-NO-COA',
            'from_branch_id' => $this->fromBranch->id,
            'to_branch_id' => $branchNoCoa->id,
            'transfer_date' => now()->toDateString(),
            'status' => 'APPROVED',
            'total_qty' => 1,
            'total_cost' => 2000000,
        ]);

        StockTransferItem::create([
            'stock_transfer_id' => $transfer->id,
            'product_variant_id' => $this->variantGuitar->id,
            'qty' => 1,
            'unit_cost' => 2000000,
            'total_cost' => 2000000,
        ]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('belum memiliki pengaturan Akun COA yang lengkap');

        app(CompleteStockTransfer::class)->execute($transfer);
    }

    #[Test]
    public function it_can_render_backoffice_stock_transfers_pages()
    {
        // 1. Index List Page
        $response = $this->get('/backoffice/stock-transfers');
        $response->assertStatus(200);

        // 2. Create Page
        $response = $this->get('/backoffice/stock-transfers/create');
        $response->assertStatus(200);

        // 3. View Detail Page
        $transfer = StockTransfer::create([
            'transfer_number' => 'TRF-202610-00999',
            'from_branch_id' => $this->fromBranch->id,
            'to_branch_id' => $this->toBranch->id,
            'transfer_date' => now()->toDateString(),
            'status' => 'DRAFT',
            'total_qty' => 1,
            'total_cost' => 2000000,
        ]);

        $response = $this->get("/backoffice/stock-transfers/{$transfer->id}");
        $response->assertStatus(200);
        $response->assertSee('TRF-202610-00999');
    }
}
