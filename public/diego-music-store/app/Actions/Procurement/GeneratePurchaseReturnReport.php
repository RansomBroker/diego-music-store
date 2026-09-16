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
     * @param string $returnType
     * @return array
     */
    public function execute(
        string $fromDate,
        string $toDate,
        ?int $branchId = null,
        ?int $supplierId = null,
        string $status = 'all',
        string $mode = 'ledger',
        ?string $search = null,
        string $returnType = 'all'
    ): array {
        $query = PurchaseReturn::with([
            'supplier',
            'branch',
            'purchaseTransaction',
            'refundAccount',
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

        if ($returnType && $returnType !== 'all') {
            $query->where('return_type', $returnType);
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('return_no', 'like', "%{$search}%")
                  ->orWhere('reason', 'like', "%{$search}%")
                  ->orWhereHas('supplier', fn ($sq) => $sq->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('purchaseTransaction', fn ($pq) => $pq->where('transaction_no', 'like', "%{$search}%"))
                  ->orWhereHas('items.productVariant.product', fn ($prq) => $prq->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('items.productVariant', fn ($vq) => $vq->where('sku', 'like', "%{$search}%"));
            });
        }

        $returns = $query->orderBy('return_date', 'asc')->orderBy('id', 'asc')->get();

        $formattedReturns = [];
        $totalReturnAmount = 0;
        $totalQtyReturned = 0;

        $typeLabels = [
            'replacement'       => 'Tukar Guling',
            'invoice_deduction' => 'Penyesuaian Faktur',
            'refund'            => 'Refund Kas/Bank',
            'supplier_credit'   => 'Saldo Deposit',
        ];

        $breakdownByType = [
            'replacement' => [
                'label' => 'Tukar Guling',
                'count' => 0,
                'qty' => 0,
                'amount' => 0,
                'badge_color' => 'amber',
            ],
            'invoice_deduction' => [
                'label' => 'Penyesuaian Faktur',
                'count' => 0,
                'qty' => 0,
                'amount' => 0,
                'badge_color' => 'blue',
            ],
            'refund' => [
                'label' => 'Refund Kas/Bank',
                'count' => 0,
                'qty' => 0,
                'amount' => 0,
                'badge_color' => 'emerald',
            ],
            'supplier_credit' => [
                'label' => 'Saldo Deposit',
                'count' => 0,
                'qty' => 0,
                'amount' => 0,
                'badge_color' => 'purple',
            ],
        ];

        // Mapping per supplier for buku besar ledger view
        $suppliersMap = [];

        foreach ($returns as $ret) {
            $items = [];
            $retQty = 0;

            foreach ($ret->items as $item) {
                $variant = $item->productVariant;
                $product = $variant?->product;
                $sku = $variant?->sku ?: '-';
                $productName = ($product?->name ?? 'Produk') . ($variant?->name && $variant->name !== 'Standard' ? " ({$variant->name})" : '');
                $qty = (int) $item->quantity;
                $unitPrice = (float) $item->unit_price;
                $totalPrice = (float) $item->total_price;

                $items[] = [
                    'id'           => $item->id,
                    'variant_id'   => $item->product_variant_id,
                    'sku'          => $sku,
                    'product_name' => $productName,
                    'qty'          => $qty,
                    'unit_price'   => $unitPrice,
                    'total_price'  => $totalPrice,
                ];

                $retQty += $qty;
            }

            $rawType = $ret->return_type ?: 'replacement';
            $typeLabel = $typeLabels[$rawType] ?? ucfirst(str_replace('_', ' ', $rawType));
            $refundAccountName = $ret->refundAccount ? "{$ret->refundAccount->code} - {$ret->refundAccount->name}" : null;

            $totalReturnAmount += (float) $ret->total_amount;
            $totalQtyReturned += $retQty;

            // Global breakdown counters
            if (isset($breakdownByType[$rawType])) {
                $breakdownByType[$rawType]['count']++;
                $breakdownByType[$rawType]['qty'] += $retQty;
                $breakdownByType[$rawType]['amount'] += (float) $ret->total_amount;
            }

            $returnEntry = [
                'id'                  => $ret->id,
                'return_no'           => $ret->return_no,
                'transaction_no'      => $ret->purchaseTransaction?->transaction_no ?: '-',
                'return_date'         => Carbon::parse($ret->return_date)->format('d/m/Y'),
                'return_date_raw'     => $ret->return_date ? Carbon::parse($ret->return_date)->format('Y-m-d') : '',
                'branch_name'         => $ret->branch?->name ?: '-',
                'supplier_id'         => $ret->supplier_id,
                'supplier_name'       => $ret->supplier?->name ?: 'Umum',
                'status'              => $ret->status,
                'status_label'        => $ret->status === 'posted' ? 'Posted' : 'Draft',
                'return_type'         => $rawType,
                'return_type_label'   => $typeLabel,
                'refund_account_name' => $refundAccountName,
                'total_amount'        => (float) $ret->total_amount,
                'reason'              => $ret->reason ?: '-',
                'created_by'          => $ret->creator?->name ?: '-',
                'total_qty'           => $retQty,
                'items'               => $items,
            ];

            $formattedReturns[] = $returnEntry;

            // Map under each supplier (Buku Besar per Supplier)
            $sId = $ret->supplier_id ?: 0;
            if (!isset($suppliersMap[$sId])) {
                $supplierObj = $ret->supplier;
                $suppliersMap[$sId] = [
                    'supplier_id'         => $sId,
                    'supplier_name'       => $supplierObj?->name ?: 'Supplier Tanpa Nama / Umum',
                    'contact_person'      => $supplierObj?->contact_person ?: '-',
                    'phone'               => $supplierObj?->phone ?: '-',
                    'total_transactions'  => 0,
                    'total_qty_returned'  => 0,
                    'total_return_amount' => 0,
                    'total_amount'        => 0,
                    'by_type'             => [
                        'replacement'       => ['count' => 0, 'qty' => 0, 'amount' => 0],
                        'invoice_deduction' => ['count' => 0, 'qty' => 0, 'amount' => 0],
                        'refund'            => ['count' => 0, 'qty' => 0, 'amount' => 0],
                        'supplier_credit'   => ['count' => 0, 'qty' => 0, 'amount' => 0],
                    ],
                    'entries'             => [],
                    'items_history'       => [],
                    'returns'             => [],
                ];
            }

            $suppliersMap[$sId]['total_transactions']++;
            $suppliersMap[$sId]['total_qty_returned'] += $retQty;
            $suppliersMap[$sId]['total_return_amount'] += (float) $ret->total_amount;
            $suppliersMap[$sId]['total_amount'] += (float) $ret->total_amount;

            if (isset($suppliersMap[$sId]['by_type'][$rawType])) {
                $suppliersMap[$sId]['by_type'][$rawType]['count']++;
                $suppliersMap[$sId]['by_type'][$rawType]['qty'] += $retQty;
                $suppliersMap[$sId]['by_type'][$rawType]['amount'] += (float) $ret->total_amount;
            }

            $suppliersMap[$sId]['entries'][] = $returnEntry;
            $suppliersMap[$sId]['returns'][] = $returnEntry;

            // Group items history for this supplier
            foreach ($items as $item) {
                $itemKey = $item['sku'] !== '-' ? $item['sku'] : $item['product_name'];
                if (!isset($suppliersMap[$sId]['items_history'][$itemKey])) {
                    $suppliersMap[$sId]['items_history'][$itemKey] = [
                        'sku'               => $item['sku'],
                        'product_name'      => $item['product_name'],
                        'total_qty'         => 0,
                        'total_amount'      => 0,
                        'last_unit_price'   => $item['unit_price'],
                        'last_return_date'  => $returnEntry['return_date'],
                        'last_return_no'    => $ret->return_no,
                        'last_return_type'  => $typeLabel,
                    ];
                }
                $suppliersMap[$sId]['items_history'][$itemKey]['total_qty'] += $item['qty'];
                $suppliersMap[$sId]['items_history'][$itemKey]['total_amount'] += $item['total_price'];
                $suppliersMap[$sId]['items_history'][$itemKey]['last_unit_price'] = $item['unit_price'];
            }
        }

        // Convert associative maps to indexed arrays
        $supplierLedgers = [];
        foreach ($suppliersMap as &$sData) {
            $sData['items_history'] = array_values($sData['items_history']);
            usort($sData['items_history'], fn($a, $b) => $b['total_qty'] <=> $a['total_qty']);
            $supplierLedgers[] = $sData;
        }
        unset($sData);

        // Sort supplier ledgers by supplier_name ascending for clean ledger browsing
        usort($supplierLedgers, fn($a, $b) => strcmp($a['supplier_name'], $b['supplier_name']));

        // Descending order for transactions in summary/detail
        $formattedReturnsDesc = array_reverse($formattedReturns);

        $branchName = $branchId ? (Branch::find($branchId)?->name ?: 'Cabang Unknown') : 'Semua Cabang (Konsolidasi)';
        $selectedSupplier = $supplierId ? Supplier::find($supplierId) : null;
        $supplierName = $selectedSupplier ? $selectedSupplier->name : 'Semua Supplier';

        return [
            'from_date'           => Carbon::parse($fromDate)->format('d/m/Y'),
            'to_date'             => Carbon::parse($toDate)->format('d/m/Y'),
            'branch_name'         => $branchName,
            'supplier_name'       => $supplierName,
            'selected_supplier'   => $selectedSupplier,
            'status'              => $status,
            'return_type'         => $returnType,
            'return_type_label'   => $returnType !== 'all' ? ($typeLabels[$returnType] ?? ucfirst($returnType)) : 'Semua Metode',
            'mode'                => $mode,
            'search'              => $search,
            'returns'             => $formattedReturnsDesc,
            'supplier_ledgers'    => $supplierLedgers,
            'supplier_history'    => $supplierLedgers, // alias for backwards compatibility
            'total_suppliers'     => count($supplierLedgers),
            'total_transactions'  => count($formattedReturns),
            'total_return_amount' => $totalReturnAmount,
            'total_qty_returned'  => $totalQtyReturned,
            'breakdown_by_type'   => $breakdownByType,
        ];
    }
}
