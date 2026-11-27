<?php

namespace Tests\Feature;

use App\Actions\OwnerDashboard\GetOwnerDashboardData;
use App\Helpers\ReportHelper;
use App\Models\Account;
use App\Models\Branch;
use App\Models\CashSession;
use App\Models\CashTransaction;
use App\Models\User;
use Database\Seeders\AccountSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DailyCashReportHelperTest extends TestCase
{
    use RefreshDatabase;

    public function test_daily_cash_report_totals_in_out_and_transfer_transactions_correctly(): void
    {
        $this->seed(AccountSeeder::class);

        $branch = Branch::create([
            'name' => 'Cabang Kas Test',
            'code' => 'CBG-KAS-01',
            'is_active' => true,
        ]);

        $user = User::factory()->create(['is_active' => true]);
        $user->branches()->attach($branch->id);

        $session = CashSession::create([
            'user_id' => $user->id,
            'branch_id' => $branch->id,
            'opened_at' => now(),
            'opening_cash' => 500000,
            'status' => 'open',
        ]);

        $cashAccount = Account::first();
        $today = now()->toDateString();

        // Kas Masuk (in) = 300.000
        CashTransaction::create([
            'transaction_no' => 'CSH-TEST-001',
            'branch_id' => $branch->id,
            'cash_session_id' => $session->id,
            'type' => 'in',
            'transaction_date' => $today,
            'source_account_id' => $cashAccount->id,
            'destination_account_id' => $cashAccount->id,
            'amount' => 300000,
            'notes' => 'Tambahan modal kasir',
            'status' => 'posted',
            'created_by' => $user->id,
        ]);

        // Kas Keluar (out) = 125.000
        CashTransaction::create([
            'transaction_no' => 'CSH-TEST-002',
            'branch_id' => $branch->id,
            'cash_session_id' => $session->id,
            'type' => 'out',
            'transaction_date' => $today,
            'source_account_id' => $cashAccount->id,
            'destination_account_id' => $cashAccount->id,
            'amount' => 125000,
            'notes' => 'Beli ATK',
            'status' => 'posted',
            'created_by' => $user->id,
        ]);

        // Setor Kas (transfer) = 75.000
        CashTransaction::create([
            'transaction_no' => 'CSH-TEST-003',
            'branch_id' => $branch->id,
            'cash_session_id' => $session->id,
            'type' => 'transfer',
            'transaction_date' => $today,
            'source_account_id' => $cashAccount->id,
            'destination_account_id' => $cashAccount->id,
            'amount' => 75000,
            'notes' => 'Setor ke kas besar',
            'status' => 'posted',
            'created_by' => $user->id,
        ]);

        // Draft tidak boleh ikut terhitung
        CashTransaction::create([
            'transaction_no' => 'CSH-TEST-004',
            'branch_id' => $branch->id,
            'cash_session_id' => $session->id,
            'type' => 'out',
            'transaction_date' => $today,
            'source_account_id' => $cashAccount->id,
            'destination_account_id' => $cashAccount->id,
            'amount' => 999999,
            'notes' => 'Draft pengeluaran',
            'status' => 'draft',
            'created_by' => $user->id,
        ]);

        $report = ReportHelper::getDailyCashReport($today, $today, $branch->id);

        $this->assertEquals(3, $report['total_count']);
        $this->assertEquals(300000.0, $report['total_inflow']);
        $this->assertEquals(200000.0, $report['total_outflow']); // 125.000 + 75.000
        $this->assertEquals(100000.0, $report['net_cash_flow']); // 300.000 - 200.000

        // Verifikasi juga Owner Dashboard Total Pengeluaran & Pengeluaran Harian
        $ownerData = app(GetOwnerDashboardData::class)->execute([
            'dateFrom' => $today,
            'dateTo' => $today,
            'branchId' => $branch->id,
        ]);

        $this->assertEquals(125000.0, $ownerData['financialSummary']['total_pengeluaran']);
        $this->assertEquals(125000.0, $ownerData['financialSummary']['total_pengeluaran_harian']);
    }
}

