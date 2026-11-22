<?php

namespace App\Helpers;

use App\Models\Sale;
use App\Models\Account;
use App\Models\CashSession;
use App\Models\JournalEntry;
use App\Models\JournalItem;

class SaleHelper
{
    /**
     * Get the cash amount of a single sale.
     *
     * @param  Sale  $sale
     * @return int
     */
    public static function getCashAmount(Sale $sale): int
    {
        // Find journal entry for this sale
        $journalEntry = JournalEntry::where('reference_type', 'Sales')
            ->where('reference_id', $sale->id)
            ->first();
            
        if (!$journalEntry) {
            // Fallback to checking payment_method
            $payMethod = strtolower($sale->payment_method);
            if ($payMethod === 'cash' || $payMethod === 'tunai' || str_contains($payMethod, 'tunai') || str_contains($payMethod, 'cash')) {
                return $sale->grand_total;
            }
            return 0;
        }
        
        $cashAccount = \App\Helpers\AccountHelper::findByCode('111101001');
        if (!$cashAccount) {
            $payMethod = strtolower($sale->payment_method);
            if ($payMethod === 'cash' || $payMethod === 'tunai' || str_contains($payMethod, 'tunai') || str_contains($payMethod, 'cash')) {
                return $sale->grand_total;
            }
            return 0;
        }
        
        return intval($journalEntry->items()
            ->where('account_id', $cashAccount->id)
            ->sum('debit'));
    }

    /**
     * Get the non-cash amount of a single sale.
     *
     * @param  Sale  $sale
     * @return int
     */
    public static function getNonCashAmount(Sale $sale): int
    {
        $cashAmount = self::getCashAmount($sale);
        return max(0, $sale->grand_total - $cashAmount);
    }

    /**
     * Resolve detailed non-cash payment breakdown (method label & amount) for a single sale.
     *
     * @return array<int, array{label: string, amount: int}>
     */
    public static function getNonCashBreakdown(Sale $sale, ?JournalEntry $journalEntry = null, ?int $cashAccountId = null): array
    {
        $journalEntry ??= JournalEntry::with('items.account')
            ->where('reference_type', 'Sales')
            ->where('reference_id', $sale->id)
            ->first();

        $cashAccountId ??= AccountHelper::findByCode('111101001')?->id;

        if ($journalEntry) {
            $breakdown = [];
            foreach ($journalEntry->items as $item) {
                $amount = intval($item->debit);
                if ($amount <= 0) {
                    continue;
                }
                if ($cashAccountId && (int) $item->account_id === $cashAccountId) {
                    continue;
                }

                $isPaymentLeg = str_starts_with((string) $item->notes, 'Penerimaan POS - ')
                    || ($item->account && strtolower((string) $item->account->classification) === 'asset');

                if (! $isPaymentLeg) {
                    continue;
                }

                $breakdown[] = [
                    'label' => self::formatPaymentLegLabel((string) $item->notes, $item->account?->name),
                    'amount' => $amount,
                ];
            }

            if (! empty($breakdown)) {
                return $breakdown;
            }
        }

        $nonCashAmt = self::getNonCashAmount($sale);
        if ($nonCashAmt <= 0) {
            return [];
        }

        $parts = array_values(array_filter(array_map('trim', explode('&', (string) $sale->payment_method)), function ($part) {
            $lower = strtolower($part);
            return $part !== '' && ! str_contains($lower, 'tunai') && ! str_contains($lower, 'cash') && $lower !== 'kas';
        }));

        $label = ! empty($parts) ? implode(' & ', $parts) : (trim((string) $sale->payment_method) ?: 'Non-Tunai');

        return [[
            'label' => $label,
            'amount' => $nonCashAmt,
        ]];
    }

    /**
     * Aggregate detailed non-cash breakdown across a collection of sales (batch-loaded journals).
     *
     * @param  \Illuminate\Support\Collection<int, Sale>  $sales
     * @return array<int, array{label: string, count: int, amount: int}>
     */
    public static function getSalesNonCashBreakdown($sales): array
    {
        if ($sales->isEmpty()) {
            return [];
        }

        $saleIds = $sales->pluck('id')->all();
        $journals = JournalEntry::with('items.account')
            ->where('reference_type', 'Sales')
            ->whereIn('reference_id', $saleIds)
            ->get()
            ->keyBy('reference_id');

        $cashAccountId = AccountHelper::findByCode('111101001')?->id;
        $grouped = [];

        foreach ($sales as $sale) {
            $legs = self::getNonCashBreakdown($sale, $journals->get($sale->id), $cashAccountId);
            $seenLabelsInSale = [];

            foreach ($legs as $leg) {
                $label = $leg['label'];
                if (! isset($grouped[$label])) {
                    $grouped[$label] = [
                        'label' => $label,
                        'count' => 0,
                        'amount' => 0,
                    ];
                }
                if (! isset($seenLabelsInSale[$label])) {
                    $grouped[$label]['count']++;
                    $seenLabelsInSale[$label] = true;
                }
                $grouped[$label]['amount'] += $leg['amount'];
            }
        }

        uasort($grouped, fn ($a, $b) => $b['amount'] <=> $a['amount'] ?: strcmp($a['label'], $b['label']));

        return array_values($grouped);
    }

    protected static function formatPaymentLegLabel(string $notes, ?string $accountName = null): string
    {
        if (str_starts_with($notes, 'Penerimaan POS - ')) {
            $label = substr($notes, strlen('Penerimaan POS - '));
            // Strip reference number tokens while preserving clean Bank/sub-method info
            $label = preg_replace('/\s*\|\s*Ref:\s*[^)]*/i', '', $label);
            $label = preg_replace('/\s*\(Ref:\s*[^)]*\)/i', '', $label);
            $label = preg_replace('/\(Bank:\s*([^)]+)\)/i', '($1)', $label);
            $label = trim($label);

            if ($label !== '') {
                return $label;
            }
        }

        return $accountName ?: 'Non-Tunai';
    }

    /**
     * Get the sum of cash sales for a cash session.
     *
     * @param  CashSession  $session
     * @return int
     */
    public static function getSessionCashSalesSum(CashSession $session): int
    {
        // Find completed sales for the session
        $sales = $session->sales()->where('status', 'completed')->get();
        if ($sales->isEmpty()) {
            return 0;
        }
        
        $saleIds = $sales->pluck('id');
        $cashAccount = \App\Helpers\AccountHelper::findByCode('111101001');
        
        $journalSum = 0;
        if ($cashAccount) {
            $journalSum = intval(JournalItem::whereHas('journalEntry', function ($query) use ($saleIds) {
                    $query->where('reference_type', 'Sales')
                        ->whereIn('reference_id', $saleIds);
                })
                ->where('account_id', $cashAccount->id)
                ->sum('debit'));
        }
        
        // If journal entries exist for these sales, return the journal sum
        $hasJournalEntries = JournalEntry::where('reference_type', 'Sales')
            ->whereIn('reference_id', $saleIds)
            ->exists();
            
        if ($hasJournalEntries) {
            return $journalSum;
        }
        
        // Fallback: calculate based on payment_method column
        $fallbackSum = 0;
        foreach ($sales as $sale) {
            $payMethod = strtolower($sale->payment_method);
            if ($payMethod === 'cash' || $payMethod === 'tunai' || str_contains($payMethod, 'tunai') || str_contains($payMethod, 'cash')) {
                $fallbackSum += $sale->grand_total;
            }
        }
        
        return $fallbackSum;
    }
}
