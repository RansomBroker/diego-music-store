<?php

namespace App\Actions\Inventory;

use App\Models\Branch;
use App\Models\ProductVariant;
use Illuminate\Support\Carbon;

class GenerateEndingInventoryReport
{
    /**
     * Execute Ending Inventory Report (Laporan Persediaan Akhir) calculation as of cut-off date.
     *
     * @param  string|null  $asOfDate
     * @param  int|null  $branchId
     * @param  string|null  $category
     * @param  string  $mode ('detail_variant', 'summary_category')
     * @param  string|null  $search
     * @return array
     */
    public function execute(
        ?string $asOfDate = null,
        ?int $branchId = null,
        ?string $category = null,
        string $mode = 'detail_variant',
        ?string $search = null
    ): array {
        $asOfDate = $asOfDate ?: now()->format('Y-m-d');
        $branch   = $branchId ? Branch::find($branchId) : null;

        $query = ProductVariant::with(['product.unit', 'branchStocks.branch'])
            ->whereHas('product', function ($pq) use ($category) {
                $pq->where('is_active', true);
                if ($category) {
                    $pq->where('category', $category);
                }
            });

        if ($search) {
            $s = trim($search);
            $query->where(function ($q) use ($s) {
                $q->where('sku', 'LIKE', "%{$s}%")
                  ->orWhere('barcode', 'LIKE', "%{$s}%")
                  ->orWhere('name', 'LIKE', "%{$s}%")
                  ->orWhereHas('product', function ($pq) use ($s) {
                      $pq->where('name', 'LIKE', "%{$s}%")
                        ->orWhere('category', 'LIKE', "%{$s}%")
                        ->orWhere('brand', 'LIKE', "%{$s}%");
                  });
            });
        }

        $variants = $query->get();

        $rows = [];
        $categorySummaries = [];

        $totalVariants         = 0;
        $totalEndingQty        = 0;
        $grandTotalValuation   = 0.0;

        foreach ($variants as $variant) {
            $product = $variant->product;
            if (!$product) {
                continue;
            }

            // Calculate current stock
            if ($branchId) {
                $stockQty = $variant->stockForBranch($branchId);
            } else {
                $stockQty = $variant->totalStock();
            }

            // For services, handle unlimited indicator
            if ($product->isService()) {
                $endingQty = 0;
            } else {
                $endingQty = max(0, $stockQty);
            }

            $costPrice = (float) ($variant->hpp ?: $variant->cost_price);
            $valuation = $product->isService() ? 0.0 : ($endingQty * $costPrice);

            $totalVariants++;
            if (!$product->isService()) {
                $totalEndingQty += $endingQty;
                $grandTotalValuation += $valuation;
            }

            $catName = $product->category ?: 'Umum';

            $rows[] = [
                'id'           => $variant->id,
                'sku'          => $variant->sku ?: '-',
                'barcode'      => $variant->barcode ?: '-',
                'product_name' => $product->name,
                'variant_name' => $variant->name ?: 'Standard',
                'full_name'    => $product->name . ($variant->name && $variant->name !== 'Standard' ? " ({$variant->name})" : ''),
                'category'     => $catName,
                'brand'        => $product->brand ?: '-',
                'unit'         => $product->unit?->name ?? 'Pcs',
                'ending_qty'   => $endingQty,
                'cost_price'   => $costPrice,
                'valuation'    => $valuation,
            ];

            // Accumulate category summaries
            if (!isset($categorySummaries[$catName])) {
                $categorySummaries[$catName] = [
                    'category_name'   => $catName,
                    'variant_count'   => 0,
                    'total_qty'       => 0,
                    'total_valuation' => 0.0,
                ];
            }

            $categorySummaries[$catName]['variant_count']++;
            if (!$product->isService()) {
                $categorySummaries[$catName]['total_qty'] += $endingQty;
                $categorySummaries[$catName]['total_valuation'] += $valuation;
            }
        }

        // Sort rows by full_name
        usort($rows, function ($a, $b) {
            return strcmp($a['full_name'], $b['full_name']);
        });

        // Sort category summaries
        usort($categorySummaries, function ($a, $b) {
            return strcmp($a['category_name'], $b['category_name']);
        });

        return [
            'as_of_date'            => $asOfDate,
            'branch_id'             => $branchId,
            'branch_name'           => $branch ? $branch->name : 'Semua Cabang (Konsolidasi)',
            'category'              => $category ?: 'Semua Kategori',
            'mode'                  => $mode,
            'search'                => $search,
            'rows'                  => $rows,
            'categories'            => array_values($categorySummaries),
            'total_variants'        => $totalVariants,
            'total_ending_qty'      => $totalEndingQty,
            'grand_total_valuation' => $grandTotalValuation,
        ];
    }
}
