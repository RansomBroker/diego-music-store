<?php

namespace Tests\Feature\Actions;

use App\Actions\Attendance\ClockIn;
use App\Actions\Attendance\ClockOut;
use App\Actions\Attendance\RecordAttendance;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\EmployeeAttendance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClockInClockOutTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_can_clock_in_and_clock_out(): void
    {
        $branch = Branch::create([
            'name' => 'Cabang Utama Test',
            'code' => 'CBG-TEST-01',
            'is_active' => true,
        ]);
        $user = User::factory()->create();
        $employee = Employee::create([
            'user_id' => $user->id,
            'branch_id' => $branch->id,
            'nik' => 'EMP-TEST-01',
            'name' => 'Budi Staf',
            'monthly_off_days_quota' => 4,
            'is_active' => true,
        ]);

        $clockInAction = app(ClockIn::class);
        $attendance = $clockInAction->execute($employee, $branch->id, 'Masuk shift pagi');

        $this->assertDatabaseHas('employee_attendances', [
            'employee_id' => $employee->id,
            'branch_id' => $branch->id,
            'status' => 'hadir',
        ]);
        $this->assertNotNull($attendance->clock_in);

        $clockOutAction = app(ClockOut::class);
        $updatedAttendance = $clockOutAction->execute($employee, 'Pulang shift');

        $this->assertNotNull($updatedAttendance->clock_out);
    }

    public function test_record_attendance_action_sets_off_day_and_calculates_quota(): void
    {
        $user = User::factory()->create();
        $employee = Employee::create([
            'user_id' => $user->id,
            'nik' => 'EMP-TEST-02',
            'name' => 'Siti Kasir',
            'monthly_off_days_quota' => 2,
            'is_active' => true,
        ]);

        $recordAction = app(RecordAttendance::class);

        // Record 3 off days in current month
        for ($i = 1; $i <= 3; $i++) {
            $date = now()->startOfMonth()->addDays($i - 1)->format('Y-m-d');
            $recordAction->execute($employee, [
                'date' => $date,
                'status' => 'off_day',
            ]);
        }

        $this->assertEquals(3, $employee->used_off_days_this_month);
        $this->assertTrue($employee->is_off_days_over_quota);
        $this->assertEquals(1, $employee->off_days_over_count);
    }

    public function test_clock_in_fails_when_outside_branch_radius(): void
    {
        $branch = Branch::create([
            'name' => 'Cabang Monas',
            'code' => 'CBG-MNS',
            'latitude' => -6.1753924,
            'longitude' => 106.8271528,
            'attendance_radius_meters' => 100,
            'is_active' => true,
        ]);
        $user = User::factory()->create();
        $employee = Employee::create([
            'user_id' => $user->id,
            'branch_id' => $branch->id,
            'nik' => 'EMP-TEST-09',
            'name' => 'Budi Monas',
            'is_active' => true,
        ]);

        $this->expectException(\InvalidArgumentException::class);

        // Clock in from Bandung (far outside 100m radius of Monas Jakarta)
        app(ClockIn::class)->execute(
            $employee,
            $branch->id,
            'Mencoba presensi dari Bandung',
            -6.9174639,
            107.6191228
        );
    }
}
