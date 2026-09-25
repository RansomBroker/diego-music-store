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

    public function mount(string $type = 'customer'): void
    {
        $this->type = $type;
        $this->requiredHeaders = $type === 'customer'
            ? ImportCustomers::REQUIRED_HEADERS
            : ImportSuppliers::REQUIRED_HEADERS;
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
        $this->detectedHeaders = $info['headers'];
        $this->rawHeaders = $info['raw_headers'];
        $this->validation = $info['validation'];
        $this->totalRows = $info['total_rows'];
        $this->previewRows = $info['preview_rows'];

        $this->resetImportState();
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
            $this->batchSize
        );

        if (empty($rows)) {
            $this->finishImport();
            return;
        }

        // Execute feature action
        if ($this->type === 'customer') {
            $result = app(ImportCustomers::class)->execute($rows);
        } else {
            $result = app(ImportSuppliers::class)->execute($rows);
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

        Notification::make()
            ->title('Proses Import Selesai')
            ->body("{$this->importedCount} data berhasil diimpor" . ($this->skippedCount > 0 ? ", {$this->skippedCount} dilewati." : "."))
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
        $this->detectedHeaders = [];
        $this->rawHeaders = [];
        $this->totalRows = 0;
        $this->previewRows = [];
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
    }

    public function render()
    {
        return view('filament.components.spreadsheet-importer');
    }
}
