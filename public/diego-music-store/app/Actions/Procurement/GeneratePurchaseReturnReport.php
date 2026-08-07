<?php

namespace App\Actions\Procurement;

use App\Models\PurchaseReturn;
use App\Models\Branch;
use App\Models\Supplier;
use Illuminate\Support\Carbon;

class GeneratePurchaseReturnReport
{
    /**
     * Generate data array for Purchase Return Report.
     *
     * @param string $fromDate
     * @param string $toDate
     * @param int|null $branchId
     * @param int|null $supplierId
     * @param string $status
     * @param string $mode
     * @param string|null $search
     * @return array
     */
    public function execute(
        string $fromDate,
        string $toDate,
        ?int $branchId = null,
        ?int $supplierId = null,
        string $status = 'all',
        string $mode = 'summary',
        ?string $search = null
    ): array {
        $query = PurchaseReturn::with([
            'supplier',
            'branch',
            'purchaseTransaction',
            'items.productVariant.product',
            'creator',
        ])
        ->whereDate('return_date', '>=', $fromDate)
        ->whereDate('return_date', '<=', $toDate);

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        if ($supplierId) {
            $query->where('supplier_id', $supplierId);
        }

        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('return_no', 'like', "%{$search}%")
                  ->orWhere('reason', 'like', "%{$search}%")
                  ->orWhereHas('supplier', fn ($sq) => $sq->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('purchaseTransaction', fn ($pq) => $pq->where('transaction_no', 'like', "%{$search}%"));
            });
        }

        $returns = $query->orderBy('return_date', 'desc')->orderBy('id', 'desc')->get();

        $formattedReturns = [];
        $totalReturnAmount = 0;
        $totalQtyReturned = 0;

        foreach ($returns as $ret) {
            $items = [];
            $retQty = 0;

            foreach ($ret->items as $item) {
                $variant = $item->productVariant;
                $product = $variant?->product;
                $items[] = [
                    'sku'          => $variant?->sku ?: '-',
                    'product_name' => ($product?->name ?? 'Produk') . ($variant?->name && $variant->name !== 'Standard' ? " ({$variant->name})" : ''),
                    'qty'          => (int) $item->quantity,
                    'unit_price'   => (float) $item->unit_price,
                    'total_price'  => (float) $item->total_price,
                ];
                $retQty += (int) $item->quantity;
            }

            $totalReturnAmount += (float) $ret->total_amount;
            $totalQtyReturned += $retQty;

            $formattedReturns[] = [
                'id'             => $ret->id,
                'return_no'      => $ret->return_no,
                'transaction_no' => $ret->purchaseTransaction?->transaction_no ?: '-',
                'return_date'    => Carbon::parse($ret->return_date)->format('d/m/Y'),
                'branch_name'    => $ret->branch?->name ?: '-',
                'supplier_name'  => $ret->supplier?->name ?: 'Umum',
                'status'         => $ret->status,
                'status_label'   => $ret->status === 'posted' ? 'Posted (Selesai)' : 'Draft',
                'total_amount'   => (float) $ret->total_amount,
                'reason'         => $ret->reason ?: '-',
                'created_by'     => $ret->creator?->name ?: '-',
                'total_qty'      => $retQty,
                'items'          => $items,
            ];
        }

        $branchName = $branchId ? (Branch::find($branchId)?->name ?: 'Cabang Unknown') : 'Semua Cabang (Konsolidasi)';
        $supplierName = $supplierId ? (Supplier::find($supplierId)?->name ?: 'Supplier Unknown') : 'Semua Supplier';

        return [
            'from_date'          => Carbon::parse($fromDate)->format('d/m/Y'),
            'to_date'            => Carbon::parse($toDate)->format('d/m/Y'),
            'branch_name'        => $branchName,
            'supplier_name'      => $supplierName,
            'status'             => $status,
            'mode'               => $mode,
            'search'             => $search,
            'returns'            => $formattedReturns,
            'total_transactions' => count($formattedReturns),
            'total_return_amount' => $totalReturnAmount,
            'total_qty_returned' => $totalQtyReturned,
        ];
    }
}
