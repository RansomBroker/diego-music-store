<?php

namespace App\Actions\FocusProduct;

use App\Models\ProductBranchStock;
use App\Models\StockMovement;
use Carbon\Carbon;

class CalculateStockAging
{
    /**
     * Calculate stock aging in days for a specific product variant in a branch based on FIFO inbound movements.
     *
     * @param int $branchId
     * @param int $productVariantId
     * @return array{aging_days: int, oldest_batch_date: ?Carbon, current_stock: int}
     */
    public function execute(int $branchId, int $productVariantId): array
    {
        $branchStock = ProductBranchStock::where([
            'branch_id' => $branchId,
            'product_variant_id' => $productVariantId,
        ])->first();

        $currentStock = $branchStock ? (int) $branchStock->stock : 0;

        if ($currentStock <= 0) {
            return [
                'aging_days' => 0,
                'oldest_batch_date' => null,
                'current_stock' => 0,
            ];
        }

        // Ambil semua mutasi stok masuk (in) dari yang terbaru ke terlama
        $inboundMovements = StockMovement::where([
            'branch_id' => $branchId,
            'product_variant_id' => $productVariantId,
            'type' => 'in',
        ])
        ->orderBy('created_at', 'desc')
        ->get();

        if ($inboundMovements->isEmpty()) {
            return [
                'aging_days' => 0,
                'oldest_batch_date' => null,
                'current_stock' => $currentStock,
            ];
        }

        $accumulatedQty = 0;
        $oldestBatchDate = null;

        foreach ($inboundMovements as $movement) {
            $qty = (int) $movement->quantity;
            $accumulatedQty += $qty;
            $oldestBatchDate = Carbon::parse($movement->created_at);

            // Jika akumulasi mutasi masuk sudah mencukupi stok yang masih ada saat ini (FIFO backward)
            if ($accumulatedQty >= $currentStock) {
                break;
            }
        }

        $agingDays = $oldestBatchDate ? (int) max(0, $oldestBatchDate->diffInDays(now())) : 0;

        return [
            'aging_days' => $agingDays,
            'oldest_batch_date' => $oldestBatchDate,
            'current_stock' => $currentStock,
        ];
    }
}
