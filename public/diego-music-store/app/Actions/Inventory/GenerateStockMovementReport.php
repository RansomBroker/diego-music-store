<?php

namespace App\Actions\Inventory;

use App\Models\Branch;
use App\Models\ProductVariant;
use App\Models\StockMovement;
use Illuminate\Support\Carbon;

class GenerateStockMovementReport
{
    /**
     * Execute Stock Movement / Ledger Report calculation.
     *
     * @param  string|null  $fromDate
     * @param  string|null  $toDate
     * @param  int|null  $branchId
     * @param  string|null  $category
     * @param  string  $typeFilter ('all', 'in', 'out', 'mutation')
     * @param  string  $mode ('log', 'summary')
     * @param  string|null  $search
     * @return array
     */
    public function execute(
        ?string $fromDate = null,
        ?string $toDate = null,
        ?int $branchId = null,
        ?string $category = null,
        string $typeFilter = 'all',
        string $mode = 'log',
        ?string $search = null
    ): array {
        $fromDate = $fromDate ?: now()->startOfMonth()->format('Y-m-d');
        $toDate   = $toDate ?: now()->format('Y-m-d');
        $branch   = $branchId ? Branch::find($branchId) : null;

        $query = StockMovement::with(['branch', 'unit', 'productVariant.product.unit'])
            ->whereDate('created_at', '>=', $fromDate)
            ->whereDate('created_at', '<=', $toDate);

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        if ($typeFilter === 'in') {
            $query->where('type', 'in');
        } elseif ($typeFilter === 'out') {
            $query->where('type', 'out');
        } elseif ($typeFilter === 'mutation') {
            $query->where('reference_type', 'LIKE', '%Mutation%');
        }

        if ($category) {
            $query->whereHas('productVariant.product', function ($pq) use ($category) {
                $pq->where('category', $category);
            });
        }

        if ($search) {
            $s = trim($search);
            $query->where(function ($q) use ($s) {
                $q->where('reference_type', 'LIKE', "%{$s}%")
                  ->orWhere('reference_id', 'LIKE', "%{$s}%")
                  ->orWhereHas('productVariant', function ($vq) use ($s) {
                      $vq->where('sku', 'LIKE', "%{$s}%")
                        ->orWhere('name', 'LIKE', "%{$s}%")
                        ->orWhereHas('product', function ($pq) use ($s) {
                            $pq->where('name', 'LIKE', "%{$s}%");
                        });
                  });
            });
        }

        $movements = $query->orderBy('created_at', 'asc')->get();

        $rows = [];
        $summaryMap = [];

        $totalTransactions = 0;
        $totalInQty        = 0;
        $totalOutQty       = 0;
        $grandTotalValuation = 0.0;

        foreach ($movements as $m) {
            $variant = $m->productVariant;
            $product = $variant?->product;

            $qty  = (int) $m->quantity;
            $type = $m->type; // 'in' or 'out'
            $hpp  = (float) ($m->hpp ?: ($m->unit_cost ?: ($variant?->hpp ?: $variant?->cost_price ?? 0)));

            $val  = $qty * $hpp;

            $totalTransactions++;
            if ($type === 'in') {
                $totalInQty += $qty;
            } else {
                $totalOutQty += $qty;
            }
            $grandTotalValuation += $val;

            $sku = $variant?->sku ?: '-';
            $fullName = ($product?->name ?? 'Produk') . ($variant?->name && $variant->name !== 'Standard' ? " ({$variant->name})" : '');
            $unitName = $m->unit?->name ?? ($product?->unit?->name ?? 'Pcs');

            $refLabel = $m->reference_type ?: 'Manual';
            if ($m->reference_id) {
                $refLabel .= " #{$m->reference_id}";
            }

            $badgeColor = $type === 'in' ? 'success' : 'danger';
            if (str_contains($m->reference_type ?? '', 'Mutation')) {
                $badgeColor = 'info';
            }

            $rows[] = [
                'id'             => $m->id,
                'date'           => $m->created_at ? $m->created_at->format('Y-m-d H:i') : '-',
                'sku'            => $sku,
                'full_name'      => $fullName,
                'category'       => $product?->category ?: 'Umum',
                'branch_name'    => $m->branch?->name ?? 'Cabang Utama',
                'type'           => strtoupper($type),
                'ref_label'      => $refLabel,
                'quantity'       => $qty,
                'unit'           => $unitName,
                'hpp'            => $hpp,
                'total_value'    => $val,
                'badge_color'    => $badgeColor,
            ];

            // Summary per Product Variant
            $varId = $variant?->id ?: 0;
            if (!isset($summaryMap[$varId])) {
                $summaryMap[$varId] = [
                    'sku'          => $sku,
                    'full_name'    => $fullName,
                    'category'     => $product?->category ?: 'Umum',
                    'unit'         => $unitName,
                    'in_qty'       => 0,
                    'out_qty'      => 0,
                    'net_qty'      => 0,
                    'hpp'          => $hpp,
                    'total_value'  => 0.0,
                ];
            }

            if ($type === 'in') {
                $summaryMap[$varId]['in_qty'] += $qty;
                $summaryMap[$varId]['net_qty'] += $qty;
            } else {
                $summaryMap[$varId]['out_qty'] += $qty;
                $summaryMap[$varId]['net_qty'] -= $qty;
            }
            $summaryMap[$varId]['total_value'] += $val;
        }

        // Sort summary rows
        usort($summaryMap, function ($a, $b) {
            return strcmp($a['full_name'], $b['full_name']);
        });

        $totalNetQty = $totalInQty - $totalOutQty;

        return [
            'from_date'             => $fromDate,
            'to_date'               => $toDate,
            'branch_id'             => $branchId,
            'branch_name'           => $branch ? $branch->name : 'Semua Cabang',
            'category'              => $category ?: 'Semua Kategori',
            'type_filter'           => $typeFilter,
            'mode'                  => $mode,
            'search'                => $search,
            'rows'                  => $rows,
            'summary_rows'          => array_values($summaryMap),
            'total_transactions'    => $totalTransactions,
            'total_in_qty'          => $totalInQty,
            'total_out_qty'         => $totalOutQty,
            'total_net_qty'         => $totalNetQty,
            'grand_total_valuation' => $grandTotalValuation,
        ];
    }
}
