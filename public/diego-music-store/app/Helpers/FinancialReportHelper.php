<?php

namespace App\Helpers;

class FinancialReportHelper
{
    /**
     * Format a numeric amount to standard Indonesian Rupiah format.
     */
    public static function formatRupiah(float|int $amount): string
    {
        if ($amount < 0) {
            return '(Rp ' . number_format(abs($amount), 0, ',', '.') . ')';
        }

        return 'Rp ' . number_format($amount, 0, ',', '.');
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
}
