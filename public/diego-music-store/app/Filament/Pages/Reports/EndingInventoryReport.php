<?php

namespace App\Filament\Pages\Reports;

use App\Actions\Inventory\GenerateEndingInventoryReport;
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

class EndingInventoryReport extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedArchiveBox;

    protected static \UnitEnum|string|null $navigationGroup = 'Inventori';

    protected static ?string $navigationLabel = 'Laporan Persediaan Akhir';

    protected static ?string $title = 'Laporan Persediaan Akhir (Ending Inventory)';

    protected string $view = 'filament.pages.reports.ending-inventory-report';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'as_of_date' => now()->format('Y-m-d'),
            'branch_id'  => null,
            'category'   => null,
            'mode'       => 'detail_variant',
            'search'     => null,
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
                Section::make('Parameter & Filter Laporan Persediaan Akhir')
                    ->compact()
                    ->schema([
                        Grid::make([
                            'default' => 1,
                            'sm'      => 4,
                        ])->schema([
                            DatePicker::make('as_of_date')
                                ->label('Per Tanggal Cut-Off')
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

                            Select::make('mode')
                                ->label('Mode Tampilan Data')
                                ->options([
                                    'detail_variant'   => 'Rincian Detail Produk & Variasi',
                                    'summary_category' => 'Rekapitulasi per Kategori Produk',
                                ])
                                ->default('detail_variant')
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

        $asOfDate = $state['as_of_date'] ?? now()->format('Y-m-d');
        $branchId = !empty($state['branch_id']) ? (int) $state['branch_id'] : null;
        $category = $state['category'] ?? null;
        $mode     = $state['mode'] ?? 'detail_variant';
        $search   = $state['search'] ?? null;

        return (new GenerateEndingInventoryReport())->execute(
            $asOfDate,
            $branchId,
            $category,
            $mode,
            $search
        );
    }

    public function printPdf()
    {
        $data = $this->report_data;

        $pdf = Pdf::loadView('reports.ending-inventory-report-pdf', [
            'data' => $data,
        ])->setPaper('a4', 'landscape');

        return response()->streamDownload(
            fn () => print($pdf->output()),
            'Laporan-Persediaan-Akhir-Per-' . ($data['as_of_date'] ?? '') . '.pdf'
        );
    }

    public function exportExcel()
    {
        $data = $this->report_data;
        $filename = 'Laporan-Persediaan-Akhir-Per-' . ($data['as_of_date'] ?? '') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($data) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF");

            fputcsv($file, ['DIEGO MUSIC STORE - LAPORAN PERSEDIAAN AKHIR (ENDING INVENTORY)']);
            fputcsv($file, ['Per Tanggal Cut-Off:', $data['as_of_date']]);
            fputcsv($file, ['Cabang:', $data['branch_name']]);
            fputcsv($file, ['Kategori:', $data['category']]);
            fputcsv($file, []);

            if ($data['mode'] === 'summary_category') {
                fputcsv($file, ['Kategori Produk', 'Jumlah SKU', 'Total Qty Persediaan Akhir', 'Total Nilai Aset (HPP) (Rp)']);
                foreach ($data['categories'] as $cat) {
                    fputcsv($file, [
                        $cat['category_name'],
                        $cat['variant_count'],
                        $cat['total_qty'],
                        $cat['total_valuation'],
                    ]);
                }
            } else {
                fputcsv($file, ['SKU', 'Barcode', 'Nama Produk & Variasi', 'Kategori', 'Merk', 'Satuan', 'Qty Persediaan Akhir', 'Harga Beli/HPP (Rp)', 'Total Nilai Aset (Rp)']);
                foreach ($data['rows'] as $row) {
                    fputcsv($file, [
                        $row['sku'],
                        $row['barcode'],
                        $row['full_name'],
                        $row['category'],
                        $row['brand'],
                        $row['unit'],
                        $row['ending_qty'],
                        $row['cost_price'],
                        $row['valuation'],
                    ]);
                }
            }

            fputcsv($file, []);
            fputcsv($file, ['RINGKASAN PERSEDIAAN AKHIR']);
            fputcsv($file, ['Total Produk & Variasi (SKU):', $data['total_variants']]);
            fputcsv($file, ['Total Qty Persediaan Akhir:', $data['total_ending_qty']]);
            fputcsv($file, ['Grand Total Nilai Aset Persediaan (Rp):', $data['grand_total_valuation']]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
