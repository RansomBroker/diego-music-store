<?php

namespace App\Actions\CashAdvance;

use App\Helpers\AccountHelper;
use App\Models\Account;
use App\Models\EmployeeCashAdvance;
use App\Models\JournalEntry;
use App\Models\JournalItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class SettleCashAdvanceEarly
{
    /**
     * Process early manual repayment of cash advance outside payroll.
     *
     * @param int $advanceId
     * @param float $repaymentAmount
     * @param string $paymentMethod
     * @param string|null $notes
     * @param User|null $user
     * @param int|null $receiptAccountId
     * @return EmployeeCashAdvance
     */
    public function execute(
        int $advanceId,
        float $repaymentAmount,
        string $paymentMethod = 'cash',
        ?string $notes = null,
        ?User $user = null,
        ?int $receiptAccountId = null
    ): EmployeeCashAdvance {
        return DB::transaction(function () use ($advanceId, $repaymentAmount, $paymentMethod, $notes, $user, $receiptAccountId) {
            $advance = EmployeeCashAdvance::with('employee')->findOrFail($advanceId);

            if ($advance->status !== 'approved' && $advance->status !== 'paid_off') {
                throw new \Exception('Hanya kasbon yang disetujui yang dapat dilakukan pelunasan awal.');
            }

            if ($advance->remaining_amount <= 0) {
                throw new \Exception('Kasbon ini sudah lunas.');
            }

            if ($repaymentAmount <= 0) {
                throw new \Exception('Nominal pelunasan harus lebih dari Rp 0.');
            }

            $actualPay = min((float) $advance->remaining_amount, $repaymentAmount);
            $newPaid = (float) $advance->paid_amount + $actualPay;
            $newRemaining = max(0.0, (float) $advance->amount - $newPaid);
            $newStatus = $newRemaining <= 0 ? 'paid_off' : 'approved';

            // 1. Resolve receipt account (Kas / Bank)
            if ($receiptAccountId) {
                $receiptAccount = Account::find($receiptAccountId);
            } elseif ($paymentMethod === 'bank') {
                $bankAccId = AccountHelper::resolveAccountId('111201001', 'BANK BCA', 'asset');
                $receiptAccount = Account::find($bankAccId);
            } else {
                $cashAccId = AccountHelper::resolveAccountId('111101001', 'KAS', 'asset');
                $receiptAccount = Account::find($cashAccId);
            }

            // 2. Resolve Piutang Karyawan account (111301002)
            $receivableAccId = AccountHelper::resolveAccountId('111301002', 'PIUTANG KARYAWAN', 'asset');

            $employeeName = $advance->employee?->name ?? 'Karyawan';
            $journalNo = 'JV-CAR-' . now()->format('Ymd') . '-' . str_pad((string) rand(1, 9999), 4, '0', STR_PAD_LEFT);

            // 3. Create posted journal entry
            $journal = JournalEntry::create([
                'branch_id'      => $advance->branch_id,
                'entry_no'       => $journalNo,
                'date'           => now()->format('Y-m-d'),
                'description'    => "Pelunasan Awal Kasbon #{$advance->advance_number} - {$employeeName}",
                'reference_type' => 'EmployeeCashAdvanceRepayment',
                'reference_id'   => $advance->id,
                'status'         => 'posted',
                'created_by'     => $user?->id,
                'posted_at'      => now(),
                'posted_by'      => $user?->id,
            ]);

            // 4. Debit: Kas / Bank (Cash inflow)
            JournalItem::create([
                'journal_entry_id' => $journal->id,
                'account_id'       => $receiptAccount ? $receiptAccount->id : $receivableAccId,
                'debit'            => (int) $actualPay,
                'credit'           => 0,
                'notes'            => "Penerimaan pelunasan kasbon #{$advance->advance_number} via " . ($receiptAccount->name ?? strtoupper($paymentMethod)),
            ]);

            // 5. Credit: 111301002 - PIUTANG KARYAWAN (Receivable reduced)
            JournalItem::create([
                'journal_entry_id' => $journal->id,
                'account_id'       => $receivableAccId,
                'debit'            => 0,
                'credit'           => (int) $actualPay,
                'notes'            => "Pelunasan piutang kasbon #{$advance->advance_number} - {$employeeName}",
            ]);

            $noteEntry = sprintf(
                " [Pelunasan Manual %s: Rp %s via %s pada %s, Jurnal: %s]",
                $user?->name ?? 'Admin',
                number_format($actualPay, 0, ',', '.'),
                strtoupper($paymentMethod),
                now()->format('Y-m-d H:i'),
                $journal->entry_no
            );

            $advance->update([
                'paid_amount'      => $newPaid,
                'remaining_amount' => $newRemaining,
                'status'           => $newStatus,
                'notes'            => trim(($advance->notes ?? '') . $noteEntry),
            ]);

            return $advance;
        });
    }
}
