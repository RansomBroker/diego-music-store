<?php

namespace App\Actions\CashAdvance;

use App\Helpers\AccountHelper;
use App\Models\Account;
use App\Models\EmployeeCashAdvance;
use App\Models\JournalEntry;
use App\Models\JournalItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ApproveCashAdvance
{
    /**
     * Approve cash advance request, disburse funds, and record GL journal entry.
     *
     * @param int $advanceId
     * @param User|null $approver
     * @param string|null $notes
     * @param int|null $disbursementAccountId
     * @param string|null $disbursedAt
     * @return EmployeeCashAdvance
     */
    public function execute(
        int $advanceId,
        ?User $approver = null,
        ?string $notes = null,
        ?int $disbursementAccountId = null,
        ?string $disbursedAt = null
    ): EmployeeCashAdvance {
        return DB::transaction(function () use ($advanceId, $approver, $notes, $disbursementAccountId, $disbursedAt) {
            $advance = EmployeeCashAdvance::with('employee')->findOrFail($advanceId);

            if ($advance->status !== 'pending') {
                throw new \Exception('Hanya pengajuan kasbon berstatus Pending yang dapat disetujui.');
            }

            // 1. Resolve disbursement account (Kas / Bank)
            if ($disbursementAccountId) {
                $disbursementAccount = Account::find($disbursementAccountId);
            } else {
                $defaultAccId = AccountHelper::resolveAccountId('111101001', 'KAS', 'asset');
                $disbursementAccount = Account::find($defaultAccId);
            }

            if (!$disbursementAccount) {
                throw new \Exception('Rekening Kas/Bank pengeluaran dana kasbon tidak ditemukan.');
            }

            // 2. Resolve Piutang Karyawan account (111301002)
            $receivableAccId = AccountHelper::resolveAccountId('111301002', 'PIUTANG KARYAWAN', 'asset');

            $date = $disbursedAt ?: now()->format('Y-m-d');
            $journalNo = 'JV-CA-' . now()->format('Ymd') . '-' . str_pad((string) rand(1, 9999), 4, '0', STR_PAD_LEFT);
            $employeeName = $advance->employee?->name ?? 'Karyawan';

            // 3. Create posted journal entry
            $journal = JournalEntry::create([
                'branch_id'      => $advance->branch_id,
                'entry_no'       => $journalNo,
                'date'           => $date,
                'description'    => "Pencairan Kasbon #{$advance->advance_number} - {$employeeName}",
                'reference_type' => 'EmployeeCashAdvance',
                'reference_id'   => $advance->id,
                'status'         => 'posted',
                'created_by'     => $approver?->id,
                'posted_at'      => now(),
                'posted_by'      => $approver?->id,
            ]);

            // 4. Debit: 111301002 - PIUTANG KARYAWAN (Asset increases)
            JournalItem::create([
                'journal_entry_id' => $journal->id,
                'account_id'       => $receivableAccId,
                'debit'            => (int) $advance->amount,
                'credit'           => 0,
                'notes'            => "Piutang Kasbon #{$advance->advance_number} - {$employeeName}",
            ]);

            // 5. Credit: Kas / Bank (Asset decreases)
            JournalItem::create([
                'journal_entry_id' => $journal->id,
                'account_id'       => $disbursementAccount->id,
                'debit'            => 0,
                'credit'           => (int) $advance->amount,
                'notes'            => "Pencairan kasbon #{$advance->advance_number} via {$disbursementAccount->name}",
            ]);

            // 6. Update advance record
            $advance->update([
                'status'                  => 'approved',
                'approved_by'             => $approver?->id,
                'approved_at'             => now(),
                'disbursed_at'            => $date,
                'disbursement_account_id' => $disbursementAccount->id,
                'journal_entry_id'        => $journal->id,
                'journal_no'              => $journal->entry_no,
                'remaining_amount'        => $advance->amount - $advance->paid_amount,
                'notes'                   => $notes ?: $advance->notes,
            ]);

            return $advance;
        });
    }
}
