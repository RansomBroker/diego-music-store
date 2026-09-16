<?php

namespace App\Actions\Purchases;

use App\Models\PurchaseReturn;
use App\Models\ProductBranchStock;
use App\Models\StockMovement;
use App\Models\JournalEntry;
use App\Models\JournalItem;
use App\Models\Account;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PostPurchaseReturn
{
    /**
     * Execute the posting of a draft Purchase Return.
     *
     * @param PurchaseReturn $purchaseReturn
     * @return PurchaseReturn
     */
    public function execute(PurchaseReturn $purchaseReturn): PurchaseReturn
    {
        return DB::transaction(function () use ($purchaseReturn) {
            if ($purchaseReturn->status === 'posted') {
                throw new \Exception('Retur Pembelian sudah diposting sebelumnya.');
            }

            $pt = $purchaseReturn->purchaseTransaction;

            // 1. Process physical stock deduction & stock movement out
            foreach ($purchaseReturn->items as $item) {
                $variant = $item->productVariant;
                if ($variant && $variant->product->isPhysical()) {
                    $branchStock = ProductBranchStock::firstOrCreate([
                        'product_variant_id' => $variant->id,
                        'branch_id'          => $purchaseReturn->branch_id,
                    ], [
                        'stock' => 0,
                        'hpp'   => $variant->cost_price ?: 0,
                    ]);

                    $branchStock->decrement('stock', $item->quantity);

                    StockMovement::create([
                        'product_variant_id' => $variant->id,
                        'branch_id'          => $purchaseReturn->branch_id,
                        'type'               => 'out',
                        'quantity'           => $item->quantity,
                        'unit_cost'          => $item->unit_price,
                        'hpp'                => $branchStock->hpp ?: $item->unit_price,
                        'reference_type'     => 'PurchaseReturn',
                        'reference_id'       => $purchaseReturn->id,
                    ]);

                    // If Tukar Guling (replacement) and replacement items received directly:
                    if ($purchaseReturn->return_type === 'replacement' && $purchaseReturn->replacement_status === 'received') {
                        $branchStock->increment('stock', $item->quantity);

                        StockMovement::create([
                            'product_variant_id' => $variant->id,
                            'branch_id'          => $purchaseReturn->branch_id,
                            'type'               => 'in',
                            'quantity'           => $item->quantity,
                            'unit_cost'          => $item->unit_price,
                            'hpp'                => $branchStock->hpp ?: $item->unit_price,
                            'reference_type'     => 'PurchaseReturnReplacement',
                            'reference_id'       => $purchaseReturn->id,
                        ]);
                    }
                }
            }

            // 2. Post Journal Entries (only for types with financial impact: invoice_deduction, refund, supplier_credit)
            if ($purchaseReturn->total_amount > 0 && $purchaseReturn->return_type !== 'replacement') {
                $journalNo = 'JV-PR-' . now()->format('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

                $journalEntry = JournalEntry::create([
                    'branch_id'      => $purchaseReturn->branch_id,
                    'entry_no'       => $journalNo,
                    'date'           => now()->toDateString(),
                    'description'    => "Posting Retur Pembelian Supplier: No. Retur {$purchaseReturn->return_no} (Ref Transaksi: {$pt?->transaction_no})",
                    'reference_type' => 'PurchaseReturn',
                    'reference_id'   => $purchaseReturn->id,
                    'status'         => 'posted',
                    'created_by'     => Auth::id() ?? $purchaseReturn->created_by,
                    'posted_at'      => now(),
                    'posted_by'      => Auth::id() ?? $purchaseReturn->created_by,
                ]);

                $resolveAccount = function($code, $defaultName, $classification) {
                    return Account::firstOrCreate(
                        ['code' => $code],
                        [
                            'name'           => $defaultName,
                            'classification' => $classification,
                            'is_active'      => true,
                        ]
                    )->id;
                };

                if ($purchaseReturn->return_type === 'invoice_deduction') {
                    $debitAccId = $resolveAccount('2-1000', 'Hutang Dagang', 'liability');
                    $notes = "Penyesuaian Faktur: Pengurangan Hutang Supplier (Retur {$purchaseReturn->return_no})";
                } elseif ($purchaseReturn->return_type === 'refund') {
                    $debitAccId = $purchaseReturn->refund_account_id ?: $resolveAccount('1-1000', 'Kas Utama', 'asset');
                    $refundAccName = Account::find($debitAccId)?->name ?? 'Kas/Bank';
                    $notes = "Penerimaan Refund Retur Pembelian ke {$refundAccName} (Retur {$purchaseReturn->return_no})";
                } elseif ($purchaseReturn->return_type === 'supplier_credit') {
                    $debitAccId = $resolveAccount('1-1400', 'Uang Muka Pembelian / Deposit Supplier', 'asset');
                    $notes = "Pencatatan Saldo Deposit / Kredit Supplier dari Retur Pembelian (Retur {$purchaseReturn->return_no})";
                } else {
                    $debitAccId = $resolveAccount('1-1000', 'Kas Utama', 'asset');
                    $notes = "Retur Pembelian Supplier (Retur {$purchaseReturn->return_no})";
                }

                JournalItem::create([
                    'journal_entry_id' => $journalEntry->id,
                    'account_id'       => $debitAccId,
                    'debit'            => $purchaseReturn->total_amount,
                    'credit'           => 0,
                    'notes'            => $notes,
                ]);

                $inventoryAccId = $resolveAccount('1-1300', 'Persediaan Barang Dagang', 'asset');
                JournalItem::create([
                    'journal_entry_id' => $journalEntry->id,
                    'account_id'       => $inventoryAccId,
                    'debit'            => 0,
                    'credit'           => $purchaseReturn->total_amount,
                    'notes'            => "Pengurangan Persediaan Barang Retur Supplier",
                ]);
            }

            $purchaseReturn->update([
                'status' => 'posted',
            ]);

            return $purchaseReturn;
        });
    }
}
