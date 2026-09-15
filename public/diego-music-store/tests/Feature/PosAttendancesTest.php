<?php

namespace Tests\Feature;

use App\Livewire\PosAttendances;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class PosAttendancesTest extends TestCase
{
    use RefreshDatabase;

    public function test_pos_attendances_component_can_render_and_clock_in(): void
    {
        Storage::fake('public');

        $branch = Branch::create([
            'name' => 'Cabang Test',
            'code' => 'CBG-TEST-02',
            'is_active' => true,
        ]);
        $user = User::factory()->create();
        $employee = $user->employee ?: Employee::create([
            'user_id' => $user->id,
            'branch_id' => $branch->id,
            'nik' => 'EMP-TEST-03',
            'name' => 'Agus Sales',
            'monthly_off_days_quota' => 4,
            'is_active' => true,
        ]);

        $this->actingAs($user);

        Livewire::test(PosAttendances::class)
            ->assertStatus(200)
            ->assertSee('Presensi')
            ->set('webcamDataUrl', 'data:image/jpeg;base64,' . base64_encode('fake-webcam-image'))
            ->call('quickClockIn')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('employee_attendances', [
            'employee_id' => $employee->id,
            'status' => 'hadir',
        ]);
    }

    public function test_pos_attendances_component_can_clock_out(): void
    {
        Storage::fake('public');

        $branch = Branch::create([
            'name' => 'Cabang Test 2',
            'code' => 'CBG-TEST-03',
            'is_active' => true,
        ]);
        $user = User::factory()->create();
        $employee = $user->employee;
        $employee->update(['branch_id' => $branch->id]);

        $this->actingAs($user);

        $test = Livewire::test(PosAttendances::class)
            ->set('webcamDataUrl', 'data:image/jpeg;base64,' . base64_encode('fake-webcam-image'))
            ->call('quickClockOut');

        $test->assertHasNoErrors()
            ->assertDispatched('toast');

        $this->assertDatabaseHas('employee_attendances', [
            'employee_id' => $employee->id,
            'status' => 'hadir',
        ]);
    }

    public function test_pos_attendances_component_can_record_attendance_off_day(): void
    {
        $branch = Branch::create([
            'name' => 'Cabang Test 3',
            'code' => 'CBG-TEST-04',
            'is_active' => true,
        ]);
        $user = User::factory()->create();
        $employee = $user->employee;
        $employee->update(['branch_id' => $branch->id]);

        $this->actingAs($user);

        Livewire::test(PosAttendances::class)
            ->call('openRecordModal')
            ->set('selectedEmployeeId', $employee->id)
            ->set('attendanceDate', now()->format('Y-m-d'))
            ->set('status', 'off_day')
            ->set('notes', 'Izin libur jatah bulanan')
            ->call('saveRecord')
            ->assertHasNoErrors()
            ->assertDispatched('toast');

        $this->assertDatabaseHas('employee_attendances', [
            'employee_id' => $employee->id,
            'status' => 'off_day',
        ]);
    }

    public function test_pos_attendances_component_can_submit_and_approve_backdate_request(): void
    {
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'owner', 'guard_name' => 'web']);

        $branch = Branch::create([
            'name' => 'Cabang Test 4',
            'code' => 'CBG-TEST-05',
            'is_active' => true,
        ]);
        $ownerUser = User::factory()->create();
        $ownerUser->assignRole('owner');
        $employee = $ownerUser->employee;
        $employee->update(['branch_id' => $branch->id]);

        $this->actingAs($ownerUser);

        $yesterday = now()->subDay()->format('Y-m-d');

        // 1. Submit Backdate Request
        Livewire::test(PosAttendances::class)
            ->call('openBackdateModal')
            ->set('backdateDate', $yesterday)
            ->set('backdateClockIn', '08:30')
            ->set('backdateClockOut', '17:00')
            ->set('backdateReason', 'Lupa clock in karena jaringan mati')
            ->call('submitBackdateRequest')
            ->assertHasNoErrors()
            ->assertDispatched('toast');

        $this->assertDatabaseHas('attendance_backdate_requests', [
            'employee_id' => $employee->id,
            'status' => 'pending',
            'reason' => 'Lupa clock in karena jaringan mati',
        ]);

        $request = \App\Models\AttendanceBackdateRequest::where('employee_id', $employee->id)->first();
        $this->assertNotNull($request);

        // 2. Approve Backdate Request
        Livewire::test(PosAttendances::class)
            ->call('processBackdateApproval', $request->id, 'approve')
            ->assertHasNoErrors()
            ->assertDispatched('toast');

        $this->assertDatabaseHas('attendance_backdate_requests', [
            'id' => $request->id,
            'status' => 'approved',
        ]);

        $this->assertDatabaseHas('employee_attendances', [
            'employee_id' => $employee->id,
            'date' => $yesterday . ' 00:00:00',
            'status' => 'hadir',
            'is_backdate' => true,
        ]);
    }

    public function test_pos_attendances_component_can_switch_tabs_to_backdate_requests(): void
    {
        $branch = Branch::create([
            'name' => 'Cabang Test 5',
            'code' => 'CBG-TEST-06',
            'is_active' => true,
        ]);
        $user = User::factory()->create();
        $employee = $user->employee;
        $employee->update(['branch_id' => $branch->id]);

        \App\Models\AttendanceBackdateRequest::create([
            'employee_id' => $employee->id,
            'branch_id' => $branch->id,
            'requested_date' => now()->subDay()->toDateString(),
            'clock_in' => '08:00:00',
            'clock_out' => '17:00:00',
            'reason' => 'Testing tab display',
            'status' => 'pending',
        ]);

        $this->actingAs($user);

        Livewire::test(PosAttendances::class)
            ->assertSee('Riwayat Presensi Karyawan')
            ->assertSee('Persetujuan Request Backdate')
            ->set('activeTab', 'backdate_requests')
            ->assertSee('Daftar permohonan presensi susulan')
            ->assertSee('Testing tab display');
    }
}
