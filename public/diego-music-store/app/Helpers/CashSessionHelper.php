<?php

namespace App\Helpers;

use App\Models\Account;
use App\Models\CashSession;

class CashSessionHelper
{
    /**
     * Resolve the cash drawer account (prioritizes 111101002 KAS Kecil, falls back to 111101001 KAS).
     */
    public static function resolveDrawerCashAccount(): ?Account
    {
        return AccountHelper::findByCode('111101002')
            ?? AccountHelper::findByCode('111101001');
    }

    /**
     * Resolve the main/owner cash account (111101001 KAS).
     */
    public static function resolveMainCashAccount(): ?Account
    {
        return AccountHelper::findByCode('111101001');
    }

    /**
     * Calculate posted cash transaction totals for a cash session.
     *
     * @return array{cash_in: int, cash_out: int, cash_transfer_out: int, total_out: int}
     */
    public static function getSessionCashTotals(CashSession $session): array
    {
        $cashIn = (int) $session->cashTransactions()
            ->where('type', 'in')
            ->where('status', 'posted')
            ->sum('amount');

        $cashOut = (int) $session->cashTransactions()
            ->where('type', 'out')
            ->where('status', 'posted')
            ->sum('amount');

        $cashTransferOut = (int) $session->cashTransactions()
            ->where('type', 'transfer')
            ->where('status', 'posted')
            ->sum('amount');

        return [
            'cash_in' => $cashIn,
            'cash_out' => $cashOut,
            'cash_transfer_out' => $cashTransferOut,
            'total_out' => $cashOut + $cashTransferOut,
        ];
    }
}
