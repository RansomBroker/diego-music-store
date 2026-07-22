<?php

namespace Tests\Feature;

use App\Helpers\ReportHelper;
use App\Models\Branch;
use App\Models\User;
use App\Models\Sale;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductBranchStock;
use App\Livewire\PosReportsSales;
use App\Livewire\PosReportsArAging;
use App\Livewire\PosReportsArSettlement;
use App\Livewire\PosReportsDailyCash;
use App\Livewire\PosReportsStockPrices;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PosReportsTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();

        $this->branch = Branch::create([
            'name' => 'Main Branch',
            'store_name' => 'Diego Store Test',
            'address' => 'Jakarta',
            'phone' => '08123456789',
            'is_active' => true,
        ]);

        $this->user = User::factory()->create([
            'name' => 'Test Admin',
            'username' => 'testadmin',
            'email' => 'admin@test.com',
            'is_active' => true,
        ]);

        $this->user->branches()->attach($this->branch->id);
    }

    /** @test */
    public function it_can_render_all_standalone_pos_report_pages()
    {
        Livewire::actingAs($this->user)
            ->test(PosReportsSales::class)
            ->assertStatus(200)
            ->assertSee('Laporan Penjualan ERP');

        Livewire::actingAs($this->user)
            ->test(PosReportsArAging::class)
            ->assertStatus(200)
            ->assertSee('Laporan Piutang Usaha (AR Aging)');

        Livewire::actingAs($this->user)
            ->test(PosReportsArSettlement::class)
            ->assertStatus(200)
            ->assertSee('Laporan Pelunasan Piutang');

        Livewire::actingAs($this->user)
            ->test(PosReportsDailyCash::class)
            ->assertStatus(200)
            ->assertSee('Laporan Kas Harian ERP');

        Livewire::actingAs($this->user)
            ->test(PosReportsStockPrices::class)
            ->assertStatus(200)
            ->assertSee('Daftar Stok & Penilaian Harga');
    }

    /** @test */
    public function it_calculates_sales_report_and_kpi()
    {
        $sale = Sale::create([
            'branch_id' => $this->branch->id,
            'invoice_number' => 'INV-TEST-001',
            'invoice_date' => now()->toDateString(),
            'subtotal' => 1000000,
            'discount_amount' => 50000,
            'tax_amount' => 10000,
            'grand_total' => 960000,
            'payment_method' => 'cash',
            'status' => 'completed',
            'created_by' => $this->user->id,
        ]);

        $data = ReportHelper::getSalesReport(now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString(), $this->branch->id);

        $this->assertEquals(1, $data['total_transactions']);
        $this->assertEquals(960000, $data['grand_total']);
    }

    /** @test */
    public function it_calculates_stock_valuation_report()
    {
        $product = Product::create([
            'name' => 'Gitar Akustik Yamaha',
            'type' => 'physical',
            'is_active' => true,
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'name' => 'Natural',
            'sku' => 'GTR-YAM-01',
            'price' => 2000000,
            'cost_price' => 1200000,
            'hpp' => 1200000,
            'is_active' => true,
        ]);

        ProductBranchStock::create([
            'product_variant_id' => $variant->id,
            'branch_id' => $this->branch->id,
            'stock' => 10,
            'hpp' => 1200000,
        ]);

        $data = ReportHelper::getStockValuationReport($this->branch->id);

        $this->assertEquals(1, $data['total_sku']);
        $this->assertEquals(10, $data['total_qty']);
        $this->assertEquals(12000000, $data['total_hpp_valuation']);
        $this->assertEquals(20000000, $data['total_retail_valuation']);
        $this->assertEquals(8000000, $data['potential_profit']);
    }
}
