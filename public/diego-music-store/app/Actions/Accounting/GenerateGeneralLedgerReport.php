<?php

namespace App\Actions\Accounting;

use App\Models\Account;
use App\Models\Branch;
use Illuminate\Support\Facades\DB;

class GenerateGeneralLedgerReport
{
    /**
     * Execute the General Ledger (Buku Besar) calculation.
     *
     * @param  string|null  $fromDate
     * @param  string|null  $toDate
     * @param  int|null  $accountId
     * @param  int|null  $branchId
     * @return array
     */
    public function execute(?string $fromDate = null, ?string $toDate = null, ?int $accountId = null, ?int $branchId = null): array
    {
        $fromDate = $fromDate ?: now()->startOfMonth()->format('Y-m-d');
        $toDate   = $toDate ?: now()->format('Y-m-d');
        $branch   = $branchId ? Branch::find($branchId) : null;

        // Fetch target accounts (if accountId is set, fetch single account; else all active non-header accounts)
        $accountQuery = Account::where('is_active', true)->orderBy('code');
        if ($accountId) {
            $accountQuery->where('id', $accountId);
        } else {
            $accountQuery->where('is_header', false);
        }
        $accounts = $accountQuery->get();

        $ledgerReports = [];
        $grandTotalDebit = 0.0;
        $grandTotalCredit = 0.0;

        foreach ($accounts as $account) {
            $classification = strtolower($account->classification);
            $code = strtolower($account->code);
            $isDebitNormal = ($classification === 'asset' || str_starts_with($code, '1') || $classification === 'expense' || str_starts_with($code, '5') || str_starts_with($code, '6') || str_starts_with($code, '8'));

            // 1. Calculate Beginning Balance (Saldo Awal) before $fromDate
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

            if ($isDebitNormal) {
                $beginningBalance = $priorDebit - $priorCredit;
            } else {
                $beginningBalance = $priorCredit - $priorDebit;
            }

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

            $periodItems = $periodQuery->select(
                'journal_entries.entry_no',
                'journal_entries.date',
                'journal_entries.description',
                'journal_items.debit',
                'journal_items.credit'
            )
            ->orderBy('journal_entries.date', 'asc')
            ->orderBy('journal_entries.id', 'asc')
            ->get();

            $runningBalance = $beginningBalance;
            $transactions = [];
            $periodDebit = 0.0;
            $periodCredit = 0.0;

            foreach ($periodItems as $tx) {
                $debit = (float) $tx->debit;
                $credit = (float) $tx->credit;

                $periodDebit += $debit;
                $periodCredit += $credit;

                if ($isDebitNormal) {
                    $runningBalance += ($debit - $credit);
                } else {
                    $runningBalance += ($credit - $debit);
                }

                $transactions[] = [
                    'entry_no'        => $tx->entry_no,
                    'date'            => $tx->date,
                    'description'     => $tx->description,
                    'debit'           => $debit,
                    'credit'          => $credit,
                    'running_balance' => $runningBalance,
                ];
            }

            $endingBalance = $runningBalance;
            $grandTotalDebit += $periodDebit;
            $grandTotalCredit += $periodCredit;

            $ledgerReports[] = [
                'account_id'        => $account->id,
                'account_code'      => $account->code,
                'account_name'      => $account->name,
                'classification'    => $account->classification,
                'is_debit_normal'   => $isDebitNormal,
                'beginning_balance' => $beginningBalance,
                'transactions'      => $transactions,
                'total_debit'       => $periodDebit,
                'total_credit'      => $periodCredit,
                'ending_balance'    => $endingBalance,
            ];
        }

        return [
            'from_date'          => $fromDate,
            'to_date'            => $toDate,
            'account_id'         => $accountId,
            'selected_account'   => $accountId ? Account::find($accountId) : null,
            'branch_id'          => $branchId,
            'branch_name'        => $branch ? $branch->name : 'Semua Cabang',
            'ledgers'            => $ledgerReports,
            'grand_total_debit'  => $grandTotalDebit,
            'grand_total_credit' => $grandTotalCredit,
        ];
    }
}
