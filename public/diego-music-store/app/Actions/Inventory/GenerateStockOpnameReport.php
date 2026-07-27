<?php

namespace App\Actions\Inventory;

use App\Models\Branch;
use App\Models\StockOpname;
use Illuminate\Support\Carbon;

class GenerateStockOpnameReport
{
    /**
     * Execute Stock Opname Audit Report calculation.
     *
     * @param  string|null  $fromDate
     * @param  string|null  $toDate
     * @param  int|null  $branchId
     * @param  string  $statusFilter ('all', 'completed', 'draft')
     * @param  string  $varianceFilter ('all', 'has_variance', 'matched', 'positive', 'negative')
     * @param  string  $mode ('summary', 'detail')
     * @param  string|null  $search
     * @return array
     */
    public function execute(
        ?string $fromDate = null,
        ?string $toDate = null,
        ?int $branchId = null,
        string $statusFilter = 'all',
        string $varianceFilter = 'all',
        string $mode = 'summary',
        ?string $search = null
    ): array {
        $fromDate = $fromDate ?: now()->startOfMonth()->format('Y-m-d');
        $toDate   = $toDate ?: now()->format('Y-m-d');
        $branch   = $branchId ? Branch::find($branchId) : null;

        $query = StockOpname::with(['branch', 'items.productVariant.product.unit'])
            ->whereDate('opname_date', '>=', $fromDate)
            ->whereDate('opname_date', '<=', $toDate);

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        if ($statusFilter !== 'all') {
            $query->where('status', $statusFilter);
        }

        if ($search) {
            $s = trim($search);
            $query->where(function ($q) use ($s) {
                $q->where('opname_number', 'LIKE', "%{$s}%")
                  ->orWhere('notes', 'LIKE', "%{$s}%");
            });
        }

        $opnames = $query->orderBy('opname_date', 'asc')->orderBy('id', 'asc')->get();

        $processedOpnames = [];
        $totalOpnameSessions        = 0;
        $totalItemsAudited          = 0;
        $totalNetVarianceQty        = 0;
        $grandTotalAdjustmentValue  = 0.0;

        foreach ($opnames as $opname) {
            $totalOpnameSessions++;

            $itemsData = [];
            $sessionSystemQty = 0;
            $sessionPhysicalQty = 0;
            $sessionDiffQty = 0;
            $sessionAdjustmentValue = 0.0;

            foreach ($opname->items as $item) {
                $variant = $item->productVariant;
                $product = $variant?->product;

                $sysQty  = (int) $item->system_qty;
                $phyQty  = (int) $item->physical_qty;
                $diffQty = (int) $item->difference;
                $cost    = (float) ($item->cost_price ?: ($variant?->hpp ?: $variant?->cost_price ?? 0));

                $adjVal  = $diffQty * $cost;

                // Apply variance filter
                if ($varianceFilter === 'has_variance' && $diffQty === 0) {
                    continue;
                }
                if ($varianceFilter === 'matched' && $diffQty !== 0) {
                    continue;
                }
                if ($varianceFilter === 'positive' && $diffQty <= 0) {
                    continue;
                }
                if ($varianceFilter === 'negative' && $diffQty >= 0) {
                    continue;
                }

                $totalItemsAudited++;
                $totalNetVarianceQty += $diffQty;
                $grandTotalAdjustmentValue += $adjVal;

                $sessionSystemQty += $sysQty;
                $sessionPhysicalQty += $phyQty;
                $sessionDiffQty += $diffQty;
                $sessionAdjustmentValue += $adjVal;

                // Item Variance Badge
                if ($diffQty === 0) {
                    $itemStatusLabel = 'COCOK';
                    $itemBadgeColor  = 'success';
                } elseif ($diffQty > 0) {
                    $itemStatusLabel = 'SELISIH LEBIH (+' . $diffQty . ')';
                    $itemBadgeColor  = 'warning';
                } else {
                    $itemStatusLabel = 'SELISIH KURANG (' . $diffQty . ')';
                    $itemBadgeColor  = 'danger';
                }

                $itemsData[] = [
                    'id'                 => $item->id,
                    'sku'                => $variant?->sku ?: '-',
                    'product_name'       => $product?->name ?? 'Produk',
                    'variant_name'       => $variant?->name ?: 'Standard',
                    'full_name'          => ($product?->name ?? 'Produk') . ($variant?->name && $variant->name !== 'Standard' ? " ({$variant->name})" : ''),
                    'unit'               => $product?->unit?->name ?? 'Pcs',
                    'system_qty'         => $sysQty,
                    'physical_qty'       => $phyQty,
                    'difference'         => $diffQty,
                    'item_status_label'  => $itemStatusLabel,
                    'item_badge_color'   => $itemBadgeColor,
                    'cost_price'         => $cost,
                    'adjustment_value'   => $adjVal,
                ];
            }

            // Skip empty sessions if variance filter filtered out all items
            if (empty($itemsData) && $varianceFilter !== 'all') {
                continue;
            }

            $processedOpnames[] = [
                'id'                       => $opname->id,
                'opname_number'            => $opname->opname_number,
                'opname_date'              => Carbon::parse($opname->opname_date)->format('Y-m-d'),
                'branch_name'              => $opname->branch?->name ?? 'Cabang Utama',
                'status'                   => strtoupper($opname->status),
                'status_badge_color'       => $opname->status === 'completed' ? 'success' : 'gray',
                'notes'                    => $opname->notes ?: '-',
                'items_count'              => count($itemsData),
                'session_system_qty'       => $sessionSystemQty,
                'session_physical_qty'     => $sessionPhysicalQty,
                'session_diff_qty'         => $sessionDiffQty,
                'session_adjustment_value' => $sessionAdjustmentValue,
                'items'                    => $itemsData,
            ];
        }

        return [
            'from_date'                      => $fromDate,
            'to_date'                        => $toDate,
            'branch_id'                      => $branchId,
            'branch_name'                    => $branch ? $branch->name : 'Semua Cabang',
            'status_filter'                  => $statusFilter,
            'variance_filter'                => $varianceFilter,
            'mode'                           => $mode,
            'search'                         => $search,
            'opnames'                        => $processedOpnames,
            'total_opname_sessions'          => $totalOpnameSessions,
            'total_items_audited'            => $totalItemsAudited,
            'total_net_variance_qty'         => $totalNetVarianceQty,
            'grand_total_adjustment_value'   => $grandTotalAdjustmentValue,
        ];
    }
}
