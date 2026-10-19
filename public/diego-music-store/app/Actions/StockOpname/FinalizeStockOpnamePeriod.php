<?php

namespace App\Actions\StockOpname;

use App\Models\ProductBranchStock;
use App\Models\ProductVariant;
use App\Models\StockOpname;
use App\Models\StockOpnameAssignment;
use App\Models\StockOpnameItem;
use App\Models\StockOpnamePeriod;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class FinalizeStockOpnamePeriod
{
    public function execute(StockOpnamePeriod $period): StockOpname
    {
        return DB::transaction(function () use ($period): StockOpname {
            $lockedPeriod = StockOpnamePeriod::query()
                ->whereKey($period->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedPeriod->status !== StockOpnamePeriod::STATUS_REVIEW) {
                throw ValidationException::withMessages([
                    'period' => 'Periode harus berada pada status review sebelum finalisasi.',
                ]);
            }

            $assignments = StockOpnameAssignment::query()
                ->where('stock_opname_period_id', $lockedPeriod->id)
                ->with('stockOpname.items')
                ->lockForUpdate()
                ->get();

            if ($assignments->isEmpty() || $assignments->contains(
                fn (StockOpnameAssignment $assignment) => $assignment->status !== StockOpnameAssignment::STATUS_COMPLETED
            )) {
                throw ValidationException::withMessages([
                    'period' => 'Semua tugas lokasi harus disetujui owner sebelum finalisasi.',
                ]);
            }

            $physicalCounts = [];
            foreach ($assignments as $assignment) {
                if (!$assignment->stockOpname || $assignment->stockOpname->status !== 'draft') {
                    throw ValidationException::withMessages([
                        'period' => 'Dokumen salah satu tugas tidak tersedia atau sudah dikunci.',
                    ]);
                }

                foreach ($assignment->stockOpname->items as $item) {
                    $variantId = (int) $item->product_variant_id;
                    $physicalCounts[$variantId] = ($physicalCounts[$variantId] ?? 0) + (int) $item->physical_qty;
                }
            }

            if ($physicalCounts === []) {
                throw ValidationException::withMessages([
                    'period' => 'Belum ada hitungan fisik untuk difinalisasi.',
                ]);
            }

            // Fail closed: every active physical variant in the catalog must be represented
            // at least once across the submitted location counts before branch stock is adjusted.
            $missingVariant = ProductVariant::query()
                ->join('products', 'products.id', '=', 'product_variants.product_id')
                ->where('products.type', 'physical')
                ->where('products.is_active', true)
                ->where('product_variants.is_active', true)
                ->whereNotIn('product_variants.id', array_keys($physicalCounts))
                ->select('product_variants.id', 'products.name', 'product_variants.name as variant_name', 'product_variants.sku')
                ->first();

            if ($missingVariant) {
                throw ValidationException::withMessages([
                    'period' => 'Finalisasi diblokir. Produk belum memiliki hitungan di lokasi mana pun: '
                        . $missingVariant->name
                        . ($missingVariant->variant_name ? ' - ' . $missingVariant->variant_name : '')
                        . ($missingVariant->sku ? ' (SKU ' . $missingVariant->sku . ')' : '')
                        . '. Pastikan seluruh produk fisik sudah dihitung sebelum finalisasi.',
                ]);
            }

            $consolidated = StockOpname::create([
                'branch_id' => $lockedPeriod->branch_id,
                'opname_date' => $lockedPeriod->period,
                'status' => 'draft',
                'notes' => 'Finalisasi periode ' . $lockedPeriod->name
                    . ' (periode #' . $lockedPeriod->id . '); hasil agregasi '
                    . $assignments->count() . ' lokasi.',
            ]);

            foreach ($physicalCounts as $variantId => $physicalQty) {
                $variant = ProductVariant::query()->with('product')->findOrFail($variantId);
                $branchStock = ProductBranchStock::query()
                    ->where('branch_id', $lockedPeriod->branch_id)
                    ->where('product_variant_id', $variantId)
                    ->lockForUpdate()
                    ->first();

                $systemQty = (int) ($branchStock?->stock ?? 0);
                $costPrice = (int) (($branchStock?->hpp > 0)
                    ? $branchStock->hpp
                    : ($variant->hpp ?: $variant->cost_price ?: 0));

                StockOpnameItem::create([
                    'stock_opname_id' => $consolidated->id,
                    'product_variant_id' => $variantId,
                    'system_qty' => $systemQty,
                    'physical_qty' => $physicalQty,
                    'difference' => $physicalQty - $systemQty,
                    'cost_price' => $costPrice,
                ]);
            }

            // This existing action is the only place that adjusts branch stock and posts journals.
            app(ProcessStockOpnameComplete::class)->execute($consolidated);

            foreach ($assignments as $assignment) {
                $assignment->stockOpname->update(['status' => 'completed']);
            }

            $lockedPeriod->update(['status' => StockOpnamePeriod::STATUS_COMPLETED]);

            return $consolidated->fresh();
        });
    }
}
