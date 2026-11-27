<?php

namespace App\Actions\Procurement;

use App\Models\JournalEntry;
use App\Models\JournalItem;
use App\Models\ProductBranchStock;
use App\Models\PurchaseOrder;
use App\Models\PurchaseTransaction;
use App\Models\PurchaseTransactionDetail;
use App\Models\StockMovement;
use InvalidArgumentException;

class RevertPurchaseTransactionPosting
{
    /**
     * Validasi integritas dan batalkan (rollback) efek posting transaksi pembelian:
     * stok cabang, kartu stok, jurnal akuntansi, serta hutang supplier.
     */
    public function execute(PurchaseTransaction $pt): void
    {
        if ($pt->status !== 'posted') {
            return;
        }

        if ($pt->supplierPaymentItems()->exists()) {
            throw new InvalidArgumentException(
                'Transaksi pembelian ini sudah memiliki pembayaran hutang supplier. Hapus pembayaran supplier terkait terlebih dahulu.'
            );
        }

        if ($pt->purchaseReturns()->exists()) {
            throw new InvalidArgumentException(
                'Transaksi pembelian ini sudah memiliki retur pembelian. Hapus retur pembelian terkait terlebih dahulu.'
            );
        }

        // 1. Rollback stok cabang & hapus mutasi stok terkait transaksi ini
        $movements = StockMovement::query()
            ->where('reference_type', 'Purchase')
            ->where('reference_id', $pt->id)
            ->get();

        foreach ($movements as $movement) {
            $branchStock = ProductBranchStock::query()
                ->where('product_variant_id', $movement->product_variant_id)
                ->where('branch_id', $movement->branch_id)
                ->first();

            if ($branchStock) {
                $delta = $movement->type === 'in' ? -$movement->quantity : $movement->quantity;
                $branchStock->update([
                    'stock' => max(0, (int) $branchStock->stock + $delta),
                ]);
            }
        }

        if ($movements->isNotEmpty()) {
            StockMovement::whereIn('id', $movements->pluck('id'))->delete();
        }

        // 2. Rollback hutang supplier jika transaksi berjenis Kredit / Konsinyasi
        if ($pt->isPayableType() && $pt->supplier) {
            $netDebt = max(0, (int) $pt->grand_total - (int) ($pt->down_payment_amount ?? 0));
            if ($netDebt > 0) {
                $pt->supplier->update([
                    'outstanding_debt' => max(0, (float) $pt->supplier->outstanding_debt - $netDebt),
                ]);
            }
        }

        // 3. Hapus jurnal akuntansi terkait
        $journalEntryId = $pt->journal_entry_id;
        $journalNo = $pt->journal_no;

        $pt->update([
            'status' => 'draft',
            'posted_at' => null,
            'journal_no' => null,
            'journal_entry_id' => null,
        ]);

        $journalIds = JournalEntry::query()
            ->where(function ($query) use ($pt, $journalEntryId, $journalNo): void {
                $query->where(function ($q) use ($pt): void {
                    $q->where('reference_type', 'Purchase')
                        ->where('reference_id', $pt->id);
                });

                if ($journalEntryId) {
                    $query->orWhere('id', $journalEntryId);
                }

                if ($journalNo) {
                    $query->orWhere('entry_no', $journalNo);
                }
            })
            ->pluck('id')
            ->all();

        if (! empty($journalIds)) {
            JournalItem::whereIn('journal_entry_id', $journalIds)->delete();
            JournalEntry::whereIn('id', $journalIds)->delete();
        }
    }

    /**
     * Evaluasi ulang status Purchase Order: buka kembali ke 'approved' jika sebelumnya 'closed'
     * namun total barang diterima dari transaksi posted tidak lagi memenuhi kuantitas PO.
     */
    public function syncPurchaseOrderStatus(?int $poId): void
    {
        if (! $poId) {
            return;
        }

        $po = PurchaseOrder::with(['items.unit'])->find($poId);
        if (! $po || $po->status !== 'closed') {
            return;
        }

        $receivedDetails = PurchaseTransactionDetail::with('unit')
            ->whereHas('purchaseTransaction', function ($query) use ($po): void {
                $query->where('po_id', $po->id)->where('status', 'posted');
            })
            ->get();

        $receivedBaseQuantities = [];
        foreach ($receivedDetails as $recDetail) {
            $cFactor = ($recDetail->unit && $recDetail->unit->base_unit_id) ? $recDetail->unit->conversion_factor : 1;
            $recBaseQty = $recDetail->qty_received * $cFactor;
            $receivedBaseQuantities[$recDetail->product_variant_id] = ($receivedBaseQuantities[$recDetail->product_variant_id] ?? 0) + $recBaseQty;
        }

        foreach ($po->items as $item) {
            $itemCFactor = ($item->unit && $item->unit->base_unit_id) ? $item->unit->conversion_factor : 1;
            $poItemBaseQty = $item->quantity * $itemCFactor;
            $receivedBase = $receivedBaseQuantities[$item->product_variant_id] ?? 0;

            if ($receivedBase < $poItemBaseQty) {
                $po->update(['status' => 'approved']);

                return;
            }
        }
    }
}

