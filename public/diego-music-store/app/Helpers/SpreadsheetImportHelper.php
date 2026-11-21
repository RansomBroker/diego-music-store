<?php

namespace App\Helpers;

use Exception;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Csv;
use PhpOffice\PhpSpreadsheet\Shared\Date as SpreadsheetDate;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SpreadsheetImportHelper
{
    /**
     * Common column header aliases mapped to canonical template keys.
     *
     * @var array<string, string>
     */
    public const HEADER_ALIASES = [
        // Row index / ignored metadata
        'no_urut'           => 'no',
        'nomor'             => 'no',
        'nomor_urut'        => 'no',

        // Product mappings
        'harga_jual_cash'   => 'harga_jual',
        'hargajualcash'     => 'harga_jual',
        'harga_cash'        => 'harga_jual',
        'hargacash'         => 'harga_jual',
        'harga_jual_tunai'  => 'harga_jual',
        'harga_jual_normal' => 'harga_jual',
        'harga_jual_retail' => 'harga_jual',
        'harga_jual_umum'   => 'harga_jual',
        'harga_tunai'       => 'harga_jual',
        'harga_retail'      => 'harga_jual',
        'harga_jual_rp'     => 'harga_jual',
        'harga_jual_cash_rp'=> 'harga_jual',
        'selling_price'     => 'harga_jual',

        'kode_brg'          => 'kode_barang',
        'kode_item'         => 'kode_barang',
        'kode_produk'       => 'kode_barang',
        'kode_stok'         => 'kode_barang',
        'kd_barang'         => 'kode_barang',
        'kd_brg'            => 'kode_barang',
        'kd_item'           => 'kode_barang',
        'kd_stok'           => 'kode_barang',
        'kodebarang'        => 'kode_barang',
        'item_code'         => 'kode_barang',
        'product_code'      => 'kode_barang',
        'sku'               => 'kode_barang',

        'nama_barang'       => 'nama_stok',
        'nama_produk'       => 'nama_stok',
        'nama_item'         => 'nama_stok',
        'nm_stok'           => 'nama_stok',
        'nm_barang'         => 'nama_stok',
        'nm_produk'         => 'nama_stok',
        'nm_item'           => 'nama_stok',
        'namastok'          => 'nama_stok',
        'namabarang'        => 'nama_stok',
        'namaproduk'        => 'nama_stok',
        'product_name'      => 'nama_stok',
        'item_name'         => 'nama_stok',
        'stock_name'        => 'nama_stok',

        'kategoribarang'    => 'kategori_barang',
        'kategori_produk'   => 'kategori_barang',
        'kategori_stok'     => 'kategori_barang',
        'kat_barang'        => 'kategori_barang',
        'kat_produk'        => 'kategori_barang',
        'jenis_barang'      => 'kategori_barang',
        'kelompok_barang'   => 'kategori_barang',
        'category'          => 'kategori_barang',

        'hargabeli'         => 'harga_beli',
        'harga_beli_bruto'  => 'harga_beli',
        'harga_beli_rp'     => 'harga_beli',
        'hrg_beli'          => 'harga_beli',
        'h_beli'            => 'harga_beli',
        'cost_price'        => 'harga_beli',
        'purchase_price'    => 'harga_beli',

        'harganetto'        => 'netto',
        'harga_netto'       => 'netto',
        'hrg_netto'         => 'netto',
        'h_netto'           => 'netto',
        'netto_rp'          => 'netto',
        'hpp_netto'         => 'netto',
        'harga_modal'       => 'netto',

        'diskon'            => 'disc',
        'discount'          => 'disc',
        'disc_persen'       => 'disc',
        'diskon_persen'     => 'disc',
        'disc_rp'           => 'disc',
        'potongan'          => 'disc',

        'harga_het'         => 'het',
        'het_rp'            => 'het',
        'h_e_t'             => 'het',
        'harga_eceran_tertinggi' => 'het',

        'jlh_stock'         => 'jlh_stok',
        'jlhstok'           => 'jlh_stok',
        'jml_stok'          => 'jlh_stok',
        'jml_stock'         => 'jlh_stok',
        'jumlah_stok'       => 'jlh_stok',
        'jumlahstok'        => 'jlh_stok',
        'jumlah_stock'      => 'jlh_stok',
        'qty_stok'          => 'jlh_stok',
        'qty_stock'         => 'jlh_stok',
        'sisa_stok'         => 'jlh_stok',
        'saldo_stok'        => 'jlh_stok',
        'stok_akhir'        => 'jlh_stok',
        'stok_awal'         => 'jlh_stok',
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
     * Indonesian shorthand token expansions applied during smart header detection.
     *
     * @var array<string, string>
     */
    private const TOKEN_EXPANSIONS = [
        'hrg'  => 'harga',
        'h'    => 'harga',
        'jml'  => 'jumlah',
        'jlh'  => 'jumlah',
        'kd'   => 'kode',
        'nm'   => 'nama',
        'kat'  => 'kategori',
        'brg'  => 'barang',
        'disk' => 'diskon',
        'nett' => 'netto',
    ];

    /**
     * Unit/currency noise tokens ignored at the end of header labels.
     *
     * @var array<int, string>
     */
    private const NOISE_UNIT_TOKENS = ['rp', 'idr', 'persen', 'pct', 'pcs', 'unit', 'qty'];

    /**
     * Get all sheet names from an Excel or CSV file.
     *
     * @param  string  $filePath
     * @return array<int, string>
     */
    public static function getSheets(string $filePath): array
    {
        try {
            $reader = self::createConfiguredReader($filePath, true);
            if (method_exists($reader, 'listWorksheetNames')) {
                $names = $reader->listWorksheetNames($filePath);
                if (! empty($names)) {
                    return $names;
                }
            }
        } catch (Exception $e) {
            // Fallback for CSV or single sheet files
        }

        return ['Sheet 1'];
    }

    /**
     * Clean invisible BOM/NBSP whitespace from a raw header string.
     */
    public static function cleanHeaderText(mixed $header): string
    {
        if ($header === null) {
            return '';
        }

        $str = (string) $header;
        $str = preg_replace('/^\xEF\xBB\xBF/', '', $str) ?? $str;
        $str = preg_replace('/[\x{00A0}\x{200B}-\x{200D}\x{FEFF}]/u', ' ', $str) ?? $str;
        $str = preg_replace('/\s+/', ' ', trim($str)) ?? trim($str);

        return $str;
    }

    /**
     * Normalize a header string to standard snake_case and resolve known aliases.
     *
     * @param  mixed  $header
     * @return string
     */
    public static function normalizeHeader(mixed $header): string
    {
        $cleaned = self::cleanHeaderText($header);
        if ($cleaned === '') {
            return '';
        }

        $str = preg_replace('/[^a-zA-Z0-9]+/', '_', strtolower($cleaned));
        $normalized = trim($str ?? '', '_');

        return self::HEADER_ALIASES[$normalized] ?? $normalized;
    }

    /**
     * Resolve a raw header string to a canonical key within the target required headers,
     * applying abbreviation expansion and pattern rules when in product-import context.
     *
     * @param  mixed  $header
     * @param  array<int, string>  $requiredHeaders
     */
    public static function resolveHeaderForRequired(mixed $header, array $requiredHeaders = []): string
    {
        $normalized = self::normalizeHeader($header);
        if ($normalized === '') {
            return '';
        }

        if ($normalized === 'no' || in_array($normalized, $requiredHeaders, true)) {
            return $normalized;
        }

        $cleaned = strtolower(self::cleanHeaderText($header));
        $collapsed = preg_replace('/[^a-z0-9]+/', '', $cleaned) ?? '';

        if ($collapsed === 'het') {
            return in_array('het', $requiredHeaders, true) ? 'het' : $normalized;
        }

        $rawTokens = array_values(array_filter(explode('_', preg_replace('/[^a-z0-9]+/', '_', $cleaned) ?? ''), fn ($t) => $t !== ''));
        $expandedTokens = array_map(fn (string $t): string => self::TOKEN_EXPANSIONS[$t] ?? $t, $rawTokens);

        while (count($expandedTokens) > 1 && in_array(end($expandedTokens), self::NOISE_UNIT_TOKENS, true)) {
            array_pop($expandedTokens);
        }

        $expandedKey = implode('_', $expandedTokens);
        $expandedAlias = self::HEADER_ALIASES[$expandedKey] ?? $expandedKey;

        if ($expandedAlias === 'no' || in_array($expandedAlias, $requiredHeaders, true)) {
            return $expandedAlias;
        }

        // Apply smart fuzzy product-header rules only when importing products
        $isProductContext = in_array('kode_barang', $requiredHeaders, true) || in_array('nama_stok', $requiredHeaders, true);
        if ($isProductContext) {
            $productMatch = self::matchProductHeaderByTokens($expandedTokens, $collapsed);
            if ($productMatch !== null) {
                return $productMatch;
            }
        }

        return $normalized;
    }

    /**
     * Read and auto-detect the best header row from a worksheet (scanning up to row 20).
     *
     * @param  string  $filePath
     * @param  string|null  $sheetName
     * @param  array<int, string>  $requiredHeaders
     * @return array{normalized: array<int, string>, raw: array<int, string>, map: array<string, string>, header_row: int, raw_by_col: array<string, string>, matched_sources: array<string, string>, ignored: array<int, string>}
     */
    public static function readHeaders(string $filePath, ?string $sheetName = null, array $requiredHeaders = []): array
    {
        $sheet = self::loadWorksheet($filePath, $sheetName, true);
        $highestColumn = $sheet->getHighestColumn();
        $highestRow = min(20, max(1, (int) $sheet->getHighestDataRow()));

        $bestResult = null;
        $bestScore = -1;

        for ($rowIdx = 1; $rowIdx <= $highestRow; $rowIdx++) {
            $cells = $sheet->rangeToArray('A' . $rowIdx . ':' . $highestColumn . $rowIdx, null, true, true, true);
            $candidateRow = $cells[$rowIdx] ?? [];

            $parsed = self::evaluateCandidateHeaderRow($candidateRow, $requiredHeaders);
            if (empty($parsed['raw'])) {
                continue;
            }

            $matchedCount = count(array_intersect($requiredHeaders, $parsed['normalized']));

            if ($bestResult === null && $rowIdx === 1) {
                $bestResult = ['row' => $rowIdx, 'parsed' => $parsed];
                $bestScore = $matchedCount;
            } elseif ($matchedCount > $bestScore && ($matchedCount >= 2 || empty($requiredHeaders))) {
                $bestResult = ['row' => $rowIdx, 'parsed' => $parsed];
                $bestScore = $matchedCount;
            } elseif ($bestResult === null && ($matchedCount >= 2 || empty($requiredHeaders))) {
                $bestResult = ['row' => $rowIdx, 'parsed' => $parsed];
                $bestScore = $matchedCount;
            }
        }

        if ($bestResult === null) {
            $row1Cells = $sheet->rangeToArray('A1:' . $highestColumn . '1', null, true, true, true)[1] ?? [];
            $bestResult = ['row' => 1, 'parsed' => self::evaluateCandidateHeaderRow($row1Cells, $requiredHeaders)];
        }

        return array_merge($bestResult['parsed'], [
            'header_row' => $bestResult['row'],
        ]);
    }

    /**
     * Validate detected headers against required template headers.
     *
     * @param  array<int, string>  $detectedHeaders
     * @param  array<int, string>  $requiredHeaders
     * @return array{is_valid: bool, matched: array<int, string>, missing: array<int, string>, extra: array<int, string>, required: array<int, string>}
     */
    public static function validateHeaders(array $detectedHeaders, array $requiredHeaders): array
    {
        $normalizedDetected = array_values(array_map(fn ($h) => self::resolveHeaderForRequired($h, $requiredHeaders), $detectedHeaders));
        $normalizedRequired = array_values(array_map(fn ($h) => self::normalizeHeader($h), $requiredHeaders));

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
     * Get count of data rows in a worksheet (excluding banner and header rows).
     *
     * @param  string  $filePath
     * @param  string|null  $sheetName
     * @param  int|null  $headerRow
     * @return int
     */
    public static function getRowCount(string $filePath, ?string $sheetName = null, ?int $headerRow = null): int
    {
        $sheet = self::loadWorksheet($filePath, $sheetName, true);
        $highestRow = (int) $sheet->getHighestDataRow();
        $resolvedHeaderRow = $headerRow ?? 1;

        return max(0, $highestRow - $resolvedHeaderRow);
    }

    /**
     * Inspect file and return full metadata for UI.
     *
     * @param  string  $filePath
     * @param  string|null  $sheetName
     * @param  array<int, string>  $requiredHeaders
     * @return array{sheets: array<int, string>, active_sheet: string, header_row: int, headers: array<int, string>, raw_headers: array<int, string>, col_map: array<string, string>, raw_by_col: array<string, string>, matched_sources: array<string, string>, ignored_headers: array<int, string>, validation: array, total_rows: int, preview_rows: array}
     */
    public static function inspectFile(string $filePath, ?string $sheetName = null, array $requiredHeaders = []): array
    {
        $sheets = self::getSheets($filePath);
        $activeSheet = $sheetName && in_array($sheetName, $sheets, true) ? $sheetName : ($sheets[0] ?? 'Sheet 1');

        $headerInfo = self::readHeaders($filePath, $activeSheet, $requiredHeaders);
        $validation = self::validateHeaders($headerInfo['normalized'], $requiredHeaders);
        $totalRows = self::getRowCount($filePath, $activeSheet, $headerInfo['header_row']);
        $previewRows = self::readRows($filePath, $activeSheet, 0, 3, $headerInfo['header_row'], $headerInfo['map']);

        return [
            'sheets' => $sheets,
            'active_sheet' => $activeSheet,
            'header_row' => $headerInfo['header_row'],
            'headers' => $headerInfo['normalized'],
            'raw_headers' => $headerInfo['raw'],
            'col_map' => $headerInfo['map'],
            'raw_by_col' => $headerInfo['raw_by_col'],
            'matched_sources' => $headerInfo['matched_sources'],
            'ignored_headers' => $headerInfo['ignored'],
            'validation' => $validation,
            'total_rows' => $totalRows,
            'preview_rows' => $previewRows,
        ];
    }

    /**
     * Read rows from a file and return associative arrays mapped by normalized/mapped headers.
     *
     * @param  string  $filePath
     * @param  string|null  $sheetName
     * @param  int  $offset
     * @param  int|null  $limit
     * @param  int|null  $headerRow
     * @param  array<string, string>|null  $colMapOverride
     * @return array<int, array<string, mixed>>
     */
    public static function readRows(
        string $filePath,
        ?string $sheetName = null,
        int $offset = 0,
        ?int $limit = null,
        ?int $headerRow = null,
        ?array $colMapOverride = null
    ): array {
        $sheet = self::loadWorksheet($filePath, $sheetName, false);

        if ($headerRow === null || $colMapOverride === null) {
            $headerInfo = self::readHeaders($filePath, $sheetName);
            $resolvedHeaderRow = $headerRow ?? $headerInfo['header_row'];
            $colMap = $colMapOverride ?? $headerInfo['map'];
        } else {
            $resolvedHeaderRow = $headerRow;
            $colMap = $colMapOverride;
        }

        if (empty($colMap)) {
            return [];
        }

        $startRow = $resolvedHeaderRow + 1 + $offset;
        $highestRow = (int) $sheet->getHighestDataRow();

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
                if ($cell->isFormula()) {
                    try {
                        $value = $cell->getCalculatedValue();
                    } catch (Exception $e) {
                        $value = $cell->getValue();
                    }
                } else {
                    $value = $cell->getValue();
                }

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

            if ($hasData) {
                $rowData['_row_number'] = $row;
                $result[] = $rowData;
            }
        }

        return $result;
    }

    /**
     * Evaluate a candidate row and extract normalized/mapped header metadata.
     *
     * @param  array<string, mixed>  $rowCells
     * @param  array<int, string>  $requiredHeaders
     * @return array{normalized: array<int, string>, raw: array<int, string>, map: array<string, string>, raw_by_col: array<string, string>, matched_sources: array<string, string>, ignored: array<int, string>}
     */
    private static function evaluateCandidateHeaderRow(array $rowCells, array $requiredHeaders): array
    {
        $raw = [];
        $normalized = [];
        $map = [];
        $rawByCol = [];
        $matchedSources = [];
        $ignored = [];
        $claimedRequired = [];

        foreach ($rowCells as $colLetter => $cellVal) {
            $cleaned = self::cleanHeaderText($cellVal);
            if ($cleaned === '') {
                continue;
            }

            $resolved = self::resolveHeaderForRequired($cleaned, $requiredHeaders);

            // Prevent vague secondary columns from overwriting a canonical required key already claimed
            if (in_array($resolved, $requiredHeaders, true) && isset($claimedRequired[$resolved])) {
                $fallbackKey = self::normalizeHeader($cleaned);
                if ($fallbackKey === $resolved) {
                    $fallbackKey = $resolved . '_' . strtolower($colLetter);
                }
                $resolved = $fallbackKey;
            }

            $raw[] = $cleaned;
            $rawByCol[$colLetter] = $cleaned;
            $normalized[] = $resolved;
            $map[$colLetter] = $resolved;

            if ($resolved === 'no') {
                $ignored[] = $cleaned;
            } elseif (in_array($resolved, $requiredHeaders, true)) {
                $claimedRequired[$resolved] = true;
                $matchedSources[$resolved] = $cleaned;
            }
        }

        return [
            'normalized' => $normalized,
            'raw' => $raw,
            'map' => $map,
            'raw_by_col' => $rawByCol,
            'matched_sources' => $matchedSources,
            'ignored' => $ignored,
        ];
    }

    /**
     * Match token-expanded product header labels to canonical product keys.
     *
     * @param  array<int, string>  $tokens
     */
    private static function matchProductHeaderByTokens(array $tokens, string $collapsed): ?string
    {
        if ($tokens === [] || is_numeric($collapsed)) {
            return null;
        }

        if (in_array($collapsed, ['no', 'nourut', 'nomor', 'nomorurut'], true)) {
            return 'no';
        }

        $joined = implode('_', $tokens);

        if (str_contains($joined, 'het') || str_contains($collapsed, 'hargaecerantertinggi')) {
            return 'het';
        }

        if (self::hasAnyToken($tokens, ['disc', 'diskon', 'discount', 'potongan']) && ! self::hasAnyToken($tokens, ['harga'])) {
            return 'disc';
        }

        if (self::hasAnyToken($tokens, ['kode', 'sku', 'code', 'barcode']) && ! self::hasAnyToken($tokens, ['nama', 'harga', 'stok', 'kategori'])) {
            return 'kode_barang';
        }

        if (self::hasAnyToken($tokens, ['kategori', 'category', 'kelompok', 'grup', 'group', 'jenis'])) {
            return 'kategori_barang';
        }

        if (self::hasAnyToken($tokens, ['nama', 'name', 'uraian', 'deskripsi', 'artikel']) && ! self::hasAnyToken($tokens, ['kategori', 'supplier', 'vendor'])) {
            return 'nama_stok';
        }

        if (self::hasAnyToken($tokens, ['netto', 'hpp', 'net', 'modal']) && ! self::hasAnyToken($tokens, ['jual'])) {
            return 'netto';
        }

        if (self::hasAnyToken($tokens, ['beli', 'cost', 'purchase', 'bruto'])) {
            return 'harga_beli';
        }

        if (self::hasAnyToken($tokens, ['jual', 'selling', 'price', 'cash', 'tunai', 'retail', 'umum', 'eceran'])) {
            return 'harga_jual';
        }

        if (self::hasAnyToken($tokens, ['stok', 'stock', 'qty', 'quantity', 'jumlah', 'sisa', 'saldo']) && ! self::hasAnyToken($tokens, ['nama', 'kode', 'kategori'])) {
            return 'jlh_stok';
        }

        return null;
    }

    /**
     * Check whether any candidate needle exists in the token array.
     *
     * @param  array<int, string>  $tokens
     * @param  array<int, string>  $needles
     */
    private static function hasAnyToken(array $tokens, array $needles): bool
    {
        return ! empty(array_intersect($tokens, $needles));
    }

    /**
     * Load a Spreadsheet with CSV delimiter/encoding auto-detection applied.
     */
    public static function loadSpreadsheet(string $filePath, bool $readDataOnly = true, ?string $sheetName = null): \PhpOffice\PhpSpreadsheet\Spreadsheet
    {
        $reader = self::createConfiguredReader($filePath, $readDataOnly);

        if ($sheetName && method_exists($reader, 'setLoadSheetsOnly')) {
            $reader->setLoadSheetsOnly($sheetName);
        }

        return $reader->load($filePath);
    }

    /**
     * Load the requested worksheet using a properly configured reader.
     */
    private static function loadWorksheet(string $filePath, ?string $sheetName = null, bool $readDataOnly = true): Worksheet
    {
        $spreadsheet = self::loadSpreadsheet($filePath, $readDataOnly, $sheetName);

        return $sheetName
            ? ($spreadsheet->getSheetByName($sheetName) ?? $spreadsheet->getActiveSheet())
            : $spreadsheet->getActiveSheet();
    }

    /**
     * Instantiate a PhpSpreadsheet reader with CSV delimiter/encoding detection enabled.
     */
    private static function createConfiguredReader(string $filePath, bool $readDataOnly): \PhpOffice\PhpSpreadsheet\Reader\IReader
    {
        $inputType = IOFactory::identify($filePath);
        $reader = IOFactory::createReader($inputType);
        $reader->setReadDataOnly($readDataOnly);

        if ($reader instanceof Csv) {
            $reader->setInputEncoding('UTF-8');
            $delimiter = self::detectCsvDelimiter($filePath);
            if ($delimiter !== null) {
                $reader->setDelimiter($delimiter);
            }
        }

        return $reader;
    }

    /**
     * Detect the most consistent delimiter for CSV/TSV files.
     */
    private static function detectCsvDelimiter(string $filePath): ?string
    {
        $handle = @fopen($filePath, 'rb');
        if (! $handle) {
            return null;
        }

        $sampleLines = [];
        while (count($sampleLines) < 5 && ($line = fgets($handle)) !== false) {
            $trimmed = trim($line);
            if ($trimmed !== '') {
                $sampleLines[] = $trimmed;
            }
        }
        fclose($handle);

        if ($sampleLines === []) {
            return null;
        }

        $candidates = ["\t", ';', ',', '|'];
        $bestDelimiter = null;
        $bestScore = 0;

        foreach ($candidates as $delimiter) {
            $counts = array_map(fn (string $l): int => count(str_getcsv($l, $delimiter)), $sampleLines);
            $maxCols = max($counts);
            $minCols = min($counts);
            if ($maxCols > 1) {
                $consistency = ($minCols === $maxCols) ? $maxCols * 2 : $maxCols;
                if ($consistency > $bestScore) {
                    $bestScore = $consistency;
                    $bestDelimiter = $delimiter;
                }
            }
        }

        return $bestDelimiter;
    }

    /**
     * Parse debt amount with support for Indonesian numbers and Excel thousands notation.
     *
     * @param  mixed  $value
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
        $str = preg_replace('/[^\d.,]/', '', $str);

        if ($str === '') {
            return 0;
        }

        $dotCount = substr_count($str, '.');
        $commaCount = substr_count($str, ',');

        if ($dotCount >= 2) {
            $clean = str_replace('.', '', $str);
            $clean = str_replace(',', '.', $clean);

            return (int) round((float) $clean);
        }

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

        if ($dotCount >= 1 && $commaCount === 1) {
            $clean = str_replace('.', '', $str);
            $clean = str_replace(',', '.', $clean);

            return (int) round((float) $clean);
        }

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
     * @param  mixed  $value
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

        if (preg_match('/^(\d{1,2})[-\/](\d{1,2})[-\/](\d{4})$/', $str, $m)) {
            return sprintf('%04d-%02d-%02d', (int) $m[3], (int) $m[2], (int) $m[1]);
        }

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
