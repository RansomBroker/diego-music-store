<?php

namespace App\Helpers;

class BarcodeHelper
{
    /**
     * Code128 patterns (Code B subset)
     */
    private static array $code128Patterns = [
        ' ' => '212222', '!' => '222122', '"' => '222221', '#' => '121223', '$' => '121322',
        '%' => '131222', '&' => '122213', "'" => '122312', '(' => '132212', ')' => '221213',
        '*' => '221312', '+' => '231212', ',' => '112232', '-' => '122132', '.' => '122231',
        '/' => '113222', '0' => '123122', '1' => '123221', '2' => '223211', '3' => '221132',
        '4' => '221231', '5' => '213212', '6' => '223112', '7' => '312131', '8' => '311222',
        '9' => '321122', ':' => '321221', ';' => '312212', '<' => '322112', '=' => '322211',
        '>' => '212123', '?' => '212321', '@' => '232121', 'A' => '111323', 'B' => '131123',
        'C' => '131321', 'D' => '112313', 'E' => '132113', 'F' => '132311', 'G' => '211313',
        'H' => '231113', 'I' => '231311', 'J' => '112133', 'K' => '112331', 'L' => '132131',
        'M' => '113123', 'N' => '113321', 'O' => '133121', 'P' => '313111', 'Q' => '314111',
        'R' => '321113', 'S' => '321311', 'T' => '331112', 'U' => '312113', 'V' => '312311',
        'W' => '332111', 'X' => '314111', 'Y' => '221411', 'Z' => '431111', '[' => '111224',
        '\\' => '111422', ']' => '121124', '^' => '121421', '_' => '141121', '`' => '141221',
        'a' => '112214', 'b' => '112412', 'c' => '122114', 'd' => '122411', 'e' => '142112',
        'f' => '142411', 'g' => '142112', 'h' => '142212', 'i' => '142311', 'j' => '141123',
        'k' => '141321', 'l' => '143121', 'm' => '151121', 'n' => '151211', 'o' => '152111',
        'p' => '151112', 'q' => '151211', 'r' => '152111', 's' => '151121', 't' => '151211',
        'u' => '152111', 'v' => '151112', 'w' => '151211', 'x' => '152111', 'y' => '151121',
        'z' => '151211', '{' => '152111', '|' => '151112', '}' => '151211', '~' => '152111',
    ];

    /**
     * Generate Code128 SVG string.
     */
    public static function generateCode128Svg(string $text, int $width = 200, int $height = 50): string
    {
        if (empty($text)) {
            $text = '000000';
        }

        // Standard Code 128 Start B
        $startPattern = '211214'; // Start Code B
        $stopPattern  = '2331112'; // Stop Code

        // Build pattern sequence
        $fullPattern = $startPattern;
        
        // Calculate checksum
        $checkSum = 104; // Start B value
        
        $len = strlen($text);
        for ($i = 0; $i < $len; $i++) {
            $char = $text[$i];
            $pattern = self::$code128Patterns[$char] ?? self::$code128Patterns['?'];
            $fullPattern .= $pattern;
            
            $val = ord($char) - 32;
            if ($val < 0 || $val > 95) {
                $val = 31; // fallback
            }
            $checkSum += $val * ($i + 1);
        }

        $checkValue = $checkSum % 103;
        // Checkvalue pattern index
        $keys = array_keys(self::$code128Patterns);
        $checkChar = $keys[$checkValue % count($keys)] ?? '?';
        $fullPattern .= self::$code128Patterns[$checkChar] ?? '212321';
        $fullPattern .= $stopPattern;

        // Render SVG bars
        $totalModules = 0;
        $pLen = strlen($fullPattern);
        for ($i = 0; $i < $pLen; $i++) {
            $totalModules += intval($fullPattern[$i]);
        }

        $moduleWidth = $width / max(1, $totalModules);
        $x = 0.0;
        $rects = '';

        for ($i = 0; $i < $pLen; $i++) {
            $modLen = intval($fullPattern[$i]) * $moduleWidth;
            if ($i % 2 === 0) {
                // Black bar
                $rects .= sprintf('<rect x="%.2f" y="0" width="%.2f" height="%d" fill="#000000" />', $x, $modLen, $height);
            }
            $x += $modLen;
        }

        return sprintf(
            '<svg viewBox="0 0 %d %d" width="100%%" height="100%%" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">%s</svg>',
            $width,
            $height,
            $rects
        );
    }
}
