<?php

namespace App\Actions\Inventory;

use App\Models\ProductVariant;
use App\Models\StockMovement;
use Illuminate\Support\Carbon;

class GetProductHppHistory
{
    /**
     * Execute fetching chronological stock movement & HPP history for a product variant.
     *
     * @param  int  $productVariantId
     * @param  int|null  $branchId
     * @param  string|null  $asOfDate
     * @return array
     */
    public function execute(int $productVariantId, ?int $branchId = null, ?string $asOfDate = null): array
    {
        $variant = ProductVariant::with(['product.unit'])->find($productVariantId);

        if (!$variant) {
            return [
                'variant' => null,
                'history' => [],
                'summary' => [],
            ];
        }

        $product = $variant->product;

        $query = StockMovement::where('product_variant_id', $productVariantId)
            ->with(['branch', 'unit']);

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        if ($asOfDate) {
            $query->whereDate('created_at', '<=', $asOfDate);
        }

        $movements = $query->orderBy('created_at', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $runningQty = 0;
        $runningHpp = (float) ($variant->hpp ?: $variant->cost_price ?: 0);
        $history = [];

        $totalInQty = 0;
        $totalOutQty = 0;

        foreach ($movements as $m) {
            $isIn = strtolower($m->type) === 'in';
            $qty = (int) $m->quantity;
            $unitCost = (float) ($m->unit_cost ?: $m->hpp ?: $runningHpp);

            $prevQty = $runningQty;
            $prevHpp = $runningHpp;

            if ($isIn) {
                $runningQty += $qty;
                $totalInQty += $qty;

                // Update Moving Average HPP on stock IN
                if ($runningQty > 0) {
                    $runningHpp = (($prevQty * $prevHpp) + ($qty * $unitCost)) / $runningQty;
                } else {
                    $runningHpp = $unitCost;
                }
            } else {
                $runningQty -= $qty;
                $totalOutQty += $qty;
            }

            $valuation = max(0, $runningQty) * $runningHpp;

            $history[] = [
                'id'                 => $m->id,
                'date'               => Carbon::parse($m->created_at)->format('Y-m-d H:i'),
                'branch_name'        => $m->branch?->name ?? 'Semua Cabang',
                'type'               => $isIn ? 'Masuk' : 'Keluar',
                'type_raw'           => $m->type,
                'reference_label'    => $m->reference_label,
                'reference_type'     => $m->reference_type,
                'qty_change'         => $isIn ? "+{$qty}" : "-{$qty}",
                'qty'                => $qty,
                'unit_cost'          => $unitCost,
                'running_qty'        => $runningQty,
                'running_hpp'        => round($runningHpp, 2),
                'total_valuation'    => round($valuation, 2),
            ];
        }

        $currentHpp = (float) ($variant->hpp ?: $variant->cost_price ?: 0);
        $totalStock = $branchId ? $variant->stockForBranch($branchId) : $variant->totalStock();

        return [
            'variant' => [
                'id'           => $variant->id,
                'sku'          => $variant->sku ?: '-',
                'barcode'      => $variant->barcode ?: '-',
                'name'         => $variant->name ?: 'Standard',
                'product_name' => $product?->name ?? 'Produk',
                'full_name'    => ($product?->name ?? '') . ($variant->name && $variant->name !== 'Standard' ? " ({$variant->name})" : ''),
                'category'     => $product?->category ?: 'Umum',
                'brand'        => $product?->brand ?: '-',
                'unit'         => $product?->unit?->name ?? 'Pcs',
                'current_hpp'  => $currentHpp,
                'total_stock'  => $totalStock,
                'total_valuation' => $totalStock * $currentHpp,
            ],
            'history' => array_reverse($history), // Latest movements first
            'summary' => [
                'total_movements' => count($movements),
                'total_in_qty'    => $totalInQty,
                'total_out_qty'   => $totalOutQty,
                'final_running_qty' => $runningQty,
                'final_running_hpp' => round($runningHpp, 2),
            ],
        ];
    }
}
