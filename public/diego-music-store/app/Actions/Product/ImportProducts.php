<?php

namespace App\Actions\Product;

use App\Helpers\AccountHelper;
use App\Helpers\ProductHelper;
use App\Models\Account;
use App\Models\Branch;
use App\Actions\Branch\EnsureBranchCoaAccounts;
use App\Models\JournalEntry;
use App\Models\JournalItem;
use App\Models\Product;
use App\Models\ProductBranchStock;
use App\Models\ProductVariant;
use App\Models\StockMovement;
use App\Models\Unit;
use Exception;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportProducts
{
    /**
     * Required headers for Product import.
     *
     * @var array<int, string>
     */
    public const REQUIRED_HEADERS = [
        'kode_barang',
        'nama_stok',
        'kategori_barang',
        'harga_beli',
        'netto',
        'disc',
        'het',
        'harga_jual',
        'jlh_stok',
    ];

    /**
     * Execute the import action directly on an array of row data (for batch progress bar).
     *
     * @param  array<int, array<string, mixed>>  $rows
     * @param  int  $branchId
     * @param  int|null  $userId
     * @return array{imported: int, skipped: int, errors: array<int, string>}
     *
     * @throws Exception
     */
    public function executeRows(array $rows, int $branchId, ?int $userId = null, ?string $hppSource = null): array
    {
        $branch = Branch::find($branchId);
        if (!$branch) {
            throw new Exception("Cabang dengan ID {$branchId} tidak ditemukan.");
        }

        $branch = $this->resolveBranchInventoryAccount($branch);
        $inventoryAccId = $branch->inventory_account_id;
        $salesAccId = AccountHelper::findByCode('411101001')?->id;
        $cogsAccId = AccountHelper::findByCode('511501001')?->id;

        $defaultUnit = Unit::where('code', 'pcs')->first()
            ?: Unit::firstOrCreate(['code' => 'pcs'], ['name' => 'Pieces', 'is_active' => true]);

        $importedCount = 0;
        $skippedCount = 0;
        $errors = [];
        $batchTotalValue = 0;

        DB::transaction(function () use (
            $rows,
            $branch,
            $branchId,
            $hppSource,
            $defaultUnit,
            $inventoryAccId,
            $salesAccId,
            $cogsAccId,
            &$importedCount,
            &$skippedCount,
            &$errors,
            &$batchTotalValue
        ) {
            foreach ($rows as $index => $row) {
                $rowNum = $row['_row_number'] ?? ($index + 2);
                try {
                    $sku = trim((string)($row['kode_barang'] ?? $row['sku'] ?? ''));
                    $name = trim((string)($row['nama_stok'] ?? $row['name'] ?? ''));

                    if ($sku === '' && $name === '') {
                        continue;
                    }

                    if ($sku === '') {
                        // Otomatis generate SKU menggunakan prefix cabang
                        $sku = ProductHelper::generateUniqueSku($branch->sku_prefix ?? null, $branchId);
                    }

                    if ($name === '') {
                        $skippedCount++;
                        $errors[] = "Baris {$rowNum}: NAMA STOK tidak boleh kosong.";
                        continue;
                    }

                    $category = !empty($row['kategori_barang']) ? trim((string)$row['kategori_barang']) : (!empty($row['category']) ? trim((string)$row['category']) : null);

                    $productType = 'physical';
                    if ($category !== null && stripos($category, 'ONGKOS') !== false) {
                        $productType = 'service';
                        $category = 'Jasa/Service';
                    }

                    $costPrice = $this->sanitizeNumber($row['harga_beli'] ?? $row['cost_price'] ?? 0);
                    $netto = $this->sanitizeNumber($row['netto'] ?? $row['hpp'] ?? 0);
                    $price = $this->sanitizeNumber($row['harga_jual'] ?? $row['harga_jual_cash'] ?? $row['price'] ?? 0);

                    if ($price === 0 && isset($row['het'])) {
                        $price = $this->sanitizeNumber($row['het']);
                    }

                    if ($hppSource !== null && isset($row[$hppSource])) {
                        $hpp = $this->sanitizeNumber($row[$hppSource]);
                    } else {
                        $hpp = $netto > 0 ? $netto : $costPrice;
                    }
                    $discountValue = $this->sanitizeNumber($row['disc'] ?? $row['discount'] ?? 0);

                    $rawStock = (string)($row['jlh_stok'] ?? $row['stock'] ?? '0');
                    $parsedStock = $this->parseStockAndUnit($rawStock, $defaultUnit);
                    $qty = $parsedStock['quantity'];
                    $unitId = $parsedStock['unit_id'];

                    $variant = ProductVariant::where('sku', $sku)->first();

                    if ($variant) {
                        $product = $variant->product;
                        $product->update([
                            'name' => $name,
                            'type' => $productType,
                            'category' => $category ?: $product->category,
                            'unit_id' => $unitId ?: $product->unit_id,
                        ]);

                        $barcode = $variant->barcode;
                        if (empty($barcode) || $barcode === $sku) {
                            $barcode = ProductHelper::generateUniqueBarcode();
                        }

                        $variant->update([
                            'price' => $price > 0 ? $price : $variant->price,
                            'cost_price' => $costPrice > 0 ? $costPrice : $variant->cost_price,
                            'hpp' => $hpp > 0 ? $hpp : $variant->hpp,
                            'discount_value' => $discountValue,
                            'barcode' => $barcode,
                        ]);

                        $branchStock = ProductBranchStock::firstOrCreate([
                            'product_variant_id' => $variant->id,
                            'branch_id' => $branchId,
                        ], [
                            'stock' => 0,
                            'hpp' => $hpp,
                        ]);

                        $prevStock = $branchStock->stock;
                        $branchStock->update([
                            'stock' => $qty,
                            'hpp' => $hpp > 0 ? $hpp : $branchStock->hpp,
                        ]);

                        $diff = $qty - $prevStock;
                        if ($diff !== 0) {
                            StockMovement::create([
                                'product_variant_id' => $variant->id,
                                'branch_id' => $branchId,
                                'type' => $diff > 0 ? 'in' : 'out',
                                'quantity' => abs($diff),
                                'original_quantity' => $prevStock,
                                'unit_id' => $unitId,
                                'unit_cost' => $hpp,
                                'hpp' => $hpp,
                                'reference_type' => 'InitialStock',
                                'reference_id' => $variant->id,
                            ]);

                            if ($diff > 0) {
                                $batchTotalValue += ($diff * $hpp);
                            }
                        }

                        $importedCount++;
                    } else {
                        $product = Product::create([
                            'name' => $name,
                            'type' => $productType,
                            'category' => $category,
                            'unit_id' => $unitId,
                            'inventory_account_id' => $inventoryAccId,
                            'sales_account_id' => $salesAccId,
                            'cogs_account_id' => $cogsAccId,
                            'is_active' => true,
                            'minimum_stock' => 0,
                        ]);

                        $barcode = ProductHelper::generateUniqueBarcode();

                        $variant = ProductVariant::create([
                            'product_id' => $product->id,
                            'sku' => $sku,
                            'barcode' => $barcode,
                            'name' => null,
                            'price' => $price,
                            'cost_price' => $costPrice,
                            'hpp' => $hpp,
                            'discount_value' => $discountValue,
                            'discount_type' => 'percent',
                            'tax_value' => 0,
                            'tax_type' => 'percent',
                            'is_active' => true,
                        ]);

                        ProductBranchStock::create([
                            'product_variant_id' => $variant->id,
                            'branch_id' => $branchId,
                            'stock' => $qty,
                            'hpp' => $hpp,
                        ]);

                        if ($qty > 0) {
                            StockMovement::create([
                                'product_variant_id' => $variant->id,
                                'branch_id' => $branchId,
                                'type' => 'in',
                                'quantity' => $qty,
                                'original_quantity' => 0,
                                'unit_id' => $unitId,
                                'unit_cost' => $hpp,
                                'hpp' => $hpp,
                                'reference_type' => 'InitialStock',
                                'reference_id' => $variant->id,
                            ]);

                            $batchTotalValue += ($qty * $hpp);
                        }

                        $importedCount++;
                    }
                } catch (Exception $e) {
                    $skippedCount++;
                    $errors[] = "Baris {$rowNum}: " . $e->getMessage();
                }
            }
        });

        return [
            'imported'    => $importedCount,
            'skipped'     => $skippedCount,
            'errors'      => $errors,
            'total_value' => $batchTotalValue,
        ];
    }

    /**
     * Record a balanced double-entry journal for initial stock.
     *
     * Debit: 111401001 - Persediaan Barang Dagang
     * Credit: 311101003 - Modal Pemilik (or 311201001 - Laba Ditahan)
     *
     * @param int $totalValue Total valuation of initial stock (Qty * HPP).
     * @param int $branchId Target branch for accounting attribution.
     * @param int|null $contraAccountId Specific equity account ID (Modal Disetor or Laba Ditahan).
     * @param int|null $userId User ID performing the action.
     * @param int $itemCount Count of product items imported.
     * @return JournalEntry|null
     * @throws Exception
     */
    public function recordInitialStockJournal(
        int $totalValue,
        int $branchId,
        ?int $contraAccountId = null,
        ?int $userId = null,
        int $itemCount = 0
    ): ?JournalEntry {
        if ($totalValue <= 0) {
            return null;
        }

        $branch = Branch::find($branchId);
        $branch = $branch ? $this->resolveBranchInventoryAccount($branch) : null;
        $inventoryAcc = $branch?->inventoryAccount;;

        $contraAcc = $contraAccountId ? Account::find($contraAccountId) : null;
        if (!$contraAcc) {
            $contraAcc = AccountHelper::findByCode('311101003')
                ?: Account::find(AccountHelper::resolveAccountId('311101003', 'MODAL PEMILIK', 'equity'));
        }

        if (!$inventoryAcc || !$contraAcc) {
            return null;
        }

        return DB::transaction(function () use ($totalValue, $branchId, $inventoryAcc, $contraAcc, $userId, $itemCount) {
            $journal = JournalEntry::create([
                'entry_no' => JournalEntry::generateEntryNo($branchId),
                'branch_id' => $branchId,
                'date' => now()->format('Y-m-d'),
                'description' => "Saldo Awal Persediaan dari Import Produk ({$itemCount} item)",
                'reference_type' => 'InitialStock',
                'status' => 'posted',
                'posted_at' => now(),
                'posted_by' => $userId,
                'created_by' => $userId,
            ]);

            // 1. Debit: Persediaan Barang Dagang (111401001)
            JournalItem::create([
                'journal_entry_id' => $journal->id,
                'account_id' => $inventoryAcc->id,
                'debit' => $totalValue,
                'credit' => 0,
                'notes' => "Saldo awal persediaan ({$itemCount} item produk)",
            ]);

            // 2. Kredit: Modal Disetor / Laba Ditahan
            JournalItem::create([
                'journal_entry_id' => $journal->id,
                'account_id' => $contraAcc->id,
                'debit' => 0,
                'credit' => $totalValue,
                'notes' => "Penyeimbang saldo awal persediaan ({$contraAcc->name})",
            ]);

            return $journal;
        });
    }

    /**
     * Resolve the posting inventory account for the target branch.
     *
     * Legacy imports used the generic 111401001 account. Re-link that legacy
     * default to a branch-specific account before importing stock.
     */
    private function resolveBranchInventoryAccount(Branch $branch): Branch
    {
        $branch->load('inventoryAccount');

        $account = $branch->inventoryAccount;
        $isLegacyGenericInventory = $account
            && (
                $account->code === '111401001'
                || mb_strtolower(trim($account->name)) === 'persediaan barang dagang'
            );

        if (!$account || $isLegacyGenericInventory) {
            if ($isLegacyGenericInventory) {
                $branch->inventory_account_id = null;
                $branch->save();
            }

            $branch = EnsureBranchCoaAccounts::execute($branch);
            $branch->load('inventoryAccount');
        }

        if (!$branch->inventoryAccount) {
            throw new Exception("Akun persediaan untuk cabang {$branch->name} belum dikonfigurasi.");
        }

        return $branch;
    }

    /**
     * Execute the import process from an Excel or CSV file.
     *
     * @param  string  $filePath  Absolute path to the uploaded Excel or CSV file.
     * @param  int  $branchId  Target branch ID for stocking the imported items.
     * @param  int|null  $userId  User ID who executed the import.
     * @return array{imported: int, updated: int, failed: int, errors: array<string>}
     *
     * @throws Exception
     */

    public function execute(string $filePath, int $branchId, ?int $userId = null, ?string $hppSource = null): array
    {
        if (!file_exists($filePath)) {
            throw new Exception("File import tidak ditemukan: {$filePath}");
        }

        $branch = Branch::find($branchId);
        if (!$branch) {
            throw new Exception("Cabang dengan ID {$branchId} tidak ditemukan.");
        }

        // 1. Resolve accounting accounts using the target branch's inventory COA.
        $branch = $this->resolveBranchInventoryAccount($branch);
        $inventoryAccId = $branch->inventory_account_id;
        $salesAccId = AccountHelper::findByCode('411101001')?->id;
        $cogsAccId = AccountHelper::findByCode('511501001')?->id;

        // 2. Resolve default unit
        $defaultUnit = Unit::where('code', 'pcs')->first()
            ?: Unit::firstOrCreate(['code' => 'pcs'], ['name' => 'Pieces', 'is_active' => true]);

        // 3. Load spreadsheet data
        $spreadsheet = IOFactory::load($filePath);
        $worksheet = $spreadsheet->getActiveSheet();
        $rawRows = $worksheet->toArray(null, true, true, true);

        if (empty($rawRows)) {
            throw new Exception('File Excel/CSV tidak memiliki data untuk di-import.');
        }

        // 4. Locate and map header row
        $headerMap = null;
        $dataRows = [];

        foreach ($rawRows as $rowIndex => $row) {
            if ($headerMap === null) {
                $candidate = $this->parseHeaderRow($row);
                if ($candidate !== null) {
                    $headerMap = $candidate;
                    continue;
                }
            } else {
                // Ensure row is not completely empty
                $filtered = array_filter($row, fn ($val) => $val !== null && trim((string)$val) !== '');
                if (!empty($filtered)) {
                    $dataRows[$rowIndex] = $row;
                }
            }
        }

        if ($headerMap === null) {
            throw new Exception('Header kolom tidak dikenali. Pastikan kolom memuat KODE BARANG, NAMA STOK, dan HARGA JUAL.');
        }

        // 5. Process records within a database transaction
        $importedCount = 0;
        $updatedCount = 0;
        $failedCount = 0;
        $errors = [];
        $totalStockValue = 0;

        DB::transaction(function () use (
            $dataRows,
            $headerMap,
            $branch,
            $branchId,
            $hppSource,
            $defaultUnit,
            $inventoryAccId,
            $salesAccId,
            $cogsAccId,
            &$importedCount,
            &$updatedCount,
            &$failedCount,
            &$errors,
            &$totalStockValue
        ) {
            foreach ($dataRows as $rowNumber => $row) {
                try {
                    $sku = trim((string)($row[$headerMap['sku']] ?? ''));
                    $name = trim((string)($row[$headerMap['name']] ?? ''));

                    if ($sku === '' && $name === '') {
                        continue; // Skip blank lines
                    }

                    if ($sku === '') {
                        // Otomatis generate SKU menggunakan prefix cabang
                        $sku = ProductHelper::generateUniqueSku($branch->sku_prefix ?? null, $branchId);
                    }

                    if ($name === '') {
                        throw new Exception("Baris {$rowNumber}: NAMA STOK tidak boleh kosong.");
                    }

                    // Extract Category
                    $category = null;
                    if (isset($headerMap['category']) && !empty($row[$headerMap['category']])) {
                        $category = trim((string)$row[$headerMap['category']]);
                    }

                    $productType = 'physical';
                    if ($category !== null && stripos($category, 'ONGKOS') !== false) {
                        $productType = 'service';
                        $category = 'Jasa/Service';
                    }

                    // Extract Prices
                    $costPrice = isset($headerMap['cost_price']) ? $this->sanitizeNumber($row[$headerMap['cost_price']]) : 0;
                    $netto = isset($headerMap['netto']) ? $this->sanitizeNumber($row[$headerMap['netto']]) : 0;
                    $price = isset($headerMap['price']) ? $this->sanitizeNumber($row[$headerMap['price']]) : 0;

                    // If price is 0, check HET
                    if ($price === 0 && isset($headerMap['het'])) {
                        $price = $this->sanitizeNumber($row[$headerMap['het']]);
                    }

                    // HPP logic: prioritize Netto, fallback to cost_price
                    if ($hppSource !== null && isset($headerMap[$hppSource])) {
                        $hpp = $this->sanitizeNumber($row[$headerMap[$hppSource]]);
                    } elseif ($hppSource !== null && isset($row[$hppSource])) {
                        $hpp = $this->sanitizeNumber($row[$hppSource]);
                    } else {
                        $hpp = $netto > 0 ? $netto : $costPrice;
                    }

                    // Discount value
                    $discountValue = isset($headerMap['discount']) ? $this->sanitizeNumber($row[$headerMap['discount']]) : 0;

                    // Extract Quantity and Unit from JLH.STOK (e.g. "5 Pcs" or numeric 5)
                    $rawStock = isset($headerMap['stock']) ? (string)$row[$headerMap['stock']] : '0';
                    $parsedStock = $this->parseStockAndUnit($rawStock, $defaultUnit);
                    $qty = $parsedStock['quantity'];
                    $unitId = $parsedStock['unit_id'];

                    // Check if variant already exists with this SKU
                    $variant = ProductVariant::where('sku', $sku)->first();

                    if ($variant) {
                        // UPDATE EXISTING PRODUCT & VARIANT
                        $product = $variant->product;
                        $product->update([
                            'name' => $name,
                            'type' => $productType,
                            'category' => $category ?: $product->category,
                            'unit_id' => $unitId ?: $product->unit_id,
                        ]);

                        // Barcode: preserve existing if valid, otherwise generate fresh EAN-13
                        $barcode = $variant->barcode;
                        if (empty($barcode) || $barcode === $sku) {
                            $barcode = ProductHelper::generateUniqueBarcode();
                        }

                        $variant->update([
                            'price' => $price > 0 ? $price : $variant->price,
                            'cost_price' => $costPrice > 0 ? $costPrice : $variant->cost_price,
                            'hpp' => $hpp > 0 ? $hpp : $variant->hpp,
                            'discount_value' => $discountValue,
                            'barcode' => $barcode,
                        ]);

                        // Stock Management
                        $branchStock = ProductBranchStock::firstOrCreate([
                            'product_variant_id' => $variant->id,
                            'branch_id' => $branchId,
                        ], [
                            'stock' => 0,
                            'hpp' => $hpp,
                        ]);

                        $prevStock = $branchStock->stock;
                        $branchStock->update([
                            'stock' => $qty,
                            'hpp' => $hpp > 0 ? $hpp : $branchStock->hpp,
                        ]);

                        $diff = $qty - $prevStock;
                        if ($diff !== 0) {
                            StockMovement::create([
                                'product_variant_id' => $variant->id,
                                'branch_id' => $branchId,
                                'type' => $diff > 0 ? 'in' : 'out',
                                'quantity' => abs($diff),
                                'original_quantity' => $prevStock,
                                'unit_id' => $unitId,
                                'unit_cost' => $hpp,
                                'hpp' => $hpp,
                                'reference_type' => 'InitialStock',
                                'reference_id' => $variant->id,
                            ]);

                            if ($diff > 0) {
                                $totalStockValue += ($diff * $hpp);
                            }
                        }

                        $updatedCount++;
                    } else {
                        // CREATE NEW PRODUCT & VARIANT
                        $product = Product::create([
                            'name' => $name,
                            'type' => $productType,
                            'category' => $category,
                            'unit_id' => $unitId,
                            'inventory_account_id' => $inventoryAccId,
                            'sales_account_id' => $salesAccId,
                            'cogs_account_id' => $cogsAccId,
                            'is_active' => true,
                            'minimum_stock' => 0,
                        ]);

                        // Generate unique EAN-13 barcode (never equal to SKU)
                        $barcode = ProductHelper::generateUniqueBarcode();

                        $variant = ProductVariant::create([
                            'product_id' => $product->id,
                            'sku' => $sku,
                            'barcode' => $barcode,
                            'name' => null,
                            'price' => $price,
                            'cost_price' => $costPrice,
                            'hpp' => $hpp,
                            'discount_value' => $discountValue,
                            'discount_type' => 'percent',
                            'tax_value' => 0,
                            'tax_type' => 'percent',
                            'is_active' => true,
                        ]);

                        // Stock entry
                        ProductBranchStock::create([
                            'product_variant_id' => $variant->id,
                            'branch_id' => $branchId,
                            'stock' => $qty,
                            'hpp' => $hpp,
                        ]);

                        if ($qty > 0) {
                            StockMovement::create([
                                'product_variant_id' => $variant->id,
                                'branch_id' => $branchId,
                                'type' => 'in',
                                'quantity' => $qty,
                                'original_quantity' => 0,
                                'unit_id' => $unitId,
                                'unit_cost' => $hpp,
                                'hpp' => $hpp,
                                'reference_type' => 'InitialStock',
                                'reference_id' => $variant->id,
                            ]);

                            $totalStockValue += ($qty * $hpp);
                        }

                        $importedCount++;
                    }
                } catch (Exception $e) {
                    $failedCount++;
                    $errors[] = "Baris {$rowNumber}: " . $e->getMessage();
                }
            }
        });

        $journal = null;
        if ($totalStockValue > 0) {
            $journal = $this->recordInitialStockJournal($totalStockValue, $branchId, null, $userId, $importedCount + $updatedCount);
        }

        return [
            'imported'         => $importedCount,
            'updated'          => $updatedCount,
            'failed'           => $failedCount,
            'errors'           => $errors,
            'total_value'      => $totalStockValue,
            'journal_entry_id' => $journal?->id,
            'journal_entry_no' => $journal?->entry_no,
        ];
    }

    /**
     * Inspect a row to determine if it is the header row, and return the column letters mapping.
     *
     * @param  array<string, mixed>  $row
     * @return array<string, string>|null
     */
    protected function parseHeaderRow(array $row): ?array
    {
        $map = [];

        foreach ($row as $colLetter => $value) {
            if ($value === null) {
                continue;
            }

            // Normalize header string (remove special characters and spaces, lowercase)
            $normalized = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', (string)$value));

            if (in_array($normalized, ['kodebarang', 'kode', 'sku', 'kodeitem', 'kodebrg'])) {
                $map['sku'] = $colLetter;
            } elseif (in_array($normalized, ['namastok', 'nama', 'namabarang', 'namaproduk', 'productname'])) {
                $map['name'] = $colLetter;
            } elseif (in_array($normalized, ['kategoribarang', 'kategori', 'category', 'kelompok'])) {
                $map['category'] = $colLetter;
            } elseif (in_array($normalized, ['hargabeli', 'beli', 'costprice', 'hargabelibruto'])) {
                $map['cost_price'] = $colLetter;
            } elseif (in_array($normalized, ['netto', 'hpp', 'harganetto', 'modal'])) {
                $map['netto'] = $colLetter;
            } elseif (in_array($normalized, ['disc', 'diskon', 'discount'])) {
                $map['discount'] = $colLetter;
            } elseif (in_array($normalized, ['het'])) {
                $map['het'] = $colLetter;
            } elseif (in_array($normalized, ['hargajual', 'jual', 'price', 'sellingprice', 'hargajualcash', 'hargacash'])) {
                $map['price'] = $colLetter;
            } elseif (in_array($normalized, ['jlhstok', 'stok', 'jumlahstok', 'qty', 'stock'])) {
                $map['stock'] = $colLetter;
            }
        }

        // Must at least identify SKU and Name to be considered a valid header row
        if (isset($map['sku']) && isset($map['name'])) {
            return $map;
        }

        return null;
    }

    /**
     * Sanitize numeric string values from Excel/CSV (e.g. "383,000" or "Rp 383.000").
     */
    protected function sanitizeNumber(mixed $value): int
    {
        if (is_numeric($value)) {
            return (int) round((float) $value);
        }

        $cleaned = preg_replace('/[^0-9]/', '', (string)$value);

        return $cleaned !== '' ? (int) $cleaned : 0;
    }

    /**
     * Parse raw stock string (e.g. "5 Pcs" or "24") into numeric quantity and resolved unit ID.
     *
     * @return array{quantity: int, unit_id: int}
     */
    protected function parseStockAndUnit(string $rawStock, Unit $defaultUnit): array
    {
        $raw = trim($rawStock);

        // Extract integer quantity
        preg_match('/(\d+)/', $raw, $matches);
        $quantity = isset($matches[1]) ? (int)$matches[1] : 0;

        // Extract unit string (remove numbers, commas, dots)
        $unitText = trim(preg_replace('/[0-9\.,]/', '', $raw));

        $unitId = $defaultUnit->id;

        if ($unitText !== '') {
            $matchedUnit = Unit::whereRaw('LOWER(code) = ?', [strtolower($unitText)])
                ->orWhereRaw('LOWER(name) = ?', [strtolower($unitText)])
                ->first();

            if ($matchedUnit) {
                $unitId = $matchedUnit->id;
            } else {
                // Create unit if not recognized yet
                $newUnit = Unit::create([
                    'name' => ucfirst(strtolower($unitText)),
                    'code' => strtolower($unitText),
                    'is_active' => true,
                ]);
                $unitId = $newUnit->id;
            }
        }

        return [
            'quantity' => $quantity,
            'unit_id' => $unitId,
        ];
    }
}
