<?php

namespace App\Actions\Accounting;

use App\Models\Account;
use App\Models\Branch;
use App\Models\JournalItem;
use Illuminate\Support\Carbon;

class GenerateCashBookReport
{
    /**
     * Execute Cash Book (Laporan Kas & Bank) calculation.
     *
     * @param  string|null  $fromDate
     * @param  string|null  $toDate
     * @param  int|null  $branchId
     * @param  int|null  $accountId
     * @param  string  $typeFilter ('all', 'inflow', 'outflow')
     * @param  string  $mode ('running_balance', 'summary_category')
     * @param  string|null  $search
     * @return array
     */
    public function execute(
        ?string $fromDate = null,
        ?string $toDate = null,
        ?int $branchId = null,
        ?int $accountId = null,
        string $typeFilter = 'all',
        string $mode = 'running_balance',
        ?string $search = null
    ): array {
        $fromDate = $fromDate ?: now()->startOfMonth()->format('Y-m-d');
        $toDate   = $toDate ?: now()->format('Y-m-d');
        $branch   = $branchId ? Branch::find($branchId) : null;
        $account  = $accountId ? Account::find($accountId) : null;

        // Cash & Bank accounts (Assets, Code 1-1%)
        $cashAccountsQuery = Account::where('is_active', true)
            ->where('is_header', false)
            ->where(function ($q) {
                $q->where('classification', 'asset')
                  ->where('code', 'LIKE', '1-1%');
            });

        if ($accountId) {
            $cashAccountIds = [$accountId];
        } else {
            $cashAccountIds = $cashAccountsQuery->pluck('id')->toArray();
        }

        // 1. Initial Balance before $fromDate
        $initialBalanceQuery = JournalItem::query()
            ->whereIn('account_id', $cashAccountIds)
            ->whereHas('journalEntry', function ($q) use ($fromDate, $branchId) {
                $q->where('status', 'posted')
                  ->whereDate('date', '<', $fromDate);
                if ($branchId) {
                    $q->where('branch_id', $branchId);
                }
            });

        $initialBalance = (float) ($initialBalanceQuery->sum('debit') - $initialBalanceQuery->sum('credit'));

        // 2. Fetch Transactions within Date Range
        $query = JournalItem::with(['account', 'journalEntry.branch', 'journalEntry.items.account'])
            ->whereIn('account_id', $cashAccountIds)
            ->whereHas('journalEntry', function ($q) use ($fromDate, $toDate, $branchId, $search) {
                $q->where('status', 'posted')
                  ->whereDate('date', '>=', $fromDate)
                  ->whereDate('date', '<=', $toDate);

                if ($branchId) {
                    $q->where('branch_id', $branchId);
                }

                if ($search) {
                    $s = trim($search);
                    $q->where(function ($sq) use ($s) {
                        $sq->where('entry_no', 'LIKE', "%{$s}%")
                           ->where('description', 'LIKE', "%{$s}%");
                    });
                }
            });

        $items = $query->get()->sortBy(function ($item) {
            return $item->journalEntry->date->format('Y-m-d') . '_' . sprintf('%09d', $item->journal_entry_id) . '_' . sprintf('%09d', $item->id);
        });

        $runningBalance = $initialBalance;
        $processedRows  = [];
        $categorySummaries = [];

        $totalInflow  = 0.0;
        $totalOutflow = 0.0;

        foreach ($items as $item) {
            $entry = $item->journalEntry;
            if (!$entry) {
                continue;
            }

            $inflow  = (float) $item->debit;
            $outflow = (float) $item->credit;

            // Apply type filter
            if ($typeFilter === 'inflow' && $inflow <= 0) {
                continue;
            }
            if ($typeFilter === 'outflow' && $outflow <= 0) {
                continue;
            }

            $totalInflow  += $inflow;
            $totalOutflow += $outflow;
            $runningBalance += ($inflow - $outflow);

            // Determine Opposing Account for Category Summary
            $opposingAccounts = [];
            foreach ($entry->items as $otherItem) {
                if (!in_array($otherItem->account_id, $cashAccountIds) && $otherItem->account) {
                    $opposingAccounts[] = $otherItem->account->name;
                }
            }

            $categoryName = !empty($opposingAccounts) ? implode(', ', array_unique($opposingAccounts)) : ($inflow > 0 ? 'Penerimaan Lainnya' : 'Pengeluaran Lainnya');

            $processedRows[] = [
                'id'              => $item->id,
                'entry_no'        => $entry->entry_no,
                'date'            => $entry->date ? $entry->date->format('Y-m-d') : '-',
                'account_name'    => $item->account ? "{$item->account->code} - {$item->account->name}" : 'Kas/Bank',
                'description'     => $entry->description ?: ($item->memo ?: '-'),
                'opposing_account'=> $categoryName,
                'inflow'          => $inflow,
                'outflow'         => $outflow,
                'running_balance' => $runningBalance,
            ];

            // Accumulate Category Summaries
            if (!isset($categorySummaries[$categoryName])) {
                $categorySummaries[$categoryName] = [
                    'category_name' => $categoryName,
                    'inflow'        => 0.0,
                    'outflow'       => 0.0,
                    'net_amount'    => 0.0,
                ];
            }

            $categorySummaries[$categoryName]['inflow']  += $inflow;
            $categorySummaries[$categoryName]['outflow'] += $outflow;
            $categorySummaries[$categoryName]['net_amount'] += ($inflow - $outflow);
        }

        // Sort Category Summaries
        usort($categorySummaries, function ($a, $b) {
            return strcmp($a['category_name'], $b['category_name']);
        });

        $endingBalance = $initialBalance + $totalInflow - $totalOutflow;

        return [
            'from_date'          => $fromDate,
            'to_date'            => $toDate,
            'branch_id'          => $branchId,
            'branch_name'        => $branch ? $branch->name : 'Semua Cabang',
            'account_id'         => $accountId,
            'account_name'       => $account ? "{$account->code} - {$account->name}" : 'Semua Akun Kas & Bank',
            'type_filter'        => $typeFilter,
            'mode'               => $mode,
            'search'             => $search,
            'initial_balance'    => $initialBalance,
            'total_inflow'       => $totalInflow,
            'total_outflow'      => $totalOutflow,
            'ending_balance'     => $endingBalance,
            'rows'               => $processedRows,
            'categories'         => array_values($categorySummaries),
            'transaction_count'  => count($processedRows),
        ];
    }
}
