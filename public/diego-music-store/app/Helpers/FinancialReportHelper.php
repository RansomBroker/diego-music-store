<?php

namespace App\Helpers;

use App\Models\Account;
use Illuminate\Support\Collection;

class FinancialReportHelper
{
    /**
     * Format a numeric amount to standard Indonesian Rupiah format with parenthesis for negative values.
     */
    public static function formatRupiah(float|int $amount): string
    {
        if ($amount < 0) {
            return '(Rp ' . number_format(abs($amount), 0, ',', '.') . ')';
        }

        return 'Rp ' . number_format($amount, 0, ',', '.');
    }

    /**
     * Format a numeric amount without Rp prefix with parenthesis for negative values.
     */
    public static function formatNumber(float|int $amount, bool $zeroAsDash = true): string
    {
        if ($amount == 0) {
            return $zeroAsDash ? '-' : '0';
        }

        if ($amount < 0) {
            return '(' . number_format(abs($amount), 0, ',', '.') . ')';
        }

        return number_format($amount, 0, ',', '.');
    }

    /**
     * Get balance status badge HTML / configuration (Monochrome Printer-Friendly).
     */
    public static function getBalanceBadgeStatus(bool $isBalanced, float $difference = 0.0): array
    {
        if ($isBalanced) {
            return [
                'status' => 'BALANCED',
                'color'  => 'gray',
                'label'  => 'NERACA SEIMBANG (Aset = Kewajiban + Ekuitas)',
                'class'  => 'bg-gray-100 text-gray-900 dark:bg-white/10 dark:text-white border-gray-300 dark:border-gray-700',
            ];
        }

        return [
            'status' => 'UNBALANCED',
            'color'  => 'danger',
            'label'  => 'SELISIH NERACA: ' . self::formatRupiah(abs($difference)),
            'class'  => 'bg-gray-200 text-gray-900 dark:bg-white/10 dark:text-white border-gray-400 dark:border-gray-600',
        ];
    }

    /**
     * Resolve active COA accounts into a single canonical row per account code
     * so consolidated ("Semua Cabang") and branch-scoped reports never duplicate COA rows.
     *
     * @return array{accounts: Collection<int, Account>, id_to_canonical_id: array<int, int>, parent_code_map: array<string, string>, levels_by_id: array<int, int>}
     */
    public static function resolveConsolidatedAccounts(?int $branchId = null): array
    {
        $rawAccounts = Account::with('parent')
            ->where('is_active', true)
            ->orderBy('code')
            ->get();

        $groupedByCode = $rawAccounts->groupBy('code');
        $canonicalAccounts = collect();
        $idToCanonicalId = [];
        $parentCodeMap = [];

        foreach ($groupedByCode as $code => $group) {
            if ($branchId !== null) {
                $canonical = $group->firstWhere('branch_id', $branchId)
                    ?? $group->firstWhere('branch_id', null);
            } else {
                $canonical = $group->firstWhere('branch_id', null)
                    ?? $group->sortBy('branch_id')->first();
            }

            if (! $canonical) {
                continue;
            }

            $canonicalAccounts->push($canonical);

            foreach ($group as $member) {
                $idToCanonicalId[$member->id] = $canonical->id;
            }

            $parentCode = $canonical->parent?->code;
            if (! $parentCode) {
                foreach ($group as $member) {
                    if ($member->parent?->code) {
                        $parentCode = $member->parent->code;
                        break;
                    }
                }
            }

            if ($parentCode && $parentCode !== $code) {
                $parentCodeMap[(string) $code] = (string) $parentCode;
            }
        }

        $canonicalAccounts = $canonicalAccounts->sortBy('code')->values();
        $canonicalByCode = [];
        foreach ($canonicalAccounts as $acc) {
            $canonicalByCode[(string) $acc->code] = $acc;
        }

        // Order accounts depth-first following parent_code hierarchy (with sibling code order)
        $childrenByParentCode = [];
        $rootAccounts = [];
        foreach ($canonicalAccounts as $acc) {
            $code = (string) $acc->code;
            $parentCode = $parentCodeMap[$code] ?? null;
            if ($parentCode !== null && isset($canonicalByCode[$parentCode]) && $parentCode !== $code) {
                $childrenByParentCode[$parentCode][] = $acc;
            } else {
                $rootAccounts[] = $acc;
            }
        }

        $orderedAccounts = collect();
        $levelsById = [];
        $visited = [];

        $visit = function (Account $acc, int $level) use (&$visit, &$orderedAccounts, &$levelsById, &$visited, &$childrenByParentCode): void {
            $code = (string) $acc->code;
            if (isset($visited[$code])) {
                return;
            }
            $visited[$code] = true;
            $orderedAccounts->push($acc);
            $levelsById[$acc->id] = $level;

            foreach ($childrenByParentCode[$code] ?? [] as $child) {
                $visit($child, $level + 1);
            }
        };

        foreach ($rootAccounts as $rootAcc) {
            $visit($rootAcc, 1);
        }

        // Fallback safety in case any disconnected cycle exists
        foreach ($canonicalAccounts as $acc) {
            $visit($acc, 1);
        }

        return [
            'accounts' => $orderedAccounts->values(),
            'id_to_canonical_id' => $idToCanonicalId,
            'parent_code_map' => $parentCodeMap,
            'levels_by_id' => $levelsById,
        ];
    }

    /**
     * Consolidate journal debit/credit sums onto canonical account IDs.
     *
     * @param  iterable<int, object>  $rows
     * @param  array<int, int>  $idToCanonicalId
     * @return Collection<int, object>
     */
    public static function consolidateAccountSums(iterable $rows, array $idToCanonicalId): Collection
    {
        $totals = [];

        foreach ($rows as $row) {
            $canonicalId = $idToCanonicalId[(int) $row->account_id] ?? (int) $row->account_id;
            if (! isset($totals[$canonicalId])) {
                $totals[$canonicalId] = (object) [
                    'account_id' => $canonicalId,
                    'total_debit' => 0.0,
                    'total_credit' => 0.0,
                ];
            }

            $totals[$canonicalId]->total_debit += (float) $row->total_debit;
            $totals[$canonicalId]->total_credit += (float) $row->total_credit;
        }

        return collect($totals);
    }

    /**
     * Check whether a child account code descends from a given ancestor account code.
     *
     * @param  array<string, string>  $parentCodeMap
     */
    public static function isDescendantOfCode(string $childCode, string $ancestorCode, array $parentCodeMap): bool
    {
        $curr = $childCode;
        $visited = [$curr => true];

        while (isset($parentCodeMap[$curr])) {
            $parent = $parentCodeMap[$curr];
            if ($parent === $ancestorCode) {
                return true;
            }
            if (isset($visited[$parent])) {
                break;
            }
            $visited[$parent] = true;
            $curr = $parent;
        }

        return false;
    }

    /**
     * Inline color style for negative financial figures (red in both web UI and PDF prints).
     */
    public static function amountStyle(float|int $amount, bool $pdf = false): string
    {
        if ($amount < 0) {
            return $pdf ? 'color: #dc2626;' : 'color: #ef4444;';
        }

        return '';
    }
}
