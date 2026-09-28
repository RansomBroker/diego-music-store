<?php

namespace App\Actions\Payroll;

use App\Helpers\AccountHelper;
use App\Models\Account;
use App\Models\JournalEntry;
use App\Models\JournalItem;
use App\Models\Payroll;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ProcessPayrollPayment
{
    /**
     * Mark payroll as paid, deduct employee cash advances, and record GL journal entry.
     *
     * @param int $payrollId
     * @param User|null $user
     * @param int|null $paymentAccountId
     * @param string|null $paidAt
     * @return Payroll
     */
    public function execute(int $payrollId, ?User $user = null, ?int $paymentAccountId = null, ?string $paidAt = null): Payroll
    {
        return DB::transaction(function () use ($payrollId, $user, $paymentAccountId, $paidAt) {
            $payroll = Payroll::with(['items', 'branch'])->findOrFail($payrollId);

            if ($payroll->status === 'paid') {
                throw new \Exception('Payroll periode ini sudah berstatus PAID (Telah Dibayar).');
            }

            // 1. Deduct active cash advances balance
            $totalCashAdvanceDeducted = 0.0;
            foreach ($payroll->items as $item) {
                if (empty($item->deduction_details) || !is_array($item->deduction_details)) {
                    continue;
                }

                foreach ($item->deduction_details as $ded) {
                    if (isset($ded['advance_id']) && isset($ded['installment_amount'])) {
                        $advance = \App\Models\EmployeeCashAdvance::find($ded['advance_id']);
                        if ($advance && $advance->status === 'approved') {
                            $installment = (float) $ded['installment_amount'];
                            $newPaid = $advance->paid_amount + $installment;
                            $newRemaining = max(0.0, $advance->amount - $newPaid);
                            $newStatus = $newRemaining <= 0 ? 'paid_off' : 'approved';

                            $advance->update([
                                'paid_amount'      => $newPaid,
                                'remaining_amount' => $newRemaining,
                                'status'           => $newStatus,
                            ]);

                            $totalCashAdvanceDeducted += $installment;
                        }
                    }
                }
            }

            // 2. Resolve Payment Account (Kas / Bank)
            if ($paymentAccountId) {
                $paymentAccount = Account::find($paymentAccountId);
            } else {
                $bankAccId = AccountHelper::resolveAccountId('111201001', 'BANK BCA', 'asset');
                $paymentAccount = Account::find($bankAccId);
                if (!$paymentAccount) {
                    $cashAccId = AccountHelper::resolveAccountId('111101001', 'KAS', 'asset');
                    $paymentAccount = Account::find($cashAccId);
                }
            }

            if (!$paymentAccount) {
                throw new \Exception('Rekening Kas/Bank pembayaran gaji tidak ditemukan.');
            }

            // 3. Resolve Accounting Accounts
            $salaryExpenseAccId = AccountHelper::resolveAccountId('611101001', 'BEBAN GAJI KARYAWAN', 'expense');
            $receivableAccId = AccountHelper::resolveAccountId('111301002', 'PIUTANG KARYAWAN', 'asset');

            $netDisbursement = (int) round((float) $payroll->total_net_salary);
            $kasbonOffset = (int) round($totalCashAdvanceDeducted);
            $grossExpense = $netDisbursement + $kasbonOffset;

            $date = $paidAt ?: now()->format('Y-m-d');
            $journalNo = 'JV-PAY-' . str_replace('-', '', $payroll->period) . '-' . str_pad((string) rand(1, 9999), 4, '0', STR_PAD_LEFT);

            // 4. Create posted journal entry
            $journal = JournalEntry::create([
                'branch_id'      => $payroll->branch_id,
                'entry_no'       => $journalNo,
                'date'           => $date,
                'description'    => "Pembayaran Payroll Periode {$payroll->period} ({$payroll->payroll_code})",
                'reference_type' => 'Payroll',
                'reference_id'   => $payroll->id,
                'status'         => 'posted',
                'created_by'     => $user?->id,
                'posted_at'      => now(),
                'posted_by'      => $user?->id,
            ]);

            // 5. Debit: 611101001 - BEBAN GAJI KARYAWAN (Gross cost)
            JournalItem::create([
                'journal_entry_id' => $journal->id,
                'account_id'       => $salaryExpenseAccId,
                'debit'            => $grossExpense,
                'credit'           => 0,
                'notes'            => "Beban gaji karyawan payroll periode {$payroll->period}",
            ]);

            // 6. Credit: 111301002 - PIUTANG KARYAWAN (Offset kasbon if any)
            if ($kasbonOffset > 0) {
                JournalItem::create([
                    'journal_entry_id' => $journal->id,
                    'account_id'       => $receivableAccId,
                    'debit'            => 0,
                    'credit'           => $kasbonOffset,
                    'notes'            => "Potongan cicilan kasbon payroll periode {$payroll->period}",
                ]);
            }

            // 7. Credit: Kas / Bank (Net salary paid)
            JournalItem::create([
                'journal_entry_id' => $journal->id,
                'account_id'       => $paymentAccount->id,
                'debit'            => 0,
                'credit'           => $netDisbursement,
                'notes'            => "Pengeluaran dana payroll periode {$payroll->period} via {$paymentAccount->name}",
            ]);

            // 8. Update Payroll Record
            $payroll->update([
                'status'             => 'paid',
                'approved_by'        => $user?->id ?: $payroll->approved_by,
                'approved_at'        => $payroll->approved_at ?: now(),
                'paid_at'            => $date,
                'payment_account_id' => $paymentAccount->id,
                'journal_entry_id'   => $journal->id,
                'journal_no'         => $journal->entry_no,
            ]);

            return $payroll;
        });
    }
}
