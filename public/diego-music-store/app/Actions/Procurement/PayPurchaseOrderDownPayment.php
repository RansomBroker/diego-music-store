<?php

namespace App\Actions\Procurement;

use App\Helpers\AccountHelper;
use App\Models\Account;
use App\Models\JournalEntry;
use App\Models\JournalItem;
use App\Models\PurchaseOrder;
use Exception;
use Illuminate\Support\Facades\DB;

class PayPurchaseOrderDownPayment
{
    /**
     * Execute the down payment payment on an approved Purchase Order.
     *
     * @param PurchaseOrder $po
     * @param array{dp_amount: int, dp_account_id: int, dp_paid_at?: string|null, dp_reference_no?: string|null, dp_notes?: string|null} $data
     * @param int|null $userId
     * @return PurchaseOrder
     * @throws Exception
     */
    public function execute(PurchaseOrder $po, array $data, ?int $userId = null): PurchaseOrder
    {
        if ($po->status !== 'approved') {
            throw new Exception("Pembayaran DP hanya dapat diproses untuk Purchase Order dengan status 'Approved'.");
        }

        if (($po->dp_amount ?? 0) > 0) {
            throw new Exception("Purchase Order ini sudah memiliki Uang Muka (DP) sebesar Rp " . number_format($po->dp_amount, 0, ',', '.') . ".");
        }

        $amount = intval($data['dp_amount'] ?? 0);
        if ($amount <= 0) {
            throw new Exception("Nominal DP harus lebih besar dari Rp 0.");
        }

        if ($amount > $po->grand_total) {
            throw new Exception("Nominal DP (Rp " . number_format($amount, 0, ',', '.') . ") tidak boleh melebihi Grand Total PO (Rp " . number_format($po->grand_total, 0, ',', '.') . ").");
        }

        $bankAccountId = intval($data['dp_account_id'] ?? 0);
        $bankAccount = Account::find($bankAccountId);
        if (!$bankAccount) {
            throw new Exception("Rekening Kas/Bank sumber dana tidak ditemukan.");
        }

        $paidAt = !empty($data['dp_paid_at']) ? $data['dp_paid_at'] : now()->format('Y-m-d');
        $referenceNo = trim((string) ($data['dp_reference_no'] ?? ''));
        $notes = trim((string) ($data['dp_notes'] ?? ''));

        return DB::transaction(function () use ($po, $amount, $bankAccount, $paidAt, $referenceNo, $notes, $userId) {
            // 1. Resolve Advance Payment account (111201006 - Uang Muka Pembelian)
            $advanceAccId = AccountHelper::resolveAccountId('111201006', 'Uang Muka Pembelian', 'asset');

            // 2. Generate journal number
            $journalNo = 'JV-DP-PO-' . now()->format('Ymd') . '-' . str_pad((string) rand(1, 9999), 4, '0', STR_PAD_LEFT);

            // 3. Create posted journal entry
            $journal = JournalEntry::create([
                'branch_id'      => $po->branch_id,
                'entry_no'       => $journalNo,
                'date'           => $paidAt,
                'description'    => "Uang Muka (DP) PO #{$po->po_number} ke {$po->supplier->name}" . ($referenceNo ? " (Ref: {$referenceNo})" : ''),
                'reference_type' => 'PurchaseOrderDP',
                'reference_id'   => $po->id,
                'status'         => 'posted',
                'created_by'     => $userId,
                'posted_at'      => now(),
                'posted_by'      => $userId,
            ]);

            // 4. Debit: 111201006 - Uang Muka Pembelian (Asset increase)
            JournalItem::create([
                'journal_entry_id' => $journal->id,
                'account_id'       => $advanceAccId,
                'debit'            => $amount,
                'credit'           => 0,
                'notes'            => "Uang Muka Pembelian PO #{$po->po_number} - {$po->supplier->name}",
            ]);

            // 5. Credit: Kas/Bank Account (Asset decrease)
            JournalItem::create([
                'journal_entry_id' => $journal->id,
                'account_id'       => $bankAccount->id,
                'debit'            => 0,
                'credit'           => $amount,
                'notes'            => "Pengeluaran dana untuk DP PO #{$po->po_number} via {$bankAccount->name}",
            ]);

            // 6. Update Purchase Order record
            $po->update([
                'dp_amount'       => $amount,
                'dp_account_id'   => $bankAccount->id,
                'dp_paid_at'      => $paidAt,
                'dp_journal_no'   => $journal->entry_no,
                'dp_reference_no' => $referenceNo ?: null,
                'dp_notes'        => $notes ?: null,
            ]);

            return $po;
        });
    }
}
