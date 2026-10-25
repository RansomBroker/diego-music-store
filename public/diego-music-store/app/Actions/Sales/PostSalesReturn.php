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
            if ($salesReturn->total_refund > 0 || $totalReturnedCOGS > 0) {
                $journalNo = JournalEntry::generateEntryNo($salesReturn->branch_id);

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
                    return \App\Helpers\AccountHelper::resolveAccountId($code, $defaultName, $classification);
                };

                if ($salesReturn->total_refund > 0) {
                    $returAccId = $resolveAccount('411201001', 'RETUR PENJUALAN', 'Revenue');
                    JournalItem::create([
                        'journal_entry_id' => $journalEntry->id,
                        'account_id'       => $returAccId,
                        'debit'            => $salesReturn->total_refund,
                        'credit'           => 0,
                        'notes'            => "Pembalikan Pendapatan Retur Penjualan",
                    ]);

                // Check outstanding receivable on original sale
                $currentPiutang = $sale ? max(0, (float) $sale->getPiutangAmount()) : 0;
                $piutangReduction = min((float) $salesReturn->total_refund, $currentPiutang);
                $cashRefund = (float) $salesReturn->total_refund - $piutangReduction;

                // Credit Piutang if there is unpaid receivable on this sale
                if ($piutangReduction > 0) {
                    $piutangAccId = $resolveAccount('111301001', 'PIUTANG DAGANG', 'Asset');
                    JournalItem::create([
                        'journal_entry_id' => $journalEntry->id,
                        'account_id'       => $piutangAccId,
                        'debit'            => 0,
                        'credit'           => $piutangReduction,
                        'notes'            => "Pengurangan Piutang Penjualan (Retur #{$salesReturn->return_number})",
                    ]);

                    if ($sale->customer_id && \Illuminate\Support\Facades\Schema::hasColumn('customers', 'outstanding_debt')) {
                        try {
                            $customer = \App\Models\Customer::find($sale->customer_id);
                            if ($customer) {
                                $customer->decrement('outstanding_debt', min($customer->outstanding_debt, $piutangReduction));
                            }
                        } catch (\Throwable $e) {
                            \Illuminate\Support\Facades\Log::warning('Could not decrement customer outstanding_debt on return: ' . $e->getMessage());
                        }
                    }

                    // If remaining piutang is now 0, update sale status to completed
                    if (($currentPiutang - $piutangReduction) <= 0 && $sale->status !== 'completed') {
                        $sale->update(['status' => 'completed']);
                    }
                }

                // Credit Kas/Bank for any remaining refund amount
                if ($cashRefund > 0) {
                    $payMethod = strtolower($sale?->payment_method ?: 'tunai');
                    if (str_contains($payMethod, 'debit') || str_contains($payMethod, 'bank') || str_contains($payMethod, 'transfer')) {
                        $creditAccId = $resolveAccount('111201001', 'BANK BCA', 'Asset');
                        $methodName = 'Bank BCA';
                    } else {
                        $creditAccId = $resolveAccount('111101001', 'KAS', 'Asset');
                        $methodName = 'Kas Utama';
                    }

                    JournalItem::create([
                        'journal_entry_id' => $journalEntry->id,
                        'account_id'       => $creditAccId,
                        'debit'            => 0,
                        'credit'           => $cashRefund,
                        'notes'            => "Pengembalian Dana POS via {$methodName}",
                    ]);
                }
            }

            if ($totalReturnedCOGS > 0) {
                $cogsAccId = $resolveAccount('511501001', 'HARGA POKOK PENJUALAN', 'Expense');
                $inventoryAccId = $resolveAccount('111401001', 'PERSEDIAAN BARANG DAGANG', 'Asset');

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
