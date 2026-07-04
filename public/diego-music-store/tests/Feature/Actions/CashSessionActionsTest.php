<?php

namespace Tests\Feature\Actions;

use App\Actions\CashSession\OpenCashSession;
use App\Actions\CashSession\CloseCashSession;
use App\Models\Branch;
use App\Models\CashSession;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class CashSessionActionsTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private User $supervisor;
    private Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->supervisor = User::factory()->create();
        $this->branch = Branch::create([
            'name' => 'Cabang Kuta',
            'address' => 'Kuta',
            'phone' => '0361-998877',
            'is_active' => true,
        ]);
    }

    public function test_it_can_open_a_cash_session(): void
    {
        $data = [
            'user_id' => $this->user->id,
            'branch_id' => $this->branch->id,
            'opening_cash' => 500000,
            'notes' => 'Sesi pagi',
        ];

        $session = app(OpenCashSession::class)->execute($data);

        $this->assertInstanceOf(CashSession::class, $session);
        $this->assertEquals('open', $session->status);
        $this->assertEquals(500000, $session->opening_cash);
        $this->assertEquals($this->user->id, $session->user_id);
        $this->assertEquals($this->branch->id, $session->branch_id);
        $this->assertNotNull($session->opened_at);
        $this->assertNull($session->closed_at);
    }

    public function test_it_cannot_open_duplicate_active_session(): void
    {
        CashSession::create([
            'user_id' => $this->user->id,
            'branch_id' => $this->branch->id,
            'opened_at' => now(),
            'opening_cash' => 500000,
            'status' => 'open',
        ]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Anda sudah memiliki sesi kasir aktif di cabang ini.');

        app(OpenCashSession::class)->execute([
            'user_id' => $this->user->id,
            'branch_id' => $this->branch->id,
            'opening_cash' => 300000,
        ]);
    }

    public function test_it_can_close_a_cash_session_and_calculate_expected_and_difference(): void
    {
        // 1. Open Session
        $session = CashSession::create([
            'user_id' => $this->user->id,
            'branch_id' => $this->branch->id,
            'opened_at' => now(),
            'opening_cash' => 500000,
            'expected_cash' => 500000,
            'status' => 'open',
        ]);

        // 2. Simulate cash sale
        Sale::create([
            'branch_id' => $this->branch->id,
            'cash_session_id' => $session->id,
            'customer_id' => null,
            'sales_rep_id' => $this->user->id,
            'invoice_number' => 'INV-TEST-0001',
            'invoice_date' => now(),
            'subtotal' => 1200000,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'grand_total' => 1200000,
            'payment_method' => 'cash',
            'status' => 'completed',
            'created_by' => $this->user->id,
        ]);

        // 3. Simulate another sale via credit (should not count towards expected laci cash)
        Sale::create([
            'branch_id' => $this->branch->id,
            'cash_session_id' => $session->id,
            'customer_id' => null,
            'sales_rep_id' => $this->user->id,
            'invoice_number' => 'INV-TEST-0002',
            'invoice_date' => now(),
            'subtotal' => 800000,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'grand_total' => 800000,
            'payment_method' => 'credit',
            'status' => 'completed',
            'created_by' => $this->user->id,
        ]);

        // Expected cash = 500,000 (opening) + 1,200,000 (cash sale) = 1,700,000
        // Actual counted cash = 1,695,000 (Shortage of 5,000)
        $closeData = [
            'actual_cash' => 1695000,
            'notes' => 'Ada selisih kurang 5rb',
            'closed_by_user_id' => $this->supervisor->id,
        ];

        $closedSession = app(CloseCashSession::class)->execute($session, $closeData);

        $this->assertEquals('closed', $closedSession->status);
        $this->assertEquals(1700000, $closedSession->expected_cash);
        $this->assertEquals(1695000, $closedSession->actual_cash);
        $this->assertEquals(-5000, $closedSession->difference);
        $this->assertEquals($this->supervisor->id, $closedSession->closed_by_user_id);
        $this->assertNotNull($closedSession->closed_at);
    }

    public function test_it_cannot_close_already_closed_session(): void
    {
        $session = CashSession::create([
            'user_id' => $this->user->id,
            'branch_id' => $this->branch->id,
            'opened_at' => now(),
            'closed_at' => now(),
            'opening_cash' => 500000,
            'expected_cash' => 500000,
            'status' => 'closed',
        ]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Hanya sesi aktif (open) yang dapat ditutup.');

        app(CloseCashSession::class)->execute($session, [
            'actual_cash' => 500000,
            'closed_by_user_id' => $this->supervisor->id,
        ]);
    }
}
