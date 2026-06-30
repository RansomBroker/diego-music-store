<?php

namespace App\Actions\StockOpname;

use App\Models\StockOpname;
use App\Models\ProductBranchStock;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ProcessStockOpnameComplete
{
    /**
     * Execute the transition to complete a Stock Opname.
     * Updates branch stock to physical quantity and records stock movements.
     *
     * @param  StockOpname  $opname
     * @return void
     */
    public function execute(StockOpname $opname): void
    {
        DB::transaction(function () use ($opname) {
            // Guard clause: status must be draft
            if ($opname->status !== 'draft') {
                throw new InvalidArgumentException('Hanya stok opname dengan status draft yang dapat diselesaikan.');
            }

            // Process each item
            foreach ($opname->items as $item) {
                // Find or create product branch stock record
                $branchStock = ProductBranchStock::firstOrCreate([
                    'branch_id' => $opname->branch_id,
                    'product_variant_id' => $item->product_variant_id,
                ], [
                    'stock' => 0,
                    'hpp' => $item->cost_price ?: ($item->productVariant->hpp ?? 0),
                ]);

                // Update stock quantity to match physical quantity
                $branchStock->update([
                    'stock' => $item->physical_qty,
                ]);

                // Log discrepancy to stock movements
                $diff = $item->difference;
                if ($diff !== 0) {
                    StockMovement::create([
                        'product_variant_id' => $item->product_variant_id,
                        'branch_id' => $opname->branch_id,
                        'type' => $diff > 0 ? 'in' : 'out',
                        'quantity' => abs($diff),
                        'reference_type' => 'Opname',
                        'reference_id' => $opname->id,
                    ]);
                }
            }

            // Update status to completed
            $opname->update([
                'status' => 'completed',
            ]);
        });
    }
}
