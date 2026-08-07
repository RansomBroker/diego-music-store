<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PosBranchPerformanceTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();

        $this->branch = Branch::create([
            'name'       => 'Cabang Utama',
            'store_name' => 'Diego Music Utama',
            'is_active'  => true,
        ]);

        $this->user = User::factory()->create();
        $this->user->branches()->attach($this->branch->id);

        $this->actingAs($this->user);
    }

    /** @test */
    public function it_can_render_pos_branch_performance_page()
    {
        Livewire::test(\App\Livewire\PosBranchPerformance::class)
            ->assertStatus(200)
            ->assertSee('Manajemen & Performa Cabang')
            ->assertSee('Konsolidasi entitas bisnis');
    }
}
