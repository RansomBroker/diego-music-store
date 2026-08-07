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
                }
            }

            // 2. Post Journal Entries
            if ($purchaseReturn->total_amount > 0) {
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

                if ($pt && strtolower($pt->purchase_type) === 'kredit') {
                    $debitAccId = $resolveAccount('2-1100', 'Hutang Usaha', 'Liability');
                    $notes = "Pengurangan Hutang Supplier (Retur Pembelian Kredit)";
                } else {
                    $debitAccId = $resolveAccount('1-1000', 'Kas Utama', 'Asset');
                    $notes = "Penerimaan Refund Retur Pembelian Supplier (Tunai)";
                }

                JournalItem::create([
                    'journal_entry_id' => $journalEntry->id,
                    'account_id'       => $debitAccId,
                    'debit'            => $purchaseReturn->total_amount,
                    'credit'           => 0,
                    'notes'            => $notes,
                ]);

                $inventoryAccId = $resolveAccount('1-1300', 'Persediaan Barang Dagang', 'Asset');
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
