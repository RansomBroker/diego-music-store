<?php

namespace App\Helpers;

use App\Actions\Procurement\EvaluatePoFinancialHealth;

class PoSmartAssistHelper
{
    /**
     * Evaluate PO financial health using the core Action class.
     */
    public static function evaluate(float|int $poGrandTotal = 0, ?int $branchId = null, ?string $paymentTerm = 'COD'): array
    {
        $action = new EvaluatePoFinancialHealth();
        return $action->execute($poGrandTotal, $branchId, $paymentTerm);
    }

    /**
     * Format Rupiah helper.
     */
    public static function formatRupiah(float|int $amount): string
    {
        if ($amount < 0) {
            return '- Rp ' . number_format(abs($amount), 0, ',', '.');
        }
        return 'Rp ' . number_format($amount, 0, ',', '.');
    }

    /**
     * Get Badge CSS & Visual properties based on risk status.
     */
    public static function getStatusBadgeConfig(string $status): array
    {
        return match ($status) {
            'AMAN' => [
                'bg_class' => 'bg-emerald-50 text-emerald-800 border-emerald-300 dark:bg-emerald-900/30 dark:text-emerald-300 dark:border-emerald-700',
                'badge_bg' => 'bg-emerald-600 text-white',
                'border_color' => 'border-emerald-500',
                'icon' => 'heroicon-o-check-circle',
                'label' => 'AMAN UNTUK DITERBITKAN',
            ],
            'WASPADA' => [
                'bg_class' => 'bg-amber-50 text-amber-800 border-amber-300 dark:bg-amber-900/30 dark:text-amber-300 dark:border-amber-700',
                'badge_bg' => 'bg-amber-500 text-white',
                'border_color' => 'border-amber-500',
                'icon' => 'heroicon-o-exclamation-triangle',
                'label' => 'WASPADA (MARGIN KAS TIPIS)',
            ],
            'TIDAK_AMAN' => [
                'bg_class' => 'bg-red-50 text-red-800 border-red-300 dark:bg-red-900/30 dark:text-red-300 dark:border-red-700',
                'badge_bg' => 'bg-red-600 text-white',
                'border_color' => 'border-red-500',
                'icon' => 'heroicon-o-x-circle',
                'label' => 'TIDAK AMAN (DEFISIT KAS)',
            ],
            default => [
                'bg_class' => 'bg-gray-50 text-gray-800 border-gray-300 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-700',
                'badge_bg' => 'bg-gray-500 text-white',
                'border_color' => 'border-gray-400',
                'icon' => 'heroicon-o-information-circle',
                'label' => 'SMART ASSIST INAKTIF',
            ],
        };
    }
}
