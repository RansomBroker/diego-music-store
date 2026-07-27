<?php

namespace Tests\Feature\Reports;

use App\Actions\Inventory\GenerateEndingInventoryReport;
use App\Filament\Pages\Reports\EndingInventoryReport;
use App\Models\Branch;
use App\Models\Product;
use App\Models\ProductBranchStock;
use App\Models\ProductVariant;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class EndingInventoryReportTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Branch $branch;
    protected Product $product;
    protected ProductVariant $variant;

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
            'name'      => 'Admin Inventori',
            'username'  => 'admininventori',
            'email'     => 'inventori@diegomusic.com',
            'is_active' => true,
        ]);

        $this->user->branches()->attach($this->branch->id);

        $unit = Unit::create(['code' => 'UNT', 'name' => 'Unit']);

        $this->product = Product::create([
            'name'          => 'Drum Elektrik Yamaha DTX402K',
            'type'          => 'physical',
            'category'      => 'Drum',
            'brand'         => 'Yamaha',
            'unit_id'       => $unit->id,
            'is_active'     => true,
        ]);

        $this->variant = ProductVariant::create([
            'product_id' => $this->product->id,
            'sku'        => 'YMH-DTX402',
            'barcode'    => '9988776655',
            'name'       => 'Black Standard',
            'price'      => 7500000,
            'cost_price' => 5000000,
            'hpp'        => 5000000,
            'is_active'  => true,
        ]);

        ProductBranchStock::create([
            'product_variant_id' => $this->variant->id,
            'branch_id'          => $this->branch->id,
            'stock'              => 4,
            'hpp'                => 5000000,
        ]);
    }

    /** @test */
    public function it_calculates_ending_inventory_quantities_and_valuations_correctly()
    {
        $action = new GenerateEndingInventoryReport();
        $report = $action->execute(now()->toDateString(), $this->branch->id, 'Drum');

        $this->assertEquals(1, $report['total_variants']);
        $this->assertEquals(4, $report['total_ending_qty']);
        $this->assertEquals(20000000, $report['grand_total_valuation']); // 4 * 5.000.000

        $row = $report['rows'][0];
        $this->assertEquals('YMH-DTX402', $row['sku']);
        $this->assertEquals('Drum Elektrik Yamaha DTX402K (Black Standard)', $row['full_name']);
        $this->assertEquals(4, $row['ending_qty']);
        $this->assertEquals(20000000, $row['valuation']);
    }

    /** @test */
    public function it_can_render_ending_inventory_report_filament_page()
    {
        Livewire::actingAs($this->user)
            ->test(EndingInventoryReport::class)
            ->assertStatus(200)
            ->assertSee('Laporan Persediaan Akhir (Ending Inventory)');
    }

    /** @test */
    public function it_can_export_pdf_and_csv_stream()
    {
        Livewire::actingAs($this->user)
            ->test(EndingInventoryReport::class)
            ->call('printPdf')
            ->call('exportExcel')
            ->assertStatus(200);
    }
}
