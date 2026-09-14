<?php

namespace Tests\Feature;

use App\Livewire\PosAttendanceRadiuses;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PosAttendanceRadiusesTest extends TestCase
{
    use RefreshDatabase;

    public function test_pos_attendance_radiuses_page_can_render_and_update_radius(): void
    {
        $branch = Branch::create([
            'name' => 'Cabang Pontianak Utama',
            'code' => 'CBG-PNK-001',
            'latitude' => -0.0347008,
            'longitude' => 109.3323921,
            'attendance_radius_meters' => 100,
            'is_active' => true,
        ]);

        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(PosAttendanceRadiuses::class)
            ->assertStatus(200)
            ->assertSee('Pengaturan Radius Presensi Cabang')
            ->assertSee('Cabang Pontianak Utama')
            ->call('openEditModal', $branch->id)
            ->set('latitude', -0.0347100)
            ->set('longitude', 109.3324000)
            ->set('attendance_radius_meters', 250)
            ->call('saveRadius')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('branches', [
            'id' => $branch->id,
            'attendance_radius_meters' => 250,
        ]);
    }
}
