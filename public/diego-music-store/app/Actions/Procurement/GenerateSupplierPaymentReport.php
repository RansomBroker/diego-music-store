<?php

namespace App\Actions\Procurement;

use App\Models\Account;
use App\Models\Branch;
use App\Models\Supplier;
use App\Models\SupplierPayment;
use Illuminate\Support\Carbon;

class GenerateSupplierPaymentReport
{
    /**
     * Execute Supplier Payment Settlement Report (Laporan Pelunasan Hutang Supplier) calculation.
     *
     * @param  string|null  $fromDate
     * @param  string|null  $toDate
     * @param  int|null  $branchId
     * @param  int|null  $supplierId
     * @param  int|null  $accountId
     * @param  string  $mode ('summary', 'detail')
     * @param  string|null  $search
     * @return array
     */
    public function execute(
        ?string $fromDate = null,
        ?string $toDate = null,
        ?int $branchId = null,
        ?int $supplierId = null,
        ?int $accountId = null,
        string $mode = 'summary',
        ?string $search = null
    ): array {
        $fromDate = $fromDate ?: now()->startOfMonth()->format('Y-m-d');
        $toDate   = $toDate ?: now()->format('Y-m-d');
        $branch   = $branchId ? Branch::find($branchId) : null;
        $supplier = $supplierId ? Supplier::find($supplierId) : null;
        $account  = $accountId ? Account::find($accountId) : null;

        $query = SupplierPayment::with(['supplier', 'branch', 'account', 'items.purchaseTransaction'])
            ->where('status', 'posted')
            ->whereDate('payment_date', '>=', $fromDate)
            ->whereDate('payment_date', '<=', $toDate);

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        if ($supplierId) {
            $query->where('supplier_id', $supplierId);
        }

        if ($accountId) {
            $query->where('account_id', $accountId);
        }

        if ($search) {
            $s = trim($search);
            $query->where(function ($q) use ($s) {
                $q->where('payment_no', 'LIKE', "%{$s}%")
                  ->orWhere('payment_reference', 'LIKE', "%{$s}%")
                  ->orWhere('notes', 'LIKE', "%{$s}%")
                  ->orWhereHas('supplier', function ($sq) use ($s) {
                      $sq->where('name', 'LIKE', "%{$s}%");
                  });
            });
        }

        $payments = $query->orderBy('payment_date', 'asc')->orderBy('id', 'asc')->get();

        $processedPayments = [];
        $uniqueSuppliers = [];

        $totalPaymentsCount = 0;
        $totalAmountPaid    = 0.0;
        $totalInvoicesPaid  = 0;

        foreach ($payments as $pay) {
            $totalPaymentsCount++;
            $amount = (float) $pay->total_amount;
            $totalAmountPaid += $amount;

            if ($pay->supplier_id) {
                $uniqueSuppliers[$pay->supplier_id] = true;
            }

            $allocatedItems = [];
            foreach ($pay->items as $item) {
                $totalInvoicesPaid++;
                $pt = $item->purchaseTransaction;

                $allocatedItems[] = [
                    'purchase_transaction_id' => $item->purchase_transaction_id,
                    'transaction_no'          => $pt?->transaction_no ?? '-',
                    'invoice_number'          => $pt?->invoice_number ?? '-',
                    'purchase_date'           => $pt?->transaction_date ? Carbon::parse($pt->transaction_date)->format('Y-m-d') : '-',
                    'grand_total'             => (float) ($pt?->grand_total ?? 0),
                    'amount_due'              => (float) $item->amount_due,
                    'amount_paid'             => (float) $item->amount_paid,
                    'remaining_balance'       => max(0, (float) $item->amount_due - (float) $item->amount_paid),
                ];
            }

            $processedPayments[] = [
                'id'                => $pay->id,
                'payment_no'        => $pay->payment_no,
                'payment_date'      => Carbon::parse($pay->payment_date)->format('Y-m-d'),
                'supplier_name'     => $pay->supplier?->name ?? 'Umum',
                'supplier_phone'    => $pay->supplier?->phone ?? '-',
                'payment_method'    => $pay->payment_method ?: 'Transfer',
                'account_name'      => $pay->account ? "{$pay->account->code} - {$pay->account->name}" : 'Kas/Bank',
                'payment_reference' => $pay->payment_reference ?: '-',
                'total_amount'      => $amount,
                'notes'             => $pay->notes ?: '-',
                'items_count'       => count($allocatedItems),
                'items'             => $allocatedItems,
            ];
        }

        return [
            'from_date'            => $fromDate,
            'to_date'              => $toDate,
            'branch_id'            => $branchId,
            'branch_name'          => $branch ? $branch->name : 'Semua Cabang',
            'supplier_id'          => $supplierId,
            'supplier_name'        => $supplier ? $supplier->name : 'Semua Supplier',
            'account_id'           => $accountId,
            'account_name'         => $account ? "{$account->code} - {$account->name}" : 'Semua Akun Kas/Bank',
            'mode'                 => $mode,
            'search'               => $search,
            'payments'             => $processedPayments,
            'total_payments_count' => $totalPaymentsCount,
            'total_amount_paid'    => $totalAmountPaid,
            'total_invoices_paid'  => $totalInvoicesPaid,
            'total_suppliers_paid' => count($uniqueSuppliers),
        ];
    }
}
