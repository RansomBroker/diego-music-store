<?php

namespace Tests\Feature;

use App\Actions\Branch\EnsureBranchCoaAccounts;
use App\Actions\StockTransfer\ApproveStockTransfer;
use App\Actions\StockTransfer\CompleteStockTransfer;
use App\Models\Branch;
use App\Models\JournalEntry;
use App\Models\Product;
use App\Models\ProductBranchStock;
use App\Models\ProductVariant;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InterBranchStockTransferDualJournalTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Branch $fromBranch;
    protected Branch $toBranch;
    protected ProductVariant $variant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['is_active' => true]);
        $this->actingAs($this->user);

        $this->fromBranch = Branch::create([
            'name'      => 'Cabang Jakarta',
            'is_active' => true,
        ]);

        $this->toBranch = Branch::create([
            'name'      => 'Cabang Bandung',
            'is_active' => true,
        ]);

        EnsureBranchCoaAccounts::execute($this->fromBranch);
        EnsureBranchCoaAccounts::execute($this->toBranch);
        $this->fromBranch->refresh();
        $this->toBranch->refresh();

        $unit = Unit::create(['name' => 'Pieces', 'code' => 'pcs']);
        $product = Product::create([
            'name'      => 'Gitar Elektrik Ibanez',
            'type'      => 'physical',
            'unit_id'   => $unit->id,
            'is_active' => true,
        ]);

        $this->variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku'        => 'IBZ-RG-01',
            'price'      => 5000000,
            'cost_price' => 3500000,
            'hpp'        => 3500000,
            'is_active'  => true,
        ]);

        ProductBranchStock::create([
            'branch_id'          => $this->fromBranch->id,
            'product_variant_id' => $this->variant->id,
            'stock'              => 20,
            'hpp'                => 3500000,
        ]);

        ProductBranchStock::create([
            'branch_id'          => $this->toBranch->id,
            'product_variant_id' => $this->variant->id,
            'stock'              => 0,
            'hpp'                => 3500000,
        ]);
    }

    public function test_completing_stock_transfer_creates_dual_balanced_branch_specific_journals(): void
    {
        $transfer = StockTransfer::create([
            'transfer_number' => 'TRF-TEST-001',
            'from_branch_id'  => $this->fromBranch->id,
            'to_branch_id'    => $this->toBranch->id,
            'status'          => 'draft',
            'created_by'      => $this->user->id,
        ]);

        StockTransferItem::create([
            'stock_transfer_id'  => $transfer->id,
            'product_variant_id' => $this->variant->id,
            'qty_requested'      => 5,
            'qty_approved'       => 5,
            'qty_sent'           => 5,
            'qty_received'       => 5,
            'unit_cost'          => 3500000,
            'total_cost'         => 17500000, // 5 * 3,500,000
        ]);

        ApproveStockTransfer::execute($transfer);
        CompleteStockTransfer::execute($transfer);

        $transfer->refresh();
        $this->assertEquals('completed', $transfer->status);

        // Fetch all journal entries associated with this transfer
        $journals = JournalEntry::where('reference_type', 'StockTransfer')
            ->where('reference_id', $transfer->id)
            ->with('items')
            ->get();

        // Must produce exactly 2 separate balanced journals
        $this->assertCount(2, $journals);

        // 1. Origin Journal
        $originJournal = $journals->firstWhere('branch_id', $this->fromBranch->id);
        $this->assertNotNull($originJournal);
        $this->assertEquals($this->fromBranch->id, $originJournal->branch_id);
        $this->assertStringContainsString('Pengiriman Transfer Stok', $originJournal->description);

        $originItems = $originJournal->items;
        $this->assertCount(2, $originItems);
        $this->assertEquals(17500000, $originItems->sum('debit'));
        $this->assertEquals(17500000, $originItems->sum('credit'));

        $originDebit = $originItems->firstWhere('debit', '>', 0);
        $originCredit = $originItems->firstWhere('credit', '>', 0);
        $this->assertEquals($this->fromBranch->interbranch_receivable_account_id, $originDebit->account_id);
        $this->assertEquals($this->fromBranch->inventory_account_id, $originCredit->account_id);

        // 2. Destination Journal
        $destinationJournal = $journals->firstWhere('branch_id', $this->toBranch->id);
        $this->assertNotNull($destinationJournal);
        $this->assertEquals($this->toBranch->id, $destinationJournal->branch_id);
        $this->assertStringContainsString('Penerimaan Transfer Stok', $destinationJournal->description);

        $destItems = $destinationJournal->items;
        $this->assertCount(2, $destItems);
        $this->assertEquals(17500000, $destItems->sum('debit'));
        $this->assertEquals(17500000, $destItems->sum('credit'));

        $destDebit = $destItems->firstWhere('debit', '>', 0);
        $destCredit = $destItems->firstWhere('credit', '>', 0);
        $this->assertEquals($this->toBranch->inventory_account_id, $destDebit->account_id);
        $this->assertEquals($this->toBranch->interbranch_payable_account_id, $destCredit->account_id);
    }
}
