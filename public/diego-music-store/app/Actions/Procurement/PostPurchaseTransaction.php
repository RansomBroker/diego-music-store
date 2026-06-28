<?php

namespace App\Actions\Procurement;

use App\Models\PurchaseTransaction;
use App\Models\PurchaseTransactionDetail;
use App\Models\ProductBranchStock;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class PostPurchaseTransaction
{
    /**
     * Execute the action to post a Purchase Transaction.
     *
     * @param  PurchaseTransaction  $pt
     * @return PurchaseTransaction
     */
    public function execute(PurchaseTransaction $pt): PurchaseTransaction
    {
        return DB::transaction(function () use ($pt) {
            if ($pt->status !== 'draft') {
                throw new InvalidArgumentException('Hanya transaksi draf yang dapat diposting.');
            }

            // 1. Generate Journal Number for bookkeeping simulation
            $date = now()->format('Ymd');
            $prefix = 'JV-' . $date . '-';
            $lastJournal = PurchaseTransaction::where('journal_no', 'like', $prefix . '%')
                ->orderBy('journal_no', 'desc')
                ->first();

            if ($lastJournal) {
                $lastNum = intval(substr($lastJournal->journal_no, strlen($prefix)));
                $nextNum = str_pad($lastNum + 1, 4, '0', STR_PAD_LEFT);
            } else {
                $nextNum = '0001';
            }
            $journalNo = $prefix . $nextNum;

            // Update Transaction Header status
            $pt->update([
                'status' => 'posted',
                'posted_at' => now(),
                'journal_no' => $journalNo,
            ]);

            // 2. Process stock changes and weighted average HPP
            $destinationBranchId = $pt->warehouse_id ?? $pt->branch_id;
            $details = $pt->details;

            // Total quantity received in this document to distribute header costs
            $totalQtyReceived = $details->sum('qty_received');
            $headerCostToAttribute = $pt->shipping_cost + $pt->other_cost - $pt->discount;

            foreach ($details as $detail) {
                if ($detail->qty_received <= 0) {
                    continue;
                }

                // Find or create branch stock record
                $stock = ProductBranchStock::firstOrCreate(
                    [
                        'branch_id' => $destinationBranchId,
                        'product_variant_id' => $detail->product_variant_id,
                    ],
                    [
                        'stock' => 0,
                        'hpp' => 0,
                    ]
                );

                $oldStock = $stock->stock;
                $oldHpp = $stock->hpp;

                // Attribute header costs proportionally
                $attributedHeaderCost = ($totalQtyReceived > 0)
                    ? (int) round(($headerCostToAttribute / $totalQtyReceived) * $detail->qty_received)
                    : 0;

                // Net cost for this line item (inclusive of tax & discount + attributed header cost)
                $itemNetCost = $detail->subtotal + $attributedHeaderCost;
                
                // Unit cost for this transaction line
                $unitCost = (int) round($itemNetCost / $detail->qty_received);

                // Compute new weighted average HPP
                $newStockQty = $oldStock + $detail->qty_received;
                
                if ($newStockQty > 0) {
                    $newHpp = (int) round((($oldStock * $oldHpp) + ($detail->qty_received * $unitCost)) / $newStockQty);
                } else {
                    $newHpp = $unitCost;
                }

                // Update stock qty and new calculated HPP
                $stock->update([
                    'stock' => $newStockQty,
                    'hpp' => $newHpp,
                ]);

                // Also sync HPP back to product variant master if destination is Central/Backoffice branch
                $centralBranch = \App\Models\Branch::where('name', 'like', '%Pusat%')
                    ->orWhere('name', 'like', '%Back Office%')
                    ->first();
                if ($centralBranch && $destinationBranchId == $centralBranch->id) {
                    $detail->productVariant->update([
                        'hpp' => $newHpp,
                    ]);
                }
            }

            // 3. Handle debt creation (jika Kredit)
            if ($pt->purchase_type === 'Kredit') {
                $pt->supplier->increment('outstanding_debt', $pt->grand_total);
            }

            // 4. Update parent Purchase Order status if linked
            if ($pt->po_id && ($po = $pt->purchaseOrder)) {
                // Determine if PO is fully received.
                // Sum all qty_received from POSTED transactions for this PO.
                $receivedQuantities = PurchaseTransactionDetail::whereHas('purchaseTransaction', function ($query) use ($po) {
                        $query->where('po_id', $po->id)->where('status', 'posted');
                    })
                    ->groupBy('product_variant_id')
                    ->select('product_variant_id', DB::raw('SUM(qty_received) as total_received'))
                    ->pluck('total_received', 'product_variant_id')
                    ->toArray();

                $isFullyReceived = true;
                foreach ($po->items as $item) {
                    $received = $receivedQuantities[$item->product_variant_id] ?? 0;
                    if ($received < $item->quantity) {
                        $isFullyReceived = false;
                        break;
                    }
                }

                if ($isFullyReceived) {
                    $po->update(['status' => 'closed']);
                }
            }

            return $pt;
        });
    }
}
