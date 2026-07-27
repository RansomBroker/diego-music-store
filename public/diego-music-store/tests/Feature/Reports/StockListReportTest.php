<?php

namespace Tests\Feature\Reports;

use App\Actions\Inventory\GenerateStockListReport;
use App\Filament\Pages\Reports\StockListReport;
use App\Models\Branch;
use App\Models\Product;
use App\Models\ProductBranchStock;
use App\Models\ProductVariant;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class StockListReportTest extends TestCase
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
            'name'      => 'Admin Logistics',
            'username'  => 'adminlogistics',
            'email'     => 'logistics@diegomusic.com',
            'is_active' => true,
        ]);

        $this->user->branches()->attach($this->branch->id);

        $unit = Unit::create(['name' => 'Unit']);

        $this->product = Product::create([
            'name'          => 'Gitar Listrik Fender Stratocaster',
            'type'          => 'physical',
            'category'      => 'Gitar',
            'brand'         => 'Fender',
            'unit_id'       => $unit->id,
            'minimum_stock' => 5,
            'is_active'     => true,
        ]);

        $this->variant = ProductVariant::create([
            'product_id'     => $this->product->id,
            'sku'            => 'FND-STRAT-01',
            'barcode'        => '8800112233',
            'name'           => 'Sunburst',
            'price'          => 15000000,
            'discount_value' => 10,
            'discount_type'  => 'percent',
            'tax_value'      => 11,
            'tax_type'       => 'percent',
            'cost_price'     => 10000000,
            'hpp'            => 10000000,
            'is_active'      => true,
        ]);

        ProductBranchStock::create([
            'product_variant_id' => $this->variant->id,
            'branch_id'          => $this->branch->id,
            'stock'              => 3, // Low stock (3 <= minimum 5)
            'hpp'                => 10000000,
        ]);
    }

    /** @test */
    public function it_calculates_stock_quantities_low_stock_alerts_and_valuations_correctly()
    {
        $action = new GenerateStockListReport();
        $report = $action->execute($this->branch->id, 'Gitar');

        $this->assertEquals(1, $report['total_variants']);
        $this->assertEquals(3, $report['total_physical_qty']);
        $this->assertEquals(1, $report['total_low_stock_count']); // Stock 3 <= min_stock 5
        $this->assertEquals(30000000, $report['grand_total_valuation']); // 3 * 10.000.000

        $row = $report['rows'][0];
        $this->assertEquals('FND-STRAT-01', $row['sku']);
        $this->assertEquals('Sunburst', $row['variant_name']);
        $this->assertEquals('STOK RENDAH', $row['status_label']);
        $this->assertEquals('10%', $row['discount']);
        $this->assertEquals('11%', $row['tax']);
    }

    /** @test */
    public function it_can_render_stock_list_report_filament_page()
    {
        Livewire::actingAs($this->user)
            ->test(StockListReport::class)
            ->assertStatus(200)
            ->assertSee('Laporan Daftar Stok & Nilai Persediaan');
    }

    /** @test */
    public function it_can_export_pdf_and_csv_stream()
    {
        $component = Livewire::actingAs($this->user)
            ->test(StockListReport::class);

        $pdfResponse = $component->call('printPdf');
        $this->assertEquals(200, $pdfResponse->status());

        $excelResponse = $component->call('exportExcel');
        $this->assertEquals(200, $excelResponse->status());
    }
}
