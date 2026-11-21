<?php

namespace App\Actions\Procurement;

use App\Helpers\BranchHelper;
use App\Helpers\PermissionHelper;
use App\Models\JournalEntry;
use App\Models\JournalItem;
use App\Models\ProductBranchStock;
use App\Models\PurchaseOrder;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;
use App\Models\PurchaseTransaction;
use App\Models\PurchaseTransactionDetail;
use App\Models\StockMovement;
use App\Models\Supplier;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ResetPurchaseTransactions
{
    /**
     * Hapus semua transaksi pembelian pada cabang target beserta jurnal dan efek stok/hutang terkait.
     *
     * @return array{deleted_transactions: int, deleted_returns: int, deleted_journals: int, deleted_stock_movements: int}
     */
    public function execute(?int $branchId = null): array
    {
        PermissionHelper::enforceActionPermission(Auth::user(), 'bo.purchase_transactions.delete');

        $targetBranchId = $branchId ?? BranchHelper::getActiveBranchId();

        return DB::transaction(function () use ($targetBranchId): array {
            $transactions = PurchaseTransaction::where('branch_id', $targetBranchId)
                ->get([
                    'id',
                    'po_id',
                    'supplier_id',
                    'purchase_type',
                    'status',
                    'grand_total',
                    'down_payment_amount',
                    'journal_no',
                ]);

            $ptIds = $transactions->pluck('id')->all();
            $journalNos = $transactions->pluck('journal_no')->filter()->unique()->values()->all();

            if (! empty($ptIds)) {
                $hasSupplierPayments = DB::table('supplier_payment_items')
                    ->whereIn('purchase_transaction_id', $ptIds)
                    ->exists();

                if ($hasSupplierPayments) {
                    throw new RuntimeException(
                        'Tidak dapat mereset transaksi pembelian karena masih terdapat Pembayaran Hutang Supplier yang terkait. Hapus pembayaran supplier terkait terlebih dahulu.'
                    );
                }
            }

            $returnIds = empty($ptIds)
                ? []
                : PurchaseReturn::whereIn('purchase_transaction_id', $ptIds)->pluck('id')->all();

            // 1. Kumpulkan & hapus mutasi stok terkait transaksi pembelian dan retur pembeliannya
            $stockMovementsQuery = StockMovement::query()->where(function ($query) use ($ptIds, $returnIds): void {
                $hasCondition = false;

                if (! empty($ptIds)) {
                    $query->where(function ($q) use ($ptIds): void {
                        $q->where('reference_type', 'Purchase')
                            ->whereIn('reference_id', $ptIds);
                    });
                    $hasCondition = true;
                }

                if (! empty($returnIds)) {
                    $method = $hasCondition ? 'orWhere' : 'where';
                    $query->{$method}(function ($q) use ($returnIds): void {
                        $q->whereIn('reference_type', ['PurchaseReturn', 'PurchaseReturnReplacement'])
                            ->whereIn('reference_id', $returnIds);
                    });
                }
            });

            $deletedStockMovements = 0;
            if (! empty($ptIds) || ! empty($returnIds)) {
                $movements = $stockMovementsQuery->get();

                foreach ($movements as $movement) {
                    $branchStock = ProductBranchStock::where('product_variant_id', $movement->product_variant_id)
                        ->where('branch_id', $movement->branch_id)
                        ->first();

                    if ($branchStock) {
                        $delta = $movement->type === 'in' ? -$movement->quantity : $movement->quantity;
                        $branchStock->update([
                            'stock' => max(0, (int) $branchStock->stock + $delta),
                        ]);
                    }
                }

                $deletedStockMovements = StockMovement::whereIn('id', $movements->pluck('id'))->delete();
            }

            // 2. Kumpulkan & hapus seluruh jurnal terkait (Purchase, entry_no, InitialDebt cabang, dan PurchaseReturn)
            $journalQuery = JournalEntry::query()->where(function ($query) use ($targetBranchId, $ptIds, $journalNos, $returnIds): void {
                $query->where(function ($q) use ($targetBranchId): void {
                    $q->where('branch_id', $targetBranchId)
                        ->where('reference_type', 'InitialDebt');
                });

                if (! empty($ptIds)) {
                    $query->orWhere(function ($q) use ($ptIds): void {
                        $q->where('reference_type', 'Purchase')
                            ->whereIn('reference_id', $ptIds);
                    });
                }

                if (! empty($journalNos)) {
                    $query->orWhereIn('entry_no', $journalNos);
                }

                if (! empty($returnIds)) {
                    $query->orWhere(function ($q) use ($returnIds): void {
                        $q->where('reference_type', 'PurchaseReturn')
                            ->whereIn('reference_id', $returnIds);
                    });
                }
            });

            $journalIds = $journalQuery->pluck('id')->all();
            $deletedJournals = 0;

            if (! empty($journalIds)) {
                JournalItem::whereIn('journal_entry_id', $journalIds)->delete();
                $deletedJournals = JournalEntry::whereIn('id', $journalIds)->delete();
            }

            if (empty($ptIds)) {
                return [
                    'deleted_transactions' => 0,
                    'deleted_returns' => 0,
                    'deleted_journals' => $deletedJournals,
                    'deleted_stock_movements' => 0,
                ];
            }

            // 3. Kembalikan outstanding_debt supplier dari transaksi kredit yang sudah diposting
            $debtReductionsBySupplier = [];
            foreach ($transactions as $transaction) {
                if ($transaction->status === 'posted' && $transaction->purchase_type === 'Kredit' && $transaction->supplier_id) {
                    $debtAmount = max(0, (int) $transaction->grand_total - (int) ($transaction->down_payment_amount ?? 0));
                    if ($debtAmount > 0) {
                        $debtReductionsBySupplier[$transaction->supplier_id] = ($debtReductionsBySupplier[$transaction->supplier_id] ?? 0) + $debtAmount;
                    }
                }
            }

            if (! empty($debtReductionsBySupplier)) {
                $suppliers = Supplier::whereIn('id', array_keys($debtReductionsBySupplier))->get();
                foreach ($suppliers as $supplier) {
                    $reduction = $debtReductionsBySupplier[$supplier->id] ?? 0;
                    if ($reduction > 0) {
                        $supplier->update([
                            'outstanding_debt' => max(0, (float) $supplier->outstanding_debt - $reduction),
                        ]);
                    }
                }
            }

            // 4. Hapus retur pembelian, detail pembelian, dan header transaksi pembelian
            $deletedReturns = 0;
            if (! empty($returnIds)) {
                PurchaseReturnItem::whereIn('purchase_return_id', $returnIds)->delete();
                $deletedReturns = PurchaseReturn::whereIn('id', $returnIds)->delete();
            }

            PurchaseTransactionDetail::whereIn('purchase_transaction_id', $ptIds)->delete();
            $deletedTransactions = PurchaseTransaction::whereIn('id', $ptIds)->delete();

            // 5. Buka kembali status PO yang sebelumnya tertutup oleh transaksi yang dihapus
            $poIds = $transactions->pluck('po_id')->filter()->unique()->values()->all();
            if (! empty($poIds)) {
                PurchaseOrder::whereIn('id', $poIds)
                    ->where('status', 'closed')
                    ->whereDoesntHave('purchaseTransactions')
                    ->update(['status' => 'approved']);
            }

            return [
                'deleted_transactions' => $deletedTransactions,
                'deleted_returns' => $deletedReturns,
                'deleted_journals' => $deletedJournals,
                'deleted_stock_movements' => $deletedStockMovements,
            ];
        });
    }
}

