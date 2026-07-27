<?php

namespace App\Filament\Pages\Reports;

use App\Actions\Inventory\GenerateStockMovementReport;
use App\Models\Branch;
use App\Models\Product;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class StockMovementReport extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowsRightLeft;

    protected static \UnitEnum|string|null $navigationGroup = 'Inventori';

    protected static ?string $navigationLabel = 'Laporan Mutasi Barang';

    protected static ?string $title = 'Laporan Mutasi Barang & Kartu Stok';

    protected string $view = 'filament.pages.reports.stock-movement-report';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'from_date'   => now()->startOfMonth()->format('Y-m-d'),
            'to_date'     => now()->format('Y-m-d'),
            'branch_id'   => null,
            'category'    => null,
            'type_filter' => 'all',
            'mode'        => 'log',
            'search'      => null,
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
                Section::make('Parameter & Filter Laporan Mutasi Barang')
                    ->compact()
                    ->schema([
                        Grid::make([
                            'default' => 1,
                            'sm'      => 4,
                        ])->schema([
                            DatePicker::make('from_date')
                                ->label('Dari Tanggal')
                                ->default(now()->startOfMonth()->format('Y-m-d'))
                                ->required()
                                ->live(),

                            DatePicker::make('to_date')
                                ->label('Sampai Tanggal')
                                ->default(now()->format('Y-m-d'))
                                ->required()
                                ->live(),

                            Select::make('branch_id')
                                ->label('Filter Cabang')
                                ->options(Branch::orderBy('name')->pluck('name', 'id'))
                                ->placeholder('Semua Cabang (Konsolidasi)')
                                ->searchable()
                                ->live(),

                            Select::make('category')
                                ->label('Kategori Produk')
                                ->options($categories)
                                ->placeholder('Semua Kategori')
                                ->searchable()
                                ->live(),

                            Select::make('type_filter')
                                ->label('Jenis Mutasi')
                                ->options([
                                    'all'      => 'Semua Jenis Mutasi',
                                    'in'       => 'Barang Masuk (Inflow +)',
                                    'out'      => 'Barang Keluar (Outflow -)',
                                    'mutation' => 'Transfer Antar Cabang',
                                ])
                                ->default('all')
                                ->live(),

                            Select::make('mode')
                                ->label('Mode Tampilan Data')
                                ->options([
                                    'log'     => 'Kartu Stok Berjalan (Stock Movement Log)',
                                    'summary' => 'Rekapitulasi Mutasi Barang per Produk',
                                ])
                                ->default('log')
                                ->live(),

                            TextInput::make('search')
                                ->label('Pencarian')
                                ->placeholder('Cari SKU / Nama / Ref Transaksi...')
                                ->live(debounce: 500),
                        ]),
                    ]),
            ]);
    }

    public function getReportDataProperty(): array
    {
        $state = $this->form->getRawState();

        $fromDate   = $state['from_date'] ?? now()->startOfMonth()->format('Y-m-d');
        $toDate     = $state['to_date'] ?? now()->format('Y-m-d');
        $branchId   = !empty($state['branch_id']) ? (int) $state['branch_id'] : null;
        $category   = $state['category'] ?? null;
        $typeFilter = $state['type_filter'] ?? 'all';
        $mode       = $state['mode'] ?? 'log';
        $search     = $state['search'] ?? null;

        return (new GenerateStockMovementReport())->execute(
            $fromDate,
            $toDate,
            $branchId,
            $category,
            $typeFilter,
            $mode,
            $search
        );
    }

    public function printPdf()
    {
        $data = $this->report_data;

        $pdf = Pdf::loadView('reports.stock-movement-report-pdf', [
            'data' => $data,
        ])->setPaper('a4', 'landscape');

        return response()->streamDownload(
            fn () => print($pdf->output()),
            'Laporan-Mutasi-Barang-' . ($data['from_date'] ?? '') . '-s.d.-' . ($data['to_date'] ?? '') . '.pdf'
        );
    }

    public function exportExcel()
    {
        $data = $this->report_data;
        $filename = 'Laporan-Mutasi-Barang-' . ($data['from_date'] ?? '') . '-s.d.-' . ($data['to_date'] ?? '') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($data) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF");

            fputcsv($file, ['DIEGO MUSIC STORE - LAPORAN MUTASI BARANG & KARTU STOK']);
            fputcsv($file, ['Periode:', $data['from_date'] . ' s.d. ' . $data['to_date']]);
            fputcsv($file, ['Cabang:', $data['branch_name']]);
            fputcsv($file, ['Kategori:', $data['category']]);
            fputcsv($file, []);

            if ($data['mode'] === 'summary') {
                fputcsv($file, ['SKU', 'Nama Produk & Variasi', 'Kategori', 'Satuan', 'Qty Masuk (+)', 'Qty Keluar (-)', 'Net Mutasi Qty', 'Harga Beli/HPP (Rp)', 'Total Valuasi Mutasi (Rp)']);
                foreach ($data['summary_rows'] as $row) {
                    fputcsv($file, [
                        $row['sku'],
                        $row['full_name'],
                        $row['category'],
                        $row['unit'],
                        $row['in_qty'],
                        $row['out_qty'],
                        $row['net_qty'],
                        $row['hpp'],
                        $row['total_value'],
                    ]);
                }
            } else {
                fputcsv($file, ['Tanggal & Waktu', 'No. Referensi / Transaksi', 'SKU', 'Nama Produk & Variasi', 'Cabang', 'Tipe', 'Qty Mutasi', 'Satuan', 'Harga Beli/HPP (Rp)', 'Total Valuasi (Rp)']);
                foreach ($data['rows'] as $row) {
                    fputcsv($file, [
                        $row['date'],
                        $row['ref_label'],
                        $row['sku'],
                        $row['full_name'],
                        $row['branch_name'],
                        $row['type'],
                        $row['quantity'],
                        $row['unit'],
                        $row['hpp'],
                        $row['total_value'],
                    ]);
                }
            }

            fputcsv($file, []);
            fputcsv($file, ['RINGKASAN MUTASI BARANG']);
            fputcsv($file, ['Total Transaksi Mutasi:', $data['total_transactions']]);
            fputcsv($file, ['Total Qty Barang Masuk (+):', $data['total_in_qty']]);
            fputcsv($file, ['Total Qty Barang Keluar (-):', $data['total_out_qty']]);
            fputcsv($file, ['Total Net Mutasi Qty:', $data['total_net_qty']]);
            fputcsv($file, ['Grand Total Valuasi Mutasi (Rp):', $data['grand_total_valuation']]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
