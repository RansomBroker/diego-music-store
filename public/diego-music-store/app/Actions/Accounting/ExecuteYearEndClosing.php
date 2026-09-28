<?php

namespace App\Actions\Accounting;

use App\Helpers\AccountHelper;
use App\Models\Account;
use App\Models\Branch;
use App\Models\JournalEntry;
use App\Models\JournalItem;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ExecuteYearEndClosing
{
    /**
     * Execute Year-End Closing: Transfer Laba Tahun Berjalan to Laba Ditahan.
     *
     * @param  int  $year
     * @param  int|null  $branchId
     * @param  int|null  $userId
     * @param  string|null  $notes
     * @return JournalEntry|null
     * @throws Exception
     */
    public function execute(int $year, ?int $branchId = null, ?int $userId = null, ?string $notes = null): ?JournalEntry
    {
        return DB::transaction(function () use ($year, $branchId, $userId, $notes) {
            $closingDate = Carbon::createFromDate($year, 12, 31)->format('Y-m-d');
            $entryNo = sprintf('JV-YEAREND-%04d%s', $year, $branchId ? "-B{$branchId}" : '');

            // 1. Prevent duplicate year-end closing entry for same year and branch
            $existing = JournalEntry::where('reference_type', 'YearEndClosing')
                ->where('entry_no', $entryNo)
                ->where('status', 'posted')
                ->first();

            if ($existing) {
                throw new Exception("Tutup buku akhir tahun {$year} sudah pernah dijalankan (Jurnal #{$existing->entry_no}).");
            }

            // 2. Resolve Accounts: Laba Tahun Berjalan (311301001) & Laba Ditahan (311201001)
            $currentYearAcc = AccountHelper::findByCode('311301001')
                ?: Account::where('is_active', true)->where('is_header', false)->where('name', 'LIKE', '%laba tahun berjalan%')->first();

            $retainedAcc = AccountHelper::findByCode('311201001')
                ?: Account::where('is_active', true)->where('is_header', false)->where(function ($q) {
                    $q->where('code', '3-2000')->orWhere('name', 'LIKE', '%laba ditahan%');
                })->first();

            if (!$currentYearAcc) {
                throw new Exception("Akun Laba Tahun Berjalan (311301001) tidak ditemukan pada Bagan Akun.");
            }

            if (!$retainedAcc) {
                throw new Exception("Akun Laba Ditahan (311201001) tidak ditemukan pada Bagan Akun.");
            }

            // 3. Calculate net cumulative balance of Laba Tahun Berjalan up to 31 Dec of $year
            $journalQuery = DB::table('journal_items')
                ->join('journal_entries', 'journal_items.journal_entry_id', '=', 'journal_entries.id')
                ->where('journal_entries.status', 'posted')
                ->where('journal_items.account_id', $currentYearAcc->id)
                ->whereDate('journal_entries.date', '<=', $closingDate);

            if ($branchId) {
                $journalQuery->where('journal_entries.branch_id', $branchId);
            }

            $sums = $journalQuery->select(
                DB::raw('SUM(journal_items.debit) as total_debit'),
                DB::raw('SUM(journal_items.credit) as total_credit')
            )->first();

            $totalDebit = (float) ($sums->total_debit ?? 0);
            $totalCredit = (float) ($sums->total_credit ?? 0);

            // Normal balance for equity is Credit (Credit - Debit)
            $netBalance = $totalCredit - $totalDebit;

            if (abs($netBalance) < 0.01) {
                // No balance to transfer
                return null;
            }

            // 4. Create Year-End Closing Journal Entry
            $journalEntry = JournalEntry::create([
                'branch_id'      => $branchId,
                'entry_no'       => $entryNo,
                'date'           => $closingDate,
                'description'    => $notes ?: "Tutup Buku Akhir Tahun {$year}: Pemindahan Laba Tahun Berjalan ke Laba Ditahan",
                'reference_type' => 'YearEndClosing',
                'status'         => 'posted',
                'posted_at'      => now(),
                'posted_by'      => $userId,
                'created_by'     => $userId,
            ]);

            if ($netBalance > 0) {
                // Profit: Debit Laba Tahun Berjalan (menolkan saldo), Credit Laba Ditahan (menambah ekuitas ditahan)
                JournalItem::create([
                    'journal_entry_id' => $journalEntry->id,
                    'account_id'       => $currentYearAcc->id,
                    'debit'            => $netBalance,
                    'credit'           => 0,
                    'notes'            => "Pemindahan Saldo Laba Tahun {$year} (Tutup Buku Tahunan)",
                ]);

                JournalItem::create([
                    'journal_entry_id' => $journalEntry->id,
                    'account_id'       => $retainedAcc->id,
                    'debit'            => 0,
                    'credit'           => $netBalance,
                    'notes'            => "Penerimaan Laba Bersih Tahun {$year} ke Laba Ditahan",
                ]);
            } else {
                // Loss: Debit Laba Ditahan, Credit Laba Tahun Berjalan (menolkan saldo)
                $absLoss = abs($netBalance);
                JournalItem::create([
                    'journal_entry_id' => $journalEntry->id,
                    'account_id'       => $retainedAcc->id,
                    'debit'            => $absLoss,
                    'credit'           => 0,
                    'notes'            => "Pembebanan Rugi Bersih Tahun {$year} ke Laba Ditahan",
                ]);

                JournalItem::create([
                    'journal_entry_id' => $journalEntry->id,
                    'account_id'       => $currentYearAcc->id,
                    'debit'            => 0,
                    'credit'           => $absLoss,
                    'notes'            => "Penolakan Saldo Rugi Tahun {$year} (Tutup Buku Tahunan)",
                ]);
            }

            return $journalEntry;
        });
    }
}
