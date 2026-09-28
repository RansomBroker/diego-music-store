<?php

namespace App\Helpers;

use Exception;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as SpreadsheetDate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SpreadsheetImportHelper
{
    /**
     * Get all sheet names from an Excel or CSV file.
     *
     * @param string $filePath
     * @return array<int, string>
     */
    public static function getSheets(string $filePath): array
    {
        try {
            $reader = IOFactory::createReaderForFile($filePath);
            if (method_exists($reader, 'listWorksheetNames')) {
                $names = $reader->listWorksheetNames($filePath);
                if (!empty($names)) {
                    return $names;
                }
            }
        } catch (Exception $e) {
            // Fallback for CSV or single sheet files
        }

        return ['Sheet 1'];
    }

    /**
     * Common column header aliases mapped to canonical template keys.
     *
     * @var array<string, string>
     */
    public const HEADER_ALIASES = [
        // Product mappings
        'harga_jual_cash'   => 'harga_jual',
        'hargajualcash'     => 'harga_jual',
        'harga_cash'        => 'harga_jual',
        'hargacash'         => 'harga_jual',
        'harga_jual_tunai'  => 'harga_jual',
        'selling_price'     => 'harga_jual',

        'kode_brg'          => 'kode_barang',
        'kode_item'         => 'kode_barang',
        'kodebarang'        => 'kode_barang',
        'sku'               => 'kode_barang',

        'nama_barang'       => 'nama_stok',
        'nama_produk'       => 'nama_stok',
        'namastok'          => 'nama_stok',
        'namabarang'        => 'nama_stok',
        'namaproduk'        => 'nama_stok',
        'product_name'      => 'nama_stok',

        'kategoribarang'    => 'kategori_barang',
        'category'          => 'kategori_barang',

        'hargabeli'         => 'harga_beli',
        'cost_price'        => 'harga_beli',

        'harganetto'        => 'netto',
        'harga_netto'       => 'netto',

        'diskon'            => 'disc',
        'discount'          => 'disc',

        'harga_het'         => 'het',

        'jlh_stock'         => 'jlh_stok',
        'jlhstok'           => 'jlh_stok',
        'jumlah_stok'       => 'jlh_stok',
        'jumlahstok'        => 'jlh_stok',
        'qty'               => 'jlh_stok',

        // Supplier Debt mappings
        'nama_supplier'     => 'supplier',
        'vendor'            => 'supplier',
        'nama_vendor'       => 'supplier',
        'supplier_name'     => 'supplier',

        'tgl'               => 'tanggal',
        'date'              => 'tanggal',
        'tgl_nota'          => 'tanggal',
        'tanggal_nota'      => 'tanggal',
        'tgl_faktur'        => 'tanggal',
        'tanggal_faktur'    => 'tanggal',
        'invoice_date'      => 'tanggal',

        'no_nota'           => 'nota',
        'nomor_nota'        => 'nota',
        'faktur'            => 'nota',
        'no_faktur'         => 'nota',
        'nomor_faktur'      => 'nota',
        'invoice'           => 'nota',
        'invoice_no'        => 'nota',
        'invoice_number'    => 'nota',

        'proyek'            => 'project',

        'totalhutang'       => 'total_hutang',
        'jumlah_hutang'     => 'total_hutang',
        'total_tagihan'     => 'total_hutang',

        'total_pembaya'     => 'total_pembayaran',
        'totalpembayaran'   => 'total_pembayaran',
        'totalpembaya'      => 'total_pembayaran',
        'pembayaran'        => 'total_pembayaran',

        'sisahutang'        => 'sisa_hutang',
        'sisa'              => 'sisa_hutang',
        'saldo_hutang'      => 'sisa_hutang',
        'sisa_tagihan'      => 'sisa_hutang',
    ];

    /**
     * Normalize a header string to standard snake_case and resolve known aliases.
     *
     * @param mixed $header
     * @return string
     */
    public static function normalizeHeader(mixed $header): string
    {
        if ($header === null) {
            return '';
        }

        $str = (string) $header;
        $str = trim($str);
        // Replace non-alphanumeric characters with underscore
        $str = preg_replace('/[^a-zA-Z0-9]+/', '_', strtolower($str));
        $normalized = trim($str ?? '', '_');

        return self::HEADER_ALIASES[$normalized] ?? $normalized;
    }

    /**
     * Read row 1 column headers from a file and sheet.
     *
     * @param string $filePath
     * @param string|null $sheetName
     * @return array{normalized: array<int, string>, raw: array<int, string>, map: array<string, string>}
     */
    public static function readHeaders(string $filePath, ?string $sheetName = null): array
    {
        $reader = IOFactory::createReaderForFile($filePath);
        $reader->setReadDataOnly(true);

        if ($sheetName && method_exists($reader, 'setLoadSheetsOnly')) {
            $reader->setLoadSheetsOnly($sheetName);
        }

        $spreadsheet = $reader->load($filePath);
        $sheet = $sheetName ? ($spreadsheet->getSheetByName($sheetName) ?? $spreadsheet->getActiveSheet()) : $spreadsheet->getActiveSheet();

        $highestColumn = $sheet->getHighestColumn();
        $headerCells = $sheet->rangeToArray('A1:' . $highestColumn . '1', null, true, true, true);
        $row1 = $headerCells[1] ?? [];

        $raw = [];
        $normalized = [];
        $map = []; // column letter => normalized header

        foreach ($row1 as $colLetter => $cellVal) {
            if ($cellVal !== null && trim((string) $cellVal) !== '') {
                $norm = self::normalizeHeader($cellVal);
                $raw[] = (string) $cellVal;
                $normalized[] = $norm;
                $map[$colLetter] = $norm;
            }
        }

        return [
            'normalized' => $normalized,
            'raw' => $raw,
            'map' => $map,
        ];
    }

    /**
     * Validate detected headers against required template headers.
     *
     * @param array<int, string> $detectedHeaders
     * @param array<int, string> $requiredHeaders
     * @return array{is_valid: bool, matched: array<int, string>, missing: array<int, string>, extra: array<int, string>, required: array<int, string>}
     */
    public static function validateHeaders(array $detectedHeaders, array $requiredHeaders): array
    {
        $normalizedDetected = array_map(fn ($h) => self::normalizeHeader($h), $detectedHeaders);
        $normalizedRequired = array_map(fn ($h) => self::normalizeHeader($h), $requiredHeaders);

        $matched = array_values(array_intersect($normalizedRequired, $normalizedDetected));
        $missing = array_values(array_diff($normalizedRequired, $normalizedDetected));
        $extra = array_values(array_diff($normalizedDetected, $normalizedRequired));

        return [
            'is_valid' => empty($missing),
            'matched' => $matched,
            'missing' => $missing,
            'extra' => $extra,
            'required' => $normalizedRequired,
        ];
    }

    /**
     * Get count of data rows in a worksheet (excluding header row).
     *
     * @param string $filePath
     * @param string|null $sheetName
     * @return int
     */
    public static function getRowCount(string $filePath, ?string $sheetName = null): int
    {
        $reader = IOFactory::createReaderForFile($filePath);
        $reader->setReadDataOnly(true);

        if ($sheetName && method_exists($reader, 'setLoadSheetsOnly')) {
            $reader->setLoadSheetsOnly($sheetName);
        }

        $spreadsheet = $reader->load($filePath);
        $sheet = $sheetName ? ($spreadsheet->getSheetByName($sheetName) ?? $spreadsheet->getActiveSheet()) : $spreadsheet->getActiveSheet();

        $highestRow = $sheet->getHighestDataRow();
        return max(0, $highestRow - 1);
    }

    /**
     * Inspect file and return full metadata for UI.
     *
     * @param string $filePath
     * @param string|null $sheetName
     * @param array<int, string> $requiredHeaders
     * @return array{sheets: array<int, string>, active_sheet: string, headers: array<int, string>, raw_headers: array<int, string>, validation: array, total_rows: int, preview_rows: array}
     */
    public static function inspectFile(string $filePath, ?string $sheetName = null, array $requiredHeaders = []): array
    {
        $sheets = self::getSheets($filePath);
        $activeSheet = $sheetName && in_array($sheetName, $sheets, true) ? $sheetName : ($sheets[0] ?? 'Sheet 1');

        $headerInfo = self::readHeaders($filePath, $activeSheet);
        $validation = self::validateHeaders($headerInfo['normalized'], $requiredHeaders);
        $totalRows = self::getRowCount($filePath, $activeSheet);
        $previewRows = self::readRows($filePath, $activeSheet, 0, 3);

        return [
            'sheets' => $sheets,
            'active_sheet' => $activeSheet,
            'headers' => $headerInfo['normalized'],
            'raw_headers' => $headerInfo['raw'],
            'validation' => $validation,
            'total_rows' => $totalRows,
            'preview_rows' => $previewRows,
        ];
    }

    /**
     * Read rows from a file and return associative arrays mapped by normalized headers.
     *
     * @param string $filePath
     * @param string|null $sheetName
     * @param int $offset
     * @param int|null $limit
     * @return array<int, array<string, mixed>>
     */
    public static function readRows(string $filePath, ?string $sheetName = null, int $offset = 0, ?int $limit = null): array
    {
        $reader = IOFactory::createReaderForFile($filePath);
        $reader->setReadDataOnly(false); // To detect formatted dates

        if ($sheetName && method_exists($reader, 'setLoadSheetsOnly')) {
            $reader->setLoadSheetsOnly($sheetName);
        }

        $spreadsheet = $reader->load($filePath);
        $sheet = $sheetName ? ($spreadsheet->getSheetByName($sheetName) ?? $spreadsheet->getActiveSheet()) : $spreadsheet->getActiveSheet();

        $headerInfo = self::readHeaders($filePath, $sheetName);
        $colMap = $headerInfo['map']; // e.g. ['A' => 'name', 'B' => 'phone', ...]

        if (empty($colMap)) {
            return [];
        }

        $startRow = 2 + $offset;
        $highestRow = $sheet->getHighestDataRow();

        if ($startRow > $highestRow) {
            return [];
        }

        $endRow = $limit !== null ? min($highestRow, $startRow + $limit - 1) : $highestRow;
        $result = [];

        for ($row = $startRow; $row <= $endRow; $row++) {
            $rowData = [];
            $hasData = false;

            foreach ($colMap as $colLetter => $headerName) {
                $cell = $sheet->getCell($colLetter . $row);
                $value = $cell->getValue();

                // Format Excel date numbers if applicable
                if (SpreadsheetDate::isDateTime($cell) && is_numeric($value)) {
                    try {
                        $value = SpreadsheetDate::excelToDateTimeObject($value)->format('Y-m-d');
                    } catch (Exception $e) {
                        // Keep raw value if conversion fails
                    }
                }

                if ($value !== null && trim((string) $value) !== '') {
                    $hasData = true;
                    $rowData[$headerName] = is_string($value) ? trim($value) : $value;
                } else {
                    $rowData[$headerName] = null;
                }
            }

            // Only include non-empty rows
            if ($hasData) {
                $rowData['_row_number'] = $row;
                $result[] = $rowData;
            }
        }

        return $result;
    }

    /**
     * Parse debt amount with support for Indonesian numbers and Excel thousands notation.
     *
     * Handles:
     * - Multi-dot Indonesian strings: "40.041.400" -> 40041400
     * - Excel thousands notation: 166.89 -> 166890, 163.9 -> 163900, 583.602 -> 583602
     * - Standard integers: 166890 -> 166890
     * - Currency formatted: "Rp 1.486.125" -> 1486125
     *
     * @param mixed $value
     * @return int
     */
    public static function parseDebtAmount(mixed $value): int
    {
        if ($value === null || $value === '') {
            return 0;
        }

        if (is_numeric($value)) {
            $floatVal = (float) $value;
            // In Indonesian music ERP exports, numbers < 1000 with fractional parts
            // represent values in thousands (ribuan) where Excel stripped trailing zeros
            if ($floatVal > 0 && $floatVal < 1000 && floor($floatVal) != $floatVal) {
                return (int) round($floatVal * 1000);
            }
            return (int) round($floatVal);
        }

        $str = trim((string) $value);
        // Strip out non-numeric characters except dots and commas
        $str = preg_replace('/[^\d.,]/', '', $str);

        if ($str === '') {
            return 0;
        }

        $dotCount = substr_count($str, '.');
        $commaCount = substr_count($str, ',');

        // Multiple dots: e.g. "40.041.400" or "1.486.125"
        if ($dotCount >= 2) {
            $clean = str_replace('.', '', $str);
            $clean = str_replace(',', '.', $clean);
            return (int) round((float) $clean);
        }

        // Single dot: e.g. "166.89", "163.9", "583.602"
        if ($dotCount === 1 && $commaCount === 0) {
            $parts = explode('.', $str);
            $beforeDot = (float) $parts[0];
            $afterDot = $parts[1];
            if ($beforeDot > 0 && $beforeDot < 1000) {
                $padded = str_pad(substr($afterDot, 0, 3), 3, '0', STR_PAD_RIGHT);
                return (int) ($beforeDot * 1000 + (int) $padded);
            }
            return (int) round((float) $str);
        }

        // Dot for thousands, comma for decimal: "40.041.400,00"
        if ($dotCount >= 1 && $commaCount === 1) {
            $clean = str_replace('.', '', $str);
            $clean = str_replace(',', '.', $clean);
            return (int) round((float) $clean);
        }

        // Single comma: "166,89" or "163,9"
        if ($dotCount === 0 && $commaCount === 1) {
            $parts = explode(',', $str);
            $beforeComma = (float) $parts[0];
            $afterComma = $parts[1];
            if ($beforeComma > 0 && $beforeComma < 1000) {
                $padded = str_pad(substr($afterComma, 0, 3), 3, '0', STR_PAD_RIGHT);
                return (int) ($beforeComma * 1000 + (int) $padded);
            }
            return (int) round((float) str_replace(',', '.', $str));
        }

        return (int) preg_replace('/[^\d]/', '', $str);
    }

    /**
     * Parse date string into standard 'Y-m-d' format.
     *
     * @param mixed $value
     * @return string
     */
    public static function parseDate(mixed $value): string
    {
        if (empty($value)) {
            return now()->format('Y-m-d');
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d');
        }

        $str = trim((string) $value);

        // Check DD-MM-YYYY or DD/MM/YYYY
        if (preg_match('/^(\d{1,2})[-\/](\d{1,2})[-\/](\d{4})$/', $str, $m)) {
            return sprintf('%04d-%02d-%02d', (int) $m[3], (int) $m[2], (int) $m[1]);
        }

        // Check YYYY-MM-DD
        if (preg_match('/^(\d{4})[-\/](\d{1,2})[-\/](\d{1,2})$/', $str, $m)) {
            return sprintf('%04d-%02d-%02d', (int) $m[1], (int) $m[2], (int) $m[3]);
        }

        try {
            return \Carbon\Carbon::parse($str)->format('Y-m-d');
        } catch (Exception $e) {
            return now()->format('Y-m-d');
        }
    }
}
