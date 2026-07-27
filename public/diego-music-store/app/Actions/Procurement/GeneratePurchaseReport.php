<?php

namespace App\Actions\Procurement;

use App\Models\Branch;
use App\Models\PurchaseTransaction;
use App\Models\Supplier;
use Illuminate\Support\Carbon;

class GeneratePurchaseReport
{
    /**
     * Execute Purchase Report (Laporan Pembelian) calculation.
     *
     * @param  string|null  $fromDate
     * @param  string|null  $toDate
     * @param  int|null  $branchId
     * @param  int|null  $supplierId
     * @param  string  $purchaseType ('all', 'Tunai', 'Kredit')
     * @param  string  $paymentStatus ('all', 'paid', 'unpaid', 'partial')
     * @param  string  $mode ('summary', 'detail')
     * @param  string|null  $search
     * @return array
     */
    public function execute(
        ?string $fromDate = null,
        ?string $toDate = null,
        ?int $branchId = null,
        ?int $supplierId = null,
        string $purchaseType = 'all',
        string $paymentStatus = 'all',
        string $mode = 'summary',
        ?string $search = null
    ): array {
        $fromDate = $fromDate ?: now()->startOfMonth()->format('Y-m-d');
        $toDate   = $toDate ?: now()->format('Y-m-d');
        $branch   = $branchId ? Branch::find($branchId) : null;
        $supplier = $supplierId ? Supplier::find($supplierId) : null;

        $query = PurchaseTransaction::with(['supplier', 'branch', 'details.productVariant.product', 'details.unit'])
            ->where('status', 'posted')
            ->whereDate('transaction_date', '>=', $fromDate)
            ->whereDate('transaction_date', '<=', $toDate);

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        if ($supplierId) {
            $query->where('supplier_id', $supplierId);
        }

        if ($purchaseType && $purchaseType !== 'all') {
            $query->where('purchase_type', $purchaseType);
        }

        if ($search) {
            $s = trim($search);
            $query->where(function ($q) use ($s) {
                $q->where('transaction_no', 'LIKE', "%{$s}%")
                  ->orWhere('invoice_number', 'LIKE', "%{$s}%")
                  ->orWhereHas('supplier', function ($sq) use ($s) {
                      $sq->where('name', 'LIKE', "%{$s}%");
                  });
            });
        }

        $purchases = $query->orderBy('transaction_date', 'asc')->orderBy('id', 'asc')->get();

        $filteredPurchases = [];
        $totalTransactions = 0;
        $totalSubtotal = 0.0;
        $totalDiscount = 0.0;
        $totalTax = 0.0;
        $totalShipping = 0.0;
        $totalGrandTotal = 0.0;
        $totalPaid = 0.0;
        $totalUnpaid = 0.0;
        $totalQty = 0;

        foreach ($purchases as $p) {
            $unpaid = $p->getRemainingUnpaidAmount();
            $grand = (float) $p->grand_total;
            $paid = max(0, $grand - $unpaid);

            $statusStr = 'Lunas';
            if ($p->purchase_type === 'Kredit') {
                if ($unpaid <= 0) {
                    $statusStr = 'Lunas';
                } elseif ($paid > 0) {
                    $statusStr = 'Sebagian';
                } else {
                    $statusStr = 'Belum Lunas';
                }
            }

            // Filter payment status if specified
            if ($paymentStatus === 'paid' && $statusStr !== 'Lunas') {
                continue;
            }
            if ($paymentStatus === 'unpaid' && $statusStr !== 'Belum Lunas') {
                continue;
            }
            if ($paymentStatus === 'partial' && $statusStr !== 'Sebagian') {
                continue;
            }

            $txQty = 0;
            $itemRows = [];

            foreach ($p->details as $d) {
                $qty = (int) ($d->qty_received ?: $d->qty_po);
                $txQty += $qty;

                $variantName = $d->productVariant?->name ?? '';
                $productName = $d->productVariant?->product?->name ?? 'Produk';
                $fullName = $variantName ? "{$productName} - {$variantName}" : $productName;
                $sku = $d->productVariant?->sku ?? '-';

                $itemRows[] = [
                    'sku'          => $sku,
                    'product_name' => $fullName,
                    'qty'          => $qty,
                    'unit'         => $d->unit?->name ?? 'Pcs',
                    'price'        => (float) $d->price,
                    'discount'     => (float) $d->discount_value,
                    'subtotal'     => (float) $d->subtotal,
                ];
            }

            $totalTransactions++;
            $totalSubtotal += (float) $p->subtotal;
            $totalDiscount += (float) $p->discount_value;
            $totalTax += (float) $p->tax_amount;
            $totalShipping += (float) $p->shipping_cost;
            $totalGrandTotal += $grand;
            $totalPaid += $paid;
            $totalUnpaid += $unpaid;
            $totalQty += $txQty;

            $filteredPurchases[] = [
                'id'               => $p->id,
                'transaction_no'   => $p->transaction_no,
                'invoice_number'   => $p->invoice_number ?: '-',
                'date'             => Carbon::parse($p->transaction_date)->format('Y-m-d'),
                'due_date'         => $p->due_date ? Carbon::parse($p->due_date)->format('Y-m-d') : '-',
                'supplier_name'    => $p->supplier?->name ?? 'Umum',
                'purchase_type'    => $p->purchase_type ?: 'Tunai',
                'subtotal'         => (float) $p->subtotal,
                'discount'         => (float) $p->discount_value,
                'tax'              => (float) $p->tax_amount,
                'shipping'         => (float) $p->shipping_cost,
                'grand_total'      => $grand,
                'paid_amount'      => $paid,
                'unpaid_amount'    => $unpaid,
                'payment_status'   => $statusStr,
                'total_qty'        => $txQty,
                'items'            => $itemRows,
            ];
        }

        return [
            'from_date'          => $fromDate,
            'to_date'            => $toDate,
            'branch_id'          => $branchId,
            'branch_name'        => $branch ? $branch->name : 'Semua Cabang',
            'supplier_id'        => $supplierId,
            'supplier_name'      => $supplier ? $supplier->name : 'Semua Supplier',
            'purchase_type'      => $purchaseType,
            'payment_status'     => $paymentStatus,
            'mode'               => $mode,
            'search'             => $search,
            'purchases'          => $filteredPurchases,
            'total_transactions' => $totalTransactions,
            'total_qty'          => $totalQty,
            'total_subtotal'     => $totalSubtotal,
            'total_discount'     => $totalDiscount,
            'total_tax'          => $totalTax,
            'total_shipping'     => $totalShipping,
            'total_grand_total'  => $totalGrandTotal,
            'total_paid'         => $totalPaid,
            'total_unpaid'       => $totalUnpaid,
        ];
    }
}
