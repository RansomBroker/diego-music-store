<?php

namespace App\Actions\Inventory;

use App\Models\Branch;
use App\Models\ProductVariant;

class GenerateStockListReport
{
    /**
     * Execute Stock List & Valuation Report (Laporan Daftar Stok & Nilai Persediaan) calculation.
     *
     * @param  int|null  $branchId
     * @param  string|null  $category
     * @param  string  $stockStatus ('all', 'available', 'low', 'out_of_stock')
     * @param  string|null  $search
     * @return array
     */
    public function execute(
        ?int $branchId = null,
        ?string $category = null,
        string $stockStatus = 'all',
        ?string $search = null
    ): array {
        $branch = $branchId ? Branch::find($branchId) : null;

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
        $totalVariants   = 0;
        $totalPhysicalQty = 0;
        $totalLowStockCount = 0;
        $totalOutOfStockCount = 0;
        $grandTotalValuation = 0.0;
        $grandTotalRetailValue = 0.0;

        foreach ($variants as $variant) {
            $product = $variant->product;
            if (!$product) {
                continue;
            }

            // Calculate stock
            if ($branchId) {
                $stockQty = $variant->stockForBranch($branchId);
            } else {
                $stockQty = $variant->totalStock();
            }

            $minStock = (int) ($product->minimum_stock ?? 0);

            // Determine stock status
            if ($product->isService()) {
                $statusKey = 'available';
                $statusLabel = 'JASA (UNLIMITED)';
                $badgeColor = 'info';
            } elseif ($stockQty <= 0) {
                $statusKey = 'out_of_stock';
                $statusLabel = 'STOK HABIS';
                $badgeColor = 'danger';
            } elseif ($minStock > 0 && $stockQty <= $minStock) {
                $statusKey = 'low';
                $statusLabel = 'STOK RENDAH';
                $badgeColor = 'warning';
            } else {
                $statusKey = 'available';
                $statusLabel = 'STOK AMAN';
                $badgeColor = 'success';
            }

            // Apply stock status filter
            if ($stockStatus === 'available' && ($statusKey === 'out_of_stock' || $statusKey === 'low')) {
                continue;
            }
            if ($stockStatus === 'low' && $statusKey !== 'low') {
                continue;
            }
            if ($stockStatus === 'out_of_stock' && $statusKey !== 'out_of_stock') {
                continue;
            }

            $costPrice = (float) ($variant->hpp ?: $variant->cost_price);
            $retailPrice = (float) $variant->price;

            $valuation = $product->isService() ? 0.0 : ($stockQty * $costPrice);
            $retailValue = $product->isService() ? 0.0 : ($stockQty * $retailPrice);

            $totalVariants++;
            if (!$product->isService()) {
                $totalPhysicalQty += $stockQty;
                $grandTotalValuation += $valuation;
                $grandTotalRetailValue += $retailValue;

                if ($statusKey === 'low') {
                    $totalLowStockCount++;
                } elseif ($statusKey === 'out_of_stock') {
                    $totalOutOfStockCount++;
                }
            }

            // Diskon String
            $discountStr = '-';
            if (!empty($variant->discount_value) && $variant->discount_value > 0) {
                if ($variant->discount_type === 'percent' || $variant->discount_type === '%') {
                    $discountStr = number_format($variant->discount_value, 0, ',', '.') . '%';
                } else {
                    $discountStr = 'Rp ' . number_format($variant->discount_value, 0, ',', '.');
                }
            }

            // PPN String
            $taxStr = '-';
            if (!empty($variant->tax_value) && $variant->tax_value > 0) {
                if ($variant->tax_type === 'percent' || $variant->tax_type === '%') {
                    $taxStr = number_format($variant->tax_value, 0, ',', '.') . '%';
                } else {
                    $taxStr = 'Rp ' . number_format($variant->tax_value, 0, ',', '.');
                }
            }

            $rows[] = [
                'id'            => $variant->id,
                'sku'           => $variant->sku ?: '-',
                'barcode'       => $variant->barcode ?: '-',
                'product_name'  => $product->name,
                'variant_name'  => $variant->name ?: 'Standard',
                'full_name'     => $product->name . ($variant->name && $variant->name !== 'Standard' ? " ({$variant->name})" : ''),
                'category'      => $product->category ?: 'Umum',
                'brand'         => $product->brand ?: '-',
                'unit'          => $product->unit?->name ?? 'Pcs',
                'stock'         => $stockQty,
                'min_stock'     => $minStock,
                'status_key'    => $statusKey,
                'status_label'  => $statusLabel,
                'badge_color'   => $badgeColor,
                'cost_price'    => $costPrice,
                'retail_price'  => $retailPrice,
                'discount'      => $discountStr,
                'tax'           => $taxStr,
                'valuation'     => $valuation,
                'retail_value'  => $retailValue,
            ];
        }

        // Sort rows by product name
        usort($rows, function ($a, $b) {
            return strcmp($a['full_name'], $b['full_name']);
        });

        return [
            'branch_id'                => $branchId,
            'branch_name'              => $branch ? $branch->name : 'Semua Cabang',
            'category'                 => $category ?: 'Semua Kategori',
            'stock_status'             => $stockStatus,
            'search'                   => $search,
            'rows'                     => $rows,
            'total_variants'           => $totalVariants,
            'total_physical_qty'       => $totalPhysicalQty,
            'total_low_stock_count'    => $totalLowStockCount,
            'total_out_of_stock_count' => $totalOutOfStockCount,
            'grand_total_valuation'    => $grandTotalValuation,
            'grand_total_retail_value' => $grandTotalRetailValue,
        ];
    }
}
