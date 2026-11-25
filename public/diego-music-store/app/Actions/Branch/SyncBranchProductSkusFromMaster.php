<?php

namespace App\Actions\Branch;

use App\Helpers\SpreadsheetImportHelper;
use App\Models\Branch;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class SyncBranchProductSkusFromMaster
{
    /**
     * Required canonical headers in the stock master spreadsheet.
     *
     * @var array<int, string>
     */
    public const REQUIRED_HEADERS = ['kode_barang', 'nama_stok'];

    /**
     * Restore product_variants.sku for a branch from a stock master spreadsheet
     * without altering stock, prices, HPP, barcodes, or accounting journals.
     *
     * @return array{
     *     updated: int,
     *     unchanged: int,
     *     not_found: array<int, array{row: int, raw_code: string, name: string, target_sku: string}>,
     *     ambiguous: array<int, array{row: int, raw_code: string, name: string, reason: string}>,
     *     total_rows: int,
     *     skipped_blank_rows: int,
     *     prefix: string,
     *     sample_skus: array<int, string>
     * }
     */
    public function execute(
        Branch $branch,
        string $filePath,
        ?string $prefixOverride = null,
        bool $savePrefixToBranch = true,
        ?string $sheetName = null
    ): array {
        $rawPrefix = $prefixOverride !== null ? $prefixOverride : $branch->sku_prefix;
        $normalizedPrefix = RegenerateBranchProductSkus::normalizePrefix($rawPrefix);

        if ($normalizedPrefix === '') {
            throw new InvalidArgumentException('Prefix Kode Produk / SKU cabang wajib diisi sebelum memulihkan SKU master.');
        }

        $headerInfo = SpreadsheetImportHelper::readHeaders($filePath, $sheetName, self::REQUIRED_HEADERS);
        $validation = SpreadsheetImportHelper::validateHeaders($headerInfo['normalized'] ?? [], self::REQUIRED_HEADERS);

        if (! ($validation['is_valid'] ?? false)) {
            $missing = implode(', ', $validation['missing'] ?? self::REQUIRED_HEADERS);
            throw new InvalidArgumentException("Header file master belum lengkap. Kolom wajib tidak ditemukan: {$missing}.");
        }

        $rows = SpreadsheetImportHelper::readRows(
            $filePath,
            $sheetName,
            0,
            null,
            $headerInfo['header_row'] ?? null,
            $headerInfo['map'] ?? null
        );

        return DB::transaction(function () use (
            $branch,
            $normalizedPrefix,
            $savePrefixToBranch,
            $rows
        ): array {
            $knownBranchPrefixes = Branch::query()
                ->whereNotNull('sku_prefix')
                ->pluck('sku_prefix')
                ->filter()
                ->values()
                ->all();

            if ($branch->sku_prefix !== null && $branch->sku_prefix !== '') {
                $knownBranchPrefixes[] = $branch->sku_prefix;
            }
            $knownBranchPrefixes[] = $normalizedPrefix;

            if ($savePrefixToBranch && $branch->sku_prefix !== $normalizedPrefix) {
                $branch->update(['sku_prefix' => $normalizedPrefix]);
            }

            $catalog = $this->loadVariantLookupCatalog((int) $branch->id);

            $plannedByVariantId = [];
            $plannedSourceNameById = [];
            $unchangedCount = 0;
            $skippedBlankRows = 0;
            $notFound = [];
            $ambiguous = [];
            $totalDataRows = 0;

            foreach ($rows as $row) {
                $rowNumber = (int) ($row['_row_number'] ?? 0);
                $rawCode = trim((string) ($row['kode_barang'] ?? ''));
                $stockName = trim((string) ($row['nama_stok'] ?? ''));
                $barcode = trim((string) ($row['barcode'] ?? ''));

                if ($rawCode === '' && $stockName === '') {
                    $skippedBlankRows++;

                    continue;
                }

                $totalDataRows++;

                if ($rawCode === '' || $stockName === '') {
                    $notFound[] = [
                        'row' => $rowNumber,
                        'raw_code' => $rawCode,
                        'name' => $stockName,
                        'target_sku' => '',
                    ];

                    continue;
                }

                $targetSku = RegenerateBranchProductSkus::buildPrefixedSkuFromRaw(
                    $normalizedPrefix,
                    $rawCode,
                    $knownBranchPrefixes
                );

                if ($targetSku === '') {
                    $notFound[] = [
                        'row' => $rowNumber,
                        'raw_code' => $rawCode,
                        'name' => $stockName,
                        'target_sku' => '',
                    ];

                    continue;
                }

                $resolution = $this->resolveVariantCandidate($catalog, $stockName, $barcode);

                if ($resolution['status'] === 'not_found') {
                    $notFound[] = [
                        'row' => $rowNumber,
                        'raw_code' => $rawCode,
                        'name' => $stockName,
                        'target_sku' => $targetSku,
                    ];

                    continue;
                }

                if ($resolution['status'] === 'ambiguous') {
                    $ambiguous[] = [
                        'row' => $rowNumber,
                        'raw_code' => $rawCode,
                        'name' => $stockName,
                        'reason' => $resolution['reason'],
                    ];

                    continue;
                }

                /** @var object{id: int, sku: ?string} $variant */
                $variant = $resolution['variant'];
                $variantId = (int) $variant->id;

                if (isset($plannedByVariantId[$variantId])) {
                    if ($plannedByVariantId[$variantId] !== $targetSku) {
                        $ambiguous[] = [
                            'row' => $rowNumber,
                            'raw_code' => $rawCode,
                            'name' => $stockName,
                            'reason' => "Produk '{$plannedSourceNameById[$variantId]}' muncul lebih dari satu kali di master dengan kode berbeda.",
                        ];
                    }

                    continue;
                }

                if ((string) ($variant->sku ?? '') === $targetSku) {
                    $unchangedCount++;
                    // Lock variant ID out of stale duplicate planning if another row hits it
                    $plannedByVariantId[$variantId] = $targetSku;
                    $plannedSourceNameById[$variantId] = $stockName;

                    continue;
                }

                $plannedByVariantId[$variantId] = $targetSku;
                $plannedSourceNameById[$variantId] = $stockName;
            }

            // Separate variants that actually need a SKU change from those already matching
            $updatesToApply = [];
            foreach ($plannedByVariantId as $variantId => $targetSku) {
                $currentSku = (string) ($catalog['by_id'][$variantId]->sku ?? '');
                if ($currentSku !== $targetSku) {
                    $updatesToApply[$variantId] = $targetSku;
                }
            }

            if ($updatesToApply === []) {
                return [
                    'updated' => 0,
                    'unchanged' => $unchangedCount,
                    'not_found' => $notFound,
                    'ambiguous' => $ambiguous,
                    'total_rows' => $totalDataRows,
                    'skipped_blank_rows' => $skippedBlankRows,
                    'prefix' => $normalizedPrefix,
                    'sample_skus' => [],
                ];
            }

            $allExistingSkus = $catalog['all_existing_skus'];
            $targetIds = array_fill_keys(array_keys($updatesToApply), true);

            $untouchedSkus = [];
            foreach ($allExistingSkus as $upperSku => $id) {
                if (! isset($targetIds[$id])) {
                    $untouchedSkus[$upperSku] = true;
                }
            }

            // Guarantee global SKU uniqueness cleanly even on edge-case duplicate codes
            $finalSkuById = [];
            $reservedUpperSkus = [];
            foreach ($updatesToApply as $variantId => $candidate) {
                $baseCandidate = $candidate;
                $upper = strtoupper($candidate);
                $duplicateCounter = 2;

                while (isset($untouchedSkus[$upper]) || isset($reservedUpperSkus[$upper])) {
                    $candidate = $baseCandidate . '-' . $duplicateCounter;
                    $upper = strtoupper($candidate);
                    $duplicateCounter++;
                }

                $reservedUpperSkus[$upper] = true;
                $finalSkuById[$variantId] = $candidate;
            }

            $needsTempPhase = false;
            foreach ($finalSkuById as $variantId => $newSku) {
                $upper = strtoupper($newSku);
                if (
                    isset($allExistingSkus[$upper])
                    && $allExistingSkus[$upper] !== $variantId
                    && isset($targetIds[$allExistingSkus[$upper]])
                ) {
                    $needsTempPhase = true;
                    break;
                }
            }

            $now = now();

            if ($needsTempPhase) {
                foreach ($finalSkuById as $variantId => $_) {
                    DB::table('product_variants')
                        ->where('id', $variantId)
                        ->update(['sku' => '__TMP-' . $variantId . '-' . Str::random(6)]);
                }
            }

            foreach ($finalSkuById as $variantId => $newSku) {
                DB::table('product_variants')
                    ->where('id', $variantId)
                    ->update([
                        'sku' => $newSku,
                        'updated_at' => $now,
                    ]);
            }

            return [
                'updated' => count($finalSkuById),
                'unchanged' => $unchangedCount,
                'not_found' => $notFound,
                'ambiguous' => $ambiguous,
                'total_rows' => $totalDataRows,
                'skipped_blank_rows' => $skippedBlankRows,
                'prefix' => $normalizedPrefix,
                'sample_skus' => array_slice(array_values($finalSkuById), 0, 5),
            ];
        });
    }

    /**
     * Format a human-readable notification summary after syncing from master.
     *
     * @param  array<string, mixed>  $result
     */
    public static function formatResultMessage(array $result): string
    {
        $updated = (int) ($result['updated'] ?? 0);
        $unchanged = (int) ($result['unchanged'] ?? 0);
        $notFound = is_array($result['not_found'] ?? null) ? $result['not_found'] : [];
        $ambiguous = is_array($result['ambiguous'] ?? null) ? $result['ambiguous'] : [];
        $totalRows = (int) ($result['total_rows'] ?? 0);
        $prefix = (string) ($result['prefix'] ?? '');
        $samples = is_array($result['sample_skus'] ?? null) ? $result['sample_skus'] : [];

        $message = "Prefix {$prefix}: {$updated} SKU dipulihkan, {$unchanged} sudah sesuai, "
            . count($notFound) . ' tidak ditemukan, '
            . count($ambiguous) . " ambigu (dari {$totalRows} baris master).";

        if ($samples !== []) {
            $message .= ' Contoh SKU: ' . implode(', ', $samples) . '.';
        }

        if ($notFound !== []) {
            $previewNames = array_map(
                static fn (array $item): string => trim(($item['name'] ?: $item['raw_code'] ?: 'Baris #' . $item['row'])),
                array_slice($notFound, 0, 3)
            );
            $message .= ' Contoh tidak ditemukan: ' . implode('; ', $previewNames) . '.';
        }

        return $message;
    }

    /**
     * Build lookup indexes across product variants for fast deterministic matching.
     *
     * @return array{
     *     by_id: array<int, object>,
     *     by_product_name: array<string, array<int, object>>,
     *     by_combined_name: array<string, array<int, object>>,
     *     by_barcode: array<string, array<int, object>>,
     *     all_existing_skus: array<string, int>
     * }
     */
    private function loadVariantLookupCatalog(int $branchId): array
    {
        $branchVariantIds = array_fill_keys(
            DB::table('product_branch_stocks')
                ->where('branch_id', $branchId)
                ->pluck('product_variant_id')
                ->map(static fn ($id): int => (int) $id)
                ->all(),
            true
        );

        $rows = DB::table('product_variants')
            ->join('products', 'products.id', '=', 'product_variants.product_id')
            ->select([
                'product_variants.id',
                'product_variants.product_id',
                'product_variants.sku',
                'product_variants.barcode',
                'product_variants.name as variant_name',
                'products.name as product_name',
            ])
            ->orderBy('product_variants.id')
            ->get();

        $byId = [];
        $byProductName = [];
        $byCombinedName = [];
        $byBarcode = [];
        $allExistingSkus = [];

        foreach ($rows as $row) {
            $id = (int) $row->id;
            $normBarcode = self::normalizeMatchKey((string) ($row->barcode ?? ''));

            $entry = (object) [
                'id' => $id,
                'product_id' => (int) $row->product_id,
                'sku' => $row->sku,
                'barcode' => $row->barcode,
                'norm_barcode' => $normBarcode,
                'in_branch' => isset($branchVariantIds[$id]),
            ];

            $byId[$id] = $entry;

            if ($row->sku !== null && $row->sku !== '') {
                $allExistingSkus[strtoupper((string) $row->sku)] = $id;
            }

            $productName = (string) ($row->product_name ?? '');
            $variantName = trim((string) ($row->variant_name ?? ''));

            $normProductName = self::normalizeMatchKey($productName);
            if ($normProductName !== '') {
                $byProductName[$normProductName][] = $entry;
            }

            if ($variantName !== '' && mb_strtolower($variantName, 'UTF-8') !== 'default') {
                $spaceCombined = self::normalizeMatchKey($productName . ' ' . $variantName);
                $dashCombined = self::normalizeMatchKey($productName . ' - ' . $variantName);

                if ($spaceCombined !== '') {
                    $byCombinedName[$spaceCombined][] = $entry;
                }
                if ($dashCombined !== '') {
                    $byCombinedName[$dashCombined][] = $entry;
                }
            }

            if ($normBarcode !== '') {
                $byBarcode[$normBarcode][] = $entry;
            }
        }

        return [
            'by_id' => $byId,
            'by_product_name' => $byProductName,
            'by_combined_name' => $byCombinedName,
            'by_barcode' => $byBarcode,
            'all_existing_skus' => $allExistingSkus,
        ];
    }

    /**
     * Resolve a single variant candidate by normalized stock name, with barcode and branch fallback.
     *
     * @param  array<string, mixed>  $catalog
     * @return array{status: 'matched', variant: object}|array{status: 'not_found'}|array{status: 'ambiguous', reason: string}
     */
    private function resolveVariantCandidate(array $catalog, string $stockName, string $barcode): array
    {
        $normName = self::normalizeMatchKey($stockName);
        $normBarcode = self::normalizeMatchKey($barcode);

        $candidates = $catalog['by_product_name'][$normName] ?? [];

        if ($candidates === []) {
            $candidates = $catalog['by_combined_name'][$normName] ?? [];
        }

        if ($candidates === [] && $normBarcode !== '') {
            $candidates = $catalog['by_barcode'][$normBarcode] ?? [];
        }

        if (count($candidates) > 1 && $normBarcode !== '') {
            $barcodeFiltered = array_values(array_filter(
                $candidates,
                static fn (object $entry): bool => $entry->norm_barcode === $normBarcode
            ));

            if ($barcodeFiltered !== []) {
                $candidates = $barcodeFiltered;
            }
        }

        if (count($candidates) > 1) {
            $branchFiltered = array_values(array_filter(
                $candidates,
                static fn (object $entry): bool => $entry->in_branch
            ));

            if ($branchFiltered !== []) {
                $candidates = $branchFiltered;
            }
        }

        if ($candidates === []) {
            return ['status' => 'not_found'];
        }

        if (count($candidates) > 1) {
            return [
                'status' => 'ambiguous',
                'reason' => 'Lebih dari satu varian produk cocok dengan nama ini.',
            ];
        }

        return [
            'status' => 'matched',
            'variant' => $candidates[0],
        ];
    }

    private static function normalizeMatchKey(?string $value): string
    {
        $trimmed = trim((string) $value);
        if ($trimmed === '') {
            return '';
        }

        $collapsed = preg_replace('/\s+/u', ' ', $trimmed) ?? $trimmed;

        return mb_strtolower($collapsed, 'UTF-8');
    }
}

