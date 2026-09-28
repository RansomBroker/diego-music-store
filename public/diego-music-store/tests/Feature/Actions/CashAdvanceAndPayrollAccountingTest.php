<?php

namespace Tests\Feature\Actions;

use App\Actions\CashAdvance\ApproveCashAdvance;
use App\Actions\CashAdvance\CreateCashAdvanceRequest;
use App\Actions\CashAdvance\SettleCashAdvanceEarly;
use App\Actions\Payroll\GenerateMonthlyPayroll;
use App\Actions\Payroll\ProcessPayrollPayment;
use App\Helpers\AccountHelper;
use App\Models\Account;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\EmployeeCashAdvance;
use App\Models\JournalEntry;
use App\Models\Payroll;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CashAdvanceAndPayrollAccountingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure primary accounts exist
        AccountHelper::resolveAccountId('111101001', 'KAS', 'asset');
        AccountHelper::resolveAccountId('111201001', 'BANK BCA', 'asset');
        AccountHelper::resolveAccountId('111301002', 'PIUTANG KARYAWAN', 'asset');
        AccountHelper::resolveAccountId('611101001', 'BEBAN GAJI KARYAWAN', 'expense');
    }

    public function test_cash_advance_approval_creates_balanced_gl_journal(): void
    {
        $branch = Branch::create(['name' => 'Cabang Kasbon Test', 'code' => 'CBG-KB-01', 'is_active' => true]);
        $user = User::factory()->create();
        $employee = Employee::create([
            'user_id' => $user->id,
            'branch_id' => $branch->id,
            'nik' => 'EMP-KB-01',
            'name' => 'Budi Kasbon',
            'basic_salary' => 5000000,
            'is_active' => true,
        ]);

        $bankAccount = Account::where('code', '111201001')->first();

        // 1. Submit cash advance request: Rp 1.000.000 (tenor 2 months)
        $advance = app(CreateCashAdvanceRequest::class)->execute($employee->id, 1000000, 2, 'Biaya darurat keluarga');
        $this->assertEquals('pending', $advance->status);

        // 2. Approve & Disburse with GL journal
        $approvedAdvance = app(ApproveCashAdvance::class)->execute(
            $advance->id,
            $user,
            'Disetujui manajer keuangan',
            $bankAccount->id,
            now()->format('Y-m-d')
        );

        $this->assertEquals('approved', $approvedAdvance->status);
        $this->assertEquals(1000000, $approvedAdvance->remaining_amount);
        $this->assertEquals($bankAccount->id, $approvedAdvance->disbursement_account_id);
        $this->assertNotNull($approvedAdvance->journal_no);

        // Verify Journal Entry
        $journal = JournalEntry::with('items.account')->where('entry_no', $approvedAdvance->journal_no)->first();
        $this->assertNotNull($journal);
        $this->assertEquals('posted', $journal->status);
        $this->assertEquals('EmployeeCashAdvance', $journal->reference_type);
        $this->assertEquals($advance->id, $journal->reference_id);

        // Verify Journal Items: Debit Piutang Karyawan, Credit Bank BCA
        $this->assertCount(2, $journal->items);
        $debitItem = $journal->items->where('debit', '>', 0)->first();
        $creditItem = $journal->items->where('credit', '>', 0)->first();

        $this->assertEquals('111301002', $debitItem->account->code);
        $this->assertEquals(1000000, $debitItem->debit);

        $this->assertEquals('111201001', $creditItem->account->code);
        $this->assertEquals(1000000, $creditItem->credit);

        // Balance Check
        $this->assertEquals($journal->items->sum('debit'), $journal->items->sum('credit'));
    }

    public function test_cash_advance_early_repayment_creates_balanced_gl_journal(): void
    {
        $branch = Branch::create(['name' => 'Cabang Repay Test', 'code' => 'CBG-RP-01', 'is_active' => true]);
        $user = User::factory()->create();
        $employee = Employee::create([
            'user_id' => $user->id,
            'branch_id' => $branch->id,
            'nik' => 'EMP-RP-01',
            'name' => 'Siti Repay',
            'basic_salary' => 6000000,
            'is_active' => true,
        ]);

        $cashAccount = Account::where('code', '111101001')->first();

        $advance = app(CreateCashAdvanceRequest::class)->execute($employee->id, 1000000, 1, 'Kebutuhan mendesak');
        app(ApproveCashAdvance::class)->execute($advance->id, $user, null, $cashAccount->id);

        // Early repayment Rp 400.000 via cash
        $repaid = app(SettleCashAdvanceEarly::class)->execute(
            $advance->id,
            400000,
            'cash',
            'Titip tunai pelunasan',
            $user,
            $cashAccount->id
        );

        $this->assertEquals(400000, $repaid->paid_amount);
        $this->assertEquals(600000, $repaid->remaining_amount);
        $this->assertEquals('approved', $repaid->status);

        // Verify Journal Entry for early repayment
        $journal = JournalEntry::with('items.account')
            ->where('reference_type', 'EmployeeCashAdvanceRepayment')
            ->where('reference_id', $advance->id)
            ->first();

        $this->assertNotNull($journal);
        $this->assertEquals('posted', $journal->status);

        $debitItem = $journal->items->where('debit', '>', 0)->first();
        $creditItem = $journal->items->where('credit', '>', 0)->first();

        // Debit Kas Toko (+400.000)
        $this->assertEquals('111101001', $debitItem->account->code);
        $this->assertEquals(400000, $debitItem->debit);

        // Credit Piutang Karyawan (-400.000)
        $this->assertEquals('111301002', $creditItem->account->code);
        $this->assertEquals(400000, $creditItem->credit);

        // Balance Check
        $this->assertEquals($journal->items->sum('debit'), $journal->items->sum('credit'));
    }

    public function test_payroll_payment_deducts_kasbon_and_creates_balanced_gl_journal(): void
    {
        $branch = Branch::create(['name' => 'Cabang Payroll & Kasbon', 'code' => 'CBG-PK-01', 'is_active' => true]);
        $user = User::factory()->create();
        $employee = Employee::create([
            'user_id' => $user->id,
            'branch_id' => $branch->id,
            'nik' => 'EMP-PK-01',
            'name' => 'Dewi Payroll',
            'basic_salary' => 4000000,
            'is_active' => true,
        ]);

        $bankAccount = Account::where('code', '111201001')->first();

        // 1. Employee takes cash advance: Rp 1.000.000 with monthly installment Rp 500.000 (tenor 2)
        $advance = app(CreateCashAdvanceRequest::class)->execute($employee->id, 1000000, 2, 'Kasbon darurat');
        app(ApproveCashAdvance::class)->execute($advance->id, $user, null, $bankAccount->id);

        $period = now()->format('Y-m');

        // 2. Generate monthly payroll: Gross = 4.000.000, Kasbon = 500.000, Net THP = 3.500.000
        $payroll = app(GenerateMonthlyPayroll::class)->execute($period, $branch->id, $user);

        $this->assertEquals(4000000, $payroll->total_basic_salary);
        $this->assertEquals(500000, $payroll->total_deductions);
        $this->assertEquals(3500000, $payroll->total_net_salary);

        // 3. Process payroll payment
        $paidPayroll = app(ProcessPayrollPayment::class)->execute($payroll->id, $user, $bankAccount->id);

        $this->assertEquals('paid', $paidPayroll->status);
        $this->assertEquals($bankAccount->id, $paidPayroll->payment_account_id);
        $this->assertNotNull($paidPayroll->journal_no);

        // 4. Verify Cash Advance remaining balance was reduced by 500.000
        $advance->refresh();
        $this->assertEquals(500000, $advance->paid_amount);
        $this->assertEquals(500000, $advance->remaining_amount);
        $this->assertEquals('approved', $advance->status);

        // 5. Verify Payroll GL Journal Entry
        $journal = JournalEntry::with('items.account')->where('entry_no', $paidPayroll->journal_no)->first();
        $this->assertNotNull($journal);
        $this->assertEquals('posted', $journal->status);
        $this->assertEquals('Payroll', $journal->reference_type);
        $this->assertEquals($payroll->id, $journal->reference_id);

        // Items should be 3:
        // [D] 611101001 - BEBAN GAJI KARYAWAN: Rp 4.000.000
        // [K] 111301002 - PIUTANG KARYAWAN: Rp 500.000
        // [K] 111201001 - BANK BCA: Rp 3.500.000
        $this->assertCount(3, $journal->items);

        $debitSalary = $journal->items->where('account.code', '611101001')->first();
        $creditReceivable = $journal->items->where('account.code', '111301002')->first();
        $creditBank = $journal->items->where('account.code', '111201001')->first();

        $this->assertNotNull($debitSalary);
        $this->assertEquals(4000000, $debitSalary->debit);
        $this->assertEquals(0, $debitSalary->credit);

        $this->assertNotNull($creditReceivable);
        $this->assertEquals(0, $creditReceivable->debit);
        $this->assertEquals(500000, $creditReceivable->credit);

        $this->assertNotNull($creditBank);
        $this->assertEquals(0, $creditBank->debit);
        $this->assertEquals(3500000, $creditBank->credit);

        // Perfect balance check
        $this->assertEquals(4000000, $journal->items->sum('debit'));
        $this->assertEquals(4000000, $journal->items->sum('credit'));
    }
}
