<?php

namespace App\Actions\Procurement;

use App\Models\Branch;
use App\Models\PurchaseTransaction;
use App\Models\Supplier;
use Illuminate\Support\Carbon;

class GenerateAccountsPayableReport
{
    /**
     * Execute Accounts Payable (Laporan Hutang Usaha) calculation.
     *
     * @param  string|null  $asOfDate
     * @param  int|null  $branchId
     * @param  int|null  $supplierId
     * @param  string  $overdueFilter ('all', 'current', 'overdue')
     * @param  string  $mode ('detail_invoice', 'summary_supplier', 'aging')
     * @param  string|null  $search
     * @return array
     */
    public function execute(
        ?string $asOfDate = null,
        ?int $branchId = null,
        ?int $supplierId = null,
        string $overdueFilter = 'all',
        string $mode = 'detail_invoice',
        ?string $search = null
    ): array {
        $asOfDate = $asOfDate ?: now()->format('Y-m-d');
        $branch   = $branchId ? Branch::find($branchId) : null;
        $supplier = $supplierId ? Supplier::find($supplierId) : null;

        $query = PurchaseTransaction::with(['supplier', 'branch', 'supplierPaymentItems.supplierPayment'])
            ->where('status', 'posted')
            ->where('purchase_type', 'Kredit')
            ->whereDate('transaction_date', '<=', $asOfDate);

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        if ($supplierId) {
            $query->where('supplier_id', $supplierId);
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

        $creditPurchases = $query->orderBy('due_date', 'asc')->orderBy('transaction_date', 'asc')->get();

        $invoices = [];
        $supplierSummaries = [];

        $totalInvoices = 0;
        $totalGrandTotal = 0.0;
        $totalPaid = 0.0;
        $totalUnpaid = 0.0;

        $totalCurrent = 0.0;
        $totalOverdue = 0.0;

        $agingBuckets = [
            'current'      => 0.0, // Belum Jatuh Tempo
            'aging_1_30'   => 0.0, // 1-30 Hari
            'aging_31_60'  => 0.0, // 31-60 Hari
            'aging_61_90'  => 0.0, // 61-90 Hari
            'aging_90_plus' => 0.0, // > 90 Hari
        ];

        $asOfCarbon = Carbon::parse($asOfDate)->startOfDay();

        foreach ($creditPurchases as $pt) {
            $grand = (float) $pt->grand_total;

            // Sum payments made on or before $asOfDate
            $paid = (float) $pt->supplierPaymentItems
                ->filter(function ($item) use ($asOfDate) {
                    $pay = $item->supplierPayment;
                    return $pay && $pay->status === 'posted' && $pay->payment_date <= $asOfDate;
                })
                ->sum('amount_paid');

            $unpaid = max(0, $grand - $paid);

            // Skip if fully paid on or before $asOfDate
            if ($unpaid <= 0.01) {
                continue;
            }

            // Calculate overdue days
            $dueDate = $pt->due_date ? Carbon::parse($pt->due_date)->startOfDay() : Carbon::parse($pt->transaction_date)->startOfDay();
            $isOverdue = $asOfCarbon->greaterThan($dueDate);
            $overdueDays = $isOverdue ? (int) $dueDate->diffInDays($asOfCarbon) : 0;

            // Apply overdue filter
            if ($overdueFilter === 'current' && $isOverdue) {
                continue;
            }
            if ($overdueFilter === 'overdue' && !$isOverdue) {
                continue;
            }

            // Assign aging bucket
            $bucketKey = 'current';
            if ($isOverdue) {
                if ($overdueDays <= 30) {
                    $bucketKey = 'aging_1_30';
                } elseif ($overdueDays <= 60) {
                    $bucketKey = 'aging_31_60';
                } elseif ($overdueDays <= 90) {
                    $bucketKey = 'aging_61_90';
                } else {
                    $bucketKey = 'aging_90_plus';
                }
            }

            $agingBuckets[$bucketKey] += $unpaid;

            if ($isOverdue) {
                $totalOverdue += $unpaid;
            } else {
                $totalCurrent += $unpaid;
            }

            $totalInvoices++;
            $totalGrandTotal += $grand;
            $totalPaid += $paid;
            $totalUnpaid += $unpaid;

            $invData = [
                'id'             => $pt->id,
                'transaction_no' => $pt->transaction_no,
                'invoice_number' => $pt->invoice_number ?: '-',
                'date'           => Carbon::parse($pt->transaction_date)->format('Y-m-d'),
                'due_date'       => $dueDate->format('Y-m-d'),
                'supplier_id'    => $pt->supplier_id,
                'supplier_name'  => $pt->supplier?->name ?? 'Umum',
                'supplier_phone' => $pt->supplier?->phone ?? '-',
                'grand_total'    => $grand,
                'paid_amount'    => $paid,
                'unpaid_amount'  => $unpaid,
                'is_overdue'     => $isOverdue,
                'overdue_days'   => $overdueDays,
                'bucket'         => $bucketKey,
            ];

            $invoices[] = $invData;

            // Supplier Summary Grouping
            $supId = $pt->supplier_id;
            if (!isset($supplierSummaries[$supId])) {
                $supplierSummaries[$supId] = [
                    'supplier_id'    => $supId,
                    'supplier_name'  => $pt->supplier?->name ?? 'Umum',
                    'supplier_phone' => $pt->supplier?->phone ?? '-',
                    'count_invoices' => 0,
                    'grand_total'    => 0.0,
                    'paid_amount'    => 0.0,
                    'unpaid_amount'  => 0.0,
                    'current'        => 0.0,
                    'aging_1_30'     => 0.0,
                    'aging_31_60'    => 0.0,
                    'aging_61_90'    => 0.0,
                    'aging_90_plus'  => 0.0,
                ];
            }

            $supplierSummaries[$supId]['count_invoices']++;
            $supplierSummaries[$supId]['grand_total']   += $grand;
            $supplierSummaries[$supId]['paid_amount']   += $paid;
            $supplierSummaries[$supId]['unpaid_amount'] += $unpaid;
            $supplierSummaries[$supId][$bucketKey]       += $unpaid;
        }

        // Sort supplier summaries by name
        usort($supplierSummaries, function ($a, $b) {
            return strcmp($a['supplier_name'], $b['supplier_name']);
        });

        return [
            'as_of_date'         => $asOfDate,
            'branch_id'          => $branchId,
            'branch_name'        => $branch ? $branch->name : 'Semua Cabang',
            'supplier_id'        => $supplierId,
            'supplier_name'      => $supplier ? $supplier->name : 'Semua Supplier',
            'overdue_filter'     => $overdueFilter,
            'mode'               => $mode,
            'search'             => $search,
            'invoices'           => $invoices,
            'suppliers'          => array_values($supplierSummaries),
            'total_invoices'     => $totalInvoices,
            'total_grand_total'  => $totalGrandTotal,
            'total_paid'         => $totalPaid,
            'total_unpaid'       => $totalUnpaid,
            'total_current'      => $totalCurrent,
            'total_overdue'      => $totalOverdue,
            'aging_buckets'      => $agingBuckets,
        ];
    }
}
