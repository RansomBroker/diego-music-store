<?php

namespace App\Actions\Accounting;

use App\Models\Branch;
use App\Models\PurchaseTransaction;
use App\Models\Supplier;
use App\Models\SupplierPayment;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class GenerateVendorLedgerReport
{
    /**
     * Execute Vendor Ledger (Buku Vendor / Kartu Hutang Supplier) calculation.
     *
     * @param  string|null  $fromDate
     * @param  string|null  $toDate
     * @param  int|null  $supplierId
     * @param  int|null  $branchId
     * @param  string|null  $search
     * @return array
     */
    public function execute(
        ?string $fromDate = null,
        ?string $toDate = null,
        ?int $supplierId = null,
        ?int $branchId = null,
        ?string $search = null
    ): array {
        $fromDate = $fromDate ?: now()->startOfMonth()->format('Y-m-d');
        $toDate   = $toDate ?: now()->format('Y-m-d');
        $branch   = $branchId ? Branch::find($branchId) : null;

        // Fetch suppliers
        $supplierQuery = Supplier::query();
        if ($supplierId) {
            $supplierQuery->where('id', $supplierId);
        }
        $suppliers = $supplierQuery->orderBy('name')->get();

        $vendorReports = [];
        $grandTotalBeginning = 0.0;
        $grandTotalAdditions = 0.0;
        $grandTotalPayments  = 0.0;
        $grandTotalEnding    = 0.0;

        foreach ($suppliers as $supplier) {
            // 1. Calculate Beginning AP Balance before $fromDate
            // AP additions prior (posted purchases)
            $priorPurchasesQuery = PurchaseTransaction::where('supplier_id', $supplier->id)
                ->where('status', 'posted')
                ->whereDate('transaction_date', '<', $fromDate);

            if ($branchId) {
                $priorPurchasesQuery->where('branch_id', $branchId);
            }
            $priorPurchasesSum = (float) $priorPurchasesQuery->sum('grand_total');

            // AP payments prior (posted payments)
            $priorPaymentsQuery = SupplierPayment::where('supplier_id', $supplier->id)
                ->where('status', 'posted')
                ->whereDate('payment_date', '<', $fromDate);

            if ($branchId) {
                $priorPaymentsQuery->where('branch_id', $branchId);
            }
            $priorPaymentsSum = (float) $priorPaymentsQuery->sum('total_amount');

            $beginningBalance = $priorPurchasesSum - $priorPaymentsSum;

            // 2. Fetch period purchases & payments within [$fromDate, $toDate]
            $periodPurchasesQuery = PurchaseTransaction::where('supplier_id', $supplier->id)
                ->where('status', 'posted')
                ->whereDate('transaction_date', '>=', $fromDate)
                ->whereDate('transaction_date', '<=', $toDate);

            if ($branchId) {
                $periodPurchasesQuery->where('branch_id', $branchId);
            }

            if ($search) {
                $s = trim($search);
                $periodPurchasesQuery->where(function ($q) use ($s) {
                    $q->where('transaction_no', 'LIKE', "%{$s}%")
                      ->orWhere('invoice_number', 'LIKE', "%{$s}%");
                });
            }

            $periodPurchases = $periodPurchasesQuery->get();

            $periodPaymentsQuery = SupplierPayment::where('supplier_id', $supplier->id)
                ->where('status', 'posted')
                ->whereDate('payment_date', '>=', $fromDate)
                ->whereDate('payment_date', '<=', $toDate);

            if ($branchId) {
                $periodPaymentsQuery->where('branch_id', $branchId);
            }

            if ($search) {
                $s = trim($search);
                $periodPaymentsQuery->where(function ($q) use ($s) {
                    $q->where('payment_no', 'LIKE', "%{$s}%")
                      ->orWhere('payment_reference', 'LIKE', "%{$s}%")
                      ->orWhere('notes', 'LIKE', "%{$s}%");
                });
            }

            $periodPayments = $periodPaymentsQuery->get();

            // Merge transactions chronologically
            $mergedTx = [];

            foreach ($periodPurchases as $p) {
                $mergedTx[] = [
                    'date'        => Carbon::parse($p->transaction_date)->format('Y-m-d'),
                    'raw_date'    => $p->transaction_date,
                    'type'        => 'Pembelian Baru (' . ($p->purchase_type ?: 'Faktur') . ')',
                    'ref_no'      => $p->transaction_no . ($p->invoice_number ? " / No. Inv: {$p->invoice_number}" : ''),
                    'description' => "Pembelian Faktur {$p->transaction_no}",
                    'addition'    => (float) $p->grand_total,
                    'payment'     => 0.0,
                ];
            }

            foreach ($periodPayments as $pay) {
                $mergedTx[] = [
                    'date'        => Carbon::parse($pay->payment_date)->format('Y-m-d'),
                    'raw_date'    => $pay->payment_date,
                    'type'        => 'Pelunasan Hutang (' . ($pay->payment_method ?: 'Kas/Bank') . ')',
                    'ref_no'      => $pay->payment_no . ($pay->payment_reference ? " / Ref: {$pay->payment_reference}" : ''),
                    'description' => $pay->notes ?: "Pembayaran Hutang {$pay->payment_no}",
                    'addition'    => 0.0,
                    'payment'     => (float) $pay->total_amount,
                ];
            }

            // Sort by date asc
            usort($mergedTx, function ($a, $b) {
                return strcmp($a['date'], $b['date']);
            });

            $runningBalance = $beginningBalance;
            $transactions = [];
            $totalAdditions = 0.0;
            $totalPayments  = 0.0;

            foreach ($mergedTx as $tx) {
                $add = $tx['addition'];
                $pay = $tx['payment'];

                $totalAdditions += $add;
                $totalPayments  += $pay;

                $runningBalance += ($add - $pay);

                $transactions[] = [
                    'date'            => $tx['date'],
                    'type'            => $tx['type'],
                    'ref_no'          => $tx['ref_no'],
                    'description'     => $tx['description'],
                    'addition'        => $add,
                    'payment'         => $pay,
                    'running_balance' => $runningBalance,
                ];
            }

            $endingBalance = $runningBalance;

            $grandTotalBeginning += $beginningBalance;
            $grandTotalAdditions += $totalAdditions;
            $grandTotalPayments  += $totalPayments;
            $grandTotalEnding    += $endingBalance;

            $vendorReports[] = [
                'supplier_id'       => $supplier->id,
                'supplier_code'     => $supplier->code ?? "SUP-{$supplier->id}",
                'supplier_name'     => $supplier->name,
                'supplier_phone'    => $supplier->phone ?? '-',
                'beginning_balance' => $beginningBalance,
                'transactions'      => $transactions,
                'total_additions'   => $totalAdditions,
                'total_payments'    => $totalPayments,
                'ending_balance'    => $endingBalance,
            ];
        }

        return [
            'from_date'             => $fromDate,
            'to_date'               => $toDate,
            'supplier_id'           => $supplierId,
            'selected_supplier'     => $supplierId ? Supplier::find($supplierId) : null,
            'branch_id'             => $branchId,
            'branch_name'           => $branch ? $branch->name : 'Semua Cabang',
            'search'                => $search,
            'vendors'               => $vendorReports,
            'grand_total_beginning' => $grandTotalBeginning,
            'grand_total_additions' => $grandTotalAdditions,
            'grand_total_payments'  => $grandTotalPayments,
            'grand_total_ending'    => $grandTotalEnding,
        ];
    }
}
