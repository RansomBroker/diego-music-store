<?php

namespace App\Actions\StockOpname;

use App\Models\StockOpname;
use App\Models\StockOpnameItem;
use App\Models\ProductBranchStock;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;

class CreateStockOpname
{
    /**
     * Execute the action to create a Stock Opname.
     *
     * @param  array<string, mixed>  $data
     * @return StockOpname
     */
    public function execute(array $data): StockOpname
    {
        return DB::transaction(function () use ($data) {
            $items = $data['items'] ?? [];

            $opnameNumber = $data['opname_number'] ?? null;
            if (empty($opnameNumber)) {
                $opnameNumber = StockOpname::generateOpnameNumber();
            }

            // 1. Create the base record as draft
            $opname = StockOpname::create([
                'branch_id' => $data['branch_id'],
                'opname_number' => $opnameNumber,
                'opname_date' => $data['opname_date'],
                'status' => 'draft',
                'notes' => $data['notes'] ?? null,
            ]);

            // 2. Create items
            foreach ($items as $item) {
                $variantId = $item['product_variant_id'];
                $physicalQty = intval($item['physical_qty']);

                // Get system qty and cost price from current branch stock record
                $branchStock = ProductBranchStock::where([
                    'branch_id' => $data['branch_id'],
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

            // 3. Process status transition if requested
            if (($data['status'] ?? 'draft') === 'completed') {
                app(ProcessStockOpnameComplete::class)->execute($opname);
            }

            return $opname;
        });
    }
}
