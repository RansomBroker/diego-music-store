<?php

namespace Tests\Feature;

use App\Actions\SalesDashboard\GetSalesEmployeeDashboardData;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SalesCommissionLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GetSalesEmployeeDashboardDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_sales_employee_dashboard_data_returns_all_8_required_widgets()
    {
        $branch = Branch::create(['name' => 'Cabang Test', 'is_active' => true]);
        $user = User::factory()->create();
        $employee = $user->employee ?: Employee::create([
            'user_id'                => $user->id,
            'name'                   => $user->name,
            'nik'                    => 'EMP-0099',
            'monthly_off_days_quota' => 4,
            'is_active'              => true,
        ]);
        $employee->update([
            'monthly_off_days_quota' => 4,
            'is_active'              => true,
        ]);

        $product = Product::create([
            'name'      => 'Gitar Electrik Ibanez',
            'category'  => 'Gitar',
            'sku'       => 'GTR-IBNZ-01',
            'type'      => 'single',
            'is_active' => true,
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku'        => 'GTR-IBNZ-01',
            'name'       => 'Ibanez RG Standard',
            'price'      => 4500000,
            'cost_price' => 3000000,
            'hpp'        => 3000000,
        ]);

        $sale = Sale::create([
            'branch_id'      => $branch->id,
            'sales_rep_id'   => $user->id,
            'invoice_number' => 'INV-SALES-001',
            'invoice_date'   => now()->format('Y-m-d'),
            'subtotal'       => 4500000,
            'grand_total'    => 4500000,
            'payment_method' => 'cash',
            'status'         => 'completed',
            'created_by'     => $user->id,
        ]);

        SaleItem::create([
            'sale_id'            => $sale->id,
            'product_variant_id' => $variant->id,
            'quantity'           => 1,
            'unit_price'         => 4500000,
            'total_price'        => 4500000,
            'subtotal'           => 4500000,
        ]);

        SalesCommissionLog::create([
            'employee_id'       => $employee->id,
            'sale_id'           => $sale->id,
            'sale_amount'       => 4500000,
            'commission_amount' => 90000,
            'date'              => now()->format('Y-m-d'),
        ]);

        $user->refresh();
        $action = new GetSalesEmployeeDashboardData();
        $data = $action->execute($user);

        $this->assertArrayHasKey('monthlyTarget', $data);
        $this->assertEquals(4500000, $data['monthlyTarget']['achieved_amount']);

        $this->assertArrayHasKey('dailyTarget', $data);
        $this->assertArrayHasKey('monthlyCommission', $data);
        $this->assertEquals(90000, $data['monthlyCommission']);

        $this->assertArrayHasKey('tierInfo', $data);
        $this->assertArrayHasKey('leaderboard', $data);
        $this->assertArrayHasKey('focusProducts', $data);
        $this->assertArrayHasKey('yearlyPerformanceChart', $data);
        $this->assertArrayHasKey('attendanceInfo', $data);
    }
}
