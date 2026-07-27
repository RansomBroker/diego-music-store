<?php

namespace App\Filament\Pages\Reports;

use App\Actions\Inventory\GenerateStockOpnameReport;
use App\Models\Branch;
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

class StockOpnameReport extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentCheck;

    protected static \UnitEnum|string|null $navigationGroup = 'Inventori';

    protected static ?string $navigationLabel = 'Laporan Stok Opname';

    protected static ?string $title = 'Laporan & Audit Stok Opname';

    protected string $view = 'filament.pages.reports.stock-opname-report';

    public ?array $data = [];
    public ?array $selectedOpnameDetail = null;

    public function mount(): void
    {
        $this->form->fill([
            'from_date'       => now()->startOfMonth()->format('Y-m-d'),
            'to_date'         => now()->format('Y-m-d'),
            'branch_id'       => null,
            'status_filter'   => 'all',
            'variance_filter' => 'all',
            'mode'            => 'summary',
            'search'          => null,
        ]);
    }

    public function openOpnameDetailModal(int $id): void
    {
        $opname = \App\Models\StockOpname::with(['branch', 'items.productVariant.product.unit'])->find($id);
        if (!$opname) {
            return;
        }

        $items = [];
        foreach ($opname->items as $item) {
            $variant = $item->productVariant;
            $product = $variant?->product;
            $diff    = (int) $item->difference;
            $cost    = (float) ($item->cost_price ?: ($variant?->hpp ?: $variant?->cost_price ?? 0));

            if ($diff === 0) {
                $itemStatusLabel = 'COCOK';
                $itemBadgeColor  = 'success';
            } elseif ($diff > 0) {
                $itemStatusLabel = 'SELISIH LEBIH (+' . $diff . ')';
                $itemBadgeColor  = 'warning';
            } else {
                $itemStatusLabel = 'SELISIH KURANG (' . $diff . ')';
                $itemBadgeColor  = 'danger';
            }

            $items[] = [
                'sku'               => $variant?->sku ?: '-',
                'full_name'         => ($product?->name ?? 'Produk') . ($variant?->name && $variant->name !== 'Standard' ? " ({$variant->name})" : ''),
                'unit'              => $product?->unit?->name ?? 'Pcs',
                'system_qty'        => (int) $item->system_qty,
                'physical_qty'      => (int) $item->physical_qty,
                'difference'        => $diff,
                'item_status_label' => $itemStatusLabel,
                'item_badge_color'  => $itemBadgeColor,
                'cost_price'        => $cost,
                'adjustment_value'  => $diff * $cost,
            ];
        }

        $this->selectedOpnameDetail = [
            'opname_number'      => $opname->opname_number,
            'opname_date'        => $opname->opname_date->format('d/m/Y'),
            'branch_name'        => $opname->branch?->name ?? 'Cabang Utama',
            'status'             => strtoupper($opname->status),
            'status_badge_color' => $opname->status === 'completed' ? 'success' : 'gray',
            'notes'              => $opname->notes ?: '-',
            'items'              => $items,
        ];

        $this->dispatch('open-modal', id: 'opname-detail-modal');
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
        return $schema
            ->statePath('data')
            ->components([
                Section::make('Parameter & Filter Audit Stok Opname')
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

                            Select::make('status_filter')
                                ->label('Status Opname')
                                ->options([
                                    'all'       => 'Semua Status (Draft & Selesai)',
                                    'completed' => 'Selesai / Posted (Completed)',
                                    'draft'     => 'Draft',
                                ])
                                ->default('all')
                                ->live(),

                            Select::make('variance_filter')
                                ->label('Filter Selisih Stok')
                                ->options([
                                    'all'          => 'Semua Item Barang',
                                    'has_variance' => 'Ada Selisih (Physical != System)',
                                    'matched'      => 'Sesuai / Fit (Physical == System)',
                                    'positive'     => 'Selisih Lebih (+ Physical > System)',
                                    'negative'     => 'Selisih Kurang (- Physical < System)',
                                ])
                                ->default('all')
                                ->live(),

                            Select::make('mode')
                                ->label('Mode Tampilan Data')
                                ->options([
                                    'summary' => 'Ringkasan Sesi Opname',
                                    'detail'  => 'Rincian Detail Barang Di-opname',
                                ])
                                ->default('summary')
                                ->live(),

                            TextInput::make('search')
                                ->label('Pencarian')
                                ->placeholder('Cari No. Opname / Catatan...')
                                ->live(debounce: 500),
                        ]),
                    ]),
            ]);
    }

    public function getReportDataProperty(): array
    {
        $state = $this->form->getRawState();

        $fromDate       = $state['from_date'] ?? now()->startOfMonth()->format('Y-m-d');
        $toDate         = $state['to_date'] ?? now()->format('Y-m-d');
        $branchId       = !empty($state['branch_id']) ? (int) $state['branch_id'] : null;
        $statusFilter   = $state['status_filter'] ?? 'all';
        $varianceFilter = $state['variance_filter'] ?? 'all';
        $mode           = $state['mode'] ?? 'summary';
        $search         = $state['search'] ?? null;

        return (new GenerateStockOpnameReport())->execute(
            $fromDate,
            $toDate,
            $branchId,
            $statusFilter,
            $varianceFilter,
            $mode,
            $search
        );
    }

    public function printPdf()
    {
        $data = $this->report_data;

        $pdf = Pdf::loadView('reports.stock-opname-report-pdf', [
            'data' => $data,
        ])->setPaper('a4', 'landscape');

        return response()->streamDownload(
            fn () => print($pdf->output()),
            'Laporan-Stok-Opname-' . ($data['from_date'] ?? '') . '-s.d.-' . ($data['to_date'] ?? '') . '.pdf'
        );
    }

    public function exportExcel()
    {
        $data = $this->report_data;
        $filename = 'Laporan-Stok-Opname-' . ($data['from_date'] ?? '') . '-s.d.-' . ($data['to_date'] ?? '') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($data) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF");

            fputcsv($file, ['DIEGO MUSIC STORE - LAPORAN & AUDIT STOK OPNAME']);
            fputcsv($file, ['Periode:', $data['from_date'] . ' s.d. ' . $data['to_date']]);
            fputcsv($file, ['Cabang:', $data['branch_name']]);
            fputcsv($file, ['Status Opname:', $data['status_filter']]);
            fputcsv($file, []);

            if ($data['mode'] === 'detail') {
                fputcsv($file, ['No. Opname', 'Tanggal', 'Cabang', 'SKU', 'Nama Produk & Variasi', 'Satuan', 'Qty Sistem', 'Qty Fisik', 'Selisih (Qty)', 'Status Audit', 'Harga Beli/HPP (Rp)', 'Nilai Selisih Adjustment (Rp)']);
                foreach ($data['opnames'] as $op) {
                    foreach ($op['items'] as $item) {
                        fputcsv($file, [
                            $op['opname_number'],
                            $op['opname_date'],
                            $op['branch_name'],
                            $item['sku'],
                            $item['full_name'],
                            $item['unit'],
                            $item['system_qty'],
                            $item['physical_qty'],
                            $item['difference'],
                            $item['item_status_label'],
                            $item['cost_price'],
                            $item['adjustment_value'],
                        ]);
                    }
                }
            } else {
                fputcsv($file, ['No. Opname', 'Tanggal Opname', 'Cabang', 'Status', 'Jumlah Item', 'Total Qty Sistem', 'Total Qty Fisik', 'Total Selisih Qty', 'Total Nilai Adjustments (Rp)', 'Catatan']);
                foreach ($data['opnames'] as $op) {
                    fputcsv($file, [
                        $op['opname_number'],
                        $op['opname_date'],
                        $op['branch_name'],
                        $op['status'],
                        $op['items_count'],
                        $op['session_system_qty'],
                        $op['session_physical_qty'],
                        $op['session_diff_qty'],
                        $op['session_adjustment_value'],
                        $op['notes'],
                    ]);
                }
            }

            fputcsv($file, []);
            fputcsv($file, ['RINGKASAN AUDIT STOK OPNAME']);
            fputcsv($file, ['Total Sesi Opname:', $data['total_opname_sessions']]);
            fputcsv($file, ['Total Item Barang Di-audit:', $data['total_items_audited']]);
            fputcsv($file, ['Total Net Selisih Qty:', $data['total_net_variance_qty']]);
            fputcsv($file, ['Grand Total Nilai Stock Adjustment (Rp):', $data['grand_total_adjustment_value']]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
