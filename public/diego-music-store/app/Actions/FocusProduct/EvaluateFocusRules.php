<?php

namespace App\Actions\FocusProduct;

use App\Helpers\FocusProductHelper;
use App\Models\Branch;
use App\Models\FocusProduct;
use App\Models\FocusProductRecommendation;
use App\Models\FocusProductRule;
use App\Models\ProductBranchStock;
use Illuminate\Support\Facades\DB;

class EvaluateFocusRules
{
    protected CalculateStockAging $calculateStockAging;

    public function __construct(CalculateStockAging $calculateStockAging)
    {
        $this->calculateStockAging = $calculateStockAging;
    }

    /**
     * Run evaluation of focus rules for a specific branch or all active branches.
     *
     * @param int|null $branchId
     * @return array{branches_evaluated: int, recommendations_generated: int, recommendations_updated: int}
     */
    public function execute(?int $branchId = null): array
    {
        $branchesQuery = Branch::where('is_active', true);
        if ($branchId) {
            $branchesQuery->where('id', $branchId);
        }
        $branches = $branchesQuery->get();

        $rules = FocusProductRule::where('is_active', true)
            ->orderBy('priority')
            ->get();

        if ($rules->isEmpty() || $branches->isEmpty()) {
            return [
                'branches_evaluated' => $branches->count(),
                'recommendations_generated' => 0,
                'recommendations_updated' => 0,
            ];
        }

        $generatedCount = 0;
        $updatedCount = 0;

        foreach ($branches as $branch) {
            // Ambil semua variant yang sudah berstatus ACTIVE focus di cabang ini agar tidak direkomendasikan ulang
            $activeFocusVariantIds = FocusProduct::where('branch_id', $branch->id)
                ->where('status', 'ACTIVE')
                ->pluck('product_variant_id')
                ->toArray();

            // Ambil stok produk > 0 di cabang ini
            $stocks = ProductBranchStock::with(['productVariant.product'])
                ->where('branch_id', $branch->id)
                ->where('stock', '>', 0)
                ->whereNotIn('product_variant_id', $activeFocusVariantIds)
                ->get();

            foreach ($stocks as $branchStock) {
                $variantId = $branchStock->product_variant_id;
                $currentStock = (int) $branchStock->stock;
                $hpp = (int) $branchStock->hpp;

                foreach ($rules as $rule) {
                    $conditions = $rule->conditions ?? [];
                    $match = false;
                    $score = 0;
                    $reason = '';
                    $salesQty = 0;
                    $agingDays = null;

                    switch ($rule->code) {
                        case 'dead_stock':
                            $periodMonths = (int) ($conditions['period_months'] ?? 6);
                            $salesSummary = FocusProductHelper::getRecentSalesSummary($branch->id, $variantId, $periodMonths);
                            $salesQty = $salesSummary['total_qty'];

                            if ($salesQty === 0 && $currentStock > 0) {
                                $match = true;
                                $score = 80;
                                // Tambah skor jika nilai modal stok tinggi
                                $stockValue = $currentStock * $hpp;
                                if ($stockValue >= 5000000) {
                                    $score += 15;
                                } elseif ($stockValue >= 1000000) {
                                    $score += 10;
                                }
                                $score = min(100, $score);
                                $reason = "Stok saat ini {$currentStock} unit tanpa ada transaksi penjualan sama sekali dalam {$periodMonths} bulan terakhir.";
                            }
                            break;

                        case 'slow_moving':
                            $periodMonths = (int) ($conditions['period_months'] ?? 6);
                            $maxSalesQty = (int) ($conditions['max_sales_qty'] ?? 2);
                            $salesSummary = FocusProductHelper::getRecentSalesSummary($branch->id, $variantId, $periodMonths);
                            $salesQty = $salesSummary['total_qty'];

                            if ($salesQty > 0 && $salesQty <= $maxSalesQty && $currentStock > 0) {
                                $match = true;
                                $score = 70;
                                $stockValue = $currentStock * $hpp;
                                if ($stockValue >= 3000000) {
                                    $score += 10;
                                }
                                $score = min(100, $score);
                                $reason = "Stok saat ini {$currentStock} unit dan hanya terjual {$salesQty} unit dalam {$periodMonths} bulan terakhir.";
                            }
                            break;

                        case 'aging_stock':
                            $minAgingDays = (int) ($conditions['min_aging_days'] ?? 180);
                            $agingResult = $this->calculateStockAging->execute($branch->id, $variantId);
                            $agingDays = $agingResult['aging_days'];

                            if ($agingDays >= $minAgingDays && $currentStock > 0) {
                                $match = true;
                                $extraDays = $agingDays - $minAgingDays;
                                $score = min(100, 65 + (int) ($extraDays / 15));
                                $reason = "Stok saat ini {$currentStock} unit telah mengendap selama {$agingDays} hari di cabang ini.";
                            }
                            break;

                        case 'overstock':
                            $salesSummary = FocusProductHelper::getRecentSalesSummary($branch->id, $variantId, 6);
                            $salesQty = $salesSummary['total_qty'];
                            $monthlyVelocity = $salesQty / 6.0;

                            if ($currentStock > 0) {
                                if ($monthlyVelocity > 0 && ($currentStock / $monthlyVelocity) >= 12) {
                                    $monthsOfInventory = round($currentStock / $monthlyVelocity, 1);
                                    $match = true;
                                    $score = 80;
                                    $reason = "Stok saat ini {$currentStock} unit jauh melebihi laju penjualan (estimasi persediaan habis {$monthsOfInventory} bulan).";
                                } elseif ($currentStock >= 10 && $salesQty <= 1) {
                                    $match = true;
                                    $score = 85;
                                    $reason = "Stok melimpah ({$currentStock} unit) sementara perputaran penjualan sangat rendah ({$salesQty} unit / 6 bulan).";
                                }
                            }
                            break;

                        case 'stock_value_at_risk':
                            $minStockValue = (int) ($conditions['min_stock_value'] ?? 5000000);
                            $maxSales = (int) ($conditions['max_sales_qty'] ?? 1);
                            $salesSummary = FocusProductHelper::getRecentSalesSummary($branch->id, $variantId, 6);
                            $salesQty = $salesSummary['total_qty'];
                            $stockValue = $currentStock * $hpp;

                            if ($stockValue >= $minStockValue && $salesQty <= $maxSales && $currentStock > 0) {
                                $match = true;
                                $score = 90;
                                $formattedVal = 'Rp ' . number_format($stockValue, 0, ',', '.');
                                $reason = "Nilai modal stok tinggi ({$formattedVal}) namun perputaran penjualan rendah ({$salesQty} unit / 6 bulan).";
                            }
                            break;
                    }

                    if ($match) {
                        // Periksa apakah rekomendasi sudah ada
                        $existing = FocusProductRecommendation::where([
                            'branch_id' => $branch->id,
                            'product_variant_id' => $variantId,
                            'rule_id' => $rule->id,
                        ])->first();

                        if ($existing) {
                            if ($existing->status === 'PENDING') {
                                $existing->update([
                                    'score' => $score,
                                    'current_stock' => $currentStock,
                                    'recent_sales_qty' => $salesQty,
                                    'aging_days' => $agingDays,
                                    'reason' => $reason,
                                    'evaluated_at' => now(),
                                ]);
                                $updatedCount++;
                            }
                            // Jika sudah ACCEPTED atau DISMISSED, jangan timpa statusnya
                        } else {
                            FocusProductRecommendation::create([
                                'branch_id' => $branch->id,
                                'product_variant_id' => $variantId,
                                'rule_id' => $rule->id,
                                'score' => $score,
                                'current_stock' => $currentStock,
                                'recent_sales_qty' => $salesQty,
                                'aging_days' => $agingDays,
                                'reason' => $reason,
                                'status' => 'PENDING',
                                'evaluated_at' => now(),
                            ]);
                            $generatedCount++;
                        }
                    }
                }
            }
        }

        return [
            'branches_evaluated' => $branches->count(),
            'recommendations_generated' => $generatedCount,
            'recommendations_updated' => $updatedCount,
        ];
    }
}
