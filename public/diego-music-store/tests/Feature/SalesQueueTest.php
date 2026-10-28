<?php

namespace Tests\Feature;

use App\Actions\SalesQueue\CompleteSalesDuty;
use App\Actions\SalesQueue\InitializeDailySalesQueue;
use App\Actions\SalesQueue\StartSalesDuty;
use App\Actions\SalesQueue\ToggleSalesDutyStatus;
use App\Helpers\SalesQueueHelper;
use App\Livewire\Pos\SalesQueueWidget;
use App\Livewire\PosReportsSalesQueue;
use App\Models\Branch;
use App\Models\CashSession;
use App\Models\Employee;
use App\Models\Sale;
use App\Models\SalesDutyLog;
use App\Models\SalesQueueMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SalesQueueTest extends TestCase
{
    use RefreshDatabase;

    protected User $cashierUser;
    protected User $salesA;
    protected User $salesB;
    protected User $salesC;
    protected Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $salesRole = Role::firstOrCreate(['name' => 'sales', 'guard_name' => 'web']);
        $cashierRole = Role::firstOrCreate(['name' => 'karyawan', 'guard_name' => 'web']);

        $this->branch = Branch::create([
            'name' => 'Cabang Utama Diego',
            'store_name' => 'Diego Music Store',
            'address' => 'Jl. Musik No. 10',
            'phone' => '08123456789',
            'is_active' => true,
        ]);

        $this->cashierUser = User::factory()->create([
            'name' => 'Kasir Utama',
            'is_active' => true,
        ]);
        $this->cashierUser->assignRole($cashierRole);
        $this->cashierUser->branches()->attach($this->branch);

        $this->salesA = User::factory()->create([
            'name' => 'Sales A (Andi)',
            'is_active' => true,
        ]);
        $this->salesA->assignRole($salesRole);
        $this->salesA->branches()->attach($this->branch);

        $this->salesB = User::factory()->create([
            'name' => 'Sales B (Budi)',
            'is_active' => true,
        ]);
        $this->salesB->assignRole($salesRole);
        $this->salesB->branches()->attach($this->branch);

        $this->salesC = User::factory()->create([
            'name' => 'Sales C (Citra)',
            'is_active' => true,
        ]);
        $this->salesC->assignRole($salesRole);
        $this->salesC->branches()->attach($this->branch);

        $this->actingAs($this->cashierUser);
        session(['pos_active_branch_id' => $this->branch->id]);
    }

    /** @test */
    public function it_can_initialize_daily_sales_queue_in_order()
    {
        $queue = app(InitializeDailySalesQueue::class)->execute($this->branch->id);

        $this->assertCount(3, $queue);
        $this->assertEquals(1, $queue[0]->queue_order);
        $this->assertEquals('waiting', $queue[0]->status);
        $this->assertEquals(2, $queue[1]->queue_order);
        $this->assertEquals(3, $queue[2]->queue_order);

        $next = SalesQueueHelper::getNextWaitingSales($this->branch->id);
        $this->assertNotNull($next);
        $this->assertEquals($queue[0]->id, $next->id);
    }

    /** @test */
    public function it_transitions_sales_rep_to_on_duty_and_creates_duty_log()
    {
        $queue = app(InitializeDailySalesQueue::class)->execute($this->branch->id);
        $memberA = $queue[0];

        $dutyLog = app(StartSalesDuty::class)->execute($memberA);

        $memberA->refresh();
        $this->assertEquals('on_duty', $memberA->status);
        $this->assertNotNull($memberA->duty_started_at);
        $this->assertEquals($dutyLog->id, $memberA->current_duty_log_id);

        $this->assertDatabaseHas('sales_duty_logs', [
            'id' => $dutyLog->id,
            'branch_id' => $this->branch->id,
            'user_id' => $memberA->user_id,
            'status' => 'serving',
        ]);

        // Next waiting sales rep should now be Sales B
        $next = SalesQueueHelper::getNextWaitingSales($this->branch->id);
        $this->assertNotNull($next);
        $this->assertEquals($queue[1]->id, $next->id);
    }

    /** @test */
    public function it_completes_duty_and_places_sales_rep_at_back_of_queue_in_round_robin()
    {
        $queue = app(InitializeDailySalesQueue::class)->execute($this->branch->id);
        $memberA = $queue[0]; // order 1
        $memberB = $queue[1]; // order 2
        $memberC = $queue[2]; // order 3

        // 1. Sales A starts duty
        app(StartSalesDuty::class)->execute($memberA);

        // 2. While Sales A is serving, next up is Sales B
        $next = SalesQueueHelper::getNextWaitingSales($this->branch->id);
        $this->assertEquals($memberB->id, $next->id);

        // 3. Sales A completes duty
        app(CompleteSalesDuty::class)->execute($memberA, null, 1500000, 'Closing gitar akustik');

        $memberA->refresh();
        $memberB->refresh();
        $memberC->refresh();

        // Status should be back to waiting
        $this->assertEquals('waiting', $memberA->status);
        $this->assertEquals(1, $memberA->turns_count);

        // Round-robin verification:
        // Sales B is now #1, Sales C is now #2, Sales A is now at the end (#3)
        $waiting = SalesQueueHelper::getWaitingMembers($this->branch->id);
        $this->assertCount(3, $waiting);
        $this->assertEquals($memberB->id, $waiting[0]->id);
        $this->assertEquals(1, $waiting[0]->queue_order);
        $this->assertEquals($memberC->id, $waiting[1]->id);
        $this->assertEquals(2, $waiting[1]->queue_order);
        $this->assertEquals($memberA->id, $waiting[2]->id);
        $this->assertEquals(3, $waiting[2]->queue_order);

        // Duty log should be completed
        $this->assertDatabaseHas('sales_duty_logs', [
            'user_id' => $memberA->user_id,
            'status' => 'completed',
            'closing_amount' => 1500000,
        ]);
    }

    /** @test */
    public function it_can_toggle_sales_rep_to_off_duty_and_skip_them_in_queue()
    {
        $queue = app(InitializeDailySalesQueue::class)->execute($this->branch->id);
        $memberA = $queue[0]; // order 1
        $memberB = $queue[1]; // order 2

        // Sales A goes to break (off_duty)
        app(ToggleSalesDutyStatus::class)->execute($memberA);

        $memberA->refresh();
        $this->assertEquals('off_duty', $memberA->status);

        // Next waiting is now Sales B
        $next = SalesQueueHelper::getNextWaitingSales($this->branch->id);
        $this->assertEquals($memberB->id, $next->id);

        // Sales A returns from break
        app(ToggleSalesDutyStatus::class)->execute($memberA);
        $memberA->refresh();
        $this->assertEquals('waiting', $memberA->status);
    }

    /** @test */
    public function it_can_interact_with_sales_queue_widget_component()
    {
        Livewire::test(SalesQueueWidget::class, ['branchId' => $this->branch->id])
            ->assertSee('Antrian:')
            ->assertSee($this->salesA->name)
            ->call('toggleExpanded')
            ->assertSet('isExpanded', true)
            ->assertSee('Antrian Sales')
            ->call('startDuty', SalesQueueMember::first()->id)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('sales_queue_members', [
            'user_id' => $this->salesA->id,
            'status' => 'on_duty',
        ]);
    }

    /** @test */
    public function it_can_render_the_daily_sales_queue_report_page()
    {
        // Seed a completed duty log
        $member = SalesQueueMember::create([
            'branch_id' => $this->branch->id,
            'user_id' => $this->salesA->id,
            'queue_order' => 1,
            'status' => 'waiting',
            'turns_count' => 1,
            'is_active' => true,
            'date' => now()->toDateString(),
        ]);

        SalesDutyLog::create([
            'branch_id' => $this->branch->id,
            'user_id' => $this->salesA->id,
            'queue_member_id' => $member->id,
            'started_at' => now()->subMinutes(15),
            'ended_at' => now(),
            'duration_seconds' => 900,
            'status' => 'completed',
            'closing_amount' => 5000000,
            'created_by' => $this->cashierUser->id,
        ]);

        $response = $this->get(route('pos.reports.sales-queue'));
        $response->assertStatus(200);
        $response->assertSee('Laporan Antrian & Giliran Sales');
        $response->assertSee('Total Giliran');
        $response->assertSee($this->salesA->name);
    }

    /** @test */
    public function it_can_export_sales_queue_report_to_excel()
    {
        \Maatwebsite\Excel\Facades\Excel::fake();

        SalesQueueMember::create([
            'branch_id' => $this->branch->id,
            'user_id' => $this->salesA->id,
            'queue_order' => 1,
            'status' => 'waiting',
            'turns_count' => 1,
            'is_active' => true,
            'date' => now()->toDateString(),
        ]);

        \Livewire\Livewire::test(\App\Livewire\PosReportsSalesQueue::class)
            ->call('exportExcel')
            ->assertHasNoErrors();

        \Maatwebsite\Excel\Facades\Excel::assertDownloaded(
            'Laporan_Antrian_Sales_' . now()->format('Y-m-d') . '_' . \Illuminate\Support\Str::slug($this->branch->name) . '.xlsx'
        );
    }
}
