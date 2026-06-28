<?php

namespace App\Helpers;

class TerbilangHelper
{
    /**
     * Convert numeric amount to Indonesian words.
     *
     * @param  int  $number
     * @return string
     */
    public static function convert(int $number): string
    {
        $number = abs($number);
        $words = ["", "Satu", "Dua", "Tiga", "Empat", "Lima", "Enam", "Tujuh", "Delapan", "Sembilan", "Sepuluh", "Sebelas"];
        $result = "";

        if ($number < 12) {
            $result = " " . $words[$number];
        } elseif ($number < 20) {
            $result = static::convert($number - 10) . " Belas";
        } elseif ($number < 100) {
            $result = static::convert((int)($number / 10)) . " Puluh" . static::convert($number % 10);
        } elseif ($number < 200) {
            $result = " Seratus" . static::convert($number - 100);
        } elseif ($number < 1000) {
            $result = static::convert((int)($number / 100)) . " Ratus" . static::convert($number % 100);
        } elseif ($number < 2000) {
            $result = " Seribu" . static::convert($number - 1000);
        } elseif ($number < 1000000) {
            $result = static::convert((int)($number / 1000)) . " Ribu" . static::convert($number % 1000);
        } elseif ($number < 1000000000) {
            $result = static::convert((int)($number / 1000000)) . " Juta" . static::convert($number % 1000000);
        } elseif ($number < 1000000000000) {
            $result = static::convert((int)($number / 1000000000)) . " Milyar" . static::convert($number % 1000000000);
        }

        return trim($result);
    }
}
