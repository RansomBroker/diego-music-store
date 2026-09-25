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
     * Normalize a header string to standard snake_case.
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
        return trim($str ?? '', '_');
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
}
