<?php

namespace App\Actions\Accounting;

use App\Models\Account;
use App\Models\Branch;
use App\Models\JournalEntry;
use Illuminate\Support\Facades\DB;

class GenerateJournalReport
{
    /**
     * Execute the Journal Report (Laporan Jurnal Umum) calculation.
     *
     * @param  string|null  $fromDate
     * @param  string|null  $toDate
     * @param  string  $status ('posted' | 'draft' | 'all')
     * @param  int|null  $branchId
     * @param  int|null  $accountId
     * @param  string|null  $search
     * @return array
     */
    public function execute(
        ?string $fromDate = null,
        ?string $toDate = null,
        string $status = 'posted',
        ?int $branchId = null,
        ?int $accountId = null,
        ?string $search = null
    ): array {
        $fromDate = $fromDate ?: now()->startOfMonth()->format('Y-m-d');
        $toDate   = $toDate ?: now()->format('Y-m-d');
        $branch   = $branchId ? Branch::find($branchId) : null;
        $account  = $accountId ? Account::find($accountId) : null;

        $query = JournalEntry::with(['items.account', 'branch', 'createdBy'])
            ->whereDate('date', '>=', $fromDate)
            ->whereDate('date', '<=', $toDate);

        // Status Filter
        if ($status === 'posted') {
            $query->where('status', 'posted');
        } elseif ($status === 'draft') {
            $query->where('status', 'draft');
        }

        // Branch Filter
        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        // Account Filter
        if ($accountId) {
            $query->whereHas('items', function ($q) use ($accountId) {
                $q->where('account_id', $accountId);
            });
        }

        // Keyword Search
        if ($search) {
            $search = trim($search);
            $query->where(function ($q) use ($search) {
                $q->where('entry_no', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }

        $entries = $query->orderBy('date', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $journalReports = [];
        $grandTotalDebit = 0.0;
        $grandTotalCredit = 0.0;

        foreach ($entries as $entry) {
            $itemsData = [];
            $entryDebit = 0.0;
            $entryCredit = 0.0;

            foreach ($entry->items as $item) {
                $debit = (float) $item->debit;
                $credit = (float) $item->credit;

                $entryDebit += $debit;
                $entryCredit += $credit;

                $itemsData[] = [
                    'id'           => $item->id,
                    'account_id'   => $item->account_id,
                    'account_code' => $item->account?->code ?? '-',
                    'account_name' => $item->account?->name ?? 'Akun Tidak Ditemukan',
                    'notes'        => $item->notes,
                    'debit'        => $debit,
                    'credit'       => $credit,
                ];
            }

            $grandTotalDebit += $entryDebit;
            $grandTotalCredit += $entryCredit;

            $journalReports[] = [
                'id'           => $entry->id,
                'entry_no'     => $entry->entry_no,
                'date'         => $entry->date,
                'description'  => $entry->description,
                'status'       => $entry->status,
                'branch_name'  => $entry->branch?->name ?? '-',
                'creator_name' => $entry->createdBy?->name ?? '-',
                'items'        => $itemsData,
                'total_debit'  => $entryDebit,
                'total_credit' => $entryCredit,
                'is_balanced'  => abs($entryDebit - $entryCredit) < 0.01,
            ];
        }

        return [
            'from_date'          => $fromDate,
            'to_date'            => $toDate,
            'status'             => $status,
            'branch_id'          => $branchId,
            'branch_name'        => $branch ? $branch->name : 'Semua Cabang',
            'account_id'         => $accountId,
            'selected_account'   => $account,
            'search'             => $search,
            'entries'            => $journalReports,
            'total_entries'      => count($journalReports),
            'grand_total_debit'  => $grandTotalDebit,
            'grand_total_credit' => $grandTotalCredit,
        ];
    }
}
