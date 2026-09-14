<?php

namespace Tests\Feature\Actions;

use App\Actions\Attendance\ProcessAttendanceViolations;
use App\Models\AttendanceViolationLog;
use App\Models\AttendanceViolationRule;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\EmployeeAttendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProcessAttendanceViolationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_process_late_in_attendance_violation(): void
    {
        $branch = Branch::create([
            'name' => 'Cabang Test Denda',
            'code' => 'CBG-DENDA-01',
            'is_active' => true,
        ]);

        $user = User::factory()->create();
        $employee = Employee::create([
            'user_id' => $user->id,
            'branch_id' => $branch->id,
            'nik' => 'EMP-DENDA-01',
            'name' => 'Sales Terlambat',
            'monthly_off_days_quota' => 4,
            'is_active' => true,
        ]);

        $rule = AttendanceViolationRule::create([
            'name' => 'Terlambat Ringan 1-15m',
            'violation_type' => 'late_in',
            'min_minutes' => 1,
            'max_minutes' => 15,
            'deduction_type' => 'fixed_amount',
            'deduction_amount' => 10000,
            'is_active' => true,
        ]);

        $attendanceDate = now()->format('Y-m-d');

        // Clock in at 08:12 (12 mins late)
        $attendance = EmployeeAttendance::create([
            'employee_id' => $employee->id,
            'branch_id' => $branch->id,
            'date' => $attendanceDate,
            'clock_in' => Carbon::parse($attendanceDate . ' 08:12:00'),
            'status' => 'hadir',
        ]);

        $action = app(ProcessAttendanceViolations::class);
        $logs = $action->execute($attendance, '08:00:00', '17:00:00');

        $this->assertNotEmpty($logs);
        $this->assertEquals(1, count($logs));

        $log = $logs[0];
        $this->assertEquals($employee->id, $log->employee_id);
        $this->assertEquals('late_in', $log->violation_type);
        $this->assertEquals(12, $log->late_early_minutes);
        $this->assertEquals(10000, $log->deduction_amount);
        $this->assertEquals('pending', $log->status);
    }
}
