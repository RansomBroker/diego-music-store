<?php

namespace Tests\Feature\Reports;

use App\Actions\Inventory\GenerateStockMovementReport;
use App\Filament\Pages\Reports\StockMovementReport;
use App\Models\Branch;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StockMovement;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class StockMovementReportTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();

        $this->branch = Branch::create([
            'name'        => 'Cabang Utama',
            'store_name'  => 'Diego Music Store Main',
            'address'     => 'Jl. Musik No. 123',
            'phone'       => '081234567890',
            'is_active'   => true,
        ]);

        $this->user = User::factory()->create([
            'name'      => 'Admin Logistik',
            'username'  => 'adminlogistik',
            'email'     => 'logistik@diegomusic.com',
            'is_active' => true,
        ]);

        $this->user->branches()->attach($this->branch->id);
    }

    /** @test */
    public function it_calculates_stock_movements_in_and_out_correctly()
    {
        $unit = Unit::create(['code' => 'PCS', 'name' => 'Pcs']);

        $product = Product::create([
            'name'      => 'Gitar Akustik Taylor GS Mini',
            'type'      => 'physical',
            'category'  => 'Gitar',
            'unit_id'   => $unit->id,
            'is_active' => true,
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku'        => 'TYL-GSMINI',
            'name'       => 'Natural',
            'cost_price' => 8000000,
            'hpp'        => 8000000,
            'is_active'  => true,
        ]);

        // Stock Movement IN (+5)
        StockMovement::create([
            'product_variant_id' => $variant->id,
            'branch_id'          => $this->branch->id,
            'type'               => 'in',
            'quantity'           => 5,
            'unit_id'            => $unit->id,
            'hpp'                => 8000000,
            'reference_type'     => 'Purchase',
            'reference_id'       => 101,
        ]);

        // Stock Movement OUT (-2)
        StockMovement::create([
            'product_variant_id' => $variant->id,
            'branch_id'          => $this->branch->id,
            'type'               => 'out',
            'quantity'           => 2,
            'unit_id'            => $unit->id,
            'hpp'                => 8000000,
            'reference_type'     => 'POS',
            'reference_id'       => 202,
        ]);

        $action = new GenerateStockMovementReport();
        $report = $action->execute(now()->startOfMonth()->toDateString(), now()->toDateString(), $this->branch->id);

        $this->assertEquals(2, $report['total_transactions']);
        $this->assertEquals(5, $report['total_in_qty']);
        $this->assertEquals(2, $report['total_out_qty']);
        $this->assertEquals(3, $report['total_net_qty']);
        $this->assertEquals(56000000, $report['grand_total_valuation']); // (5*8m) + (2*8m)

        $this->assertCount(2, $report['rows']);
    }

    /** @test */
    public function it_can_render_stock_movement_report_filament_page()
    {
        Livewire::actingAs($this->user)
            ->test(StockMovementReport::class)
            ->assertStatus(200)
            ->assertSee('Laporan Mutasi Barang & Kartu Stok');
    }

    /** @test */
    public function it_can_export_pdf_and_csv_stream()
    {
        Livewire::actingAs($this->user)
            ->test(StockMovementReport::class)
            ->call('printPdf')
            ->call('exportExcel')
            ->assertStatus(200);
    }
}
