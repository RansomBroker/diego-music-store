<?php

namespace App\Actions\Sales;

use App\Helpers\AccountHelper;
use App\Models\Account;
use App\Models\Customer;
use App\Models\JournalEntry;
use App\Models\JournalItem;
use App\Models\Sale;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;

class SettleCustomerReceivable
{
    /**
     * Settle an outstanding receivable for a sale and record balanced journal entry.
     *
     * @param Sale $sale
     * @param array{
     *     amount: float,
     *     account_id: int,
     *     payment_method?: string,
     *     payment_date?: string|null,
     *     payment_reference?: string|null,
     *     user_id?: int|null,
     * } $data
     * @return array{sale: Sale, journal_entry: JournalEntry, paid: float, is_fully_paid: bool}
     */
    public function execute(Sale $sale, array $data): array
    {
        return DB::transaction(function () use ($sale, $data) {
            $currentPiutang = max(0, (float) $sale->getPiutangAmount());
            if ($currentPiutang <= 0) {
                throw new InvalidArgumentException("Transaksi #{$sale->invoice_number} tidak memiliki sisa piutang.");
            }

            $amount = floatval($data['amount'] ?? 0);
            if ($amount <= 0) {
                throw new InvalidArgumentException('Nominal pembayaran pelunasan harus lebih besar dari Rp 0.');
            }

            $paid = min($amount, $currentPiutang);

            $accountId = intval($data['account_id'] ?? 0);
            $cashAccount = Account::find($accountId);
            if (!$cashAccount) {
                throw new InvalidArgumentException('Rekening Kas/Bank tujuan penerimaan tidak ditemukan atau belum dipilih.');
            }

            $piutangAccount = AccountHelper::findByCode('111301001')
                ?? Account::where('code', '111301001')->orWhere('code', '1-1200')->first();
            if (!$piutangAccount) {
                $piutangAccId = AccountHelper::resolveAccountId('111301001', 'PIUTANG DAGANG', 'Asset');
                $piutangAccount = Account::find($piutangAccId);
            }

            $userId = $data['user_id'] ?? Auth::id();
            $paymentDate = $data['payment_date'] ?? now()->toDateString();
            $paymentMethod = $data['payment_method'] ?? 'cash';
            $reference = $data['payment_reference'] ?? null;
            $refText = $reference ? " | Ref: {$reference}" : '';
            $methodLabel = strtoupper($paymentMethod);
            $isFullyPaid = ($paid >= $currentPiutang);

            // Generate Journal Entry
            $journalEntry = JournalEntry::create([
                'branch_id' => $sale->branch_id,
                'entry_no' => JournalEntry::generateEntryNo($sale->branch_id),
                'date' => $paymentDate,
                'reference_type' => 'Sales',
                'reference_id' => $sale->id,
                'description' => "Pelunasan Piutang Inv #{$sale->invoice_number}",
                'status' => 'posted',
                'created_by' => $userId,
                'posted_at' => now(),
                'posted_by' => $userId,
            ]);

            // 1. Debit: Kas / Bank
            JournalItem::create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $cashAccount->id,
                'debit' => $paid,
                'credit' => 0,
                'notes' => "Penerimaan Pelunasan - Inv #{$sale->invoice_number}",
            ]);

            // 2. Credit: Piutang Dagang
            JournalItem::create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $piutangAccount->id,
                'debit' => 0,
                'credit' => $paid,
                'notes' => "Pelunasan Piutang - Inv #{$sale->invoice_number}",
            ]);

            // Update Sale Status & Payment Method
            if ($isFullyPaid) {
                $sale->update([
                    'payment_method' => $sale->payment_method . " (Lunas via {$methodLabel}{$refText})",
                    'status' => 'completed',
                ]);
            } else {
                $sale->update([
                    'payment_method' => $sale->payment_method . " (Dicicil Rp " . number_format($paid, 0, ',', '.') . " via {$methodLabel}{$refText})",
                    'status' => 'pending',
                ]);
            }

            // Decrement Customer Outstanding Debt
            if ($sale->customer_id) {
                try {
                    $customer = Customer::find($sale->customer_id);
                    if ($customer && Schema::hasColumn('customers', 'outstanding_debt') && $customer->outstanding_debt > 0) {
                        $customer->decrement('outstanding_debt', min($customer->outstanding_debt, $paid));
                    }
                } catch (\Throwable $e) {
                    Log::warning('Could not decrement customer outstanding_debt: ' . $e->getMessage());
                }
            }

            return [
                'sale' => $sale->fresh(),
                'journal_entry' => $journalEntry,
                'paid' => $paid,
                'is_fully_paid' => $isFullyPaid,
            ];
        });
    }
}
