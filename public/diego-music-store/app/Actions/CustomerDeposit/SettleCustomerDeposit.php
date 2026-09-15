<?php

namespace App\Actions\CustomerDeposit;

use App\Models\Account;
use App\Models\Customer;
use App\Models\CustomerDeposit;
use App\Models\JournalEntry;
use App\Models\JournalItem;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class SettleCustomerDeposit
{
    /**
     * Execute settlement (Pelunasan) of customer deposit.
     * Moves funds from Penitipan Dana + remaining cash to Sales Revenue (Pendapatan).
     */
    public function execute(CustomerDeposit $deposit, array $data, ?User $user = null): CustomerDeposit
    {
        return DB::transaction(function () use ($deposit, $data, $user) {
            if (!$deposit->isPending()) {
                throw new InvalidArgumentException('Hanya deposit berstatus pending yang dapat dilunasi.');
            }

            $user = $user ?: Auth::user();
            $userId = $user?->id;
            $customer = $deposit->customer;

            $settlementAccountId = $data['settlement_account_id'] ?? $deposit->account_id;
            $settlementMethod = $data['settlement_payment_method'] ?? 'Tunai';
            $settlementRef = $data['settlement_reference'] ?? null;
            $settlementNotes = $data['settlement_notes'] ?? null;
            $settledAt = now();

            $depositAmount = floatval($deposit->deposit_amount);
            $remainingAmount = floatval($deposit->remaining_amount);
            $totalAmount = floatval($deposit->total_amount);

            // 1. Resolve Accounts
            // Penitipan Dana (Liability)
            $penitipanDanaAcc = Account::firstOrCreate(
                ['code' => '2-1200'],
                [
                    'name' => 'Penitipan Dana',
                    'classification' => 'liability',
                    'is_header' => false,
                    'is_active' => true,
                ]
            );

            // Pendapatan Penjualan (Revenue / Pemasukan)
            $salesAcc = null;
            if ($deposit->product && $deposit->product->sales_account_id) {
                $salesAcc = Account::find($deposit->product->sales_account_id);
            }
            if (!$salesAcc) {
                $salesAcc = Account::firstOrCreate(
                    ['code' => '4-1000'],
                    [
                        'name' => 'Pendapatan Penjualan',
                        'classification' => 'revenue',
                        'is_header' => false,
                        'is_active' => true,
                    ]
                );
            }

            // Kas / Bank for remaining payment
            $cashAcc = null;
            if ($remainingAmount > 0) {
                if ($settlementAccountId) {
                    $cashAcc = Account::find($settlementAccountId);
                }
                if (!$cashAcc) {
                    $cashAcc = Account::where('classification', 'asset')
                        ->where('is_header', false)
                        ->where(function ($q) {
                            $q->where('code', '1-1000')->orWhere('name', 'like', '%kas%');
                        })
                        ->first();
                }
            }

            // 2. Create Settlement Journal Entry
            $journalNo = 'JV-DEP-SETTLE-' . now()->format('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

            $journalEntry = JournalEntry::create([
                'branch_id' => $deposit->branch_id,
                'entry_no' => $journalNo,
                'date' => now()->toDateString(),
                'description' => "Pelunasan Pesanan/Deposit {$deposit->deposit_number} - Pelanggan: {$customer->name} ({$deposit->product_name})",
                'reference_type' => 'CustomerDepositSettlement',
                'reference_id' => $deposit->id,
                'status' => 'posted',
                'created_by' => $userId ?: 1,
                'posted_at' => now(),
                'posted_by' => $userId ?: 1,
            ]);

            // 2a. Debit: Penitipan Dana (sebesar deposit awal yang dititipkan)
            if ($depositAmount > 0) {
                JournalItem::create([
                    'journal_entry_id' => $journalEntry->id,
                    'account_id' => $penitipanDanaAcc->id,
                    'debit' => $depositAmount,
                    'credit' => 0,
                    'notes' => "Pemindahan Titipan Dana ke Pendapatan - {$deposit->deposit_number}",
                ]);
            }

            // 2b. Debit: Kas/Bank Pelunasan (sebesar sisa harga yang baru dibayarkan)
            if ($remainingAmount > 0 && $cashAcc) {
                JournalItem::create([
                    'journal_entry_id' => $journalEntry->id,
                    'account_id' => $cashAcc->id,
                    'debit' => $remainingAmount,
                    'credit' => 0,
                    'notes' => "Penerimaan Sisa Pelunasan {$deposit->deposit_number} ({$settlementMethod})",
                ]);
            }

            // 2c. Kredit: Pendapatan Penjualan (sebesar total harga pesanan)
            JournalItem::create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $salesAcc->id,
                'debit' => 0,
                'credit' => $totalAmount,
                'notes' => "Pendapatan Penjualan Pelunasan Pesanan {$deposit->deposit_number}",
            ]);

            // 3. Update Deposit Status
            $deposit->update([
                'status' => 'settled',
                'settled_at' => $settledAt,
                'settled_by' => $userId ?: 1,
                'settlement_account_id' => $cashAcc?->id ?: $settlementAccountId,
                'settlement_payment_method' => $settlementMethod,
                'settlement_reference' => $settlementRef,
                'settlement_notes' => $settlementNotes,
                'settlement_journal_entry_id' => $journalEntry->id,
            ]);

            // 4. Decrement Customer deposit balance since it's now utilized
            if ($depositAmount > 0 && \Illuminate\Support\Facades\Schema::hasColumn('customers', 'deposit_balance')) {
                $customer->decrement('deposit_balance', min($customer->deposit_balance, $depositAmount));
            }

            return $deposit;
        });
    }
}
