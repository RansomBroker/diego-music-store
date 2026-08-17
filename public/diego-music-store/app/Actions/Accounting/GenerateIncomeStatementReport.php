<?php

namespace App\Actions\Accounting;

use App\Models\Account;
use App\Models\Branch;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class GenerateIncomeStatementReport
{
    /**
     * Execute the Multi-Step Income Statement (Laba Rugi) calculation with Toko, Gudang, and YTD comparison.
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

        $ytdFromDate = Carbon::parse($toDate)->startOfYear()->format('Y-m-d');
        $monthLabel  = Carbon::parse($toDate)->translatedFormat('M');

        // 1. Classify branches into Toko vs Gudang
        $allBranches = Branch::all();
        $gudangBranchIds = [];
        foreach ($allBranches as $b) {
            $bName = strtolower($b->name . ' ' . $b->store_name);
            if (str_contains($bName, 'gudang') || str_contains($bName, 'warehouse')) {
                $gudangBranchIds[] = $b->id;
            }
        }

        // 2. Fetch sums for Current Period [fromDate, toDate] grouped by account_id and branch_id
        $currentQuery = DB::table('journal_items')
            ->join('journal_entries', 'journal_items.journal_entry_id', '=', 'journal_entries.id')
            ->where('journal_entries.status', 'posted')
            ->whereDate('journal_entries.date', '>=', $fromDate)
            ->whereDate('journal_entries.date', '<=', $toDate);

        if ($branchId) {
            $currentQuery->where('journal_entries.branch_id', $branchId);
        }

        $currentSums = $currentQuery
            ->select(
                'journal_items.account_id',
                'journal_entries.branch_id',
                DB::raw('SUM(journal_items.debit) as total_debit'),
                DB::raw('SUM(journal_items.credit) as total_credit')
            )
            ->groupBy('journal_items.account_id', 'journal_entries.branch_id')
            ->get();

        // 3. Fetch sums for YTD Period [ytdFromDate, toDate] grouped by account_id and branch_id
        $ytdQuery = DB::table('journal_items')
            ->join('journal_entries', 'journal_items.journal_entry_id', '=', 'journal_entries.id')
            ->where('journal_entries.status', 'posted')
            ->whereDate('journal_entries.date', '>=', $ytdFromDate)
            ->whereDate('journal_entries.date', '<=', $toDate);

        if ($branchId) {
            $ytdQuery->where('journal_entries.branch_id', $branchId);
        }

        $ytdSums = $ytdQuery
            ->select(
                'journal_items.account_id',
                'journal_entries.branch_id',
                DB::raw('SUM(journal_items.debit) as total_debit'),
                DB::raw('SUM(journal_items.credit) as total_credit')
            )
            ->groupBy('journal_items.account_id', 'journal_entries.branch_id')
            ->get();

        // 4. Fetch revenue (4, 7) and expense/COGS (5, 6, 8) accounts
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

        // Helper to calculate balance array [toko, gudang, total] for an account from sums collection
        $calcBalances = function (Account $acc, $sumsCollection) use ($gudangBranchIds): array {
            $toko = 0.0;
            $gudang = 0.0;

            $class = strtolower($acc->classification);
            $code = strtolower($acc->code);
            $isRevenue = ($class === 'revenue' || str_starts_with($code, '4') || str_starts_with($code, '7'));

            $rows = $sumsCollection->where('account_id', $acc->id);
            foreach ($rows as $row) {
                $debits = (float) $row->total_debit;
                $credits = (float) $row->total_credit;
                $bal = $isRevenue ? ($credits - $debits) : ($debits - $credits);

                if (in_array($row->branch_id, $gudangBranchIds)) {
                    $gudang += $bal;
                } else {
                    $toko += $bal;
                }
            }

            return [
                'toko'   => $toko,
                'gudang' => $gudang,
                'total'  => $toko + $gudang,
            ];
        };

        // Calculate raw balances per account
        $rawCurrent = [];
        $rawYtd     = [];
        foreach ($allAccounts as $acc) {
            $rawCurrent[$acc->id] = $calcBalances($acc, $currentSums);
            $rawYtd[$acc->id]     = $calcBalances($acc, $ytdSums);
        }

        // Calculate cumulative balances for header accounts
        $cumCurrent = [];
        $cumYtd     = [];
        foreach ($allAccounts as $acc) {
            if ($acc->is_header) {
                $cToko = 0.0; $cGudang = 0.0;
                $yToko = 0.0; $yGudang = 0.0;

                foreach ($allAccounts as $child) {
                    if (!$child->is_header) {
                        $curr = $child;
                        while ($curr->parent_id) {
                            if ($curr->parent_id == $acc->id) {
                                $cToko += $rawCurrent[$child->id]['toko'];
                                $cGudang += $rawCurrent[$child->id]['gudang'];
                                $yToko += $rawYtd[$child->id]['toko'];
                                $yGudang += $rawYtd[$child->id]['gudang'];
                                break;
                            }
                            if (!isset($accountsById[$curr->parent_id])) break;
                            $curr = $accountsById[$curr->parent_id];
                        }
                    }
                }

                $cumCurrent[$acc->id] = ['toko' => $cToko, 'gudang' => $cGudang, 'total' => $cToko + $cGudang];
                $cumYtd[$acc->id]     = ['toko' => $yToko, 'gudang' => $yGudang, 'total' => $yToko + $yGudang];
            } else {
                $cumCurrent[$acc->id] = $rawCurrent[$acc->id];
                $cumYtd[$acc->id]     = $rawYtd[$acc->id];
            }
        }

        // Categorize into sections
        $revenueItems          = [];
        $cogsItems             = [];
        $operatingExpenseItems = [];
        $otherRevenueItems     = [];
        $otherExpenseItems     = [];

        foreach ($allAccounts as $acc) {
            $level = $getAccountLevel($acc);

            $itemData = [
                'id'             => $acc->id,
                'code'           => $acc->code,
                'name'           => $acc->name,
                'is_header'      => $acc->is_header,
                'level'          => $level,
                'parent_id'      => $acc->parent_id,
                'parent_code'    => $acc->parent?->code,
                'balance'        => $cumCurrent[$acc->id]['total'],
                'balance_toko'   => $cumCurrent[$acc->id]['toko'],
                'balance_gudang' => $cumCurrent[$acc->id]['gudang'],
                'ytd_total'      => $cumYtd[$acc->id]['total'],
                'ytd_toko'       => $cumYtd[$acc->id]['toko'],
                'ytd_gudang'     => $cumYtd[$acc->id]['gudang'],
            ];

            if ($viewType === 'summary' && !$acc->is_header) {
                continue;
            }

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

        // Section Totals calculation from raw non-header balances
        $sumGroup = function (string $prefix) use ($allAccounts, $rawCurrent, $rawYtd) {
            $cToko = 0.0; $cGudang = 0.0;
            $yToko = 0.0; $yGudang = 0.0;

            foreach ($allAccounts as $acc) {
                if (!$acc->is_header) {
                    $code = strtolower($acc->code);
                    $class = strtolower($acc->classification);
                    $match = false;

                    if ($prefix === '4' && (str_starts_with($code, '4') || $class === 'revenue')) $match = true;
                    elseif ($prefix === '5' && str_starts_with($code, '5')) $match = true;
                    elseif ($prefix === '6' && (str_starts_with($code, '6') || ($class !== 'revenue' && !str_starts_with($code, '4') && !str_starts_with($code, '5') && !str_starts_with($code, '7') && !str_starts_with($code, '8')))) $match = true;
                    elseif ($prefix === '7' && str_starts_with($code, '7')) $match = true;
                    elseif ($prefix === '8' && str_starts_with($code, '8')) $match = true;

                    if ($match) {
                        $cToko += $rawCurrent[$acc->id]['toko'];
                        $cGudang += $rawCurrent[$acc->id]['gudang'];
                        $yToko += $rawYtd[$acc->id]['toko'];
                        $yGudang += $rawYtd[$acc->id]['gudang'];
                    }
                }
            }

            return [
                'toko'       => $cToko,
                'gudang'     => $cGudang,
                'total'      => $cToko + $cGudang,
                'ytd_toko'   => $yToko,
                'ytd_gudang' => $yGudang,
                'ytd_total'  => $yToko + $yGudang,
            ];
        };

        $revenueTotal   = $sumGroup('4');
        $cogsTotal      = $sumGroup('5');
        $opExpenseTotal = $sumGroup('6');
        $othRevTotal    = $sumGroup('7');
        $othExpTotal    = $sumGroup('8');

        $calcDiff = function (array $a, array $b) {
            return [
                'toko'       => $a['toko'] - $b['toko'],
                'gudang'     => $a['gudang'] - $b['gudang'],
                'total'      => $a['total'] - $b['total'],
                'ytd_toko'   => $a['ytd_toko'] - $b['ytd_toko'],
                'ytd_gudang' => $a['ytd_gudang'] - $b['ytd_gudang'],
                'ytd_total'  => $a['ytd_total'] - $b['ytd_total'],
            ];
        };

        $grossProfit     = $calcDiff($revenueTotal, $cogsTotal);
        $operatingIncome = $calcDiff($grossProfit, $opExpenseTotal);
        $netOther        = $calcDiff($othRevTotal, $othExpTotal);
        $netIncome       = [
            'toko'       => $operatingIncome['toko'] + $netOther['toko'],
            'gudang'     => $operatingIncome['gudang'] + $netOther['gudang'],
            'total'      => $operatingIncome['total'] + $netOther['total'],
            'ytd_toko'   => $operatingIncome['ytd_toko'] + $netOther['ytd_toko'],
            'ytd_gudang' => $operatingIncome['ytd_gudang'] + $netOther['ytd_gudang'],
            'ytd_total'  => $operatingIncome['ytd_total'] + $netOther['ytd_total'],
        ];

        return [
            'from_date'               => $fromDate,
            'to_date'                 => $toDate,
            'branch_id'               => $branchId,
            'branch_name'             => $branch ? $branch->name : 'Semua Cabang',
            'view_type'               => $viewType,
            'account_level'           => $accountLevel,
            'period_month_label'      => $monthLabel,

            'revenue'                 => array_merge(['items' => $revenueItems], $revenueTotal),
            'cogs'                    => array_merge(['items' => $cogsItems], $cogsTotal),
            'gross_profit'            => $grossProfit['total'],
            'gross_profit_details'    => $grossProfit,

            'operating_expenses'      => array_merge(['items' => $operatingExpenseItems], $opExpenseTotal),
            'operating_income'        => $operatingIncome['total'],
            'operating_income_details'=> $operatingIncome,

            'other_revenue'           => array_merge(['items' => $otherRevenueItems], $othRevTotal),
            'other_expenses'          => array_merge(['items' => $otherExpenseItems], $othExpTotal),
            'net_other'               => $netOther['total'],
            'net_other_details'       => $netOther,

            'net_income'              => $netIncome['total'],
            'net_income_details'      => $netIncome,
            'is_profit'               => $netIncome['total'] >= 0,
        ];
    }
}
