<?php

namespace App\Actions\FocusProduct;

use App\Models\FocusProduct;
use App\Models\FocusProductRecommendation;
use App\Models\FocusProductRule;
use Illuminate\Support\Facades\DB;

class ApplyFocusRulesToBranch
{
    protected EvaluateFocusRules $evaluateFocusRules;

    public function __construct(EvaluateFocusRules $evaluateFocusRules)
    {
        $this->evaluateFocusRules = $evaluateFocusRules;
    }

    /**
     * Apply selected focus rules to a branch, automatically creating active focus products.
     *
     * @param int $branchId
     * @param array<string> $ruleCodes
     * @param string|null $activeUntil
     * @param string|null $note
     * @param int|null $userId
     * @return array{focused_count: int, rules_applied: int}
     */
    public function execute(
        int $branchId,
        array $ruleCodes,
        ?string $activeUntil = null,
        ?string $note = null,
        ?int $userId = null
    ): array {
        if (empty($ruleCodes)) {
            return [
                'focused_count' => 0,
                'rules_applied' => 0,
            ];
        }

        // 1. Jalankan evaluasi rule realtime agar rekomendasi stok cabang paling akurat
        $this->evaluateFocusRules->execute($branchId);

        $ruleIds = FocusProductRule::whereIn('code', $ruleCodes)->pluck('id');

        return DB::transaction(function () use ($branchId, $ruleIds, $activeUntil, $note, $userId, $ruleCodes) {
            $recommendations = FocusProductRecommendation::where('branch_id', $branchId)
                ->whereIn('rule_id', $ruleIds)
                ->where('status', 'PENDING')
                ->get();

            $focusedCount = 0;

            foreach ($recommendations as $recommendation) {
                // Cegah duplikasi jika produk ini sudah aktif sebagai fokus di cabang ini
                $alreadyActive = FocusProduct::where('branch_id', $branchId)
                    ->where('product_variant_id', $recommendation->product_variant_id)
                    ->where('status', 'ACTIVE')
                    ->exists();

                if (!$alreadyActive) {
                    FocusProduct::create([
                        'branch_id' => $branchId,
                        'product_variant_id' => $recommendation->product_variant_id,
                        'source' => 'RULE',
                        'rule_id' => $recommendation->rule_id,
                        'recommendation_id' => $recommendation->id,
                        'status' => 'ACTIVE',
                        'reason' => $recommendation->reason,
                        'note' => $note,
                        'active_from' => now(),
                        'active_until' => $activeUntil,
                        'focused_at' => now(),
                        'focused_by' => $userId,
                    ]);

                    $focusedCount++;
                }

                $recommendation->update(['status' => 'ACCEPTED']);
            }

            return [
                'focused_count' => $focusedCount,
                'rules_applied' => count($ruleCodes),
            ];
        });
    }
}
