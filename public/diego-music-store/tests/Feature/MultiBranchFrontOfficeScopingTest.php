<?php

namespace Tests\Feature;

use App\Livewire\PosCustomerPayments;
use App\Livewire\PosEmployees;
use App\Livewire\PosSupplierPayments;
use App\Livewire\StockOpnameReview;
use App\Livewire\StockOpnameTasks;
use App\Models\Account;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\PaymentMethod;
use App\Models\Sale;
use App\Models\StockOpnameAssignment;
use App\Models\StockOpnamePeriod;
use App\Models\StockOpnamePreset;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MultiBranchFrontOfficeScopingTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Branch $branch1;
    protected Branch $branch2;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'owner', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);

        $this->user = User::factory()->create(['is_active' => true]);
        $this->user->assignRole('owner');
        $this->actingAs($this->user);

        $this->branch1 = Branch::create(['name' => 'Cabang Jakarta', 'is_active' => true]);
        $this->branch2 = Branch::create(['name' => 'Cabang Surabaya', 'is_active' => true]);

        $this->user->branches()->attach([$this->branch1->id, $this->branch2->id]);

        // Default to Branch 1 active in session
        session(['pos_active_branch_id' => $this->branch1->id]);
    }

    public function test_pos_supplier_payments_isolates_sales_and_accounts_to_active_branch(): void
    {
        $customer = Customer::create(['name' => 'Pelanggan X']);

        $acc1 = Account::create(['branch_id' => $this->branch1->id, 'name' => 'Kas Jakarta', 'code' => '1111', 'classification' => 'asset', 'type' => 'debit']);
        $acc2 = Account::create(['branch_id' => $this->branch2->id, 'name' => 'Kas Surabaya', 'code' => '1111', 'classification' => 'asset', 'type' => 'debit']);

        $sale1 = Sale::create([
            'branch_id' => $this->branch1->id,
            'customer_id' => $customer->id,
            'sales_rep_id' => $this->user->id,
            'created_by' => $this->user->id,
            'invoice_number' => 'INV-JKT-001',
            'invoice_date' => now(),
            'payment_method' => 'piutang',
            'status' => 'draft',
            'subtotal' => 50000,
            'tax' => 0,
            'discount' => 0,
            'grand_total' => 50000,
            'paid_amount' => 0,
            'change_amount' => 0,
        ]);

        $sale2 = Sale::create([
            'branch_id' => $this->branch2->id,
            'customer_id' => $customer->id,
            'sales_rep_id' => $this->user->id,
            'created_by' => $this->user->id,
            'invoice_number' => 'INV-SBY-001',
            'invoice_date' => now(),
            'payment_method' => 'piutang',
            'status' => 'draft',
            'subtotal' => 75000,
            'tax' => 0,
            'discount' => 0,
            'grand_total' => 75000,
            'paid_amount' => 0,
            'change_amount' => 0,
        ]);

        Livewire::test(PosSupplierPayments::class)
            ->assertViewHas('payments', function ($payments) use ($sale1, $sale2) {
                return $payments->contains('id', $sale1->id) && !$payments->contains('id', $sale2->id);
            })
            ->assertViewHas('accounts', function ($accounts) use ($acc1, $acc2) {
                return $accounts->contains('id', $acc1->id) && !$accounts->contains('id', $acc2->id);
            });
    }

    public function test_pos_customer_payments_isolates_accounts_to_active_branch(): void
    {
        $acc1 = Account::create(['branch_id' => $this->branch1->id, 'name' => 'Kas Jakarta', 'code' => '1111', 'classification' => 'asset', 'type' => 'debit']);
        $acc2 = Account::create(['branch_id' => $this->branch2->id, 'name' => 'Kas Surabaya', 'code' => '1111', 'classification' => 'asset', 'type' => 'debit']);

        PaymentMethod::create([
            'name' => 'Tunai',
            'code' => 'TUNAI',
            'is_active' => true,
        ]);

        Livewire::test(PosCustomerPayments::class)
            ->assertViewHas('accounts', function ($accounts) use ($acc1, $acc2) {
                return $accounts->contains('id', $acc1->id) && !$accounts->contains('id', $acc2->id);
            });
    }

    public function test_pos_employees_isolates_employee_list_to_active_branch(): void
    {
        $emp1 = Employee::create([
            'name' => 'Budi Jakarta',
            'nik' => 'EMP-001',
            'branch_id' => $this->branch1->id,
            'is_active' => true,
        ]);
        $emp2 = Employee::create([
            'name' => 'Siti Surabaya',
            'nik' => 'EMP-002',
            'branch_id' => $this->branch2->id,
            'is_active' => true,
        ]);

        Livewire::test(PosEmployees::class)
            ->assertViewHas('employees', function ($employees) use ($emp1, $emp2) {
                return $employees->contains('id', $emp1->id) && !$employees->contains('id', $emp2->id);
            });
    }

    public function test_stock_opname_review_isolates_periods_to_active_branch(): void
    {
        $period1 = StockOpnamePeriod::create([
            'branch_id' => $this->branch1->id,
            'name'      => 'Opname Jakarta',
            'period'    => '2026-10-01',
            'status'    => 'active',
        ]);
        $period2 = StockOpnamePeriod::create([
            'branch_id' => $this->branch2->id,
            'name'      => 'Opname Surabaya',
            'period'    => '2026-10-01',
            'status'    => 'active',
        ]);

        Livewire::test(StockOpnameReview::class)
            ->assertViewHas('periods', function ($periods) use ($period1, $period2) {
                return $periods->contains('id', $period1->id) && !$periods->contains('id', $period2->id);
            });
    }

    public function test_stock_opname_tasks_isolates_assignments_to_active_branch(): void
    {
        Employee::create([
            'user_id'   => $this->user->id,
            'name'      => 'Staff JKT',
            'nik'       => 'EMP-003',
            'branch_id' => $this->branch1->id,
            'is_active' => true,
        ]);

        $user2 = User::factory()->create();
        $user2->assignRole('admin');
        $user2->branches()->attach($this->branch2->id);
        Employee::create([
            'user_id'   => $user2->id,
            'name'      => 'Staff SBY',
            'nik'       => 'EMP-004',
            'branch_id' => $this->branch2->id,
            'is_active' => true,
        ]);

        $period1 = StockOpnamePeriod::create([
            'branch_id' => $this->branch1->id,
            'name'      => 'Opname Jakarta',
            'period'    => '2026-10-01',
            'status'    => 'draft',
        ]);
        $preset1 = StockOpnamePreset::create([
            'branch_id' => $this->branch1->id,
            'name'      => 'Area JKT',
        ]);
        $assign1 = StockOpnameAssignment::create([
            'stock_opname_period_id' => $period1->id,
            'stock_opname_preset_id' => $preset1->id,
            'assigned_user_id'       => $this->user->id,
            'status'                 => 'assigned',
        ]);

        $period2 = StockOpnamePeriod::create([
            'branch_id' => $this->branch2->id,
            'name'      => 'Opname Surabaya',
            'period'    => '2026-10-01',
            'status'    => 'draft',
        ]);
        $preset2 = StockOpnamePreset::create([
            'branch_id' => $this->branch2->id,
            'name'      => 'Area SBY',
        ]);
        $assign2 = StockOpnameAssignment::create([
            'stock_opname_period_id' => $period2->id,
            'stock_opname_preset_id' => $preset2->id,
            'assigned_user_id'       => $user2->id,
            'status'                 => 'assigned',
        ]);

        $period1->update(['status' => 'active']);
        $period2->update(['status' => 'active']);

        Livewire::test(StockOpnameTasks::class)
            ->assertViewHas('assignments', function ($assignments) use ($assign1, $assign2) {
                return $assignments->contains('id', $assign1->id) && !$assignments->contains('id', $assign2->id);
            });
    }
}
