<?php

namespace Tests\Feature\Actions;

use App\Actions\InventoryMutation\CreateInventoryMutation;
use App\Actions\InventoryMutation\UpdateInventoryMutation;
use App\Actions\InventoryMutation\ProcessInventoryMutationTransit;
use App\Actions\InventoryMutation\ProcessInventoryMutationReceived;
use App\Models\Branch;
use App\Models\InventoryMutation;
use App\Models\Product;
use App\Models\ProductBranchStock;
use App\Models\ProductVariant;
use App\Models\StockMovement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class InventoryMutationActionsTest extends TestCase
{
    use RefreshDatabase;

    private Branch $branchSender;
    private Branch $branchReceiver;
    private ProductVariant $variant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->branchSender = Branch::create([
            'name' => 'Cabang Pengirim',
            'address' => 'Jakarta',
            'phone' => '021-11111',
            'is_active' => true,
        ]);

        $this->branchReceiver = Branch::create([
            'name' => 'Cabang Penerima',
            'address' => 'Bandung',
            'phone' => '022-22222',
            'is_active' => true,
        ]);

        $product = Product::create([
            'name' => 'Gitar Fender Mustang',
            'type' => 'physical',
            'is_active' => true,
        ]);

        $this->variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'FND-MST-BLU',
            'barcode' => '777111222',
            'name' => 'Blue Mustang',
            'price' => 12000000,
            'cost_price' => 8000000,
            'hpp' => 8000000,
            'is_active' => true,
        ]);
    }

    public function test_it_can_create_and_update_mutation_drafts(): void
    {
        $createData = [
            'sender_branch_id' => $this->branchSender->id,
            'receiver_branch_id' => $this->branchReceiver->id,
            'mutation_date' => '2026-06-30',
            'status' => 'draft',
            'notes' => 'Test mutation notes',
            'items' => [
                [
                    'product_variant_id' => $this->variant->id,
                    'quantity' => 5,
                ]
            ]
        ];

        $mutation = app(CreateInventoryMutation::class)->execute($createData);

        $this->assertInstanceOf(InventoryMutation::class, $mutation);
        $this->assertEquals('draft', $mutation->status);
        $this->assertCount(1, $mutation->items);
        $this->assertEquals(5, $mutation->items->first()->quantity);

        $updateData = [
            'sender_branch_id' => $this->branchSender->id,
            'receiver_branch_id' => $this->branchReceiver->id,
            'mutation_date' => '2026-06-30',
            'status' => 'draft',
            'notes' => 'Updated notes',
            'items' => [
                [
                    'product_variant_id' => $this->variant->id,
                    'quantity' => 8,
                ]
            ]
        ];

        $updatedMutation = app(UpdateInventoryMutation::class)->execute($mutation, $updateData);
        $this->assertEquals('Updated notes', $updatedMutation->notes);
        $this->assertEquals(8, $updatedMutation->fresh()->items->first()->quantity);
    }

    public function test_it_deducts_sender_stock_and_logs_movement_when_transit(): void
    {
        // 1. Setup sender branch stock to 10
        ProductBranchStock::create([
            'branch_id' => $this->branchSender->id,
            'product_variant_id' => $this->variant->id,
            'stock' => 10,
            'hpp' => 8000000,
        ]);

        $mutation = InventoryMutation::create([
            'sender_branch_id' => $this->branchSender->id,
            'receiver_branch_id' => $this->branchReceiver->id,
            'mutation_date' => '2026-06-30',
            'status' => 'draft',
        ]);

        $mutation->items()->create([
            'product_variant_id' => $this->variant->id,
            'quantity' => 4,
        ]);

        // 2. Process Transit
        app(ProcessInventoryMutationTransit::class)->execute($mutation);

        $this->assertEquals('transit', $mutation->fresh()->status);

        // Sender stock should be 6
        $senderStock = ProductBranchStock::where([
            'branch_id' => $this->branchSender->id,
            'product_variant_id' => $this->variant->id,
        ])->first();
        $this->assertEquals(6, $senderStock->stock);

        // Receiver stock should not exist yet
        $receiverStock = ProductBranchStock::where([
            'branch_id' => $this->branchReceiver->id,
            'product_variant_id' => $this->variant->id,
        ])->first();
        $this->assertNull($receiverStock);

        // Assert StockMovement OUT logged
        $movement = StockMovement::where([
            'branch_id' => $this->branchSender->id,
            'product_variant_id' => $this->variant->id,
            'type' => 'out',
            'reference_type' => 'Mutation',
            'reference_id' => $mutation->id,
        ])->first();
        $this->assertNotNull($movement);
        $this->assertEquals(4, $movement->quantity);
    }

    public function test_it_adds_receiver_stock_and_logs_movement_when_received(): void
    {
        // Setup initial sender stock and process transit first
        ProductBranchStock::create([
            'branch_id' => $this->branchSender->id,
            'product_variant_id' => $this->variant->id,
            'stock' => 10,
            'hpp' => 8000000,
        ]);

        $mutation = InventoryMutation::create([
            'sender_branch_id' => $this->branchSender->id,
            'receiver_branch_id' => $this->branchReceiver->id,
            'mutation_date' => '2026-06-30',
            'status' => 'transit', // Set to transit
        ]);

        $mutation->items()->create([
            'product_variant_id' => $this->variant->id,
            'quantity' => 4,
        ]);

        // Process Received
        app(ProcessInventoryMutationReceived::class)->execute($mutation);

        $this->assertEquals('received', $mutation->fresh()->status);

        // Receiver stock should be 4
        $receiverStock = ProductBranchStock::where([
            'branch_id' => $this->branchReceiver->id,
            'product_variant_id' => $this->variant->id,
        ])->first();
        $this->assertNotNull($receiverStock);
        $this->assertEquals(4, $receiverStock->stock);
        $this->assertEquals(8000000, $receiverStock->hpp); // verify HPP copy

        // Assert StockMovement IN logged
        $movement = StockMovement::where([
            'branch_id' => $this->branchReceiver->id,
            'product_variant_id' => $this->variant->id,
            'type' => 'in',
            'reference_type' => 'Mutation',
            'reference_id' => $mutation->id,
        ])->first();
        $this->assertNotNull($movement);
        $this->assertEquals(4, $movement->quantity);
    }

    public function test_it_prevents_transit_if_sender_stock_is_insufficient(): void
    {
        // 1. Setup sender branch stock to only 3 (mutation asks for 5)
        ProductBranchStock::create([
            'branch_id' => $this->branchSender->id,
            'product_variant_id' => $this->variant->id,
            'stock' => 3,
            'hpp' => 8000000,
        ]);

        $mutation = InventoryMutation::create([
            'sender_branch_id' => $this->branchSender->id,
            'receiver_branch_id' => $this->branchReceiver->id,
            'mutation_date' => '2026-06-30',
            'status' => 'draft',
        ]);

        $mutation->items()->create([
            'product_variant_id' => $this->variant->id,
            'quantity' => 5,
        ]);

        // 2. Expect InvalidArgumentException
        $this->expectException(\InvalidArgumentException::class);
        app(ProcessInventoryMutationTransit::class)->execute($mutation);
    }
}
