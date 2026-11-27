<?php

namespace Tests\Feature;

use App\Actions\Procurement\GenerateAccountsPayableReport;
use App\Models\Account;
use App\Models\Branch;
use App\Models\PurchaseReturn;
use App\Models\PurchaseTransaction;
use App\Models\Supplier;
use App\Models\SupplierPayment;
use App\Models\SupplierPaymentItem;
use App\Models\User;
use Database\Seeders\AccountSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountsPayableReportTest extends TestCase
{
    use RefreshDatabase;

    private Branch $branch;
    private Supplier $supplier;
    private User $user;
    private Account $cashAccount;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(AccountSeeder::class);

        $this->branch = Branch::create([
            'name' => 'Cabang AP Test',
            'code' => 'CBG-AP-01',
            'is_active' => true,
        ]);

        $this->supplier = Supplier::create([
            'name' => 'Supplier AP Test',
            'code' => 'SUP-AP-01',
            'is_active' => true,
        ]);

        $this->user = User::factory()->create(['is_active' => true]);
        $this->cashAccount = Account::first();
    }

    public function test_fully_paid_credit_invoice_disappears_from_accounts_payable_report(): void
    {
        $today = now()->format('Y-m-d');

        $ptPaid = PurchaseTransaction::create([
            'transaction_no' => 'PT-AP-PAID-01',
            'transaction_date' => $today,
            'due_date' => now()->addDays(14)->format('Y-m-d'),
            'supplier_id' => $this->supplier->id,
            'branch_id' => $this->branch->id,
            'warehouse_id' => $this->branch->id,
            'purchase_type' => PurchaseTransaction::TYPE_KREDIT,
            'subtotal' => 5000000,
            'grand_total' => 5000000,
            'down_payment_amount' => 1000000,
            'status' => 'posted',
            'created_by' => $this->user->id,
        ]);

        $payment = SupplierPayment::create([
            'payment_no' => 'SP-AP-001',
            'payment_date' => $today,
            'supplier_id' => $this->supplier->id,
            'branch_id' => $this->branch->id,
            'account_id' => $this->cashAccount->id,
            'payment_method' => 'Tunai',
            'total_amount' => 4000000,
            'status' => 'posted',
            'created_by' => $this->user->id,
        ]);

        SupplierPaymentItem::create([
            'supplier_payment_id' => $payment->id,
            'purchase_transaction_id' => $ptPaid->id,
            'amount_due' => 4000000,
            'amount_paid' => 4000000,
        ]);

        $report = app(GenerateAccountsPayableReport::class)->execute($today, $this->branch->id);

        $this->assertEquals(0, $report['total_invoices']);
        $this->assertEquals(0.0, $report['total_unpaid']);
        $this->assertEmpty($report['invoices']);
    }

    public function test_partial_payment_return_deduction_and_consignment_are_calculated_accurately(): void
    {
        $today = now()->format('Y-m-d');

        // 1. Kredit 3.000.000, dibayar 1.000.000, diretur invoice_deduction 500.000 => sisa 1.500.000
        $ptCredit = PurchaseTransaction::create([
            'transaction_no' => 'PT-AP-PARTIAL-01',
            'transaction_date' => $today,
            'due_date' => now()->addDays(7)->format('Y-m-d'),
            'supplier_id' => $this->supplier->id,
            'branch_id' => $this->branch->id,
            'warehouse_id' => $this->branch->id,
            'purchase_type' => PurchaseTransaction::TYPE_KREDIT,
            'subtotal' => 3000000,
            'grand_total' => 3000000,
            'status' => 'posted',
            'created_by' => $this->user->id,
        ]);

        $payment = SupplierPayment::create([
            'payment_no' => 'SP-AP-002',
            'payment_date' => $today,
            'supplier_id' => $this->supplier->id,
            'branch_id' => $this->branch->id,
            'account_id' => $this->cashAccount->id,
            'payment_method' => 'Tunai',
            'total_amount' => 1000000,
            'status' => 'posted',
            'created_by' => $this->user->id,
        ]);

        SupplierPaymentItem::create([
            'supplier_payment_id' => $payment->id,
            'purchase_transaction_id' => $ptCredit->id,
            'amount_due' => 3000000,
            'amount_paid' => 1000000,
        ]);

        PurchaseReturn::create([
            'return_no' => 'PR-AP-001',
            'return_date' => $today,
            'purchase_transaction_id' => $ptCredit->id,
            'supplier_id' => $this->supplier->id,
            'branch_id' => $this->branch->id,
            'return_type' => 'invoice_deduction',
            'total_amount' => 500000,
            'status' => 'posted',
            'created_by' => $this->user->id,
        ]);

        // 2. Konsinyasi 2.000.000 (tanpa jatuh tempo), belum dibayar => sisa 2.000.000 & tidak overdue
        PurchaseTransaction::create([
            'transaction_no' => 'PT-AP-KONS-01',
            'transaction_date' => now()->subDays(10)->format('Y-m-d'),
            'due_date' => null,
            'supplier_id' => $this->supplier->id,
            'branch_id' => $this->branch->id,
            'warehouse_id' => $this->branch->id,
            'purchase_type' => PurchaseTransaction::TYPE_KONSINYASI,
            'subtotal' => 2000000,
            'grand_total' => 2000000,
            'status' => 'posted',
            'created_by' => $this->user->id,
        ]);

        $report = app(GenerateAccountsPayableReport::class)->execute($today, $this->branch->id);

        $this->assertEquals(2, $report['total_invoices']);
        $this->assertEquals(3500000.0, $report['total_unpaid']);

        $byNo = collect($report['invoices'])->keyBy('transaction_no');
        $this->assertEquals(1500000.0, $byNo['PT-AP-PARTIAL-01']['unpaid_amount']);
        $this->assertEquals(1500000.0, $byNo['PT-AP-PARTIAL-01']['paid_amount']);
        $this->assertEquals(2000000.0, $byNo['PT-AP-KONS-01']['unpaid_amount']);
        $this->assertFalse($byNo['PT-AP-KONS-01']['is_overdue']);
        $this->assertEquals('-', $byNo['PT-AP-KONS-01']['due_date']);
    }
}

