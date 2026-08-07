<?php

namespace App\Actions\Sales;

use App\Models\SalesReturn;
use App\Models\ProductBranchStock;
use App\Models\StockMovement;
use App\Models\JournalEntry;
use App\Models\JournalItem;
use App\Models\Account;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PostSalesReturn
{
    /**
     * Execute the posting of a draft Sales Return.
     *
     * @param SalesReturn $salesReturn
     * @return SalesReturn
     */
    public function execute(SalesReturn $salesReturn): SalesReturn
    {
        return DB::transaction(function () use ($salesReturn) {
            if ($salesReturn->status === 'posted') {
                throw new \Exception('Retur Penjualan sudah diposting sebelumnya.');
            }

            $sale = $salesReturn->sale;
            $totalReturnedCOGS = 0;

            // 1. Process physical stock increment & stock movement in
            foreach ($salesReturn->items as $item) {
                $variant = $item->variant ?: ($item->saleItem?->variant);
                if ($variant && ($variant->product?->isPhysical() ?? true)) {
                    $branchStock = ProductBranchStock::firstOrCreate([
                        'product_variant_id' => $variant->id,
                        'branch_id'          => $salesReturn->branch_id,
                    ], [
                        'stock' => 0,
                        'hpp'   => $variant->hpp ?: $variant->cost_price ?: 0,
                    ]);

                    $branchStock->increment('stock', $item->quantity);

                    $itemHPP = $branchStock->hpp ?: ($variant->hpp ?: ($variant->cost_price ?: 0));
                    $totalReturnedCOGS += ($itemHPP * $item->quantity);

                    StockMovement::create([
                        'product_variant_id' => $variant->id,
                        'branch_id'          => $salesReturn->branch_id,
                        'type'               => 'in',
                        'quantity'           => $item->quantity,
                        'unit_cost'          => $itemHPP,
                        'hpp'                => $itemHPP,
                        'reference_type'     => 'SalesReturn',
                        'reference_id'       => $salesReturn->id,
                    ]);
                }
            }

            // 2. Post Journal Entries
            if ($salesReturn->total_refund > 0) {
                $journalNo = 'JV-SR-' . now()->format('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

                $journalEntry = JournalEntry::create([
                    'branch_id'      => $salesReturn->branch_id,
                    'entry_no'       => $journalNo,
                    'date'           => now()->toDateString(),
                    'description'    => "Posting Retur Penjualan: No. Retur {$salesReturn->return_number} (Ref Invoice: {$sale?->invoice_number})",
                    'reference_type' => 'SalesReturn',
                    'reference_id'   => $salesReturn->id,
                    'status'         => 'posted',
                    'created_by'     => Auth::id() ?? $salesReturn->created_by,
                    'posted_at'      => now(),
                    'posted_by'      => Auth::id() ?? $salesReturn->created_by,
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

                $returAccId = $resolveAccount('4-1100', 'Retur & Potongan Penjualan', 'Revenue');
                JournalItem::create([
                    'journal_entry_id' => $journalEntry->id,
                    'account_id'       => $returAccId,
                    'debit'            => $salesReturn->total_refund,
                    'credit'           => 0,
                    'notes'            => "Pembalikan Pendapatan Retur Penjualan",
                ]);

                $payMethod = strtolower($sale?->payment_method ?: 'tunai');
                if (str_contains($payMethod, 'debit')) {
                    $creditAccId = $resolveAccount('1-1110', 'Bank BCA', 'Asset');
                    $methodName = 'Bank BCA';
                } else {
                    $creditAccId = $resolveAccount('1-1000', 'Kas Utama', 'Asset');
                    $methodName = 'Kas Utama';
                }

                JournalItem::create([
                    'journal_entry_id' => $journalEntry->id,
                    'account_id'       => $creditAccId,
                    'debit'            => 0,
                    'credit'           => $salesReturn->total_refund,
                    'notes'            => "Pengembalian Dana POS via {$methodName}",
                ]);

                if ($totalReturnedCOGS > 0) {
                    $cogsAccId = $resolveAccount('5-1000', 'Harga Pokok Penjualan', 'Expense');
                    $inventoryAccId = $resolveAccount('1-1300', 'Persediaan Barang', 'Asset');

                    JournalItem::create([
                        'journal_entry_id' => $journalEntry->id,
                        'account_id'       => $inventoryAccId,
                        'debit'            => $totalReturnedCOGS,
                        'credit'           => 0,
                        'notes'            => "Pengembalian Persediaan Retur POS",
                    ]);

                    JournalItem::create([
                        'journal_entry_id' => $journalEntry->id,
                        'account_id'       => $cogsAccId,
                        'debit'            => 0,
                        'credit'           => $totalReturnedCOGS,
                        'notes'            => "Pembalikan HPP Retur POS",
                    ]);
                }
            }

            $salesReturn->update([
                'status' => 'posted',
            ]);

            return $salesReturn;
        });
    }
}
