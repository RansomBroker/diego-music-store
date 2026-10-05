<?php

namespace App\Helpers;

use App\Models\FocusProduct;
use App\Models\FocusProductRecommendation;
use App\Models\ProductBranchStock;
use App\Models\SaleItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class FocusProductHelper
{
    /**
     * Get stock valuation (Qty * HPP) for a branch or specific variant in a branch.
     */
    public static function getStockValuation(int $branchId, ?int $productVariantId = null): int
    {
        $query = ProductBranchStock::where('branch_id', $branchId)
            ->where('stock', '>', 0);

        if ($productVariantId) {
            $query->where('product_variant_id', $productVariantId);
        }

        return (int) $query->select(DB::raw('COALESCE(SUM(stock * hpp), 0) as total_val'))
            ->value('total_val');
    }

    /**
     * Get total stock valuation for active focus products in a branch.
     */
    public static function getActiveFocusStockValuation(int $branchId): int
    {
        $activeVariantIds = FocusProduct::where('branch_id', $branchId)
            ->where('status', 'ACTIVE')
            ->pluck('product_variant_id');

        if ($activeVariantIds->isEmpty()) {
            return 0;
        }

        return (int) ProductBranchStock::where('branch_id', $branchId)
            ->whereIn('product_variant_id', $activeVariantIds)
            ->where('stock', '>', 0)
            ->select(DB::raw('COALESCE(SUM(stock * hpp), 0) as total_val'))
            ->value('total_val');
    }

    /**
     * Get recent sales summary for a variant in a branch within the specified months.
     *
     * @return array{total_qty: int, total_amount: int, last_sale_date: ?string, transaction_count: int}
     */
    public static function getRecentSalesSummary(int $branchId, int $productVariantId, int $months = 6): array
    {
        $startDate = now()->subMonths($months)->startOfDay();

        $summary = SaleItem::query()
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->where('sales.branch_id', $branchId)
            ->where('sale_items.product_variant_id', $productVariantId)
            ->where('sales.invoice_date', '>=', $startDate)
            ->where('sales.status', '!=', 'cancelled')
            ->select(
                DB::raw('COALESCE(SUM(sale_items.quantity), 0) as total_qty'),
                DB::raw('COALESCE(SUM(sale_items.total_price), 0) as total_amount'),
                DB::raw('MAX(sales.invoice_date) as last_sale_date'),
                DB::raw('COUNT(DISTINCT sales.id) as transaction_count')
            )
            ->first();

        return [
            'total_qty' => (int) ($summary->total_qty ?? 0),
            'total_amount' => (int) ($summary->total_amount ?? 0),
            'last_sale_date' => $summary->last_sale_date ? Carbon::parse($summary->last_sale_date)->format('Y-m-d') : null,
            'transaction_count' => (int) ($summary->transaction_count ?? 0),
        ];
    }

    /**
     * Get count of active focus products for a branch.
     */
    public static function getActiveFocusCount(int $branchId): int
    {
        return FocusProduct::where('branch_id', $branchId)
            ->where('status', 'ACTIVE')
            ->count();
    }

    /**
     * Get count of pending recommendations grouped by rule code for a branch.
     *
     * @return array<string, int>
     */
    public static function getPendingRecommendationCountByRule(int $branchId): array
    {
        $counts = FocusProductRecommendation::query()
            ->join('focus_product_rules', 'focus_product_recommendations.rule_id', '=', 'focus_product_rules.id')
            ->where('focus_product_recommendations.branch_id', $branchId)
            ->where('focus_product_recommendations.status', 'PENDING')
            ->select('focus_product_rules.code', DB::raw('COUNT(*) as total'))
            ->groupBy('focus_product_rules.code')
            ->pluck('total', 'code')
            ->toArray();

        return [
            'dead_stock' => (int) ($counts['dead_stock'] ?? 0),
            'slow_moving' => (int) ($counts['slow_moving'] ?? 0),
            'aging_stock' => (int) ($counts['aging_stock'] ?? 0),
            'total' => array_sum($counts),
        ];
    }
}
