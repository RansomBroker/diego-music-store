<?php

namespace App\Helpers;

use App\Models\Sale;
use App\Models\ProductVariant;
use App\Models\ProductBranchStock;
use App\Models\CashTransaction;
use App\Models\JournalEntry;
use App\Models\JournalItem;
use App\Models\Account;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ReportHelper
{
    /**
     * Get Sales Summary and Breakdown for a given date range and optional branch.
     */
    public static function getSalesReport(?string $dateFrom, ?string $dateTo, ?int $branchId = null, ?string $search = null): array
    {
        $query = Sale::with(['customer', 'salesRep', 'items.variant.product', 'branch'])
            ->where('status', 'completed');

        if ($dateFrom) {
            $query->whereDate('invoice_date', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('invoice_date', '<=', $dateTo);
        }
        if ($branchId) {
            $query->where('branch_id', $branchId);
        }
        if (!empty($search)) {
            $s = '%' . trim($search) . '%';
            $query->where(function ($q) use ($s) {
                $q->where('invoice_number', 'like', $s)
                  ->orWhereHas('customer', fn($cq) => $cq->where('name', 'like', $s))
                  ->orWhereHas('salesRep', fn($sq) => $sq->where('name', 'like', $s));
            });
        }

        $sales = $query->latest('invoice_date')->latest('id')->get();

        $totalTransactions = $sales->count();
        $totalSubtotal = $sales->sum('subtotal');
        $totalDiscount = $sales->sum('discount_amount');
        $totalTax = $sales->sum('tax_amount');
        $grandTotal = $sales->sum('grand_total');

        // Estimate HPP & Gross Profit from items
        $totalCOGS = 0;
        foreach ($sales as $sale) {
            foreach ($sale->items as $item) {
                $variant = $item->variant;
                $hpp = $variant ? ($variant->hpp ?: $variant->cost_price ?: 0) : 0;
                $totalCOGS += ($hpp * $item->quantity);
            }
        }

        $grossProfit = $grandTotal - $totalCOGS;
        $profitMargin = $grandTotal > 0 ? round(($grossProfit / $grandTotal) * 100, 1) : 0;

        return [
            'sales'             => $sales,
            'total_transactions'=> $totalTransactions,
            'total_subtotal'    => $totalSubtotal,
            'total_discount'    => $totalDiscount,
            'total_tax'         => $totalTax,
            'grand_total'       => $grandTotal,
            'total_cogs'        => $totalCOGS,
            'gross_profit'      => $grossProfit,
            'profit_margin'     => $profitMargin,
        ];
    }

    /**
     * Get Accounts Receivable (Piutang Usaha) Aging & Outstanding Report.
     */
    public static function getARAgingReport(?int $branchId = null, ?string $search = null): array
    {
        // Query sales with credit or piutang payment method
        $query = Sale::with(['customer', 'branch'])
            ->where('status', 'completed')
            ->where(function ($q) {
                $q->where('payment_method', 'like', '%credit%')
                  ->orWhere('payment_method', 'like', '%piutang%');
            });

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        if (!empty($search)) {
            $s = '%' . trim($search) . '%';
            $query->where(function ($q) use ($s) {
                $q->where('invoice_number', 'like', $s)
                  ->orWhereHas('customer', fn($cq) => $cq->where('name', 'like', $s));
            });
        }

        $sales = $query->latest('invoice_date')->get();

        $arItems = [];
        $totalOutstanding = 0;
        $aging0to30 = 0;
        $aging31to60 = 0;
        $aging61to90 = 0;
        $agingOver90 = 0;

        $today = Carbon::today();

        foreach ($sales as $sale) {
            // Find total settlement payments recorded in journal entries for this sale's AR
            $settledAmount = JournalItem::whereHas('journalEntry', function ($jq) use ($sale) {
                $jq->where('reference_type', 'AR_Payment')
                   ->where('reference_id', $sale->id)
                   ->where('status', 'posted');
            })->where('credit', '>', 0)->sum('credit');

            $outstanding = max(0, $sale->grand_total - $settledAmount);

            if ($outstanding <= 0) {
                continue; // Fully paid
            }

            $invDate = Carbon::parse($sale->invoice_date);
            $ageDays = max(0, $invDate->diffInDays($today));

            if ($ageDays <= 30) {
                $agingGroup = '0 - 30 Hari';
                $aging0to30 += $outstanding;
            } elseif ($ageDays <= 60) {
                $agingGroup = '31 - 60 Hari';
                $aging31to60 += $outstanding;
            } elseif ($ageDays <= 90) {
                $agingGroup = '61 - 90 Hari';
                $aging61to90 += $outstanding;
            } else {
                $agingGroup = '> 90 Hari';
                $agingOver90 += $outstanding;
            }

            $totalOutstanding += $outstanding;

            $arItems[] = [
                'sale'            => $sale,
                'invoice_number'  => $sale->invoice_number,
                'customer_name'   => $sale->customer->name ?? 'Walk-in / Umum',
                'invoice_date'    => $sale->invoice_date->format('d/m/Y'),
                'due_date'        => $invDate->addDays(30)->format('d/m/Y'),
                'grand_total'     => $sale->grand_total,
                'paid_amount'     => $settledAmount,
                'outstanding'     => $outstanding,
                'age_days'        => $ageDays,
                'aging_group'     => $agingGroup,
            ];
        }

        return [
            'items'             => $arItems,
            'total_outstanding' => $totalOutstanding,
            'aging_0_30'        => $aging0to30,
            'aging_31_60'       => $aging31to60,
            'aging_61_90'       => $aging61to90,
            'aging_over_90'     => $agingOver90,
            'count_invoices'    => count($arItems),
        ];
    }

    /**
     * Get AR Settlement (Pelunasan Piutang) Report.
     */
    public static function getARSettlementReport(?string $dateFrom, ?string $dateTo, ?int $branchId = null, ?string $search = null): array
    {
        $query = JournalEntry::with(['items.account', 'branch'])
            ->where('reference_type', 'AR_Payment')
            ->where('status', 'posted');

        if ($dateFrom) {
            $query->whereDate('date', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('date', '<=', $dateTo);
        }
        if ($branchId) {
            $query->where('branch_id', $branchId);
        }
        if (!empty($search)) {
            $s = '%' . trim($search) . '%';
            $query->where(function ($q) use ($s) {
                $q->where('entry_no', 'like', $s)
                  ->orWhere('description', 'like', $s);
            });
        }

        $entries = $query->latest('date')->get();

        $settlements = [];
        $totalSettled = 0;

        foreach ($entries as $entry) {
            $debitItem = $entry->items->firstWhere('debit', '>', 0);
            $creditItem = $entry->items->firstWhere('credit', '>', 0);
            $amount = $debitItem ? $debitItem->debit : ($creditItem ? $creditItem->credit : 0);

            $sale = Sale::with('customer')->find($entry->reference_id);

            $totalSettled += $amount;

            $settlements[] = [
                'entry_no'      => $entry->entry_no,
                'date'          => Carbon::parse($entry->date)->format('d/m/Y'),
                'customer_name' => $sale?->customer?->name ?? 'Umum / Walk-in',
                'invoice_no'    => $sale?->invoice_number ?? '-',
                'account_name'  => $debitItem?->account?->name ?? 'Kas / Bank',
                'amount'        => $amount,
                'description'   => $entry->description,
            ];
        }

        return [
            'settlements'   => $settlements,
            'total_settled' => $totalSettled,
            'total_count'   => count($settlements),
        ];
    }

    /**
     * Get Daily Cash Report (Buku Kas & Arus Kas Harian).
     */
    public static function getDailyCashReport(?string $dateFrom, ?string $dateTo, ?int $branchId = null): array
    {
        $query = CashTransaction::with(['creator', 'user', 'cashSession.branch']);

        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }
        if ($branchId) {
            $query->whereHas('cashSession', fn($sq) => $sq->where('branch_id', $branchId));
        }

        $transactions = $query->latest('created_at')->get();

        $totalInflow = $transactions->where('type', 'inflow')->sum('amount');
        $totalOutflow = $transactions->where('type', 'outflow')->sum('amount');
        $netCashFlow = $totalInflow - $totalOutflow;

        return [
            'transactions'  => $transactions,
            'total_inflow'  => $totalInflow,
            'total_outflow' => $totalOutflow,
            'net_cash_flow' => $netCashFlow,
            'total_count'   => $transactions->count(),
        ];
    }

    /**
     * Get Inventory Stock & Price Valuation Report.
     */
    public static function getStockValuationReport(?int $branchId = null, ?string $search = null, ?string $category = null): array
    {
        $query = ProductVariant::with(['product', 'branchStocks']);

        if (!empty($search)) {
            $s = '%' . trim($search) . '%';
            $query->where(function ($q) use ($s) {
                $q->where('sku', 'like', $s)
                  ->orWhere('barcode', 'like', $s)
                  ->orWhere('name', 'like', $s)
                  ->orWhereHas('product', fn($pq) => $pq->where('name', 'like', $s));
            });
        }

        $variants = $query->get();

        $stockItems = [];
        $totalQty = 0;
        $totalHppValuation = 0;
        $totalRetailValuation = 0;

        foreach ($variants as $variant) {
            if ($branchId) {
                $stock = $variant->stockForBranch($branchId);
            } else {
                $stock = $variant->branchStocks->sum('stock');
            }

            $hpp = $variant->hpp ?: ($variant->cost_price ?: 0);
            $price = $variant->price ?: 0;

            $totalHppItem = $stock * $hpp;
            $totalRetailItem = $stock * $price;

            $totalQty += $stock;
            $totalHppValuation += $totalHppItem;
            $totalRetailValuation += $totalRetailItem;

            $stockItems[] = [
                'sku'                   => $variant->sku ?: ('SKU-' . $variant->id),
                'barcode'               => $variant->barcode ?: '-',
                'product_name'          => $variant->product->name . ($variant->name ? ' (' . $variant->name . ')' : ''),
                'type'                  => strtoupper($variant->product->type),
                'stock'                 => $stock,
                'hpp'                   => $hpp,
                'price'                 => $price,
                'total_hpp_valuation'   => $totalHppItem,
                'total_retail_valuation'=> $totalRetailItem,
            ];
        }

        $potentialProfit = $totalRetailValuation - $totalHppValuation;

        return [
            'items'                     => $stockItems,
            'total_sku'                 => count($stockItems),
            'total_qty'                 => $totalQty,
            'total_hpp_valuation'       => $totalHppValuation,
            'total_retail_valuation'    => $totalRetailValuation,
            'potential_profit'          => $potentialProfit,
        ];
    }
}
