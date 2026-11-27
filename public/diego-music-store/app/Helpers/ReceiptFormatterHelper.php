<?php

namespace App\Helpers;

use App\Models\ReceiptSetting;
use App\Models\Sale;

class ReceiptFormatterHelper
{
    /**
     * Format struk penjualan ke plain-text thermal (ESC/POS friendly)
     * dengan lebar kolom presisi (32 char untuk 58mm, 48 char untuk 80mm/default).
     */
    public static function formatThermalText(Sale $sale, ?ReceiptSetting $setting = null): string
    {
        $setting ??= ReceiptSetting::where('branch_id', $sale->branch_id)->first();
        $width = ($setting?->paper_width === '58mm') ? 32 : 48;

        $lines = [];
        $doubleDivider = str_repeat('=', $width);
        $singleDivider = str_repeat('-', $width);

        $storeName = strtoupper(trim((string) ($setting?->store_display_name ?: ($sale->branch?->store_name ?: 'Diego Music Store'))));
        foreach (self::wrapCenter($storeName, $width) as $line) {
            $lines[] = $line;
        }

        $headerNote = $sale->resolveReceiptHeader($setting);
        if (! empty($headerNote)) {
            foreach (self::wrapCenter($headerNote, $width) as $line) {
                $lines[] = $line;
            }
        }

        if (! empty($sale->branch?->name) && strtoupper(trim($sale->branch->name)) !== $storeName) {
            foreach (self::wrapCenter($sale->branch->name, $width) as $line) {
                $lines[] = $line;
            }
        }

        if (! empty($sale->branch?->address)) {
            foreach (self::wrapCenter($sale->branch->address, $width) as $line) {
                $lines[] = $line;
            }
        }

        if (! empty($sale->branch?->phone)) {
            foreach (self::wrapCenter('Telp: ' . $sale->branch->phone, $width) as $line) {
                $lines[] = $line;
            }
        }

        $lines[] = $doubleDivider;
        $lines[] = self::leftRight('No. Faktur', (string) $sale->invoice_number, $width);
        $lines[] = self::leftRight('Tanggal', ($sale->created_at ?? now())->format('d/m/Y H:i'), $width);

        if ($setting?->show_cashier ?? true) {
            $lines[] = self::leftRight('Kasir', (string) ($sale->receipt_cashier_name ?: '-'), $width);
        }

        if ($setting?->show_customer ?? true) {
            $lines[] = self::leftRight('Pelanggan', (string) ($sale->customer?->name ?: 'Umum / Walk-in'), $width);
        }

        $lines[] = $singleDivider;
        $lines[] = 'Rincian Belanja:';

        $totalQty = 0;
        foreach ($sale->items as $item) {
            $totalQty += (int) $item->quantity;
            $sku = $item->variant?->sku ?? '-';
            $productName = $item->variant?->product?->name ?? 'Produk';
            $variantName = $item->variant?->name ? ' (' . $item->variant->name . ')' : '';
            $itemTitle = "[{$sku}] {$productName}{$variantName}";

            foreach (self::wrapLeft($itemTitle, $width) as $wrappedLine) {
                $lines[] = $wrappedLine;
            }

            $qtyPrice = '  ' . (int) $item->quantity . ' x ' . number_format((int) $item->unit_price, 0, ',', '.');
            $lineTotal = 'Rp ' . number_format((int) $item->total_price, 0, ',', '.');
            $lines[] = self::leftRight($qtyPrice, $lineTotal, $width);

            if ((int) $item->discount_amount > 0) {
                $lines[] = self::leftRight('  (Diskon Item)', '-Rp ' . number_format((int) $item->discount_amount, 0, ',', '.'), $width);
            }
        }

        $lines[] = $singleDivider;
        $lines[] = self::leftRight('Subtotal', 'Rp ' . number_format((int) $sale->subtotal, 0, ',', '.'), $width);

        if ((int) $sale->discount_amount > 0) {
            $lines[] = self::leftRight('Diskon', '-Rp ' . number_format((int) $sale->discount_amount, 0, ',', '.'), $width);
        }

        if (($setting?->show_tax_details ?? true) && (int) $sale->tax_amount > 0) {
            $lines[] = self::leftRight('PPN', 'Rp ' . number_format((int) $sale->tax_amount, 0, ',', '.'), $width);
        }

        $lines[] = $doubleDivider;
        $lines[] = self::leftRight('TOTAL AKHIR', 'Rp ' . number_format((int) $sale->grand_total, 0, ',', '.'), $width);
        $lines[] = $singleDivider;
        $lines[] = self::leftRight('Total Qty', $totalQty . ' Item', $width);

        $paymentLabel = $sale->payment_method_display ?: ($sale->payment_method ?: 'Tunai');
        $displayPayment = strtoupper((string) $paymentLabel) === 'KAS' ? 'CASH' : strtoupper((string) $paymentLabel);

        $lines[] = self::leftRight('Pembayaran', $displayPayment, $width);

        if ($sale->usesCashPayment()) {
            $paidAmount = (int) ($sale->paid_amount ?? 0);
            if ($paidAmount > 0) {
                $lines[] = self::leftRight('Dibayar', 'Rp ' . number_format($paidAmount, 0, ',', '.'), $width);
            }
            $changeAmount = max(0, (int) ($sale->change_amount ?? 0));
            $lines[] = self::leftRight('Kembalian', 'Rp ' . number_format($changeAmount, 0, ',', '.'), $width);
        }

        if (! empty($sale->receipt_sales_rep_name)) {
            $lines[] = $singleDivider;
            $lines[] = self::leftRight('Sales', (string) $sale->receipt_sales_rep_name, $width);
        }

        $lines[] = $singleDivider;

        $footerNote = $sale->resolveReceiptFooter($setting) ?: 'Terima Kasih atas Kunjungan Anda';
        foreach (self::wrapCenter($footerNote, $width) as $line) {
            $lines[] = $line;
        }

        return implode("\n", $lines);
    }

    private static function leftRight(string $left, string $right, int $width): string
    {
        $left = trim(preg_replace('/\s+/', ' ', $left) ?? $left);
        $right = trim(preg_replace('/\s+/', ' ', $right) ?? $right);

        $leftLen = mb_strlen($left);
        $rightLen = mb_strlen($right);

        if ($leftLen + $rightLen + 1 <= $width) {
            return $left . str_repeat(' ', $width - $leftLen - $rightLen) . $right;
        }

        $maxLeft = max(1, $width - $rightLen - 1);
        $truncatedLeft = mb_substr($left, 0, $maxLeft);
        $spaces = max(1, $width - mb_strlen($truncatedLeft) - $rightLen);

        return $truncatedLeft . str_repeat(' ', $spaces) . $right;
    }

    /**
     * @return array<int, string>
     */
    private static function wrapCenter(string $text, int $width): array
    {
        $result = [];
        foreach (preg_split("/\r\n|\n|\r/", $text) ?: [] as $paragraph) {
            $paragraph = trim($paragraph);
            if ($paragraph === '') {
                continue;
            }
            $wrapped = wordwrap($paragraph, $width, "\n", true);
            foreach (explode("\n", $wrapped) as $line) {
                $line = trim($line);
                $len = mb_strlen($line);
                if ($len >= $width) {
                    $result[] = $line;
                } else {
                    $padLeft = (int) floor(($width - $len) / 2);
                    $result[] = str_repeat(' ', $padLeft) . $line;
                }
            }
        }

        return $result;
    }

    /**
     * @return array<int, string>
     */
    private static function wrapLeft(string $text, int $width): array
    {
        $clean = trim(preg_replace('/\s+/', ' ', $text) ?? $text);
        if ($clean === '') {
            return [];
        }

        return explode("\n", wordwrap($clean, $width, "\n", true));
    }
}

