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
        
        if (!empty($prefix)) {
            return $prefix . '-' . $seqString;
        }
        
        return $seqString;
    }
}
