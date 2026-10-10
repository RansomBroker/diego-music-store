<?php

namespace Tests\Feature;

use App\Livewire\EmployeeDayOffCalendar;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\EmployeeDayOff;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class EmployeeDayOffCalendarTest extends TestCase
{
    use RefreshDatabase;

    private Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();

        $this->branch = Branch::create([
            'name' => 'Cabang Test',
            'store_name' => 'Cabang Test',
            'monthly_off_days_quota' => 4,
            'is_active' => true,
        ]);
    }

    private function createEmployeeInBranch(): array
    {
        $user = User::factory()->create();
        $user->employee()->update(['branch_id' => $this->branch->id]);

        return [$user, $user->employee()->firstOrFail()];
    }

    private function createDayOff(Employee $employee, string $date, string $status = 'active'): EmployeeDayOff
    {
        return EmployeeDayOff::create([
            'employee_id' => $employee->id,
            'branch_id' => $this->branch->id,
            'off_date' => $date,
            'status' => $status,
            'created_by' => $employee->user_id,
        ]);
    }

    public function test_employee_can_register_themselves_for_a_valid_day_off_date(): void
    {
        [$user, $employee] = $this->createEmployeeInBranch();
        $date = now()->addDays(2)->toDateString();

        $this->actingAs($user);

        Livewire::test(EmployeeDayOffCalendar::class)
            ->call('openDate', $date)
            ->call('registerMyself')
            ->assertHasNoErrors()
            ->assertDispatched('toast');

        $this->assertDatabaseHas('employee_day_offs', [
            'employee_id' => $employee->id,
            'branch_id' => $this->branch->id,
            'off_date' => $date,
            'status' => 'active',
            'created_by' => $user->id,
        ]);
    }

    public function test_employee_cannot_register_for_a_date_outside_the_allowed_window(): void
    {
        [$user] = $this->createEmployeeInBranch();
        $this->actingAs($user);

        Livewire::test(EmployeeDayOffCalendar::class)
            ->set('selectedDate', now()->addMonths(2)->addDays(2)->toDateString())
            ->call('registerMyself')
            ->assertHasErrors('selectedDate');

        $this->assertDatabaseCount('employee_day_offs', 0);
    }

    public function test_employee_cannot_register_twice_for_the_same_active_date(): void
    {
        [$user, $employee] = $this->createEmployeeInBranch();
        $date = now()->addDays(3)->toDateString();
        $this->createDayOff($employee, $date);

        $this->actingAs($user);

        Livewire::test(EmployeeDayOffCalendar::class)
            ->set('selectedDate', $date)
            ->call('registerMyself')
            ->assertHasErrors('selectedDate');

        $this->assertDatabaseCount('employee_day_offs', 1);
    }

    public function test_branch_monthly_quota_applies_to_each_employee_individually(): void
    {
        $this->branch->update(['monthly_off_days_quota' => 2]);
        [$applicant, $employee] = $this->createEmployeeInBranch();
        $this->createDayOff($employee, now()->addDays(2)->toDateString());
        $this->createDayOff($employee, now()->addDays(3)->toDateString());

        $this->actingAs($applicant);

        Livewire::test(EmployeeDayOffCalendar::class)
            ->set('selectedDate', now()->addDays(4)->toDateString())
            ->call('registerMyself')
            ->assertHasErrors('selectedDate');

        $this->assertDatabaseCount('employee_day_offs', 2);
    }

    public function test_owner_can_cancel_only_the_selected_employee_day_off(): void
    {
        Role::firstOrCreate(['name' => 'owner', 'guard_name' => 'web']);

        $owner = User::factory()->create();
        $owner->assignRole('owner');

        [, $employeeA] = $this->createEmployeeInBranch();
        [, $employeeB] = $this->createEmployeeInBranch();
        $date = now()->addDays(4)->toDateString();

        $dayOffA = $this->createDayOff($employeeA, $date);
        $dayOffB = $this->createDayOff($employeeB, $date);

        $this->actingAs($owner);

        Livewire::test(EmployeeDayOffCalendar::class)
            ->call('openCancelModal', $dayOffA->id)
            ->set('cancellationReason', 'Penyesuaian jadwal toko')
            ->call('cancelSelectedDayOff')
            ->assertHasNoErrors()
            ->assertDispatched('toast');

        $this->assertDatabaseHas('employee_day_offs', [
            'id' => $dayOffA->id,
            'status' => 'cancelled',
            'cancelled_by' => $owner->id,
            'cancellation_reason' => 'Penyesuaian jadwal toko',
        ]);
        $this->assertDatabaseHas('employee_day_offs', [
            'id' => $dayOffB->id,
            'status' => 'active',
            'cancelled_by' => null,
        ]);
    }

    public function test_non_owner_cannot_cancel_a_day_off(): void
    {
        [$user] = $this->createEmployeeInBranch();
        [, $otherEmployee] = $this->createEmployeeInBranch();
        $dayOff = $this->createDayOff($otherEmployee, now()->addDays(5)->toDateString());

        $this->actingAs($user);

        Livewire::test(EmployeeDayOffCalendar::class)
            ->call('openCancelModal', $dayOff->id)
            ->assertStatus(403);

        $this->assertDatabaseHas('employee_day_offs', [
            'id' => $dayOff->id,
            'status' => 'active',
            'cancelled_by' => null,
        ]);
    }
}
