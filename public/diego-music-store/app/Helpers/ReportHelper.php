<?php

namespace App\Helpers;

use App\Models\Sale;
use App\Models\SaleItem;
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
     * Apply comprehensive sales filters to Eloquent query.
     */
    private static function applySalesFilters($query, array $filters = []): void
    {
        if (!empty($filters['dateFrom'])) {
            $query->whereDate('invoice_date', '>=', $filters['dateFrom']);
        }
        if (!empty($filters['dateTo'])) {
            $query->whereDate('invoice_date', '<=', $filters['dateTo']);
        }
        if (!empty($filters['branchId'])) {
            $query->where('branch_id', $filters['branchId']);
        }
        if (!empty($filters['customerId'])) {
            $query->where('customer_id', $filters['customerId']);
        }
        if (!empty($filters['paymentMethod'])) {
            $query->where('payment_method', $filters['paymentMethod']);
        }
        if (!empty($filters['salesRepId'])) {
            $query->where('sales_rep_id', $filters['salesRepId']);
        }
        if (!empty($filters['cashierId'])) {
            $query->where('created_by', $filters['cashierId']);
        }
        if (!empty($filters['saleCategory'])) {
            $query->where('sale_category', $filters['saleCategory']);
        }
        if (!empty($filters['productCategory'])) {
            $query->whereHas('items.variant.product', fn($pq) => $pq->where('category', $filters['productCategory']));
        }
        if (!empty($filters['productId'])) {
            $query->whereHas('items.variant', fn($vq) => $vq->where('product_id', $filters['productId']));
        }
        if (!empty($filters['search'])) {
            $s = '%' . trim($filters['search']) . '%';
            $query->where(function ($q) use ($s) {
                $q->where('invoice_number', 'like', $s)
                  ->orWhereHas('customer', fn($cq) => $cq->where('name', 'like', $s))
                  ->orWhereHas('salesRep', fn($sq) => $sq->where('name', 'like', $s))
                  ->orWhereHas('items.variant', fn($vq) => $vq->where('name', 'like', $s)->orWhere('sku', 'like', $s))
                  ->orWhereHas('items.variant.product', fn($pq) => $pq->where('name', 'like', $s));
            });
        }
    }

    /**
     * Get Sales Summary and Breakdown for a given date range and optional filters.
     */
    public static function getSalesReport(array|string|null $dateFromOrFilters = null, ?string $dateTo = null, ?int $branchId = null, ?string $search = null): array
    {
        $filters = is_array($dateFromOrFilters) ? $dateFromOrFilters : [
            'dateFrom' => $dateFromOrFilters,
            'dateTo'   => $dateTo,
            'branchId' => $branchId,
            'search'   => $search,
        ];

        $query = Sale::with(['customer', 'salesRep', 'items.variant.product', 'branch'])
            ->where('status', 'completed');

        static::applySalesFilters($query, $filters);

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
    public static function getARAgingReport(
        ?int $branchId = null,
        ?string $search = null,
        ?int $customerId = null,
        ?string $agingGroupFilter = null,
        ?string $dateFrom = null,
        ?string $dateTo = null
    ): array {
        // Query sales with credit or piutang payment method
        $query = Sale::with(['customer', 'branch'])
            ->where(function ($q) {
                $q->where('payment_method', 'like', '%credit%')
                  ->orWhere('payment_method', 'like', '%piutang%')
                  ->orWhere('status', '!=', 'completed');
            });

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        if ($customerId) {
            $query->where('customer_id', $customerId);
        }

        if ($dateFrom) {
            $query->whereDate('invoice_date', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('invoice_date', '<=', $dateTo);
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
            $outstanding = $sale->getPiutangAmount();
            $settledAmount = max(0, floatval($sale->grand_total) - $outstanding);

            if ($outstanding <= 0) {
                continue; // Fully paid
            }

            $invDate = Carbon::parse($sale->invoice_date);
            $ageDays = max(0, $invDate->diffInDays($today));

            if ($ageDays <= 30) {
                $agingGroup = '0 - 30 Hari';
                $agingGroupKey = '0-30';
                $aging0to30 += $outstanding;
            } elseif ($ageDays <= 60) {
                $agingGroup = '31 - 60 Hari';
                $agingGroupKey = '31-60';
                $aging31to60 += $outstanding;
            } elseif ($ageDays <= 90) {
                $agingGroup = '61 - 90 Hari';
                $agingGroupKey = '61-90';
                $aging61to90 += $outstanding;
            } else {
                $agingGroup = '> 90 Hari';
                $agingGroupKey = 'over-90';
                $agingOver90 += $outstanding;
            }

            if (!empty($agingGroupFilter) && $agingGroupFilter !== $agingGroupKey) {
                continue;
            }

            $totalOutstanding += $outstanding;

            $arItems[] = [
                'sale'            => $sale,
                'sale_id'         => $sale->id,
                'invoice_number'  => $sale->invoice_number,
                'customer_name'   => $sale->customer->name ?? 'Walk-in / Umum',
                'invoice_date'    => $sale->invoice_date->format('d/m/Y'),
                'due_date'        => $invDate->copy()->addDays(30)->format('d/m/Y'),
                'grand_total'     => floatval($sale->grand_total),
                'paid_amount'     => $settledAmount,
                'outstanding'     => $outstanding,
                'age_days'        => $ageDays,
                'aging_group'     => $agingGroup,
                'aging_group_key' => $agingGroupKey,
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
            ->whereIn('reference_type', ['AR_Payment', 'Sales'])
            ->where('status', 'posted')
            ->where('description', 'like', '%Pelunasan Piutang%');

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

    /**
     * Get Daily Sales Detail Report (Flattened Item level details).
     */
    public static function getDailySalesDetailReport(array|string|null $dateFromOrFilters = null, ?string $dateTo = null, ?int $branchId = null, ?string $search = null): array
    {
        $filters = is_array($dateFromOrFilters) ? $dateFromOrFilters : [
            'dateFrom' => $dateFromOrFilters,
            'dateTo'   => $dateTo,
            'branchId' => $branchId,
            'search'   => $search,
        ];

        $query = Sale::with(['customer', 'salesRep', 'creator', 'branch', 'items.variant.product'])
            ->where('status', 'completed');

        static::applySalesFilters($query, $filters);

        $sales = $query->latest('invoice_date')->latest('id')->get();

        $detailItems = [];
        $grandTotalSum = 0;
        $totalQtySum = 0;
        $totalDiscountSum = 0;
        $totalTaxSum = 0;

        foreach ($sales as $sale) {
            $grandTotalSum += floatval($sale->grand_total);
            $totalTaxSum += floatval($sale->tax_amount);

            $matchingItems = $sale->items->filter(function ($item) use ($filters) {
                if (!empty($filters['productId'])) {
                    return $item->variant?->product_id == $filters['productId'];
                }
                return true;
            })->values();

            $itemCount = max(1, $matchingItems->count());

            foreach ($matchingItems as $idx => $item) {
                $variant = $item->variant;
                $productName = $variant?->product?->name ?? 'Barang';
                if ($variant && !empty($variant->name)) {
                    $productName .= ' (' . $variant->name . ')';
                }
                $sku = $variant?->sku ?? $variant?->barcode ?? '-';

                $qty = intval($item->quantity);
                $unitPrice = floatval($item->unit_price);
                $discount = floatval($item->discount_amount);
                $subtotal = floatval($item->total_price);

                $totalQtySum += $qty;
                $totalDiscountSum += $discount;

                $detailItems[] = [
                    'sale_id'          => $sale->id,
                    'invoice_date'     => $sale->invoice_date->format('d/m/Y'),
                    'sale_category'    => $sale->sale_category ?? 'Retail',
                    'invoice_number'   => $sale->invoice_number,
                    'customer_name'    => $sale->customer->name ?? 'Pelanggan Umum',
                    'cashier_name'     => $sale->creator->name ?? 'Kasir',
                    'sales_rep_name'   => $sale->salesRep->name ?? '-',
                    'payment_method'   => $sale->payment_method ?? 'Tunai',
                    'sku'              => $sku,
                    'product_name'     => $productName,
                    'quantity'         => $qty,
                    'unit_price'       => $unitPrice,
                    'discount_amount'  => $discount,
                    'tax_amount'       => floatval($sale->tax_amount),
                    'subtotal'         => $subtotal,
                    'notes'            => $item->notes ?: ($sale->notes ?? '-'),
                    'is_first_item'    => ($idx === 0),
                    'rowspan'          => $itemCount,
                ];
            }
        }

        return [
            'items'               => $detailItems,
            'total_transactions'  => $sales->count(),
            'total_qty'           => $totalQtySum,
            'total_discount'      => $totalDiscountSum,
            'total_tax'           => $totalTaxSum,
            'grand_total'         => $grandTotalSum,
        ];
    }

    /**
     * Get Daily Sales Per Day Report (Grouped by Date).
     */
    public static function getDailySalesPerDayReport(array|string|null $dateFromOrFilters = null, ?string $dateTo = null, ?int $branchId = null): array
    {
        $filters = is_array($dateFromOrFilters) ? $dateFromOrFilters : [
            'dateFrom' => $dateFromOrFilters,
            'dateTo'   => $dateTo,
            'branchId' => $branchId,
        ];

        $query = Sale::where('status', 'completed');

        static::applySalesFilters($query, $filters);

        $sales = $query->orderBy('invoice_date', 'desc')->get();

        $grouped = $sales->groupBy(fn($s) => $s->invoice_date->format('Y-m-d'));

        $items = [];
        $totalGrandTotal = 0;
        $totalSubtotal = 0;
        $totalDiscount = 0;
        $totalTax = 0;
        $totalInvoices = 0;

        foreach ($grouped as $dateStr => $daySales) {
            $invCount = $daySales->count();
            $sub = $daySales->sum('subtotal');
            $disc = $daySales->sum('discount_amount');
            $tax = $daySales->sum('tax_amount');
            $grand = $daySales->sum('grand_total');

            $totalInvoices += $invCount;
            $totalSubtotal += $sub;
            $totalDiscount += $disc;
            $totalTax += $tax;
            $totalGrandTotal += $grand;

            $items[] = [
                'date'            => Carbon::parse($dateStr)->format('d/m/Y'),
                'raw_date'        => $dateStr,
                'invoice_count'   => $invCount,
                'subtotal'        => $sub,
                'discount_amount' => $disc,
                'tax_amount'      => $tax,
                'grand_total'     => $grand,
            ];
        }

        return [
            'items'            => $items,
            'total_invoices'   => $totalInvoices,
            'total_subtotal'   => $totalSubtotal,
            'total_discount'   => $totalDiscount,
            'total_tax'        => $totalTax,
            'total_grand_total'=> $totalGrandTotal,
        ];
    }

    /**
     * Get Daily Sales Per Nota Report.
     */
    public static function getDailySalesPerNotaReport(array|string|null $dateFromOrFilters = null, ?string $dateTo = null, ?int $branchId = null, ?string $search = null): array
    {
        $filters = is_array($dateFromOrFilters) ? $dateFromOrFilters : [
            'dateFrom' => $dateFromOrFilters,
            'dateTo'   => $dateTo,
            'branchId' => $branchId,
            'search'   => $search,
        ];

        $query = Sale::with(['customer', 'salesRep', 'creator', 'items'])
            ->where('status', 'completed');

        static::applySalesFilters($query, $filters);

        $sales = $query->latest('invoice_date')->latest('id')->get();

        $items = [];
        $totalSubtotal = 0;
        $totalDiscount = 0;
        $totalTax = 0;
        $totalGrandTotal = 0;

        foreach ($sales as $sale) {
            $sub = floatval($sale->subtotal);
            $disc = floatval($sale->discount_amount);
            $tax = floatval($sale->tax_amount);
            $grand = floatval($sale->grand_total);

            $totalSubtotal += $sub;
            $totalDiscount += $disc;
            $totalTax += $tax;
            $totalGrandTotal += $grand;

            $items[] = [
                'sale_id'         => $sale->id,
                'invoice_number'  => $sale->invoice_number,
                'date'            => $sale->invoice_date->format('d/m/Y'),
                'sale_category'   => $sale->sale_category ?? 'Retail',
                'customer_name'   => $sale->customer->name ?? 'Pelanggan Umum',
                'cashier_name'    => $sale->creator->name ?? 'Kasir',
                'sales_rep_name'  => $sale->salesRep->name ?? '-',
                'payment_method'  => $sale->payment_method ?? 'Tunai',
                'item_count'      => $sale->items->count(),
                'subtotal'        => $sub,
                'discount_amount' => $disc,
                'tax_amount'      => $tax,
                'grand_total'     => $grand,
            ];
        }

        return [
            'items'            => $items,
            'total_invoices'   => count($items),
            'total_subtotal'   => $totalSubtotal,
            'total_discount'   => $totalDiscount,
            'total_tax'        => $totalTax,
            'total_grand_total'=> $totalGrandTotal,
        ];
    }

    /**
     * Get Daily Sales Top Selling Products Report.
     */
    public static function getDailySalesTopSellingReport(array|string|null $dateFromOrFilters = null, ?string $dateTo = null, ?int $branchId = null, ?string $search = null): array
    {
        $filters = is_array($dateFromOrFilters) ? $dateFromOrFilters : [
            'dateFrom' => $dateFromOrFilters,
            'dateTo'   => $dateTo,
            'branchId' => $branchId,
            'search'   => $search,
        ];

        $query = SaleItem::with(['variant.product', 'sale']);

        if (!empty($filters['productId'])) {
            $query->whereHas('variant', fn($vq) => $vq->where('product_id', $filters['productId']));
        }

        $query->whereHas('sale', function ($sq) use ($filters) {
            $sq->where('status', 'completed');
            static::applySalesFilters($sq, $filters);
        });

        $saleItems = $query->get();

        $grouped = $saleItems->groupBy('product_variant_id');

        $items = [];
        $grandTotalQty = 0;
        $grandTotalRevenue = 0;

        foreach ($grouped as $variantId => $groupItems) {
            $first = $groupItems->first();
            $variant = $first?->variant;
            $productName = $variant?->product?->name ?? 'Barang';
            if ($variant && !empty($variant->name)) {
                $productName .= ' (' . $variant->name . ')';
            }
            $sku = $variant?->sku ?? $variant?->barcode ?? '-';
            $categoryName = $variant?->product?->category ?? 'Umum';

            $totalQty = $groupItems->sum('quantity');
            $totalRevenue = $groupItems->sum('total_price');

            $grandTotalQty += $totalQty;
            $grandTotalRevenue += $totalRevenue;

            $items[] = [
                'sku'             => $sku,
                'product_name'    => $productName,
                'category_name'   => $categoryName,
                'total_qty'       => $totalQty,
                'total_revenue'   => $totalRevenue,
            ];
        }

        // Sort by total_qty descending
        usort($items, fn($a, $b) => $b['total_qty'] <=> $a['total_qty']);

        return [
            'items'              => $items,
            'total_products'     => count($items),
            'grand_total_qty'    => $grandTotalQty,
            'grand_total_revenue'=> $grandTotalRevenue,
        ];
    }
}

