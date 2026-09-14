<?php

namespace Tests\Feature;

use App\Livewire\PosCommissions;
use App\Models\Branch;
use App\Models\CommissionScheme;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PosCommissionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_pos_commissions_page_renders_successfully(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('pos.commissions'))
            ->assertStatus(200);
    }

    public function test_user_can_create_commission_scheme(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(PosCommissions::class)
            ->set('schemeName', 'Komisi Sales 3%')
            ->set('calculationType', 'percentage')
            ->set('rate', 3.0)
            ->set('appliesTo', 'all_sales')
            ->call('saveScheme');

        $this->assertDatabaseHas('commission_schemes', [
            'name' => 'Komisi Sales 3%',
            'calculation_type' => 'percentage',
            'rate' => 3.0,
            'is_active' => true,
        ]);
    }
}
