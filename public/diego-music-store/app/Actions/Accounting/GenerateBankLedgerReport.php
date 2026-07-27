<?php

namespace App\Actions\Accounting;

use App\Models\Account;
use App\Models\Branch;
use Illuminate\Support\Facades\DB;

class GenerateBankLedgerReport
{
    /**
     * Execute the Bank Ledger (Buku Bank) calculation.
     *
     * @param  string|null  $fromDate
     * @param  string|null  $toDate
     * @param  int|null  $bankAccountId
     * @param  int|null  $branchId
     * @param  string  $mutationType ('all' | 'in' | 'out')
     * @param  string|null  $search
     * @return array
     */
    public function execute(
        ?string $fromDate = null,
        ?string $toDate = null,
        ?int $bankAccountId = null,
        ?int $branchId = null,
        string $mutationType = 'all',
        ?string $search = null
    ): array {
        $fromDate = $fromDate ?: now()->startOfMonth()->format('Y-m-d');
        $toDate   = $toDate ?: now()->format('Y-m-d');
        $branch   = $branchId ? Branch::find($branchId) : null;

        // Fetch bank accounts
        $accountQuery = Account::where('is_active', true);
        if ($bankAccountId) {
            $accountQuery->where('id', $bankAccountId);
        } else {
            $accountQuery->where('is_header', false)
                ->where('classification', 'asset')
                ->where(function ($q) {
                    $q->where('name', 'LIKE', '%bank%')
                      ->orWhere('name', 'LIKE', '%bca%')
                      ->orWhere('name', 'LIKE', '%mandiri%')
                      ->orWhere('name', 'LIKE', '%bni%')
                      ->orWhere('name', 'LIKE', '%bri%')
                      ->orWhere('code', 'LIKE', '1-11%')
                      ->orWhere('code', 'LIKE', '1-12%');
                });
        }

        $bankAccounts = $accountQuery->orderBy('code')->get();

        // Fallback: If no bank accounts matched the search criteria, get asset accounts starting with code 1
        if ($bankAccounts->isEmpty() && !$bankAccountId) {
            $bankAccounts = Account::where('is_active', true)
                ->where('is_header', false)
                ->where('classification', 'asset')
                ->where('code', 'LIKE', '1%')
                ->orderBy('code')
                ->get();
        }

        $bankReports = [];
        $grandTotalIn = 0.0;
        $grandTotalOut = 0.0;

        foreach ($bankAccounts as $account) {
            // 1. Calculate Beginning Balance (Saldo Awal Bank) before $fromDate
            $priorQuery = DB::table('journal_items')
                ->join('journal_entries', 'journal_items.journal_entry_id', '=', 'journal_entries.id')
                ->where('journal_items.account_id', $account->id)
                ->where('journal_entries.status', 'posted')
                ->whereDate('journal_entries.date', '<', $fromDate);

            if ($branchId) {
                $priorQuery->where('journal_entries.branch_id', $branchId);
            }

            $priorSums = $priorQuery->select(
                DB::raw('SUM(journal_items.debit) as total_debit'),
                DB::raw('SUM(journal_items.credit) as total_credit')
            )->first();

            $priorDebit = $priorSums ? (float) $priorSums->total_debit : 0.0;
            $priorCredit = $priorSums ? (float) $priorSums->total_credit : 0.0;

            // Bank is Asset => Debit Normal (debit - credit)
            $beginningBalance = $priorDebit - $priorCredit;

            // 2. Fetch period transactions within [$fromDate, $toDate]
            $periodQuery = DB::table('journal_items')
                ->join('journal_entries', 'journal_items.journal_entry_id', '=', 'journal_entries.id')
                ->where('journal_items.account_id', $account->id)
                ->where('journal_entries.status', 'posted')
                ->whereDate('journal_entries.date', '>=', $fromDate)
                ->whereDate('journal_entries.date', '<=', $toDate);

            if ($branchId) {
                $periodQuery->where('journal_entries.branch_id', $branchId);
            }

            if ($search) {
                $search = trim($search);
                $periodQuery->where(function ($q) use ($search) {
                    $q->where('journal_entries.entry_no', 'LIKE', "%{$search}%")
                      ->orWhere('journal_entries.description', 'LIKE', "%{$search}%")
                      ->orWhere('journal_items.notes', 'LIKE', "%{$search}%");
                });
            }

            if ($mutationType === 'in') {
                $periodQuery->where('journal_items.debit', '>', 0);
            } elseif ($mutationType === 'out') {
                $periodQuery->where('journal_items.credit', '>', 0);
            }

            $periodItems = $periodQuery->select(
                'journal_entries.entry_no',
                'journal_entries.date',
                'journal_entries.description',
                'journal_items.notes',
                'journal_items.debit',
                'journal_items.credit'
            )
            ->orderBy('journal_entries.date', 'asc')
            ->orderBy('journal_entries.id', 'asc')
            ->get();

            $runningBalance = $beginningBalance;
            $transactions = [];
            $totalIn = 0.0;
            $totalOut = 0.0;

            foreach ($periodItems as $tx) {
                $debit = (float) $tx->debit;
                $credit = (float) $tx->credit;

                $totalIn += $debit;
                $totalOut += $credit;

                $runningBalance += ($debit - $credit);

                $transactions[] = [
                    'entry_no'        => $tx->entry_no,
                    'date'            => $tx->date,
                    'description'     => $tx->description ?: $tx->notes,
                    'debit'           => $debit,
                    'credit'          => $credit,
                    'running_balance' => $runningBalance,
                ];
            }

            $endingBalance = $runningBalance;
            $grandTotalIn += $totalIn;
            $grandTotalOut += $totalOut;

            $bankReports[] = [
                'account_id'        => $account->id,
                'account_code'      => $account->code,
                'account_name'      => $account->name,
                'beginning_balance' => $beginningBalance,
                'transactions'      => $transactions,
                'total_in'          => $totalIn,
                'total_out'         => $totalOut,
                'ending_balance'    => $endingBalance,
            ];
        }

        return [
            'from_date'         => $fromDate,
            'to_date'           => $toDate,
            'account_id'        => $bankAccountId,
            'selected_account'  => $bankAccountId ? Account::find($bankAccountId) : null,
            'branch_id'         => $branchId,
            'branch_name'       => $branch ? $branch->name : 'Semua Cabang',
            'mutation_type'     => $mutationType,
            'search'            => $search,
            'ledgers'           => $bankReports,
            'grand_total_in'    => $grandTotalIn,
            'grand_total_out'   => $grandTotalOut,
        ];
    }
}
