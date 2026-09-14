<?php

namespace Tests\Feature;

use App\Livewire\PosAttendanceViolations;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PosAttendanceViolationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_pos_attendance_violations_page_renders_successfully(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('pos.attendance-violations'));

        $response->assertStatus(200);
        $response->assertSee('Potongan');
        $response->assertSee('1. Rekap Potongan Presensi');
        $response->assertSee('3. Log Detail Pelanggaran');
    }

    public function test_livewire_component_can_create_violation_rule(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(PosAttendanceViolations::class)
            ->set('ruleName', 'Terlambat Test 10k')
            ->set('violationType', 'late_in')
            ->set('minMinutes', 1)
            ->set('maxMinutes', 20)
            ->set('deductionType', 'fixed_amount')
            ->set('deductionAmount', 10000)
            ->set('isActive', true)
            ->call('saveRule');

        $this->assertDatabaseHas('attendance_violation_rules', [
            'name' => 'Terlambat Test 10k',
            'violation_type' => 'late_in',
            'deduction_amount' => 10000,
        ]);
    }
}
