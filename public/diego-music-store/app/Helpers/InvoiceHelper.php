<?php

namespace App\Helpers;

use Exception;

class InvoiceHelper
{
    /**
     * Generate dynamic alphanumeric sequence string.
     * Format: {ALPHABET}{NUMBERS} (e.g. A0001)
     */
    public static function generateSequence(int $sequence, string $startAlphabet = 'A', string $endAlphabet = 'Z', int $length = 4): string
    {
        if ($sequence <= 0) {
            $sequence = 1;
        }
        
        $maxNumber = (int)pow(10, $length) - 1; // e.g. 9999
        
        $numberVal = (($sequence - 1) % $maxNumber) + 1;
        $alphabetIncrement = (int)floor(($sequence - 1) / $maxNumber);
        
        $currentAlphabet = $startAlphabet;
        for ($i = 0; $i < $alphabetIncrement; $i++) {
            $currentAlphabet++;
        }
        
        // Validate if it exceeds the end alphabet
        // PHP's string increment can go from Z -> AA.
        // We compare using length first, then alphabetically.
        $exceeds = false;
        if (strlen($currentAlphabet) > strlen($endAlphabet)) {
            $exceeds = true;
        } elseif (strlen($currentAlphabet) === strlen($endAlphabet) && $currentAlphabet > $endAlphabet) {
            $exceeds = true;
        }
        
        if ($exceeds) {
            throw new Exception("Batas nomor urut (sequence) untuk kategori ini telah melampaui abjad akhir ({$endAlphabet}). Silakan perbarui rentang abjad di pengaturan kategori penjualan.");
        }
        
        $numberPartStr = str_pad((string)$numberVal, $length, '0', STR_PAD_LEFT);
        
        return $currentAlphabet . $numberPartStr;
    }
    
    /**
     * Get the next full invoice number based on prefix, alphabet range, digit length, and current sequence counter.
     */
    public static function getInvoiceNumber(?string $prefix, string $startAlphabet, string $endAlphabet, int $digitLength, int $sequence): string
    {
        $seqString = self::generateSequence($sequence, $startAlphabet, $endAlphabet, $digitLength);
        $normalizedPrefix = $prefix !== null ? rtrim(trim($prefix), '-') : '';

        if ($normalizedPrefix !== '') {
            return $normalizedPrefix . '-' . $seqString;
        }

        return $seqString;
    }

    /**
     * Parse an invoice number back into its 1-based sequence counter for the given prefix/format.
     */
    public static function extractSequence(string $invoiceNumber, ?string $prefix, string $startAlphabet = 'A', int $length = 4): ?int
    {
        $normalizedPrefix = $prefix !== null ? rtrim(trim($prefix), '-') : '';
        $body = trim($invoiceNumber);

        if ($normalizedPrefix !== '') {
            $expectedPrefix = $normalizedPrefix . '-';
            if (! str_starts_with($body, $expectedPrefix)) {
                return null;
            }
            $body = substr($body, strlen($expectedPrefix));
        }

        if (! preg_match('/^([A-Z]+)(\d{' . $length . ',})$/', $body, $matches)) {
            return null;
        }

        $alpha = $matches[1];
        $numberVal = intval($matches[2]);
        if ($numberVal <= 0) {
            return null;
        }

        $startAlphabet = strtoupper(trim($startAlphabet ?: 'A'));
        $maxNumber = (int) pow(10, $length) - 1;

        // Calculate alphabet offset from startAlphabet using PHP's string increment rules
        if (strlen($alpha) < strlen($startAlphabet) || (strlen($alpha) === strlen($startAlphabet) && $alpha < $startAlphabet)) {
            return null;
        }

        $alphabetIncrement = 0;
        $cursor = $startAlphabet;
        while ($cursor !== $alpha && $alphabetIncrement < 10000) {
            $cursor++;
            $alphabetIncrement++;
        }

        if ($cursor !== $alpha) {
            return null;
        }

        return ($alphabetIncrement * $maxNumber) + $numberVal;
    }
}
