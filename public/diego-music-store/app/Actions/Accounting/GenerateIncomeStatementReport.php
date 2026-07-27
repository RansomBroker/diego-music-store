<?php

namespace App\Actions\Accounting;

use App\Models\Account;
use App\Models\Branch;
use Illuminate\Support\Facades\DB;

class GenerateIncomeStatementReport
{
    /**
     * Execute the Multi-Step Income Statement (Laba Rugi) calculation.
     *
     * @param  string|null  $fromDate
     * @param  string|null  $toDate
     * @param  int|null  $branchId
     * @param  string  $viewType ('summary' | 'detail')
     * @param  string|int  $accountLevel ('all' | 1 | 2 | 3)
     * @return array
     */
    public function execute(?string $fromDate = null, ?string $toDate = null, ?int $branchId = null, string $viewType = 'detail', string|int $accountLevel = 'all'): array
    {
        $fromDate = $fromDate ?: now()->startOfMonth()->format('Y-m-d');
        $toDate   = $toDate ?: now()->format('Y-m-d');
        $branch   = $branchId ? Branch::find($branchId) : null;

        // 1. Fetch sums of debits and credits from posted journal items within [fromDate, toDate]
        $journalQuery = DB::table('journal_items')
            ->join('journal_entries', 'journal_items.journal_entry_id', '=', 'journal_entries.id')
            ->where('journal_entries.status', 'posted')
            ->whereDate('journal_entries.date', '>=', $fromDate)
            ->whereDate('journal_entries.date', '<=', $toDate);

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

        // 2. Fetch revenue (4, 7) and expense/COGS (5, 6, 8) accounts
        $allAccounts = Account::with('parent')
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereIn('classification', ['revenue', 'expense'])
                  ->orWhere('code', 'LIKE', '4%')
                  ->orWhere('code', 'LIKE', '5%')
                  ->orWhere('code', 'LIKE', '6%')
                  ->orWhere('code', 'LIKE', '7%')
                  ->orWhere('code', 'LIKE', '8%');
            })
            ->orderBy('code')
            ->get();

        $accountsById = Account::where('is_active', true)->get()->keyBy('id');

        // Calculate account depth level (1 = Root, 2 = Sub, 3 = Detail)
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

        // 3. Calculate raw account balances within period
        $rawBalances = [];
        foreach ($allAccounts as $acc) {
            $sums = $journalSums->get($acc->id);
            $debits = $sums ? (float) $sums->total_debit : 0.0;
            $credits = $sums ? (float) $sums->total_credit : 0.0;

            $class = strtolower($acc->classification);
            $code = strtolower($acc->code);

            // Revenue (4, 7): normal balance is Credit (credits - debits)
            if ($class === 'revenue' || str_starts_with($code, '4') || str_starts_with($code, '7')) {
                $rawBalances[$acc->id] = $credits - $debits;
            } else {
                // Expense & COGS (5, 6, 8): normal balance is Debit (debits - credits)
                $rawBalances[$acc->id] = $debits - $credits;
            }
        }

        // Calculate cumulative balances for header accounts
        $cumulativeBalances = [];
        foreach ($allAccounts as $acc) {
            if ($acc->is_header) {
                $descendantSum = 0.0;
                foreach ($allAccounts as $child) {
                    if (!$child->is_header) {
                        $curr = $child;
                        while ($curr->parent_id) {
                            if ($curr->parent_id == $acc->id) {
                                $descendantSum += $rawBalances[$child->id];
                                break;
                            }
                            if (!isset($accountsById[$curr->parent_id])) break;
                            $curr = $accountsById[$curr->parent_id];
                        }
                    }
                }
                $cumulativeBalances[$acc->id] = $descendantSum;
            } else {
                $cumulativeBalances[$acc->id] = $rawBalances[$acc->id];
            }
        }

        // 4. Categorize accounts into Multi-Step sections
        $revenueItems          = [];
        $cogsItems             = [];
        $operatingExpenseItems = [];
        $otherRevenueItems     = [];
        $otherExpenseItems     = [];

        foreach ($allAccounts as $acc) {
            $level = $getAccountLevel($acc);
            $balance = $cumulativeBalances[$acc->id];

            $itemData = [
                'id'          => $acc->id,
                'code'        => $acc->code,
                'name'        => $acc->name,
                'is_header'   => $acc->is_header,
                'level'       => $level,
                'parent_id'   => $acc->parent_id,
                'parent_code' => $acc->parent?->code,
                'balance'     => $balance,
            ];

            // Summary view filtering (only headers)
            if ($viewType === 'summary' && !$acc->is_header) {
                continue;
            }

            // Account level filtering
            if ($accountLevel !== 'all') {
                $maxLvl = (int) $accountLevel;
                if ($level > $maxLvl) {
                    continue;
                }
            }

            $class = strtolower($acc->classification);
            $code = strtolower($acc->code);

            if (str_starts_with($code, '4')) {
                $revenueItems[] = $itemData;
            } elseif (str_starts_with($code, '5')) {
                $cogsItems[] = $itemData;
            } elseif (str_starts_with($code, '6')) {
                $operatingExpenseItems[] = $itemData;
            } elseif (str_starts_with($code, '7')) {
                $otherRevenueItems[] = $itemData;
            } elseif (str_starts_with($code, '8')) {
                $otherExpenseItems[] = $itemData;
            } else {
                if ($class === 'revenue') {
                    $revenueItems[] = $itemData;
                } else {
                    $operatingExpenseItems[] = $itemData;
                }
            }
        }

        // 5. Calculate overall Multi-Step totals from raw non-header balances
        $totalRevenue = 0.0;
        $totalCogs = 0.0;
        $totalOperatingExpenses = 0.0;
        $totalOtherRevenue = 0.0;
        $totalOtherExpenses = 0.0;

        foreach ($allAccounts as $acc) {
            if (!$acc->is_header) {
                $code = strtolower($acc->code);
                $class = strtolower($acc->classification);
                $bal = $rawBalances[$acc->id];

                if (str_starts_with($code, '4')) {
                    $totalRevenue += $bal;
                } elseif (str_starts_with($code, '5')) {
                    $totalCogs += $bal;
                } elseif (str_starts_with($code, '6')) {
                    $totalOperatingExpenses += $bal;
                } elseif (str_starts_with($code, '7')) {
                    $totalOtherRevenue += $bal;
                } elseif (str_starts_with($code, '8')) {
                    $totalOtherExpenses += $bal;
                } else {
                    if ($class === 'revenue') {
                        $totalRevenue += $bal;
                    } else {
                        $totalOperatingExpenses += $bal;
                    }
                }
            }
        }

        $grossProfit = $totalRevenue - $totalCogs;
        $operatingIncome = $grossProfit - $totalOperatingExpenses;
        $netOther = $totalOtherRevenue - $totalOtherExpenses;
        $netIncome = $operatingIncome + $netOther;

        return [
            'from_date'               => $fromDate,
            'to_date'                 => $toDate,
            'branch_id'               => $branchId,
            'branch_name'             => $branch ? $branch->name : 'Semua Cabang',
            'view_type'               => $viewType,
            'account_level'           => $accountLevel,

            'revenue'                 => [
                'items' => $revenueItems,
                'total' => $totalRevenue,
            ],
            'cogs'                    => [
                'items' => $cogsItems,
                'total' => $totalCogs,
            ],
            'gross_profit'            => $grossProfit,

            'operating_expenses'      => [
                'items' => $operatingExpenseItems,
                'total' => $totalOperatingExpenses,
            ],
            'operating_income'        => $operatingIncome,

            'other_revenue'           => [
                'items' => $otherRevenueItems,
                'total' => $totalOtherRevenue,
            ],
            'other_expenses'          => [
                'items' => $otherExpenseItems,
                'total' => $totalOtherExpenses,
            ],
            'net_other'               => $netOther,

            'net_income'              => $netIncome,
            'is_profit'               => $netIncome >= 0,
        ];
    }
}
