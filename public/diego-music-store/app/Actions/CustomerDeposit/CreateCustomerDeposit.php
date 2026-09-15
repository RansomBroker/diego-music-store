<?php

namespace App\Actions\CustomerDeposit;

use App\Models\Account;
use App\Models\Customer;
use App\Models\CustomerDeposit;
use App\Models\JournalEntry;
use App\Models\JournalItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class CreateCustomerDeposit
{
    /**
     * Execute customer deposit creation and its accounting journal entry.
     */
    public function execute(array $data, ?User $user = null): CustomerDeposit
    {
        return DB::transaction(function () use ($data, $user) {
            $user = $user ?: Auth::user();
            $userId = $user?->id;

            $customerId = $data['customer_id'] ?? null;
            $customer = Customer::findOrFail($customerId);

            $branchId = $data['branch_id']
                ?? $user?->branches()->first()?->id
                ?? $user?->branch_id
                ?? 1;

            $productType = $data['product_type'] ?? 'existing';
            $productId = null;
            $productVariantId = null;
            $productName = trim($data['product_name'] ?? '');

            if ($productType === 'existing') {
                $productId = $data['product_id'] ?? null;
                $productVariantId = $data['product_variant_id'] ?? null;

                if ($productId) {
                    $product = Product::find($productId);
                    if ($product && empty($productName)) {
                        $productName = $product->name;
                    }
                }
            }

            if (empty($productName)) {
                throw new InvalidArgumentException('Nama produk/barang pesanan harus diisi.');
            }

            $price = max(0, floatval($data['price'] ?? 0));
            $qty = max(1, intval($data['qty'] ?? 1));
            $totalAmount = round($price * $qty, 2);

            $depositAmount = max(0, floatval($data['deposit_amount'] ?? 0));
            if ($depositAmount > $totalAmount) {
                throw new InvalidArgumentException('Nilai deposit tidak boleh melebihi total harga pesanan.');
            }

            $remainingAmount = max(0, round($totalAmount - $depositAmount, 2));

            // Create Deposit record
            $deposit = CustomerDeposit::create([
                'deposit_number' => CustomerDeposit::generateDepositNumber(),
                'customer_id' => $customer->id,
                'branch_id' => $branchId,
                'user_id' => $userId ?: 1,
                'deposit_date' => $data['deposit_date'] ?? now()->toDateString(),
                'product_type' => $productType,
                'product_id' => $productId,
                'product_variant_id' => $productVariantId,
                'product_name' => $productName,
                'price' => $price,
                'qty' => $qty,
                'total_amount' => $totalAmount,
                'deposit_amount' => $depositAmount,
                'remaining_amount' => $remainingAmount,
                'account_id' => $data['account_id'] ?? null,
                'payment_method' => $data['payment_method'] ?? 'Tunai',
                'payment_reference' => $data['payment_reference'] ?? null,
                'notes' => $data['notes'] ?? null,
                'status' => 'pending',
            ]);

            // Create Journal Entry if deposit amount > 0
            if ($depositAmount > 0) {
                // Ensure Penitipan Dana account exists
                $penitipanDanaAcc = Account::firstOrCreate(
                    ['code' => '2-1200'],
                    [
                        'name' => 'Penitipan Dana',
                        'classification' => 'liability',
                        'is_header' => false,
                        'is_active' => true,
                    ]
                );

                // Default debit account (Kas Utama) if not provided or if pointing to liability
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

                $journalNo = 'JV-DEP-' . now()->format('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

                $journalEntry = JournalEntry::create([
                    'branch_id' => $branchId,
                    'entry_no' => $journalNo,
                    'date' => $deposit->deposit_date,
                    'description' => "Penerimaan Deposit {$deposit->deposit_number} - Pelanggan: {$customer->name} ({$deposit->product_name})",
                    'reference_type' => 'CustomerDeposit',
                    'reference_id' => $deposit->id,
                    'status' => 'posted',
                    'created_by' => $userId ?: 1,
                    'posted_at' => now(),
                    'posted_by' => $userId ?: 1,
                ]);

                // 1. Debit: Kas/Bank penerima
                if ($debitAccountId) {
                    JournalItem::create([
                        'journal_entry_id' => $journalEntry->id,
                        'account_id' => $debitAccountId,
                        'debit' => $depositAmount,
                        'credit' => 0,
                        'notes' => "Penerimaan Deposit {$deposit->deposit_number} ({$deposit->payment_method})",
                    ]);
                }

                // 2. Kredit: Penitipan Dana (Liabilitas)
                JournalItem::create([
                    'journal_entry_id' => $journalEntry->id,
                    'account_id' => $penitipanDanaAcc->id,
                    'debit' => 0,
                    'credit' => $depositAmount,
                    'notes' => "Titipan Dana Pelanggan {$customer->name} ({$deposit->deposit_number})",
                ]);

                $deposit->update(['deposit_journal_entry_id' => $journalEntry->id]);
            }

            // Update customer deposit balance
            if (\Illuminate\Support\Facades\Schema::hasColumn('customers', 'deposit_balance')) {
                $customer->increment('deposit_balance', $depositAmount);
            }

            return $deposit;
        });
    }
}
