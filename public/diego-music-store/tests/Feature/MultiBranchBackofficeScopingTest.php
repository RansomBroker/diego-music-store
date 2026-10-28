<?php

namespace Tests\Feature;

use App\Filament\Resources\Accounts\AccountResource;
use App\Filament\Resources\AssetDisposals\AssetDisposalResource;
use App\Filament\Resources\AttendanceViolationLogs\AttendanceViolationLogResource;
use App\Filament\Resources\DeliveryOrders\DeliveryOrderResource;
use App\Filament\Resources\Employees\EmployeeResource;
use App\Filament\Resources\FocusProductResource;
use App\Filament\Resources\KpiEvaluations\KpiEvaluationResource;
use App\Filament\Resources\SalesCommissions\SalesCommissionResource;
use App\Filament\Resources\SalesQuotations\SalesQuotationResource;
use App\Filament\Resources\ScheduledJournalEntries\ScheduledJournalEntryResource;
use App\Models\Account;
use App\Models\Asset;
use App\Models\AssetDisposal;
use App\Models\AttendanceViolationLog;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\DeliveryOrder;
use App\Models\Employee;
use App\Models\FocusProduct;
use App\Models\KpiEvaluation;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\SalesCommissionLog;
use App\Models\SalesQuotation;
use App\Models\ScheduledJournalEntry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MultiBranchBackofficeScopingTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Branch $branch1;
    protected Branch $branch2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['is_active' => true]);
        $this->actingAs($this->user);

        $this->branch1 = Branch::create(['name' => 'Cabang 1', 'is_active' => true]);
        $this->branch2 = Branch::create(['name' => 'Cabang 2', 'is_active' => true]);

        // Default to Branch 1 active in session
        session(['pos_active_branch_id' => $this->branch1->id]);
    }

    public function test_account_resource_scopes_to_active_branch(): void
    {
        Account::create(['branch_id' => $this->branch1->id, 'name' => 'Kas Cabang 1', 'code' => '111101', 'classification' => 'asset', 'type' => 'debit']);
        Account::create(['branch_id' => $this->branch2->id, 'name' => 'Kas Cabang 2', 'code' => '111101', 'classification' => 'asset', 'type' => 'debit']);

        $query = AccountResource::getEloquentQuery();
        $this->assertEquals(1, $query->count());
        $this->assertEquals($this->branch1->id, $query->first()->branch_id);
    }

    public function test_delivery_order_resource_scopes_to_active_branch(): void
    {
        $customer = Customer::create(['name' => 'Customer A']);

        DeliveryOrder::create([
            'branch_id'     => $this->branch1->id,
            'customer_id'   => $customer->id,
            'do_number'     => 'DO-001',
            'delivery_date' => now(),
            'status'        => 'draft',
        ]);
        DeliveryOrder::create([
            'branch_id'     => $this->branch2->id,
            'customer_id'   => $customer->id,
            'do_number'     => 'DO-002',
            'delivery_date' => now(),
            'status'        => 'draft',
        ]);

        $query = DeliveryOrderResource::getEloquentQuery();
        $this->assertEquals(1, $query->count());
        $this->assertEquals('DO-001', $query->first()->do_number);
    }

    public function test_sales_quotation_resource_scopes_to_active_branch(): void
    {
        $customer = Customer::create(['name' => 'Customer B']);

        SalesQuotation::create([
            'branch_id'        => $this->branch1->id,
            'customer_id'      => $customer->id,
            'quotation_number' => 'SQ-001',
            'quotation_date'   => now(),
            'status'           => 'draft',
            'subtotal'         => 100000,
            'grand_total'      => 100000,
        ]);
        SalesQuotation::create([
            'branch_id'        => $this->branch2->id,
            'customer_id'      => $customer->id,
            'quotation_number' => 'SQ-002',
            'quotation_date'   => now(),
            'status'           => 'draft',
            'subtotal'         => 200000,
            'grand_total'      => 200000,
        ]);

        $query = SalesQuotationResource::getEloquentQuery();
        $this->assertEquals(1, $query->count());
        $this->assertEquals('SQ-001', $query->first()->quotation_number);
    }

    public function test_scheduled_journal_entry_resource_scopes_to_active_branch(): void
    {
        ScheduledJournalEntry::create([
            'branch_id'   => $this->branch1->id,
            'description' => 'Sewa Gedung B1',
            'start_date'  => now(),
            'frequency'   => 'monthly',
            'interval'    => 1,
            'status'      => 'active',
        ]);
        ScheduledJournalEntry::create([
            'branch_id'   => $this->branch2->id,
            'description' => 'Sewa Gedung B2',
            'start_date'  => now(),
            'frequency'   => 'monthly',
            'interval'    => 1,
            'status'      => 'active',
        ]);

        $query = ScheduledJournalEntryResource::getEloquentQuery();
        $this->assertEquals(1, $query->count());
        $this->assertEquals('Sewa Gedung B1', $query->first()->description);
    }

    public function test_asset_disposal_resource_scopes_to_active_branch(): void
    {
        $asset1 = Asset::create([
            'name'          => 'Gitar Display 1',
            'branch_id'     => $this->branch1->id,
            'category'      => 'equipment',
            'purchase_date' => now(),
            'purchase_cost' => 1000000,
        ]);
        $asset2 = Asset::create([
            'name'          => 'Gitar Display 2',
            'branch_id'     => $this->branch2->id,
            'category'      => 'equipment',
            'purchase_date' => now(),
            'purchase_cost' => 1000000,
        ]);

        AssetDisposal::create([
            'branch_id'       => $this->branch1->id,
            'asset_id'        => $asset1->id,
            'disposal_number' => 'DSP-001',
            'disposal_date'   => now(),
            'disposal_type'   => 'sale',
            'book_value'      => 500000,
            'disposal_amount' => 600000,
            'status'          => 'draft',
        ]);
        AssetDisposal::create([
            'branch_id'       => $this->branch2->id,
            'asset_id'        => $asset2->id,
            'disposal_number' => 'DSP-002',
            'disposal_date'   => now(),
            'disposal_type'   => 'sale',
            'book_value'      => 500000,
            'disposal_amount' => 600000,
            'status'          => 'draft',
        ]);

        $query = AssetDisposalResource::getEloquentQuery();
        $this->assertEquals(1, $query->count());
        $this->assertEquals('DSP-001', $query->first()->disposal_number);
    }

    public function test_employee_and_commission_and_violation_resources_scope_to_active_branch(): void
    {
        $emp1 = Employee::create([
            'name'      => 'Staff Cabang 1',
            'branch_id' => $this->branch1->id,
            'is_active' => true,
        ]);
        $emp2 = Employee::create([
            'name'      => 'Staff Cabang 2',
            'branch_id' => $this->branch2->id,
            'is_active' => true,
        ]);

        // EmployeeResource
        $empQuery = EmployeeResource::getEloquentQuery();
        $this->assertEquals(1, $empQuery->count());
        $this->assertEquals($emp1->id, $empQuery->first()->id);

        // SalesCommissionResource
        SalesCommissionLog::create([
            'employee_id'       => $emp1->id,
            'date'              => now(),
            'sale_amount'       => 1000000,
            'commission_amount' => 50000,
            'status'            => 'pending',
        ]);
        SalesCommissionLog::create([
            'employee_id'       => $emp2->id,
            'date'              => now(),
            'sale_amount'       => 2000000,
            'commission_amount' => 100000,
            'status'            => 'pending',
        ]);

        $commQuery = SalesCommissionResource::getEloquentQuery();
        $this->assertEquals(1, $commQuery->count());
        $this->assertEquals($emp1->id, $commQuery->first()->employee_id);

        // AttendanceViolationLogResource
        AttendanceViolationLog::create([
            'employee_id'     => $emp1->id,
            'date'            => now(),
            'violation_type'  => 'late_in',
            'deduction_amount'=> 25000,
            'status'          => 'pending',
        ]);
        AttendanceViolationLog::create([
            'employee_id'     => $emp2->id,
            'date'            => now(),
            'violation_type'  => 'late_in',
            'deduction_amount'=> 25000,
            'status'          => 'pending',
        ]);

        $violQuery = AttendanceViolationLogResource::getEloquentQuery();
        $this->assertEquals(1, $violQuery->count());
        $this->assertEquals($emp1->id, $violQuery->first()->employee_id);
    }

    public function test_kpi_evaluation_and_focus_product_resources_scope_to_active_branch(): void
    {
        $emp1 = Employee::create(['name' => 'Emp 1', 'branch_id' => $this->branch1->id, 'is_active' => true]);
        $emp2 = Employee::create(['name' => 'Emp 2', 'branch_id' => $this->branch2->id, 'is_active' => true]);

        KpiEvaluation::create([
            'employee_id' => $emp1->id,
            'branch_id'   => $this->branch1->id,
            'period'      => '2026-10',
        ]);
        KpiEvaluation::create([
            'employee_id' => $emp2->id,
            'branch_id'   => $this->branch2->id,
            'period'      => '2026-10',
        ]);

        $kpiQuery = KpiEvaluationResource::getEloquentQuery();
        $this->assertEquals(1, $kpiQuery->count());
        $this->assertEquals($this->branch1->id, $kpiQuery->first()->branch_id);

        $product = Product::create(['name' => 'Guitar Focus', 'type' => 'physical']);
        $variant = ProductVariant::create(['product_id' => $product->id, 'sku' => 'SKU-FOC-01', 'price' => 1000]);

        FocusProduct::create([
            'product_variant_id' => $variant->id,
            'branch_id'          => $this->branch1->id,
            'focus_number'       => 'FP-001',
            'status'             => 'ACTIVE',
            'active_from'        => now(),
        ]);
        FocusProduct::create([
            'product_variant_id' => $variant->id,
            'branch_id'          => $this->branch2->id,
            'focus_number'       => 'FP-002',
            'status'             => 'ACTIVE',
            'active_from'        => now(),
        ]);

        $focusQuery = FocusProductResource::getEloquentQuery();
        $this->assertEquals(1, $focusQuery->count());
        $this->assertEquals('FP-001', $focusQuery->first()->focus_number);
    }

    public function test_stock_opname_period_and_preset_resources_scope_to_active_branch(): void
    {
        $period1 = \App\Models\StockOpnamePeriod::create([
            'branch_id' => $this->branch1->id,
            'name'      => 'Opname Cabang 1',
            'period'    => '2026-10-01',
            'status'    => 'active',
        ]);
        \App\Models\StockOpnamePeriod::create([
            'branch_id' => $this->branch2->id,
            'name'      => 'Opname Cabang 2',
            'period'    => '2026-10-01',
            'status'    => 'active',
        ]);

        $periodQuery = \App\Filament\Resources\StockOpnamePeriods\StockOpnamePeriodResource::getEloquentQuery();
        $this->assertEquals(1, $periodQuery->count());
        $this->assertEquals($period1->id, $periodQuery->first()->id);

        $preset1 = \App\Models\StockOpnamePreset::create([
            'branch_id' => $this->branch1->id,
            'name'      => 'Gudang Utama Cabang 1',
        ]);
        \App\Models\StockOpnamePreset::create([
            'branch_id' => $this->branch2->id,
            'name'      => 'Gudang Utama Cabang 2',
        ]);

        $presetQuery = \App\Filament\Resources\StockOpnamePresets\StockOpnamePresetResource::getEloquentQuery();
        $this->assertEquals(1, $presetQuery->count());
        $this->assertEquals($preset1->id, $presetQuery->first()->id);
    }

    public function test_commission_scheme_and_attendance_radius_resources_scope_to_active_branch(): void
    {
        $scheme1 = \App\Models\CommissionScheme::create([
            'branch_id' => $this->branch1->id,
            'name'      => 'Skema Cabang 1',
            'rate'      => 5,
        ]);
        \App\Models\CommissionScheme::create([
            'branch_id' => $this->branch2->id,
            'name'      => 'Skema Cabang 2',
            'rate'      => 5,
        ]);

        $schemeQuery = \App\Filament\Resources\CommissionSchemes\CommissionSchemeResource::getEloquentQuery();
        $this->assertEquals(1, $schemeQuery->count());
        $this->assertEquals($scheme1->id, $schemeQuery->first()->id);

        $radiusQuery = \App\Filament\Resources\AttendanceRadiuses\AttendanceRadiusResource::getEloquentQuery();
        $this->assertEquals(1, $radiusQuery->count());
        $this->assertEquals($this->branch1->id, $radiusQuery->first()->id);
    }
}
