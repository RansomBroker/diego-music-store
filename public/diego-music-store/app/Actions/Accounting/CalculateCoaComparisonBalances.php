<?php

namespace App\Actions\Accounting;

use App\Helpers\FinancialReportHelper;
use App\Models\Account;
use Illuminate\Support\Facades\DB;

class CalculateCoaComparisonBalances
{
    /**
     * Vibrant and accessible palette for financial charts.
     */
    public const CHART_COLORS = [
        '#3b82f6', // Blue (Aset)
        '#f43f5e', // Rose / Red (Liabilitas)
        '#10b981', // Emerald (Ekuitas)
        '#f59e0b', // Amber (Pendapatan)
        '#8b5cf6', // Violet (Beban)
        '#06b6d4', // Cyan (HPP)
        '#ec4899', // Pink
        '#14b8a6', // Teal
        '#6366f1', // Indigo
        '#84cc16', // Lime
    ];

    /**
     * Execute balance calculation for selected COA accounts.
     *
     * @param  array  $accountCodesOrIds
     * @param  int|null  $branchId
     * @param  string|null  $asOfDate
     * @return array
     */
    public function execute(array $accountCodesOrIds, ?int $branchId = null, ?string $asOfDate = null): array
    {
        if (empty($accountCodesOrIds)) {
            return [
                'items' => [],
                'total_chart_value' => 0.0,
                'ratio' => null,
                'as_of_date' => $asOfDate ?: now()->format('Y-m-d'),
                'branch_id' => $branchId,
            ];
        }

        $asOfDate = $asOfDate ?: now()->format('Y-m-d');

        // 1. Fetch all active accounts
        $allAccounts = Account::where('is_active', true)->get()->keyBy('id');
        $accountsByCode = $allAccounts->keyBy('code');

        // 2. Fetch posted journal item sums
        $journalQuery = DB::table('journal_items')
            ->join('journal_entries', 'journal_items.journal_entry_id', '=', 'journal_entries.id')
            ->where('journal_entries.status', 'posted')
            ->whereDate('journal_entries.date', '<=', $asOfDate);

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

        // 3. Compute raw balances for detail accounts
        $rawBalances = [];
        foreach ($allAccounts as $acc) {
            $sums = $journalSums->get($acc->id);
            $deb = $sums ? (float) $sums->total_debit : 0.0;
            $crd = $sums ? (float) $sums->total_credit : 0.0;

            $classification = strtolower((string) $acc->classification);
            $code = strtolower((string) $acc->code);

            // Asset (1) & Expense (5, 6) normal balance is Debit
            if ($classification === 'asset' || str_starts_with($code, '1') || $classification === 'expense' || str_starts_with($code, '5') || str_starts_with($code, '6') || str_contains($classification, 'beban') || str_contains($classification, 'cost')) {
                $rawBalances[$acc->id] = $deb - $crd;
            } else {
                // Liabilities (2), Equity (3), Revenue (4) normal balance is Credit
                $rawBalances[$acc->id] = $crd - $deb;
            }
        }

        // 4. Compute cumulative balances for header accounts (sum of descendant detail accounts)
        $cumulativeBalances = [];
        foreach ($allAccounts as $acc) {
            if ($acc->is_header) {
                $descendantSum = 0.0;
                foreach ($allAccounts as $candidate) {
                    if (!$candidate->is_header) {
                        $curr = $candidate;
                        while ($curr->parent_id) {
                            if ($curr->parent_id == $acc->id) {
                                $descendantSum += $rawBalances[$candidate->id];
                                break;
                            }
                            if (!isset($allAccounts[$curr->parent_id])) {
                                break;
                            }
                            $curr = $allAccounts[$curr->parent_id];
                        }
                    }
                }
                $cumulativeBalances[$acc->id] = $descendantSum;
            } else {
                $cumulativeBalances[$acc->id] = $rawBalances[$acc->id];
            }
        }

        // 5. Build items for requested accounts in order
        $items = [];
        $colorIndex = 0;

        foreach ($accountCodesOrIds as $target) {
            $targetStr = trim((string) $target);
            $account = $accountsByCode->get($targetStr) ?? $allAccounts->get((int) $targetStr);

            if (!$account) {
                continue;
            }

            // Prevent duplicate entries in comparison
            if (isset($items[$account->id])) {
                continue;
            }

            $rawBalance = $cumulativeBalances[$account->id] ?? 0.0;

            // Fallback for generic inventory account (111401001) to branch-specific inventory account if 0
            if (($account->code === '111401001' || $account->code === '1-1300') && $rawBalance == 0 && $branchId) {
                $branch = \App\Models\Branch::find($branchId);
                if ($branch && $branch->inventory_account_id && isset($cumulativeBalances[$branch->inventory_account_id])) {
                    $rawBalance = $cumulativeBalances[$branch->inventory_account_id];
                }
            }
            $chartVal = abs($rawBalance);
            $color = self::CHART_COLORS[$colorIndex % count(self::CHART_COLORS)];
            $colorIndex++;

            $items[$account->id] = [
                'id' => $account->id,
                'code' => $account->code,
                'name' => $account->name,
                'full_label' => "{$account->code} - {$account->name}",
                'classification' => $account->classification,
                'is_header' => (bool) $account->is_header,
                'normal_balance' => $account->getNormalBalance(),
                'raw_balance' => $rawBalance,
                'chart_value' => $chartVal,
                'formatted_balance' => FinancialReportHelper::formatRupiah($rawBalance),
                'color' => $color,
            ];
        }

        $itemsList = array_values($items);
        $totalChartValue = array_sum(array_column($itemsList, 'chart_value'));

        // 6. Compute percentage share
        foreach ($itemsList as &$item) {
            $item['percentage'] = $totalChartValue > 0
                ? round(($item['chart_value'] / $totalChartValue) * 100, 1)
                : 0.0;
        }
        unset($item);

        // 7. Compute ratio if exactly 2 items are selected
        $ratio = null;
        if (count($itemsList) === 2) {
            $itemA = $itemsList[0];
            $itemB = $itemsList[1];

            // If user selected Aset and Liabilitas (in any order), calculate Liabilitas / Aset (Debt to Asset ratio)
            $isAssetA = str_contains(strtolower($itemA['classification'] . ' ' . $itemA['name']), 'aset') || str_starts_with($itemA['code'], '1');
            $isLiabB = str_contains(strtolower($itemB['classification'] . ' ' . $itemB['name']), 'liab') || str_contains(strtolower($itemB['classification'] . ' ' . $itemB['name']), 'kewajiban') || str_starts_with($itemB['code'], '2');

            $isAssetB = str_contains(strtolower($itemB['classification'] . ' ' . $itemB['name']), 'aset') || str_starts_with($itemB['code'], '1');
            $isLiabA = str_contains(strtolower($itemA['classification'] . ' ' . $itemA['name']), 'liab') || str_contains(strtolower($itemA['classification'] . ' ' . $itemA['name']), 'kewajiban') || str_starts_with($itemA['code'], '2');

            if ($isAssetA && $isLiabB) {
                $numerator = $itemB;
                $denominator = $itemA;
            } elseif ($isAssetB && $isLiabA) {
                $numerator = $itemA;
                $denominator = $itemB;
            } else {
                $numerator = $itemB;
                $denominator = $itemA;
            }

            $denomVal = $denominator['chart_value'];
            $numVal = $numerator['chart_value'];
            $ratioPercent = $denomVal > 0 ? round(($numVal / $denomVal) * 100, 1) : 0.0;

            $ratio = [
                'numerator_name' => $numerator['name'],
                'denominator_name' => $denominator['name'],
                'label' => "Rasio {$numerator['name']} terhadap {$denominator['name']}",
                'percentage' => $ratioPercent,
                'description' => "{$numerator['name']} setara {$ratioPercent}% dari {$denominator['name']}",
            ];
        }

        return [
            'items' => $itemsList,
            'total_chart_value' => $totalChartValue,
            'formatted_total' => FinancialReportHelper::formatRupiah($totalChartValue),
            'ratio' => $ratio,
            'as_of_date' => $asOfDate,
            'branch_id' => $branchId,
        ];
    }
}
