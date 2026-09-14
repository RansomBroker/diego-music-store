<?php

namespace Tests\Feature;

use App\Livewire\PosAttendances;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PosAttendancesTest extends TestCase
{
    use RefreshDatabase;

    public function test_pos_attendances_component_can_render_and_clock_in(): void
    {
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
}
