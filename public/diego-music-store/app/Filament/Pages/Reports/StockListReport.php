<?php

namespace App\Filament\Pages\Reports;

use App\Actions\Inventory\GenerateStockListReport;
use App\Models\Branch;
use App\Models\Product;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class StockListReport extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static \UnitEnum|string|null $navigationGroup = 'Inventori';

    protected static ?string $navigationLabel = 'Laporan Daftar Stok';

    protected static ?string $title = 'Laporan Daftar Stok & Nilai Persediaan';

    protected string $view = 'filament.pages.reports.stock-list-report';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'branch_id'    => null,
            'category'     => null,
            'stock_status' => 'all',
            'search'       => null,
        ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('printPdf')
                ->label('Cetak / PDF')
                ->icon(Heroicon::OutlinedPrinter)
                ->color('success')
                ->extraAttributes([
                    'class' => '!text-white dark:!text-white',
                ])
                ->action('printPdf'),

            Action::make('exportExcel')
                ->label('Ekspor Excel')
                ->icon(Heroicon::OutlinedArrowDownTray)
                ->color('gray')
                ->action('exportExcel'),
        ];
    }

    public function form(Schema $schema): Schema
    {
        $categories = Product::whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->pluck('category', 'category')
            ->toArray();

        return $schema
            ->statePath('data')
            ->components([
                Section::make('Parameter & Filter Laporan Daftar Stok')
                    ->compact()
                    ->schema([
                        Grid::make([
                            'default' => 1,
                            'sm'      => 4,
                        ])->schema([
                            Select::make('branch_id')
                                ->label('Filter Cabang')
                                ->options(Branch::orderBy('name')->pluck('name', 'id'))
                                ->placeholder('Semua Cabang (Total Stok)')
                                ->searchable()
                                ->live(),

                            Select::make('category')
                                ->label('Kategori Produk')
                                ->options($categories)
                                ->placeholder('Semua Kategori')
                                ->searchable()
                                ->live(),

                            Select::make('stock_status')
                                ->label('Status Ketersediaan Stok')
                                ->options([
                                    'all'          => 'Semua Status Stok',
                                    'available'    => 'Stok Tersedia / Aman',
                                    'low'          => 'Stok Rendah (Peringatan Batas Min)',
                                    'out_of_stock' => 'Stok Habis (Kosong)',
                                ])
                                ->default('all')
                                ->live(),

                            TextInput::make('search')
                                ->label('Pencarian')
                                ->placeholder('Cari SKU / Barcode / Nama / Merk...')
                                ->live(debounce: 500),
                        ]),
                    ]),
            ]);
    }

    public function getReportDataProperty(): array
    {
        $state = $this->form->getRawState();

        $branchId    = !empty($state['branch_id']) ? (int) $state['branch_id'] : null;
        $category    = $state['category'] ?? null;
        $stockStatus = $state['stock_status'] ?? 'all';
        $search      = $state['search'] ?? null;

        return (new GenerateStockListReport())->execute(
            $branchId,
            $category,
            $stockStatus,
            $search
        );
    }

    public function printPdf()
    {
        $data = $this->report_data;

        $pdf = Pdf::loadView('reports.stock-list-report-pdf', [
            'data' => $data,
        ])->setPaper('a4', 'landscape');

        return response()->streamDownload(
            fn () => print($pdf->output()),
            'Laporan-Daftar-Stok-' . now()->format('Ymd-His') . '.pdf'
        );
    }

    public function exportExcel()
    {
        $data = $this->report_data;
        $filename = 'Laporan-Daftar-Stok-' . now()->format('Ymd-His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($data) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF");

            fputcsv($file, ['DIEGO MUSIC STORE - LAPORAN DAFTAR STOK & NILAI PERSEDIAAN']);
            fputcsv($file, ['Cabang:', $data['branch_name']]);
            fputcsv($file, ['Kategori:', $data['category']]);
            fputcsv($file, ['Tanggal Dicetak:', now()->format('d/m/Y H:i')]);
            fputcsv($file, []);

            fputcsv($file, ['SKU', 'Barcode', 'Nama Produk & Variasi', 'Kategori', 'Merk', 'Stok Fisik', 'Batas Min', 'Satuan', 'Status Stok', 'Diskon', 'PPN', 'Harga Beli/HPP (Rp)', 'Harga Jual (Rp)', 'Total Nilai Aset (Rp)', 'Potensi Nilai Jual (Rp)']);

            foreach ($data['rows'] as $row) {
                fputcsv($file, [
                    $row['sku'],
                    $row['barcode'],
                    $row['full_name'],
                    $row['category'],
                    $row['brand'],
                    $row['stock'],
                    $row['min_stock'],
                    $row['unit'],
                    $row['status_label'],
                    $row['discount'],
                    $row['tax'],
                    $row['cost_price'],
                    $row['retail_price'],
                    $row['valuation'],
                    $row['retail_value'],
                ]);
            }

            fputcsv($file, []);
            fputcsv($file, ['RINGKASAN PERSEDIAAN BARANG']);
            fputcsv($file, ['Total Produk & Variasi:', $data['total_variants']]);
            fputcsv($file, ['Total Qty Stok Fisik:', $data['total_physical_qty']]);
            fputcsv($file, ['Jumlah Produk Stok Rendah:', $data['total_low_stock_count']]);
            fputcsv($file, ['Jumlah Produk Stok Habis:', $data['total_out_of_stock_count']]);
            fputcsv($file, ['Grand Total Nilai Aset Persediaan (Rp):', $data['grand_total_valuation']]);
            fputcsv($file, ['Grand Total Potensi Nilai Jual (Rp):', $data['grand_total_retail_value']]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
