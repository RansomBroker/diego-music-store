<?php

namespace App\Actions\CashManagement;

use App\Helpers\CashSessionHelper;
use App\Helpers\SaleHelper;
use App\Models\CashSession;
use App\Models\CashTransaction;
use App\Models\JournalEntry;
use App\Models\JournalItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UpdateCashTransaction
{
    public function execute(CashTransaction $cashTransaction, array $data): CashTransaction
    {
        \App\Helpers\PermissionHelper::enforceActionPermission(Auth::user(), ['fo.daily_cash.create', 'bo.cash_transactions.edit']);
        $allowedBranchIds = \App\Helpers\BranchHelper::getAllowedBranchesQuery()
            ->pluck('branches.id')->map(fn ($id) => (int) $id)->all();
        $targetBranchId = (int) ($data['branch_id'] ?? $cashTransaction->branch_id);
        abort_unless(in_array((int) $cashTransaction->branch_id, $allowedBranchIds, true)
            && in_array($targetBranchId, $allowedBranchIds, true), 403);
        if ($cashTransaction->cash_session_id) {
            abort_unless(CashSession::query()->whereKey($cashTransaction->cash_session_id)
                ->where('branch_id', $targetBranchId)->exists(), 403);
        }

        return DB::transaction(function () use ($cashTransaction, $data, $targetBranchId) {
            $wasPosted = $cashTransaction->status === 'posted';
            $targetStatus = (string) ($data['status'] ?? $cashTransaction->status);

            $cashTransaction->update([
                'branch_id' => $targetBranchId,
                'type' => $data['type'] ?? $cashTransaction->type,
                'transaction_date' => $data['transaction_date'] ?? $cashTransaction->transaction_date,
                'source_account_id' => $data['source_account_id'] ?? $cashTransaction->source_account_id,
                'destination_account_id' => $data['destination_account_id'] ?? $cashTransaction->destination_account_id,
                'amount' => $data['amount'] ?? $cashTransaction->amount,
                'notes' => $data['notes'] ?? $cashTransaction->notes,
            ]);

            $cashTransaction->refresh();

            if (! $wasPosted && $targetStatus === 'posted') {
                app(PostCashTransaction::class)->execute($cashTransaction);
            } elseif ($wasPosted && $targetStatus === 'posted') {
                $this->syncPostedJournal($cashTransaction);
            } elseif ($wasPosted && $targetStatus === 'draft') {
                $this->revertPostedJournalToDraft($cashTransaction);
            }

            if ($cashTransaction->cash_session_id) {
                $this->recomputeCashSession($cashTransaction->cash_session_id);
            }

            return $cashTransaction->fresh();
        });
    }

    protected function syncPostedJournal(CashTransaction $cashTransaction): void
    {
        $journalEntry = $cashTransaction->journal_entry_id
            ? JournalEntry::find($cashTransaction->journal_entry_id)
            : JournalEntry::where('reference_type', 'CashTransaction')
                ->where('reference_id', $cashTransaction->id)
                ->first();

        if (! $journalEntry) {
            $cashTransaction->update(['status' => 'draft', 'journal_entry_id' => null]);
            app(PostCashTransaction::class)->execute($cashTransaction);
            return;
        }

        $descriptionMap = [
            'in' => 'Kas Masuk otomatis',
            'out' => 'Kas Keluar otomatis',
            'transfer' => 'Transfer Kas otomatis',
        ];
        $typeDesc = $descriptionMap[$cashTransaction->type] ?? 'Transaksi Kas';

        $journalEntry->update([
            'branch_id' => $cashTransaction->branch_id,
            'date' => $cashTransaction->transaction_date,
            'description' => "{$typeDesc}: No. Transaksi {$cashTransaction->transaction_no}",
            'status' => 'posted',
            'posted_at' => now(),
            'posted_by' => Auth::id() ?? $cashTransaction->posted_by,
        ]);

        // Replace journal items with updated accounts, amount, and notes
        $journalEntry->items()->delete();

        JournalItem::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $cashTransaction->destination_account_id,
            'debit' => $cashTransaction->amount,
            'credit' => 0,
            'notes' => $cashTransaction->notes ?? $typeDesc,
        ]);

        JournalItem::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $cashTransaction->source_account_id,
            'debit' => 0,
            'credit' => $cashTransaction->amount,
            'notes' => $cashTransaction->notes ?? $typeDesc,
        ]);

        if ((int) $cashTransaction->journal_entry_id !== (int) $journalEntry->id) {
            $cashTransaction->update(['journal_entry_id' => $journalEntry->id]);
        }
    }

    protected function revertPostedJournalToDraft(CashTransaction $cashTransaction): void
    {
        $journalEntryId = $cashTransaction->journal_entry_id;
        $journalEntries = JournalEntry::query()
            ->where(function ($q) use ($cashTransaction, $journalEntryId) {
                $q->where(function ($sub) use ($cashTransaction) {
                    $sub->where('reference_type', 'CashTransaction')
                        ->where('reference_id', $cashTransaction->id);
                });
                if ($journalEntryId) {
                    $q->orWhere('id', $journalEntryId);
                }
            })
            ->get();

        $cashTransaction->update([
            'status' => 'draft',
            'journal_entry_id' => null,
            'posted_at' => null,
            'posted_by' => null,
        ]);

        foreach ($journalEntries as $je) {
            $je->items()->delete();
            $je->delete();
        }
    }

    protected function recomputeCashSession(int $sessionId): void
    {
        $session = CashSession::find($sessionId);
        if (! $session) {
            return;
        }

        $cashSales = SaleHelper::getSessionCashSalesSum($session);
        $cashTotals = CashSessionHelper::getSessionCashTotals($session);
        $expectedCash = (int) $session->opening_cash + $cashSales + $cashTotals['cash_in'] - $cashTotals['total_out'];

        $updateData = ['expected_cash' => $expectedCash];
        if ($session->status === 'closed' && $session->actual_cash !== null) {
            $updateData['difference'] = (int) $session->actual_cash - $expectedCash;
        }
        $session->update($updateData);
    }
}
