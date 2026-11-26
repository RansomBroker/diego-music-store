<?php

namespace App\Actions\Product;

use App\Helpers\ProductHelper;
use App\Models\ProductVariant;
use App\Models\SkuSequence;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class ReserveNextSku
{
    /**
     * Atomically reserve and format the next unique sequential SKU for a branch/prefix.
     */
    public function execute(?string $prefix = null, ?int $branchId = null, int $minDigits = 4): string
    {
        $context = ProductHelper::resolveSkuContext($prefix, $branchId);
        $normalizedPrefix = $context['prefix'];
        $resolvedBranchId = $context['branch_id'];
        $safeMinDigits = max(1, $minDigits);

        return DB::transaction(function () use ($normalizedPrefix, $resolvedBranchId, $safeMinDigits): string {
            $sequence = SkuSequence::query()
                ->where('prefix', $normalizedPrefix)
                ->lockForUpdate()
                ->first();

            if ($sequence === null) {
                $initialLastNumber = ProductHelper::findMaxNumericSuffix($normalizedPrefix);

                try {
                    $sequence = SkuSequence::create([
                        'branch_id' => $resolvedBranchId,
                        'prefix' => $normalizedPrefix,
                        'last_number' => $initialLastNumber,
                        'min_digits' => $safeMinDigits,
                    ]);
                } catch (QueryException $e) {
                    $sequence = SkuSequence::query()
                        ->where('prefix', $normalizedPrefix)
                        ->lockForUpdate()
                        ->first();

                    if ($sequence === null) {
                        throw $e;
                    }
                }
            }

            if ($sequence->branch_id === null && $resolvedBranchId !== null) {
                $sequence->branch_id = $resolvedBranchId;
            }

            $effectiveMinDigits = max(1, (int) ($sequence->min_digits ?: $safeMinDigits));
            $nextNumber = (int) $sequence->last_number;

            do {
                $nextNumber++;
                $padWidth = max($effectiveMinDigits, strlen((string) $nextNumber));
                $candidate = $normalizedPrefix . '-' . str_pad((string) $nextNumber, $padWidth, '0', STR_PAD_LEFT);
            } while (ProductVariant::where('sku', $candidate)->exists());

            $sequence->last_number = $nextNumber;
            $sequence->save();

            return $candidate;
        });
    }
}

