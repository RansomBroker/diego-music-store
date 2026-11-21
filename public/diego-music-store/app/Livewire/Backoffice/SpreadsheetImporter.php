<?php

namespace App\Livewire\Backoffice;

use App\Actions\Customer\ImportCustomers;
use App\Actions\Supplier\ImportSuppliers;
use App\Helpers\SpreadsheetImportHelper;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\File;
use Livewire\Component;
use Livewire\WithFileUploads;

class SpreadsheetImporter extends Component
{
    use WithFileUploads;

    public string $type = 'customer'; // 'customer' or 'supplier'

    /** @var mixed */
    public $file = null;

    public ?string $filePath = null;
    public ?string $originalFileName = null;

    /** @var array<int, string> */
    public array $sheets = [];
    public ?string $selectedSheet = null;

    /** @var array<int, string> */
    public array $detectedHeaders = [];
    /** @var array<int, string> */
    public array $rawHeaders = [];
    /** @var array<int, string> */
    public array $requiredHeaders = [];

    /** @var array{is_valid: bool, matched: array<int, string>, missing: array<int, string>, extra: array<int, string>, required: array<int, string>} */
    public array $validation = [
        'is_valid' => false,
        'matched' => [],
        'missing' => [],
        'extra' => [],
        'required' => [],
    ];

    public int $totalRows = 0;
    /** @var array<int, array<string, mixed>> */
    public array $previewRows = [];

    public bool $isImporting = false;
    public bool $importFinished = false;
    public int $processedRows = 0;
    public int $progressPercent = 0;
    public int $importedCount = 0;
    public int $skippedCount = 0;
    /** @var array<int, string> */
    public array $importErrors = [];

    public int $batchSize = 50;
    public int $currentOffset = 0;
    public ?int $branchId = null;
    public ?int $contraAccountId = null;
    public int $totalStockValue = 0;
    public int $totalDebtValue = 0;
    public ?string $lastSupplierName = null;
    public ?string $generatedJournalNumber = null;
    public ?string $hppColumn = null;

    public bool $resetBeforeImport = false;
    public int $headerRowIndex = 1;
    /** @var array<string, string> */
    public array $columnMap = [];
    /** @var array<string, string> */
    public array $rawColumnHeaders = [];
    /** @var array<string, string> */
    public array $matchedSources = [];
    /** @var array<int, string> */
    public array $ignoredHeaders = [];
    /** @var array<string, string> */
    public array $manualMappings = [];
    /** @var array{deleted_products?: int, deleted_variants?: int, deleted_journals?: int} */
    public array $resetSummary = [];

    public function mount(string $type = 'customer'): void
    {
        $this->type = $type;
        $this->requiredHeaders = match ($type) {
            'customer'      => ImportCustomers::REQUIRED_HEADERS,
            'supplier'      => ImportSuppliers::REQUIRED_HEADERS,
            'supplier_debt' => \App\Actions\Supplier\ImportSupplierDebts::REQUIRED_HEADERS,
            'product'       => \App\Actions\Product\ImportProducts::REQUIRED_HEADERS,
            default         => [],
        };

        if ($this->type === 'product' || $this->type === 'supplier_debt') {
            $this->branchId = \App\Models\Branch::where('is_active', true)->first()?->id;
            $modalAcc = \App\Helpers\AccountHelper::findByCode('311101003');
            $this->contraAccountId = $modalAcc?->id ?? \App\Helpers\AccountHelper::findByCode('311201001')?->id;
        }
    }

    public function updatedFile(): void
    {
        $this->validate([
            'file' => 'required|file|max:20480',
        ]);

        $this->originalFileName = $this->file->getClientOriginalName();
        $ext = strtolower($this->file->getClientOriginalExtension());

        if (!in_array($ext, ['csv', 'xlsx', 'xls', 'txt'], true)) {
            $this->addError('file', 'Format berkas harus berupa Excel (.xlsx, .xls) atau CSV (.csv).');
            return;
        }

        // Store to temporary directory
        $tempDir = storage_path('app/temp-imports');
        if (!File::exists($tempDir)) {
            File::makeDirectory($tempDir, 0755, true);
        }

        $tempFileName = uniqid('import_', true) . '.' . $ext;
        $tempPath = $tempDir . '/' . $tempFileName;
        copy($this->file->getRealPath(), $tempPath);
        $this->filePath = $tempPath;

        $this->inspectActiveFile();
    }

    public function selectSheet(string $sheetName): void
    {
        if (!$this->filePath || !File::exists($this->filePath)) {
            return;
        }

        $this->selectedSheet = $sheetName;
        $this->inspectActiveFile($sheetName);
    }

    protected function inspectActiveFile(?string $sheetName = null): void
    {
        if (!$this->filePath || !File::exists($this->filePath)) {
            return;
        }

        $info = SpreadsheetImportHelper::inspectFile($this->filePath, $sheetName, $this->requiredHeaders);

        $this->sheets = $info['sheets'];
        $this->selectedSheet = $info['active_sheet'];
        $this->headerRowIndex = $info['header_row'] ?? 1;
        $this->detectedHeaders = $info['headers'];
        $this->rawHeaders = $info['raw_headers'];
        $this->columnMap = $info['col_map'] ?? [];
        $this->rawColumnHeaders = $info['raw_by_col'] ?? [];
        $this->matchedSources = $info['matched_sources'] ?? [];
        $this->ignoredHeaders = $info['ignored_headers'] ?? [];
        $this->validation = $info['validation'];
        $this->totalRows = $info['total_rows'];
        $this->previewRows = $info['preview_rows'];

        $this->manualMappings = [];
        foreach ($this->requiredHeaders as $reqHeader) {
            $matchedCol = array_search($reqHeader, $this->columnMap, true);
            $this->manualMappings[$reqHeader] = $matchedCol !== false ? (string) $matchedCol : '';
        }

        if ($this->type === 'product') {
            $this->syncDefaultHppColumn();
        }

        $this->resetImportState();
    }

    /**
     * Re-apply column validation and preview rows when the user changes manual column mappings.
     */
    public function updatedManualMappings(): void
    {
        if (!$this->filePath || !File::exists($this->filePath) || empty($this->rawColumnHeaders)) {
            return;
        }

        $newMap = [];
        $usedCols = [];
        $newMatchedSources = [];

        foreach ($this->manualMappings as $targetField => $selectedCol) {
            if ($selectedCol !== '' && isset($this->rawColumnHeaders[$selectedCol]) && !isset($usedCols[$selectedCol])) {
                $newMap[$selectedCol] = $targetField;
                $usedCols[$selectedCol] = true;
                $newMatchedSources[$targetField] = $this->rawColumnHeaders[$selectedCol];
            }
        }

        foreach ($this->rawColumnHeaders as $colLetter => $rawText) {
            if (isset($newMap[$colLetter])) {
                continue;
            }

            $normalized = SpreadsheetImportHelper::normalizeHeader($rawText);
            if ($normalized === 'no') {
                $newMap[$colLetter] = 'no';
            } else {
                // Keep unmapped required names from falsely claiming a required slot after manual unmapping
                $newMap[$colLetter] = in_array($normalized, $this->requiredHeaders, true)
                    ? $normalized . '_unmapped_' . strtolower($colLetter)
                    : $normalized;
            }
        }

        $this->columnMap = $newMap;
        $this->matchedSources = $newMatchedSources;
        $this->detectedHeaders = array_values($newMap);
        $this->validation = SpreadsheetImportHelper::validateHeaders($this->detectedHeaders, $this->requiredHeaders);
        $this->previewRows = SpreadsheetImportHelper::readRows(
            $this->filePath,
            $this->selectedSheet,
            0,
            3,
            $this->headerRowIndex,
            $this->columnMap
        );

        if ($this->type === 'product') {
            $this->syncDefaultHppColumn();
        }
    }

    /**
     * Resolve default HPP source column from currently active headers.
     */
    protected function syncDefaultHppColumn(): void
    {
        if (in_array('netto', $this->detectedHeaders, true)) {
            $this->hppColumn = 'netto';
        } elseif (in_array('harga_beli', $this->detectedHeaders, true) || in_array('cost_price', $this->detectedHeaders, true)) {
            $this->hppColumn = 'harga_beli';
        } else {
            $this->hppColumn = null;
        }
    }

    public function startImport(): void
    {
        if (!$this->validation['is_valid']) {
            Notification::make()
                ->title('Format Kolom Tidak Valid')
                ->body('Pastikan semua kolom sesuai dengan template sebelum memulai import.')
                ->danger()
                ->send();
            return;
        }

        if ($this->totalRows === 0) {
            Notification::make()
                ->title('Tidak Ada Data')
                ->body('Lembar kerja yang dipilih tidak memiliki baris data untuk diimpor.')
                ->warning()
                ->send();
            return;
        }

        $this->resetSummary = [];

        if ($this->type === 'product' && $this->resetBeforeImport) {
            try {
                $this->resetSummary = app(\App\Actions\Product\ResetProductsForImport::class)->execute();
            } catch (\Throwable $e) {
                Notification::make()
                    ->title('Gagal Menghapus Data Produk Lama')
                    ->body($e->getMessage())
                    ->danger()
                    ->send();
                return;
            }
        }

        $this->isImporting = true;
        $this->importFinished = false;
        $this->processedRows = 0;
        $this->progressPercent = 0;
        $this->currentOffset = 0;
        $this->importedCount = 0;
        $this->skippedCount = 0;
        $this->importErrors = [];
    }

    public function processBatch(): void
    {
        if (!$this->isImporting || !$this->filePath || !File::exists($this->filePath)) {
            $this->isImporting = false;
            return;
        }

        $rows = SpreadsheetImportHelper::readRows(
            $this->filePath,
            $this->selectedSheet,
            $this->currentOffset,
            $this->batchSize,
            $this->headerRowIndex,
            $this->columnMap ?: null
        );

        if (empty($rows)) {
            $this->finishImport();
            return;
        }

        // Execute feature action
        if ($this->type === 'customer') {
            $result = app(ImportCustomers::class)->execute($rows);
        } elseif ($this->type === 'supplier') {
            $result = app(ImportSuppliers::class)->execute($rows);
        } elseif ($this->type === 'supplier_debt') {
            $branchId = $this->branchId ?: (\App\Models\Branch::where('is_active', true)->first()?->id ?? 1);
            $result = app(\App\Actions\Supplier\ImportSupplierDebts::class)->execute(
                $rows,
                $branchId,
                auth()->id(),
                $this->lastSupplierName
            );
            $this->totalDebtValue += ($result['total_debt_value'] ?? 0);
            $this->lastSupplierName = $result['last_supplier'] ?? $this->lastSupplierName;
        } elseif ($this->type === 'product') {
            $branchId = $this->branchId ?: (\App\Models\Branch::where('is_active', true)->first()?->id ?? 1);
            $result = app(\App\Actions\Product\ImportProducts::class)->executeRows($rows, $branchId, auth()->id(), $this->hppColumn);
            $this->totalStockValue += ($result['total_value'] ?? 0);
        } else {
            $result = ['imported' => 0, 'skipped' => 0, 'errors' => []];
        }

        $this->importedCount += $result['imported'];
        $this->skippedCount += $result['skipped'];
        $this->importErrors = array_merge($this->importErrors, $result['errors']);

        $this->currentOffset += count($rows);
        $this->processedRows = min($this->totalRows, $this->currentOffset);
        $this->progressPercent = $this->totalRows > 0
            ? (int) min(100, round(($this->processedRows / $this->totalRows) * 100))
            : 100;

        if ($this->processedRows >= $this->totalRows) {
            $this->finishImport();
        }
    }

    protected function finishImport(): void
    {
        $this->isImporting = false;
        $this->importFinished = true;
        $this->progressPercent = 100;

        // Cleanup temporary file
        if ($this->filePath && File::exists($this->filePath)) {
            @unlink($this->filePath);
            $this->filePath = null;
        }

        // Automatic Accounting Journal for Initial Stock
        if ($this->type === 'product' && $this->totalStockValue > 0) {
            $branchId = $this->branchId ?: (\App\Models\Branch::where('is_active', true)->first()?->id ?? 1);
            $journal = app(\App\Actions\Product\ImportProducts::class)->recordInitialStockJournal(
                totalValue: $this->totalStockValue,
                branchId: $branchId,
                contraAccountId: $this->contraAccountId,
                userId: auth()->id(),
                itemCount: $this->importedCount
            );

            if ($journal) {
                $this->generatedJournalNumber = $journal->entry_no;
            }
        }

        // Automatic Accounting Journal for Initial Supplier Debt (Option 1)
        if ($this->type === 'supplier_debt' && $this->totalDebtValue > 0) {
            $branchId = $this->branchId ?: (\App\Models\Branch::where('is_active', true)->first()?->id ?? 1);
            $journal = app(\App\Actions\Supplier\ImportSupplierDebts::class)->recordInitialDebtJournal(
                totalValue: $this->totalDebtValue,
                branchId: $branchId,
                contraAccountId: $this->contraAccountId,
                userId: auth()->id(),
                invoiceCount: $this->importedCount
            );

            if ($journal) {
                $this->generatedJournalNumber = $journal->entry_no;
            }
        }

        $noun = match($this->type) {
            'product'       => 'produk',
            'supplier_debt' => 'faktur hutang',
            default         => 'data',
        };
        $resetPrefix = '';
        if ($this->type === 'product' && ($this->resetSummary['deleted_products'] ?? 0) > 0) {
            $resetPrefix = "{$this->resetSummary['deleted_products']} produk lama dihapus. ";
        }
        $notificationBody = $resetPrefix . "{$this->importedCount} {$noun} berhasil diimpor" . ($this->skippedCount > 0 ? ", {$this->skippedCount} dilewati." : ".");
        if ($this->generatedJournalNumber) {
            $val = $this->type === 'supplier_debt' ? $this->totalDebtValue : $this->totalStockValue;
            $formattedValue = 'Rp ' . number_format($val, 0, ',', '.');
            $journalLabel = $this->type === 'supplier_debt' ? 'saldo awal hutang dagang' : 'saldo awal persediaan';
            $notificationBody .= " Jurnal {$journalLabel} {$this->generatedJournalNumber} ({$formattedValue}) otomatis dibukukan.";
        }

        Notification::make()
            ->title('Proses Import Selesai')
            ->body($notificationBody)
            ->success()
            ->send();
    }

    public function resetAll(): void
    {
        if ($this->filePath && File::exists($this->filePath)) {
            @unlink($this->filePath);
        }

        $this->file = null;
        $this->filePath = null;
        $this->originalFileName = null;
        $this->sheets = [];
        $this->selectedSheet = null;
        $this->headerRowIndex = 1;
        $this->detectedHeaders = [];
        $this->rawHeaders = [];
        $this->columnMap = [];
        $this->rawColumnHeaders = [];
        $this->matchedSources = [];
        $this->ignoredHeaders = [];
        $this->manualMappings = [];
        $this->totalRows = 0;
        $this->previewRows = [];
        $this->totalStockValue = 0;
        $this->totalDebtValue = 0;
        $this->lastSupplierName = null;
        $this->generatedJournalNumber = null;
        $this->resetBeforeImport = false;
        $this->resetSummary = [];
        $this->resetImportState();
    }

    protected function resetImportState(): void
    {
        $this->isImporting = false;
        $this->importFinished = false;
        $this->processedRows = 0;
        $this->progressPercent = 0;
        $this->importedCount = 0;
        $this->skippedCount = 0;
        $this->importErrors = [];
        $this->currentOffset = 0;
        $this->totalStockValue = 0;
        $this->totalDebtValue = 0;
        $this->lastSupplierName = null;
        $this->generatedJournalNumber = null;
        $this->resetSummary = [];
    }

    public function render()
    {
        return view('filament.components.spreadsheet-importer');
    }
}
