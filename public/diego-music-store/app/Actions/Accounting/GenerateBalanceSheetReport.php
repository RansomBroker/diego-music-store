<?php

namespace App\Actions\Accounting;

use App\Helpers\AccountHelper;
use App\Helpers\FinancialReportHelper;
use App\Models\Account;
use App\Models\Branch;
use Illuminate\Support\Facades\DB;

class GenerateBalanceSheetReport
{
    /**
     * Execute the Balance Sheet (Neraca) calculation following standard accounting (Skontro).
     *
     * @param  string|null  $asOfDate
     * @param  int|null  $branchId
     * @param  string  $viewType ('summary' | 'detail')
     * @param  string|int  $accountLevel ('all' | 1 | 2 | 3)
     * @return array
     */
    public function execute(?string $asOfDate = null, ?int $branchId = null, string $viewType = 'detail', string|int $accountLevel = 'all'): array
    {
        $asOfDate = $asOfDate ?: now()->format('Y-m-d');
        $branch = $branchId ? Branch::find($branchId) : null;

        // 1. Fetch sums of debits and credits from posted journal items up to asOfDate
        $journalQuery = DB::table('journal_items')
            ->join('journal_entries', 'journal_items.journal_entry_id', '=', 'journal_entries.id')
            ->where('journal_entries.status', 'posted')
            ->whereDate('journal_entries.date', '<=', $asOfDate);

        if ($branchId) {
            $journalQuery->where('journal_entries.branch_id', $branchId);
        }

        $rawJournalSums = $journalQuery
            ->select(
                'journal_items.account_id',
                DB::raw('SUM(journal_items.debit) as total_debit'),
                DB::raw('SUM(journal_items.credit) as total_credit')
            )
            ->groupBy('journal_items.account_id')
            ->get();

        // 2. Fetch canonical active accounts (1 row per code across consolidated/single branch)
        $resolved = FinancialReportHelper::resolveConsolidatedAccounts($branchId);
        $allAccounts = $resolved['accounts'];
        $idToCanonicalId = $resolved['id_to_canonical_id'];
        $parentCodeMap = $resolved['parent_code_map'];
        $levelsById = $resolved['levels_by_id'];

        $journalSums = FinancialReportHelper::consolidateAccountSums($rawJournalSums, $idToCanonicalId);

        // 3. Helper to calculate individual detail account balance
        $calculateAccountBalance = function (Account $account) use ($journalSums): float {
            $sums = $journalSums->get($account->id);
            $debits = $sums ? (float) $sums->total_debit : 0.0;
            $credits = $sums ? (float) $sums->total_credit : 0.0;

            // Asset (1), Cost/COGS (5), Expense (6, 8) normal balance is Debit; Revenue/Liability/Equity is Credit
            if (AccountHelper::isGroupDebitNormal($account)) {
                return $debits - $credits;
            }

            return $credits - $debits;
        };

        // Calculate raw balances first
        $rawBalances = [];
        foreach ($allAccounts as $acc) {
            $rawBalances[$acc->id] = $calculateAccountBalance($acc);
        }

        // Calculate cumulative balances for header accounts (sum of non-header descendants)
        $cumulativeBalances = [];
        foreach ($allAccounts as $acc) {
            if ($acc->is_header) {
                $descendantSum = 0.0;
                foreach ($allAccounts as $child) {
                    if (!$child->is_header && FinancialReportHelper::isDescendantOfCode((string) $child->code, (string) $acc->code, $parentCodeMap)) {
                        $descendantSum += $rawBalances[$child->id];
                    }
                }
                $cumulativeBalances[$acc->id] = $descendantSum;
            } else {
                $cumulativeBalances[$acc->id] = $rawBalances[$acc->id];
            }
        }

        // 4. Group accounts by standard sub-classifications
        $currentAssets = [];
        $fixedAssets = [];
        $allAssetItems = [];

        $currentLiabilities = [];
        $longTermLiabilities = [];
        $allLiabilityItems = [];

        $equityItems = [];
        $revenueTotal = 0.0;
        $expenseTotal = 0.0;

        $isFixedAssetAccount = function (Account $acc): bool {
            $class = strtolower((string) $acc->classification);
            $code = strtolower((string) $acc->code);
            $subtype = strtolower((string) $acc->account_subtype);

            return str_starts_with($code, '1-2')
                || str_starts_with($code, '1-3')
                || str_starts_with($code, '12')
                || in_array($subtype, ['fixed_asset', 'accumulated_depreciation', 'intangible_asset', 'accumulated_amortization'], true)
                || str_contains($class, 'tetap')
                || str_contains($class, 'fixed');
        };

        $isLongTermLiabilityAccount = function (Account $acc): bool {
            $class = strtolower((string) $acc->classification);
            $code = strtolower((string) $acc->code);
            $subtype = strtolower((string) $acc->account_subtype);

            return str_starts_with($code, '2-2')
                || str_starts_with($code, '2-3')
                || str_starts_with($code, '22')
                || $subtype === 'long_term_liability'
                || str_contains($class, 'panjang')
                || str_contains($class, 'long');
        };

        foreach ($allAccounts as $acc) {
            $level = $levelsById[$acc->id] ?? 1;
            $balance = $cumulativeBalances[$acc->id];

            $itemData = [
                'id'          => $acc->id,
                'code'        => $acc->code,
                'name'        => $acc->name,
                'is_header'   => $acc->is_header,
                'level'       => $level,
                'parent_id'   => $acc->parent_id,
                'parent_code' => $parentCodeMap[$acc->code] ?? $acc->parent?->code,
                'balance'     => $balance,
            ];

            // Filter 1: Mode Ringkas (summary) vs Mode Detail (detail)
            // In Summary mode, only show header accounts (is_header == true)
            if ($viewType === 'summary' && !$acc->is_header) {
                continue;
            }

            // Filter 2: Account Level Filter (1, 2, 3, all)
            if ($accountLevel !== 'all') {
                $maxLvl = (int) $accountLevel;
                if ($level > $maxLvl) {
                    continue;
                }
            }

            $class = strtolower($acc->classification);
            $code = strtolower($acc->code);

            if ($class === 'asset' || str_starts_with($code, '1')) {
                $allAssetItems[] = $itemData;
                if ($isFixedAssetAccount($acc)) {
                    $fixedAssets[] = $itemData;
                } else {
                    $currentAssets[] = $itemData;
                }
            } elseif ($class === 'liability' || str_starts_with($code, '2')) {
                $allLiabilityItems[] = $itemData;
                if ($isLongTermLiabilityAccount($acc)) {
                    $longTermLiabilities[] = $itemData;
                } else {
                    $currentLiabilities[] = $itemData;
                }
            } elseif ($class === 'equity' || str_starts_with($code, '3')) {
                $equityItems[] = $itemData;
            } elseif ($class === 'revenue' || str_starts_with($code, '4') || str_starts_with($code, '7')) {
                if (!$acc->is_header) {
                    $revenueTotal += $rawBalances[$acc->id];
                }
            } elseif ($class === 'expense' || $class === 'cost' || str_starts_with($code, '5') || str_starts_with($code, '6') || str_starts_with($code, '8')) {
                if (!$acc->is_header) {
                    $expenseTotal += $rawBalances[$acc->id];
                }
            }
        }

        // Compute overall totals from raw non-header balances
        $totalCurrentAssets = 0.0;
        $totalFixedAssets = 0.0;
        foreach ($allAccounts as $acc) {
            if (!$acc->is_header) {
                $class = strtolower($acc->classification);
                $code = strtolower($acc->code);
                if ($class === 'asset' || str_starts_with($code, '1')) {
                    if ($isFixedAssetAccount($acc)) {
                        $totalFixedAssets += $rawBalances[$acc->id];
                    } else {
                        $totalCurrentAssets += $rawBalances[$acc->id];
                    }
                }
            }
        }
        $totalAssets = $totalCurrentAssets + $totalFixedAssets;

        $totalCurrentLiabilities = 0.0;
        $totalLongTermLiabilities = 0.0;
        foreach ($allAccounts as $acc) {
            if (!$acc->is_header) {
                $class = strtolower($acc->classification);
                $code = strtolower($acc->code);
                if ($class === 'liability' || str_starts_with($code, '2')) {
                    if ($isLongTermLiabilityAccount($acc)) {
                        $totalLongTermLiabilities += $rawBalances[$acc->id];
                    } else {
                        $totalCurrentLiabilities += $rawBalances[$acc->id];
                    }
                }
            }
        }
        $totalLiabilities = $totalCurrentLiabilities + $totalLongTermLiabilities;

        $totalDetailEquity = 0.0;
        foreach ($allAccounts as $acc) {
            if (!$acc->is_header) {
                $class = strtolower($acc->classification);
                $code = strtolower($acc->code);
                if ($class === 'equity' || str_starts_with($code, '3')) {
                    $totalDetailEquity += $rawBalances[$acc->id];
                }
            }
        }

        // 5. Compute Current Net Income (Laba / Rugi Periode Berjalan)
        $currentNetIncome = $revenueTotal - $expenseTotal;

        // Total Equity includes Current Net Income
        $totalEquity = $totalDetailEquity + $currentNetIncome;
        $totalLiabilitiesAndEquity = $totalLiabilities + $totalEquity;

        $difference = round($totalAssets - $totalLiabilitiesAndEquity, 2);
        $isBalanced = abs($difference) < 0.01;

        return [
            'as_of_date'                   => $asOfDate,
            'branch_id'                    => $branchId,
            'branch_name'                  => $branch ? $branch->name : 'Semua Cabang',
            'view_type'                    => $viewType,
            'account_level'                => $accountLevel,

            // Assets Sub-Classifications
            'assets'                       => [
                'current_assets'        => $currentAssets,
                'total_current_assets'  => $totalCurrentAssets,
                'fixed_assets'          => $fixedAssets,
                'total_fixed_assets'    => $totalFixedAssets,
                'items'                 => $allAssetItems,
                'total'                 => $totalAssets,
            ],

            // Liabilities Sub-Classifications
            'liabilities'                  => [
                'current_liabilities'       => $currentLiabilities,
                'total_current_liabilities' => $totalCurrentLiabilities,
                'long_term_liabilities'     => $longTermLiabilities,
                'total_long_term_liabilities'=> $totalLongTermLiabilities,
                'items'                     => $allLiabilityItems,
                'total'                     => $totalLiabilities,
            ],

            // Equity Sub-Classifications
            'equity'                       => [
                'items'              => $equityItems,
                'current_net_income' => $currentNetIncome,
                'revenue_total'      => $revenueTotal,
                'expense_total'      => $expenseTotal,
                'total'              => $totalEquity,
            ],

            'total_assets'                 => $totalAssets,
            'total_liabilities'            => $totalLiabilities,
            'total_equity'                 => $totalEquity,
            'total_liabilities_and_equity' => $totalLiabilitiesAndEquity,
            'difference'                   => $difference,
            'is_balanced'                  => $isBalanced,
        ];
    }
}
