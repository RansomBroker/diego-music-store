<?php

namespace App\Actions\CustomerDeposit;

use App\Models\Account;
use App\Models\Customer;
use App\Models\CustomerDeposit;
use App\Models\JournalEntry;
use App\Models\JournalItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class UpdateCustomerDeposit
{
    /**
     * Execute update on an existing pending customer deposit.
     */
    public function execute(CustomerDeposit $deposit, array $data, ?User $user = null): CustomerDeposit
    {
        return DB::transaction(function () use ($deposit, $data, $user) {
            if (!$deposit->isPending()) {
                throw new InvalidArgumentException('Hanya deposit berstatus pending yang dapat diubah.');
            }

            $user = $user ?: Auth::user();
            $userId = $user?->id ?: $deposit->user_id;

            $customerId = $data['customer_id'] ?? $deposit->customer_id;
            $customer = Customer::findOrFail($customerId);

            $productType = $data['product_type'] ?? $deposit->product_type;
            $productId = null;
            $productVariantId = null;
            $productName = trim($data['product_name'] ?? '');

            if ($productType === 'existing') {
                $productId = $data['product_id'] ?? $deposit->product_id;
                $productVariantId = $data['product_variant_id'] ?? $deposit->product_variant_id;
                if ($productId) {
                    $product = Product::find($productId);
                    if ($product && empty($productName)) {
                        $productName = $product->name;
                    }
                }
            }

            if (empty($productName)) {
                $productName = $deposit->product_name;
            }

            $price = max(0, floatval($data['price'] ?? $deposit->price));
            $qty = max(1, intval($data['qty'] ?? $deposit->qty));
            $totalAmount = round($price * $qty, 2);

            $newDepositAmount = max(0, floatval($data['deposit_amount'] ?? $deposit->deposit_amount));
            if ($newDepositAmount > $totalAmount) {
                throw new InvalidArgumentException('Nilai deposit tidak boleh melebihi total harga pesanan.');
            }

            $oldDepositAmount = floatval($deposit->deposit_amount);
            $remainingAmount = max(0, round($totalAmount - $newDepositAmount, 2));

            // Update deposit attributes
            $deposit->update([
                'customer_id' => $customer->id,
                'deposit_date' => $data['deposit_date'] ?? $deposit->deposit_date,
                'product_type' => $productType,
                'product_id' => $productId,
                'product_variant_id' => $productVariantId,
                'product_name' => $productName,
                'price' => $price,
                'qty' => $qty,
                'total_amount' => $totalAmount,
                'deposit_amount' => $newDepositAmount,
                'remaining_amount' => $remainingAmount,
                'account_id' => $data['account_id'] ?? $deposit->account_id,
                'payment_method' => $data['payment_method'] ?? $deposit->payment_method,
                'payment_reference' => $data['payment_reference'] ?? $deposit->payment_reference,
                'notes' => $data['notes'] ?? $deposit->notes,
            ]);

            // Synchronize Journal Entry
            $penitipanDanaAcc = Account::firstOrCreate(
                ['code' => '2-1200'],
                [
                    'name' => 'Penitipan Dana',
                    'classification' => 'liability',
                    'is_header' => false,
                    'is_active' => true,
                ]
            );

            if ($deposit->deposit_journal_entry_id) {
                $journalEntry = JournalEntry::find($deposit->deposit_journal_entry_id);
                if ($journalEntry) {
                    if ($newDepositAmount > 0) {
                        $journalEntry->update([
                            'description' => "Penerimaan Deposit {$deposit->deposit_number} - Pelanggan: {$customer->name} ({$deposit->product_name})",
                        ]);
                        // Delete old items and re-create
                        $journalEntry->items()->delete();

                        $debitAccountId = $deposit->account_id;
                        if (!$debitAccountId || $debitAccountId == $penitipanDanaAcc->id) {
                            $defaultKas = Account::where('classification', 'asset')
                                ->where('is_header', false)
                                ->where(function ($q) {
                                    $q->where('code', '1-1000')->orWhere('name', 'like', '%kas%');
                                })
                                ->first();
                            $debitAccountId = $defaultKas?->id;
                        }

                        if ($debitAccountId) {
                            JournalItem::create([
                                'journal_entry_id' => $journalEntry->id,
                                'account_id' => $debitAccountId,
                                'debit' => $newDepositAmount,
                                'credit' => 0,
                                'notes' => "Penerimaan Deposit {$deposit->deposit_number} ({$deposit->payment_method})",
                            ]);
                        }

                        JournalItem::create([
                            'journal_entry_id' => $journalEntry->id,
                            'account_id' => $penitipanDanaAcc->id,
                            'debit' => 0,
                            'credit' => $newDepositAmount,
                            'notes' => "Titipan Dana Pelanggan {$customer->name} ({$deposit->deposit_number})",
                        ]);
                    } else {
                        // Deposit amount reduced to 0 -> remove journal items or delete entry
                        $journalEntry->items()->delete();
                        $journalEntry->delete();
                        $deposit->update(['deposit_journal_entry_id' => null]);
                    }
                }
            } elseif ($newDepositAmount > 0) {
                // Previously 0, now has deposit amount -> create journal entry
                $journalNo = 'JV-DEP-' . now()->format('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
                $journalEntry = JournalEntry::create([
                    'branch_id' => $deposit->branch_id,
                    'entry_no' => $journalNo,
                    'date' => $deposit->deposit_date,
                    'description' => "Penerimaan Deposit {$deposit->deposit_number} - Pelanggan: {$customer->name} ({$deposit->product_name})",
                    'reference_type' => 'CustomerDeposit',
                    'reference_id' => $deposit->id,
                    'status' => 'posted',
                    'created_by' => $userId,
                    'posted_at' => now(),
                    'posted_by' => $userId,
                ]);

                $debitAccountId = $deposit->account_id;
                if (!$debitAccountId || $debitAccountId == $penitipanDanaAcc->id) {
                    $defaultKas = Account::where('classification', 'asset')
                        ->where('is_header', false)
                        ->where(function ($q) {
                            $q->where('code', '1-1000')->orWhere('name', 'like', '%kas%');
                        })
                        ->first();
                    $debitAccountId = $defaultKas?->id;
                }

                if ($debitAccountId) {
                    JournalItem::create([
                        'journal_entry_id' => $journalEntry->id,
                        'account_id' => $debitAccountId,
                        'debit' => $newDepositAmount,
                        'credit' => 0,
                        'notes' => "Penerimaan Deposit {$deposit->deposit_number} ({$deposit->payment_method})",
                    ]);
                }

                JournalItem::create([
                    'journal_entry_id' => $journalEntry->id,
                    'account_id' => $penitipanDanaAcc->id,
                    'debit' => 0,
                    'credit' => $newDepositAmount,
                    'notes' => "Titipan Dana Pelanggan {$customer->name} ({$deposit->deposit_number})",
                ]);

                $deposit->update(['deposit_journal_entry_id' => $journalEntry->id]);
            }

            // Update customer deposit balance difference
            if (\Illuminate\Support\Facades\Schema::hasColumn('customers', 'deposit_balance')) {
                $diff = $newDepositAmount - $oldDepositAmount;
                if ($diff > 0) {
                    $customer->increment('deposit_balance', $diff);
                } elseif ($diff < 0) {
                    $customer->decrement('deposit_balance', abs($diff));
                }
            }

            return $deposit;
        });
    }
}
