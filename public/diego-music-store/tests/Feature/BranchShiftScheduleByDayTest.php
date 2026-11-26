<?php

namespace Tests\Feature;

use App\Actions\Attendance\ClockIn;
use App\Actions\Branch\CreateBranch;
use App\Actions\Branch\UpdateBranch;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class BranchShiftScheduleByDayTest extends TestCase
{
    use RefreshDatabase;

    public function test_branch_resolves_different_shift_times_for_weekdays_vs_weekends_and_applies_in_clock_in(): void
    {
        $branch = CreateBranch::execute([
            'name' => 'Cabang Jadwal Shift',
            'store_name' => 'Diego Musik Shift',
            'shift_start_time' => '09:00:00',
            'shift_end_time' => '17:00:00',
            'shift_schedules' => [
                [
                    'days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
                    'start_time' => '09:00',
                    'end_time' => '17:00',
                ],
                [
                    'days' => ['saturday', 'sunday'],
                    'start_time' => '10:00',
                    'end_time' => '19:00',
                ],
            ],
        ]);

        // Wednesday -> 09:00 - 17:00
        $wednesday = Carbon::parse('2026-11-25 09:30:00');
        $this->assertSame(['start' => '09:00:00', 'end' => '17:00:00'], $branch->getShiftTimesForDate($wednesday));

        // Saturday -> 10:00 - 19:00
        $saturday = Carbon::parse('2026-11-28 09:30:00');
        $this->assertSame(['start' => '10:00:00', 'end' => '19:00:00'], $branch->getShiftTimesForDate($saturday));

        // Summary displays weekday & weekend schedules
        $this->assertStringContainsString('09:00-17:00', $branch->getShiftScheduleSummary());
        $this->assertStringContainsString('10:00-19:00', $branch->getShiftScheduleSummary());

        // ClockIn at 09:30 on Wednesday => 30 minutes late; on Saturday => 0 minutes late
        $ownerRole = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'owner', 'guard_name' => 'web']);
        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole($ownerRole);
        $user->branches()->attach($branch->id);
        $employee = Employee::create([
            'user_id' => $user->id,
            'branch_id' => $branch->id,
            'name' => 'Staf Shift',
            'nik' => 'EMP-SHIFT-01',
            'position' => 'Sales',
            'status' => 'active',
        ]);
        $this->actingAs($user);

        Carbon::setTestNow($wednesday);
        $weekdayAttendance = app(ClockIn::class)->execute($employee, $branch->id);
        $this->assertSame(30, $weekdayAttendance->late_minutes);

        Carbon::setTestNow($saturday);
        $weekendAttendance = app(ClockIn::class)->execute($employee, $branch->id);
        $this->assertSame(0, $weekendAttendance->late_minutes);

        Carbon::setTestNow();

        // UpdateBranch can update shift_schedules
        UpdateBranch::execute($branch, [
            'shift_schedules' => [
                [
                    'days' => ['sunday'],
                    'start_time' => '11:00',
                    'end_time' => '20:00',
                ],
            ],
        ]);
        $branch->refresh();
        $this->assertSame(['start' => '11:00:00', 'end' => '20:00:00'], $branch->getShiftTimesForDate(Carbon::parse('2026-11-29')));
        // Unconfigured day falls back to default 09:00 - 17:00
        $this->assertSame(['start' => '09:00:00', 'end' => '17:00:00'], $branch->getShiftTimesForDate($wednesday));
    }
}
