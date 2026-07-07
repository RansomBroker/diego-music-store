<?php

namespace App\Helpers;

class FormatHelper
{
    /**
     * Format currency value as rupiah string (e.g. Rp 1.000).
     *
     * @param  int|float  $value
     * @return string
     */
    public static function rupiah(int|float $value): string
    {
        return 'Rp ' . number_format($value, 0, ',', '.');
    }

    /**
     * Format the cash change amount or show unpaid deficiency.
     *
     * @param  int  $amountPaid
     * @param  int  $grandTotal
     * @return string
     */
    public static function formatChange(int $amountPaid, int $grandTotal): string
    {
        $change = $amountPaid - $grandTotal;
        if ($change >= 0) {
            return self::rupiah($change);
        }

        return 'Kurang ' . self::rupiah(abs($change));
    }
}
