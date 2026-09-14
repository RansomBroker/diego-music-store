<?php

namespace Tests\Feature;

use App\Actions\OwnerDashboard\GetOwnerDashboardData;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GetOwnerDashboardDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_owner_dashboard_data_returns_all_11_required_widgets()
    {
        $branch = Branch::create(['name' => 'Cabang Utama Surabaya', 'is_active' => true]);
        $customer = Customer::create(['name' => 'Budi Gitaris', 'phone' => '08123456789']);
        $user = User::factory()->create();

        $product = Product::create([
            'name' => 'Gitar Akustik Yamaha',
            'category' => 'Gitar',
            'sku' => 'GTR-YMH-01',
            'type' => 'single',
            'is_active' => true,
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'GTR-YMH-01',
            'name' => 'Gitar Akustik Yamaha Standard',
            'price' => 1500000,
            'cost_price' => 1000000,
            'hpp' => 1000000,
        ]);

        $sale = Sale::create([
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'sales_rep_id' => $user->id,
            'invoice_number' => 'INV-TEST-001',
            'invoice_date' => now()->format('Y-m-d'),
            'subtotal' => 1500000,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'grand_total' => 1500000,
            'payment_method' => 'cash',
            'status' => 'completed',
            'created_by' => $user->id,
        ]);

        SaleItem::create([
            'sale_id' => $sale->id,
            'product_variant_id' => $variant->id,
            'quantity' => 1,
            'unit_price' => 1500000,
            'subtotal' => 1500000,
        ]);

        $action = new GetOwnerDashboardData();
        $data = $action->execute([
            'dateFrom' => now()->startOfMonth()->format('Y-m-d'),
            'dateTo'   => now()->format('Y-m-d'),
            'branchId' => $branch->id,
        ]);

        $this->assertArrayHasKey('financialSummary', $data);
        $this->assertEquals(1500000, $data['financialSummary']['total_penjualan']);
        $this->assertEquals(500000, $data['financialSummary']['laba_kotor_est']);

        $this->assertArrayHasKey('salesVsPurchasesChart', $data);
        $this->assertArrayHasKey('stockTurnoverChart', $data);
        $this->assertArrayHasKey('paretoChart', $data);
        $this->assertArrayHasKey('monthlyTrendChart', $data);
        $this->assertArrayHasKey('categoryChart', $data);
        $this->assertArrayHasKey('branchChart', $data);
        $this->assertArrayHasKey('dailyTrafficChart', $data);
        $this->assertArrayHasKey('hourlyTrafficChart', $data);
        $this->assertArrayHasKey('salesPerformanceChart', $data);
    }
}
