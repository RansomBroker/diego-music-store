<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\InventoryMutations\Pages\ListInventoryMutations;
use App\Models\Branch;
use App\Models\InventoryMutation;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class InventoryMutationResourceModalTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Branch $branchA;
    private Branch $branchB;
    private ProductVariant $variant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->actingAs($this->user);

        $this->branchA = Branch::create(['name' => 'Cabang Jakarta', 'store_name' => 'Store JKT', 'is_active' => true]);
        $this->branchB = Branch::create(['name' => 'Cabang Bandung', 'store_name' => 'Store BDG', 'is_active' => true]);

        $unit = Unit::create(['name' => 'Unit', 'code' => 'UNT', 'is_active' => true]);
        $product = Product::create(['name' => 'Gitar Akustik', 'type' => 'physical', 'unit_id' => $unit->id, 'is_active' => true]);
        $this->variant = ProductVariant::create(['product_id' => $product->id, 'sku' => 'SKU-GTR-01', 'price' => 1500000, 'cost_price' => 1000000, 'hpp' => 1000000, 'is_active' => true]);
    }

    public function test_it_can_render_list_inventory_mutations_page(): void
    {
        Livewire::test(ListInventoryMutations::class)
            ->assertSuccessful();
    }

    public function test_it_can_create_inventory_mutation_via_modal_action(): void
    {
        $test = Livewire::test(ListInventoryMutations::class)
            ->mountAction('create');

        $itemKey = array_key_first($test->get('mountedActions.0.data.items') ?? []);

        $test->setActionData([
                'sender_branch_id' => $this->branchA->id,
                'receiver_branch_id' => $this->branchB->id,
                'mutation_date' => now()->toDateString(),
                'status' => 'draft',
                'notes' => 'Transfer stok pertengahan bulan',
                'items' => [
                    ($itemKey ?: 'item-1') => [
                        'product_variant_id' => $this->variant->id,
                        'quantity' => 5,
                    ],
                ],
            ])
            ->callMountedAction()
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('inventory_mutations', [
            'sender_branch_id' => $this->branchA->id,
            'receiver_branch_id' => $this->branchB->id,
            'status' => 'draft',
            'notes' => 'Transfer stok pertengahan bulan',
        ]);

        $mutation = InventoryMutation::latest('id')->first();
        $this->assertDatabaseHas('inventory_mutation_items', [
            'inventory_mutation_id' => $mutation->id,
            'product_variant_id' => $this->variant->id,
            'quantity' => 5,
        ]);
    }

    public function test_it_can_edit_inventory_mutation_via_modal_action(): void
    {
        $mutation = InventoryMutation::create([
            'sender_branch_id' => $this->branchA->id,
            'receiver_branch_id' => $this->branchB->id,
            'mutation_number' => 'MUT-TEST-001',
            'mutation_date' => now()->toDateString(),
            'status' => 'draft',
            'notes' => 'Note lama',
        ]);

        $mutation->items()->create([
            'product_variant_id' => $this->variant->id,
            'quantity' => 2,
        ]);

        $test = Livewire::test(ListInventoryMutations::class)
            ->mountTableAction('edit', $mutation);

        $itemKey = array_key_first($test->get('mountedTableActions.0.data.items') ?? []);

        $test->setTableActionData([
                'sender_branch_id' => $this->branchA->id,
                'receiver_branch_id' => $this->branchB->id,
                'mutation_date' => now()->toDateString(),
                'status' => 'draft',
                'notes' => 'Note baru direvisi',
                'items' => [
                    ($itemKey ?: 'item-1') => [
                        'product_variant_id' => $this->variant->id,
                        'quantity' => 10,
                    ],
                ],
            ])
            ->callMountedTableAction()
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('inventory_mutations', [
            'id' => $mutation->id,
            'notes' => 'Note baru direvisi',
        ]);

        $this->assertDatabaseHas('inventory_mutation_items', [
            'inventory_mutation_id' => $mutation->id,
            'product_variant_id' => $this->variant->id,
            'quantity' => 10,
        ]);
    }

    public function test_it_can_delete_inventory_mutation_via_table_action(): void
    {
        $mutation = InventoryMutation::create([
            'sender_branch_id' => $this->branchA->id,
            'receiver_branch_id' => $this->branchB->id,
            'mutation_number' => 'MUT-TEST-DEL',
            'mutation_date' => now()->toDateString(),
            'status' => 'draft',
            'notes' => 'Akan dihapus',
        ]);

        Livewire::test(ListInventoryMutations::class)
            ->callTableAction('delete', $mutation)
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseMissing('inventory_mutations', [
            'id' => $mutation->id,
        ]);
    }

    public function test_it_can_render_print_document_page(): void
    {
        $mutation = InventoryMutation::create([
            'sender_branch_id' => $this->branchA->id,
            'receiver_branch_id' => $this->branchB->id,
            'mutation_number' => 'MUT-TEST-PRINT',
            'mutation_date' => now()->toDateString(),
            'status' => 'transit',
            'notes' => 'Dokumen diprint',
        ]);

        $response = $this->get(route('backoffice.inventory-mutations.print', $mutation));

        $response->assertStatus(200);
        $response->assertSee('SURAT MUTASI / TRANSFER BARANG');
        $response->assertSee('MUT-TEST-PRINT');
    }
}
