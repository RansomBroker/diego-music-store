<?php

namespace App\Actions\StockOpname;

use App\Models\StockOpname;
use App\Models\StockOpnameItem;
use App\Models\ProductBranchStock;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class UpdateStockOpname
{
    /**
     * Execute the action to update a Stock Opname.
     *
     * @param  StockOpname  $opname
     * @param  array<string, mixed>  $data
     * @return StockOpname
     */
    public function execute(StockOpname $opname, array $data): StockOpname
    {
        return DB::transaction(function () use ($opname, $data) {
            // Guard clause: status must be draft
            if ($opname->status !== 'draft') {
                throw new InvalidArgumentException('Stok Opname yang sudah selesai tidak dapat diubah.');
            }

            // 1. Update header details
            $opname->update([
                'branch_id' => $data['branch_id'] ?? $opname->branch_id,
                'opname_date' => $data['opname_date'] ?? $opname->opname_date,
                'notes' => $data['notes'] ?? $opname->notes,
            ]);

            // 2. Sync items (delete existing and recreate)
            if (isset($data['items'])) {
                $opname->items()->delete();
                foreach ($data['items'] as $item) {
                    $variantId = $item['product_variant_id'];
                    $physicalQty = intval($item['physical_qty']);

                    // Get system qty and cost price from current branch stock record
                    $branchStock = ProductBranchStock::where([
                        'branch_id' => $opname->branch_id,
                        'product_variant_id' => $variantId,
                    ])->first();

                    $systemQty = $branchStock ? $branchStock->stock : 0;
                    
                    // Use HPP from branch stock, fallback to variant HPP or cost_price
                    $costPrice = 0;
                    if ($branchStock && $branchStock->hpp > 0) {
                        $costPrice = $branchStock->hpp;
                    } else {
                        $variant = ProductVariant::find($variantId);
                        $costPrice = $variant ? ($variant->hpp ?: $variant->cost_price ?: 0) : 0;
                    }

                    StockOpnameItem::create([
                        'stock_opname_id' => $opname->id,
                        'product_variant_id' => $variantId,
                        'system_qty' => $systemQty,
                        'physical_qty' => $physicalQty,
                        'difference' => $physicalQty - $systemQty,
                        'cost_price' => $costPrice,
                    ]);
                }
            }

            // 3. Process status transition if requested
            if (($data['status'] ?? 'draft') === 'completed') {
                app(ProcessStockOpnameComplete::class)->execute($opname);
            }

            return $opname;
        });
    }
}
