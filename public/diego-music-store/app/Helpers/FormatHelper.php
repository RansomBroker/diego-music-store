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

    /**
     * Parse formatted currency string back to float.
     * e.g. "1.000.000" or "Rp 1.000.000" -> 1000000.0
     *
     * @param  string|int|float|null  $value
     * @return float
     */
    public static function parseRupiah(string|int|float|null $value): float
    {
        if (is_null($value) || $value === '') {
            return 0.0;
        }

        if (is_int($value) || is_float($value)) {
            return (float) $value;
        }

        $str = (string) $value;

        // Indonesian thousand separators use dots (e.g. 250.000 or 1.000.000)
        if (str_contains($str, '.')) {
            $str = str_replace('.', '', $str);
        }

        // Strip non-digits
        $cleaned = preg_replace('/[^\d\-]/', '', $str);

        return $cleaned !== '' ? (float) $cleaned : 0.0;
    }

    /**
     * Format number for input field with thousands separator.
     * e.g. 1000000 -> "1.000.000"
     *
     * @param  int|float|string|null  $value
     * @return string
     */
    public static function formatInputNumber(int|float|string|null $value): string
    {
        $num = self::parseRupiah($value);
        if ($num == 0) {
            return '0';
        }

        return number_format($num, 0, ',', '.');
    }
}
