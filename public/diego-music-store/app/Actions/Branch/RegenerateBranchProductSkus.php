<?php

namespace App\Actions\Branch;

use App\Models\Branch;
use App\Models\ProductBranchStock;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class RegenerateBranchProductSkus
{
    public const SCOPE_ALL = 'all';
    public const SCOPE_BRANCH_STOCK = 'branch_stock';
    public const SCOPE_BRANCH_POSITIVE_STOCK = 'branch_positive_stock';

    public const MODE_MISSING_PREFIX = 'missing_prefix';
    public const MODE_ALL_OVERWRITE = 'all_overwrite';

    public const FORMAT_RANDOM = 'random';
    public const FORMAT_SEQUENTIAL = 'sequential';

    /**
     * Normalize a raw branch SKU prefix into an uppercase code without trailing dashes.
     */
    public static function normalizePrefix(?string $prefix): string
    {
        $clean = strtoupper(trim((string) $prefix));
        if ($clean === '') {
            return '';
        }

        $clean = preg_replace('/[^A-Z0-9_-]+/', '-', $clean) ?? $clean;
        $clean = preg_replace('/-+/', '-', $clean) ?? $clean;

        return trim($clean, '-');
    }

    /**
     * Count candidate product variants across regeneration scopes for a branch.
     *
     * @return array{all: int, branch_stock: int, branch_positive_stock: int}
     */
    public static function getScopeCounts(Branch $branch): array
    {
        return [
            self::SCOPE_ALL => (int) ProductVariant::query()->count(),
            self::SCOPE_BRANCH_STOCK => (int) ProductBranchStock::query()
                ->where('branch_id', $branch->id)
                ->distinct()
                ->count('product_variant_id'),
            self::SCOPE_BRANCH_POSITIVE_STOCK => (int) ProductBranchStock::query()
                ->where('branch_id', $branch->id)
                ->where('stock', '>', 0)
                ->distinct()
                ->count('product_variant_id'),
        ];
    }

    /**
     * Select options for regeneration scope.
     *
     * @return array<string, string>
     */
    public static function scopeOptions(?Branch $branch = null): array
    {
        if ($branch === null) {
            return [
                self::SCOPE_ALL => 'Semua Produk di Katalog',
                self::SCOPE_BRANCH_STOCK => 'Produk Terdaftar di Stok Cabang Ini',
                self::SCOPE_BRANCH_POSITIVE_STOCK => 'Hanya Produk dengan Stok > 0 di Cabang Ini',
            ];
        }

        $counts = self::getScopeCounts($branch);

        return [
            self::SCOPE_ALL => "Semua Produk di Katalog ({$counts[self::SCOPE_ALL]} varian)",
            self::SCOPE_BRANCH_STOCK => "Produk Terdaftar di Cabang Ini ({$counts[self::SCOPE_BRANCH_STOCK]} varian)",
            self::SCOPE_BRANCH_POSITIVE_STOCK => "Hanya Produk Stok > 0 di Cabang Ini ({$counts[self::SCOPE_BRANCH_POSITIVE_STOCK]} varian)",
        ];
    }

    /**
     * Select options for regeneration overwrite mode.
     *
     * @return array<string, string>
     */
    public static function modeOptions(): array
    {
        return [
            self::MODE_MISSING_PREFIX => 'Hanya yang belum memakai prefix ini (Aman)',
            self::MODE_ALL_OVERWRITE => 'Timpa semua SKU target (Generate ulang seluruhnya)',
        ];
    }

    /**
     * Select options for generated SKU suffix format.
     *
     * @return array<string, string>
     */
    public static function formatOptions(): array
    {
        return [
            self::FORMAT_RANDOM => 'Acak 8 karakter (contoh: PREFIX-A1B2C3D4)',
            self::FORMAT_SEQUENTIAL => 'Berurutan angka (contoh: PREFIX-0001, PREFIX-0002)',
        ];
    }

    /**
     * Format a human-readable notification summary for regeneration results.
     *
     * @param  array{updated: int, skipped: int, total: int, prefix: string, sample_skus: array<int, string>}  $result
     */
    public static function formatResultMessage(array $result): string
    {
        $summary = "{$result['updated']} SKU diperbarui dengan prefix {$result['prefix']}-"
            . ($result['skipped'] > 0 ? " ({$result['skipped']} dilewati karena sudah sesuai)." : '.');

        if (! empty($result['sample_skus'])) {
            $summary .= ' Contoh: ' . implode(', ', $result['sample_skus']) . '.';
        }

        return $summary;
    }

    /**
     * Regenerate product variant SKUs using a branch's SKU prefix without altering barcodes, prices, or stocks.
     *
     * @return array{updated: int, skipped: int, total: int, prefix: string, sample_skus: array<int, string>}
     */
    public function execute(
        Branch $branch,
        ?string $prefixOverride = null,
        string $scope = self::SCOPE_ALL,
        string $mode = self::MODE_MISSING_PREFIX,
        string $format = self::FORMAT_RANDOM,
        bool $savePrefixToBranch = false
    ): array {
        $rawPrefix = $prefixOverride !== null ? $prefixOverride : $branch->sku_prefix;
        $normalizedPrefix = self::normalizePrefix($rawPrefix);

        if ($normalizedPrefix === '') {
            throw new InvalidArgumentException('Prefix Kode Produk / SKU cabang wajib diisi sebelum generate ulang.');
        }

        return DB::transaction(function () use (
            $branch,
            $normalizedPrefix,
            $scope,
            $mode,
            $format,
            $savePrefixToBranch
        ): array {
            if ($savePrefixToBranch && $branch->sku_prefix !== $normalizedPrefix) {
                $branch->update(['sku_prefix' => $normalizedPrefix]);
            }

            $prefixWithDash = $normalizedPrefix . '-';

            $query = ProductVariant::query()
                ->select(['id', 'sku'])
                ->orderBy('id');

            if ($scope === self::SCOPE_BRANCH_POSITIVE_STOCK) {
                $query->whereHas('branchStocks', fn ($q) => $q->where('branch_id', $branch->id)->where('stock', '>', 0));
            } elseif ($scope === self::SCOPE_BRANCH_STOCK) {
                $query->whereHas('branchStocks', fn ($q) => $q->where('branch_id', $branch->id));
            }

            $variants = $query->get();
            $totalEvaluated = $variants->count();

            $targetsToUpdate = [];
            $skippedCount = 0;

            foreach ($variants as $variant) {
                $currentSku = strtoupper(trim((string) ($variant->sku ?? '')));
                if ($mode === self::MODE_MISSING_PREFIX && $currentSku !== '' && str_starts_with($currentSku, $prefixWithDash)) {
                    $skippedCount++;
                    continue;
                }

                $targetsToUpdate[] = $variant;
            }

            if ($targetsToUpdate === []) {
                return [
                    'updated' => 0,
                    'skipped' => $skippedCount,
                    'total' => $totalEvaluated,
                    'prefix' => $normalizedPrefix,
                    'sample_skus' => [],
                ];
            }

            // Preload all existing SKUs in one query to guarantee global uniqueness cleanly
            $allExistingSkus = [];
            foreach (ProductVariant::query()->whereNotNull('sku')->pluck('id', 'sku') as $sku => $id) {
                $allExistingSkus[strtoupper((string) $sku)] = (int) $id;
            }

            $targetIds = [];
            foreach ($targetsToUpdate as $target) {
                $targetIds[(int) $target->id] = true;
            }

            $untouchedSkus = [];
            foreach ($allExistingSkus as $upperSku => $id) {
                if (! isset($targetIds[$id])) {
                    $untouchedSkus[$upperSku] = true;
                }
            }

            $newSkuById = $this->buildNewSkuMap(
                $targetsToUpdate,
                $prefixWithDash,
                $format,
                $allExistingSkus,
                $untouchedSkus
            );

            // Two-phase update prevents unique index collisions when target SKUs swap/overlap (e.g. PTK-0002 -> PTK-0001)
            $needsTempPhase = false;
            foreach ($newSkuById as $id => $newSku) {
                $upper = strtoupper($newSku);
                if (isset($allExistingSkus[$upper]) && $allExistingSkus[$upper] !== $id && isset($targetIds[$allExistingSkus[$upper]])) {
                    $needsTempPhase = true;
                    break;
                }
            }

            $now = now();

            if ($needsTempPhase) {
                foreach ($newSkuById as $id => $_) {
                    DB::table('product_variants')
                        ->where('id', $id)
                        ->update(['sku' => '__TMP-' . $id . '-' . Str::random(6)]);
                }
            }

            foreach ($newSkuById as $id => $newSku) {
                DB::table('product_variants')
                    ->where('id', $id)
                    ->update([
                        'sku' => $newSku,
                        'updated_at' => $now,
                    ]);
            }

            return [
                'updated' => count($newSkuById),
                'skipped' => $skippedCount,
                'total' => $totalEvaluated,
                'prefix' => $normalizedPrefix,
                'sample_skus' => array_slice(array_values($newSkuById), 0, 5),
            ];
        });
    }

    /**
     * Build unique target SKUs mapped by product_variant_id.
     *
     * @param  array<int, ProductVariant>  $targetsToUpdate
     * @param  array<string, int>  $allExistingSkus
     * @param  array<string, bool>  $untouchedSkus
     * @return array<int, string>
     */
    private function buildNewSkuMap(
        array $targetsToUpdate,
        string $prefixWithDash,
        string $format,
        array $allExistingSkus,
        array $untouchedSkus
    ): array {
        $newSkuById = [];
        $reservedNewSkus = [];

        if ($format === self::FORMAT_SEQUENTIAL) {
            $maxSeq = 0;
            $pattern = '/^' . preg_quote($prefixWithDash, '/') . '(\d+)$/';
            foreach (array_keys($untouchedSkus) as $upperSku) {
                if (preg_match($pattern, $upperSku, $matches)) {
                    $maxSeq = max($maxSeq, (int) $matches[1]);
                }
            }

            $padWidth = max(4, strlen((string) ($maxSeq + count($targetsToUpdate))));
            $seq = $maxSeq;

            foreach ($targetsToUpdate as $variant) {
                do {
                    $seq++;
                    $candidate = $prefixWithDash . str_pad((string) $seq, $padWidth, '0', STR_PAD_LEFT);
                    $upper = strtoupper($candidate);
                } while (isset($untouchedSkus[$upper]) || isset($reservedNewSkus[$upper]));

                $reservedNewSkus[$upper] = true;
                $newSkuById[(int) $variant->id] = $candidate;
            }

            return $newSkuById;
        }

        foreach ($targetsToUpdate as $variant) {
            do {
                $candidate = $prefixWithDash . strtoupper(Str::random(8));
                $upper = strtoupper($candidate);
            } while (isset($allExistingSkus[$upper]) || isset($reservedNewSkus[$upper]));

            $reservedNewSkus[$upper] = true;
            $newSkuById[(int) $variant->id] = $candidate;
        }

        return $newSkuById;
    }
}

