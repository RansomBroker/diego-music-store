<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\AttendanceRadiuses\AttendanceRadiusResource;
use App\Filament\Resources\AttendanceRadiuses\Pages\ListAttendanceRadiuses;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AttendanceRadiusResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_filament_attendance_radius_resource_can_render_and_update_radius(): void
    {
        $branch = Branch::create([
            'name' => 'Cabang Pontianak Backoffice',
            'code' => 'CBG-BO-PNK',
            'latitude' => -0.0347008,
            'longitude' => 109.3323921,
            'attendance_radius_meters' => 100,
            'is_active' => true,
        ]);

        $user = User::factory()->create([
            'is_active' => true,
        ]);

        Livewire::actingAs($user)
            ->test(ListAttendanceRadiuses::class)
            ->assertStatus(200)
            ->assertSee('Cabang Pontianak Backoffice');
    }
}
