<?php

namespace App\Actions\Accounting;

use App\Models\Account;
use App\Models\Branch;
use Illuminate\Support\Facades\DB;

class GenerateTrialBalanceReport
{
    /**
     * Execute 6-Column Trial Balance (Neraca Saldo) calculation.
     *
     * @param  string|null  $fromDate
     * @param  string|null  $toDate
     * @param  int|null  $branchId
     * @param  bool  $hideZeroBalances
     * @param  string|int  $accountLevel ('all' | 1 | 2 | 3)
     * @return array
     */
    public function execute(
        ?string $fromDate = null,
        ?string $toDate = null,
        ?int $branchId = null,
        bool $hideZeroBalances = true,
        string|int $accountLevel = 'all'
    ): array {
        $fromDate = $fromDate ?: now()->startOfMonth()->format('Y-m-d');
        $toDate   = $toDate ?: now()->format('Y-m-d');
        $branch   = $branchId ? Branch::find($branchId) : null;

        // 1. Prior sums before $fromDate
        $priorQuery = DB::table('journal_items')
            ->join('journal_entries', 'journal_items.journal_entry_id', '=', 'journal_entries.id')
            ->where('journal_entries.status', 'posted')
            ->whereDate('journal_entries.date', '<', $fromDate);

        if ($branchId) {
            $priorQuery->where('journal_entries.branch_id', $branchId);
        }

        $priorSums = $priorQuery
            ->select(
                'journal_items.account_id',
                DB::raw('SUM(journal_items.debit) as total_debit'),
                DB::raw('SUM(journal_items.credit) as total_credit')
            )
            ->groupBy('journal_items.account_id')
            ->get()
            ->keyBy('account_id');

        // 2. Period sums within [$fromDate, $toDate]
        $periodQuery = DB::table('journal_items')
            ->join('journal_entries', 'journal_items.journal_entry_id', '=', 'journal_entries.id')
            ->where('journal_entries.status', 'posted')
            ->whereDate('journal_entries.date', '>=', $fromDate)
            ->whereDate('journal_entries.date', '<=', $toDate);

        if ($branchId) {
            $periodQuery->where('journal_entries.branch_id', $branchId);
        }

        $periodSums = $periodQuery
            ->select(
                'journal_items.account_id',
                DB::raw('SUM(journal_items.debit) as total_debit'),
                DB::raw('SUM(journal_items.credit) as total_credit')
            )
            ->groupBy('journal_items.account_id')
            ->get()
            ->keyBy('account_id');

        // 3. Fetch active accounts
        $allAccounts = Account::with('parent')
            ->where('is_active', true)
            ->orderBy('code')
            ->get();

        $accountsById = Account::where('is_active', true)->get()->keyBy('id');

        $getAccountLevel = function (Account $account) use ($accountsById): int {
            $level = 1;
            $current = $account;
            while ($current->parent_id && isset($accountsById[$current->parent_id])) {
                $level++;
                $current = $accountsById[$current->parent_id];
                if ($level > 10) break;
            }
            return $level;
        };

        $items = [];
        $totalBeginningDebit = 0.0;
        $totalBeginningCredit = 0.0;
        $totalPeriodDebit = 0.0;
        $totalPeriodCredit = 0.0;
        $totalEndingDebit = 0.0;
        $totalEndingCredit = 0.0;

        foreach ($allAccounts as $acc) {
            $level = $getAccountLevel($acc);

            // Account level filtering
            if ($accountLevel !== 'all') {
                $maxLvl = (int) $accountLevel;
                if ($level > $maxLvl) {
                    continue;
                }
            }

            $pSums = $priorSums->get($acc->id);
            $pDeb = $pSums ? (float) $pSums->total_debit : 0.0;
            $pCrd = $pSums ? (float) $pSums->total_credit : 0.0;

            $netPrior = $pDeb - $pCrd;
            $begDebit  = $netPrior > 0 ? $netPrior : 0.0;
            $begCredit = $netPrior < 0 ? abs($netPrior) : 0.0;

            $mSums = $periodSums->get($acc->id);
            $perDebit  = $mSums ? (float) $mSums->total_debit : 0.0;
            $perCredit = $mSums ? (float) $mSums->total_credit : 0.0;

            $netEnding = ($pDeb + $perDebit) - ($pCrd + $perCredit);
            $endDebit  = $netEnding > 0 ? $netEnding : 0.0;
            $endCredit = $netEnding < 0 ? abs($netEnding) : 0.0;

            // Zero balance filtering
            if ($hideZeroBalances && !$acc->is_header) {
                if ($begDebit == 0 && $begCredit == 0 && $perDebit == 0 && $perCredit == 0 && $endDebit == 0 && $endCredit == 0) {
                    continue;
                }
            }

            if (!$acc->is_header) {
                $totalBeginningDebit += $begDebit;
                $totalBeginningCredit += $begCredit;
                $totalPeriodDebit += $perDebit;
                $totalPeriodCredit += $perCredit;
                $totalEndingDebit += $endDebit;
                $totalEndingCredit += $endCredit;
            }

            $items[] = [
                'id'               => $acc->id,
                'code'             => $acc->code,
                'name'             => $acc->name,
                'is_header'        => $acc->is_header,
                'level'            => $level,
                'classification'   => $acc->classification,
                'beginning_debit'  => $begDebit,
                'beginning_credit' => $begCredit,
                'period_debit'     => $perDebit,
                'period_credit'    => $perCredit,
                'ending_debit'     => $endDebit,
                'ending_credit'    => $endCredit,
            ];
        }

        $difference = abs($totalEndingDebit - $totalEndingCredit);
        $isBalanced = $difference < 0.01;

        return [
            'from_date'              => $fromDate,
            'to_date'                => $toDate,
            'branch_id'              => $branchId,
            'branch_name'            => $branch ? $branch->name : 'Semua Cabang',
            'hide_zero_balances'     => $hideZeroBalances,
            'account_level'          => $accountLevel,

            'items'                  => $items,
            'total_beginning_debit'  => $totalBeginningDebit,
            'total_beginning_credit' => $totalBeginningCredit,
            'total_period_debit'     => $totalPeriodDebit,
            'total_period_credit'    => $totalPeriodCredit,
            'total_ending_debit'     => $totalEndingDebit,
            'total_ending_credit'    => $totalEndingCredit,

            'difference'             => $difference,
            'is_balanced'            => $isBalanced,
        ];
    }
}
