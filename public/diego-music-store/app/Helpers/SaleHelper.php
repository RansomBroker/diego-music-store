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
        
        $cashAccount = Account::where('code', '1-1000')->first();
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
        $cashAccount = Account::where('code', '1-1000')->first();
        
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
