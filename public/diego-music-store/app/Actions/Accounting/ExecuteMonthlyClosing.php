<?php

namespace App\Actions\Accounting;

use App\Models\Account;
use App\Models\JournalEntry;
use App\Models\JournalItem;
use App\Models\MonthlyClosing;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ExecuteMonthlyClosing
{
    /**
     * Execute Monthly Closing and generate Closing Journal Entry.
     *
     * @param  int  $year
     * @param  int  $month
     * @param  int|null  $branchId
     * @param  int|null  $userId
     * @param  string|null  $notes
     * @return MonthlyClosing
     * @throws Exception
     */
    public function execute(int $year, int $month, ?int $branchId = null, ?int $userId = null, ?string $notes = null): MonthlyClosing
    {
        $periodKey = sprintf('%04d-%02d', $year, $month);

        // Check if period is already closed
        $existing = MonthlyClosing::where('period_key', $periodKey)
            ->where('status', 'closed')
            ->first();

        if ($existing) {
            throw new Exception("Periode keuangan {$periodKey} sudah dalam status DITUTUP (Closed).");
        }

        return DB::transaction(function () use ($year, $month, $periodKey, $branchId, $userId, $notes) {
            $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth()->format('Y-m-d');
            $endDate   = Carbon::createFromDate($year, $month, 1)->endOfMonth()->format('Y-m-d');

            // 1. Fetch posted journal sums for Revenue & Expense accounts
            $journalQuery = DB::table('journal_items')
                ->join('journal_entries', 'journal_items.journal_entry_id', '=', 'journal_entries.id')
                ->where('journal_entries.status', 'posted')
                ->whereDate('journal_entries.date', '>=', $startDate)
                ->whereDate('journal_entries.date', '<=', $endDate);

            if ($branchId) {
                $journalQuery->where('journal_entries.branch_id', $branchId);
            }

            $journalSums = $journalQuery
                ->select(
                    'journal_items.account_id',
                    DB::raw('SUM(journal_items.debit) as total_debit'),
                    DB::raw('SUM(journal_items.credit) as total_credit')
                )
                ->groupBy('journal_items.account_id')
                ->get()
                ->keyBy('account_id');

            $allAccounts = Account::where('is_active', true)->where('is_header', false)->get();

            // Find Retained Earnings Account (Laba Ditahan: 3-2000 or equity)
            $retainedAccount = Account::where('is_active', true)
                ->where('is_header', false)
                ->where(function ($q) {
                    $q->where('code', '3-2000')
                      ->orWhere('name', 'LIKE', '%laba ditahan%')
                      ->orWhere('name', 'LIKE', '%retained%');
                })
                ->first();

            if (!$retainedAccount) {
                $retainedAccount = Account::where('is_active', true)
                    ->where('is_header', false)
                    ->where('classification', 'equity')
                    ->first();
            }

            if (!$retainedAccount) {
                throw new Exception("Akun Retained Earnings / Laba Ditahan (3-2000) tidak ditemukan pada Bagan Akun.");
            }

            $revenueBalances = [];
            $expenseBalances = [];
            $totalRevenue = 0.0;
            $totalExpense = 0.0;

            foreach ($allAccounts as $acc) {
                $sums = $journalSums->get($acc->id);
                if (!$sums) continue;

                $debits = (float) $sums->total_debit;
                $credits = (float) $sums->total_credit;

                $code = strtolower($acc->code);
                $class = strtolower($acc->classification);

                if (str_starts_with($code, '4') || str_starts_with($code, '7') || $class === 'revenue') {
                    $bal = $credits - $debits;
                    if (abs($bal) > 0.01) {
                        $revenueBalances[] = ['account' => $acc, 'balance' => $bal];
                        $totalRevenue += $bal;
                    }
                } elseif (str_starts_with($code, '5') || str_starts_with($code, '6') || str_starts_with($code, '8') || $class === 'expense') {
                    $bal = $debits - $credits;
                    if (abs($bal) > 0.01) {
                        $expenseBalances[] = ['account' => $acc, 'balance' => $bal];
                        $totalExpense += $bal;
                    }
                }
            }

            $netIncome = $totalRevenue - $totalExpense;

            // 2. Create Closing Journal Entry
            $entryNo = sprintf('JV-CLOSE-%04d%02d', $year, $month);

            $closingJournal = JournalEntry::create([
                'branch_id'   => $branchId,
                'entry_no'    => $entryNo,
                'date'        => $endDate,
                'description' => "Jurnal Penutup Otomatis Periode {$month}/{$year}",
                'status'      => 'posted',
                'posted_at'   => now(),
                'posted_by'   => $userId,
                'created_by'  => $userId,
            ]);

            // Zero out Revenue accounts (Debit Revenue, Credit Retained Earnings)
            foreach ($revenueBalances as $revItem) {
                $acc = $revItem['account'];
                $bal = $revItem['balance'];
                if ($bal > 0) {
                    JournalItem::create([
                        'journal_entry_id' => $closingJournal->id,
                        'account_id'       => $acc->id,
                        'debit'            => $bal,
                        'credit'           => 0,
                        'notes'            => 'Penutupan Saldo Pendapatan Periode',
                    ]);
                } else {
                    JournalItem::create([
                        'journal_entry_id' => $closingJournal->id,
                        'account_id'       => $acc->id,
                        'debit'            => 0,
                        'credit'           => abs($bal),
                        'notes'            => 'Penutupan Saldo Pendapatan Periode',
                    ]);
                }
            }

            // Zero out Expense & COGS accounts (Credit Expense, Debit Retained Earnings)
            foreach ($expenseBalances as $expItem) {
                $acc = $expItem['account'];
                $bal = $expItem['balance'];
                if ($bal > 0) {
                    JournalItem::create([
                        'journal_entry_id' => $closingJournal->id,
                        'account_id'       => $acc->id,
                        'debit'            => 0,
                        'credit'           => $bal,
                        'notes'            => 'Penutupan Saldo Beban/HPP Periode',
                    ]);
                } else {
                    JournalItem::create([
                        'journal_entry_id' => $closingJournal->id,
                        'account_id'       => $acc->id,
                        'debit'            => abs($bal),
                        'credit'           => 0,
                        'notes'            => 'Penutupan Saldo Beban/HPP Periode',
                    ]);
                }
            }

            // Transfer Net Income / Net Loss to Retained Earnings
            if ($netIncome > 0) {
                // Net Profit => Credit Retained Earnings
                JournalItem::create([
                    'journal_entry_id' => $closingJournal->id,
                    'account_id'       => $retainedAccount->id,
                    'debit'            => 0,
                    'credit'           => $netIncome,
                    'notes'            => 'Alokasi Laba Bersih Periode ke Laba Ditahan',
                ]);
            } elseif ($netIncome < 0) {
                // Net Loss => Debit Retained Earnings
                JournalItem::create([
                    'journal_entry_id' => $closingJournal->id,
                    'account_id'       => $retainedAccount->id,
                    'debit'            => abs($netIncome),
                    'credit'           => 0,
                    'notes'            => 'Alokasi Rugi Bersih Periode ke Laba Ditahan',
                ]);
            }

            // 3. Create or update MonthlyClosing record
            $closing = MonthlyClosing::updateOrCreate(
                ['period_key' => $periodKey],
                [
                    'year'               => $year,
                    'month'              => $month,
                    'branch_id'          => $branchId,
                    'closed_at'          => now(),
                    'closed_by'          => $userId,
                    'closing_journal_id' => $closingJournal->id,
                    'total_revenue'      => $totalRevenue,
                    'total_expense'      => $totalExpense,
                    'net_income'         => $netIncome,
                    'status'             => 'closed',
                    'reopened_at'        => null,
                    'reopened_by'        => null,
                    'notes'              => $notes ?: "Tutup Buku Bulanan Periode {$periodKey}",
                ]
            );

            return $closing;
        });
    }
}
