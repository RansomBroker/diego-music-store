<?php

namespace Tests\Feature\Reports;

use App\Actions\Inventory\GenerateStockOpnameReport;
use App\Filament\Pages\Reports\StockOpnameReport;
use App\Models\Branch;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StockOpname;
use App\Models\StockOpnameItem;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class StockOpnameReportTest extends TestCase
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
            'name'      => 'Admin Auditor',
            'username'  => 'adminauditor',
            'email'     => 'audit@diegomusic.com',
            'is_active' => true,
        ]);

        $this->user->branches()->attach($this->branch->id);
    }

    /** @test */
    public function it_calculates_stock_opname_variances_and_adjustments_correctly()
    {
        $unit = Unit::create(['name' => 'Pcs']);

        $product = Product::create([
            'name'          => 'Keyboard Roland XPS-30',
            'type'          => 'physical',
            'category'      => 'Keyboard',
            'unit_id'       => $unit->id,
            'is_active'     => true,
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku'        => 'RLD-XPS30',
            'name'       => 'Black',
            'cost_price' => 12000000,
            'hpp'        => 12000000,
            'is_active'  => true,
        ]);

        // Stock Opname Session: System = 10, Physical = 8 => Difference = -2, Cost = 12.000.000 => Adjustment Value = -24.000.000
        $opname = StockOpname::create([
            'opname_number' => 'OPN-202607-001',
            'opname_date'   => now()->toDateString(),
            'branch_id'     => $this->branch->id,
            'status'        => 'completed',
            'notes'         => 'Audit bulanan area keyboard',
        ]);

        StockOpnameItem::create([
            'stock_opname_id'    => $opname->id,
            'product_variant_id' => $variant->id,
            'system_qty'         => 10,
            'physical_qty'       => 8,
            'difference'         => -2,
            'cost_price'         => 12000000,
        ]);

        $action = new GenerateStockOpnameReport();
        $report = $action->execute(now()->startOfMonth()->toDateString(), now()->toDateString(), $this->branch->id);

        $this->assertEquals(1, $report['total_opname_sessions']);
        $this->assertEquals(1, $report['total_items_audited']);
        $this->assertEquals(-2, $report['total_net_variance_qty']);
        $this->assertEquals(-24000000, $report['grand_total_adjustment_value']);

        $row = $report['opnames'][0];
        $this->assertEquals('OPN-202607-001', $row['opname_number']);
        $this->assertEquals(-2, $row['session_diff_qty']);
        $this->assertEquals(-24000000, $row['session_adjustment_value']);
    }

    /** @test */
    public function it_can_render_stock_opname_report_filament_page()
    {
        Livewire::actingAs($this->user)
            ->test(StockOpnameReport::class)
            ->assertStatus(200)
            ->assertSee('Laporan & Audit Stok Opname');
    }

    /** @test */
    public function it_can_export_pdf_and_csv_stream()
    {
        $component = Livewire::actingAs($this->user)
            ->test(StockOpnameReport::class);

        $pdfResponse = $component->call('printPdf');
        $this->assertEquals(200, $pdfResponse->status());

        $excelResponse = $component->call('exportExcel');
        $this->assertEquals(200, $excelResponse->status());
    }
}
