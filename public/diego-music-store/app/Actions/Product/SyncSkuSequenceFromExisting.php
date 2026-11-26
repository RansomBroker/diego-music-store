<?php

namespace App\Actions\Product;

use App\Actions\Branch\RegenerateBranchProductSkus;
use App\Helpers\ProductHelper;
use App\Models\SkuSequence;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class SyncSkuSequenceFromExisting
{
    /**
     * Ensure the sequence counter for a normalized SKU prefix is synchronized
     * with the highest numeric SKU currently stored and any optional floor number.
     */
    public function execute(string $prefix, int $minDigits = 4, ?int $floorNumber = null, ?int $branchId = null): SkuSequence
    {
        $normalizedPrefix = RegenerateBranchProductSkus::normalizePrefix($prefix);
        if ($normalizedPrefix === '') {
            $normalizedPrefix = 'DG';
        }

        $resolvedBranchId = $branchId ?? ProductHelper::resolveSkuContext($normalizedPrefix)['branch_id'];
        $maxInDb = ProductHelper::findMaxNumericSuffix($normalizedPrefix);
        $safeMinDigits = max(1, $minDigits);
        $safeFloor = max(0, (int) ($floorNumber ?? 0));

        return DB::transaction(function () use ($normalizedPrefix, $resolvedBranchId, $branchId, $maxInDb, $safeMinDigits, $safeFloor): SkuSequence {
            $sequence = SkuSequence::query()
                ->where('prefix', $normalizedPrefix)
                ->lockForUpdate()
                ->first();

            if ($sequence === null) {
                $initialLastNumber = max($maxInDb, $safeFloor);

                try {
                    return SkuSequence::create([
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

            $targetLastNumber = max((int) $sequence->last_number, $maxInDb, $safeFloor);
            $effectiveMinDigits = max(1, (int) ($sequence->min_digits ?: $safeMinDigits));

            $currentBranchId = $sequence->branch_id !== null ? (int) $sequence->branch_id : null;
            $targetBranchId = $currentBranchId;
            if ($branchId !== null && $targetBranchId !== $branchId) {
                $targetBranchId = $branchId;
            } elseif ($targetBranchId === null && $resolvedBranchId !== null) {
                $targetBranchId = $resolvedBranchId;
            }

            if (
                (int) $sequence->last_number !== $targetLastNumber
                || (int) $sequence->min_digits !== $effectiveMinDigits
                || $currentBranchId !== $targetBranchId
            ) {
                $sequence->branch_id = $targetBranchId;
                $sequence->last_number = $targetLastNumber;
                $sequence->min_digits = $effectiveMinDigits;
                $sequence->save();
            }

            return $sequence;
        });
    }
}

