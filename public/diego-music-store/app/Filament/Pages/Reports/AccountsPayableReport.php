<?php

namespace App\Filament\Pages\Reports;

use App\Actions\Procurement\GenerateAccountsPayableReport;
use App\Models\Branch;
use App\Models\Supplier;
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

class AccountsPayableReport extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedCreditCard;

    protected static \UnitEnum|string|null $navigationGroup = 'Pembelian';

    protected static ?string $navigationLabel = 'Laporan Hutang';

    protected static ?string $title = 'Laporan Hutang Usaha (Accounts Payable)';

    protected string $view = 'filament.pages.reports.accounts-payable-report';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'as_of_date'     => now()->format('Y-m-d'),
            'branch_id'      => null,
            'supplier_id'    => null,
            'overdue_filter' => 'all',
            'mode'           => 'detail_invoice',
            'search'         => null,
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
        return $schema
            ->statePath('data')
            ->components([
                Section::make('Parameter & Filter Laporan Hutang Usaha')
                    ->compact()
                    ->schema([
                        Grid::make([
                            'default' => 1,
                            'sm'      => 4,
                        ])->schema([
                            DatePicker::make('as_of_date')
                                ->label('Per Tanggal (As of Date)')
                                ->default(now()->format('Y-m-d'))
                                ->required()
                                ->live(),

                            Select::make('branch_id')
                                ->label('Filter Cabang')
                                ->options(Branch::orderBy('name')->pluck('name', 'id'))
                                ->placeholder('Semua Cabang (Konsolidasi)')
                                ->searchable()
                                ->live(),

                            Select::make('supplier_id')
                                ->label('Pilih Supplier / Vendor')
                                ->options(Supplier::orderBy('name')->pluck('name', 'id'))
                                ->placeholder('Semua Supplier')
                                ->searchable()
                                ->live(),

                            Select::make('overdue_filter')
                                ->label('Status Jatuh Tempo')
                                ->options([
                                    'all'     => 'Semua Hutang (Belum & Jatuh Tempo)',
                                    'current' => 'Belum Jatuh Tempo',
                                    'overdue' => 'Sudah Jatuh Tempo',
                                ])
                                ->default('all')
                                ->live(),

                            Select::make('mode')
                                ->label('Mode Tampilan Data')
                                ->options([
                                    'detail_invoice'   => 'Rincian Faktur Hutang Belum Lunas',
                                    'summary_supplier' => 'Rekapitulasi Total Hutang per Supplier',
                                    'aging'            => 'Analisis Umur Hutang (AP Aging Matrix)',
                                ])
                                ->default('detail_invoice')
                                ->live(),

                            TextInput::make('search')
                                ->label('Pencarian')
                                ->placeholder('Cari No. Faktur / Supplier...')
                                ->live(debounce: 500),
                        ]),
                    ]),
            ]);
    }

    public function getReportDataProperty(): array
    {
        $state = $this->form->getRawState();

        $asOfDate      = $state['as_of_date'] ?? now()->format('Y-m-d');
        $branchId      = !empty($state['branch_id']) ? (int) $state['branch_id'] : null;
        $supplierId    = !empty($state['supplier_id']) ? (int) $state['supplier_id'] : null;
        $overdueFilter = $state['overdue_filter'] ?? 'all';
        $mode          = $state['mode'] ?? 'detail_invoice';
        $search        = $state['search'] ?? null;

        return (new GenerateAccountsPayableReport())->execute(
            $asOfDate,
            $branchId,
            $supplierId,
            $overdueFilter,
            $mode,
            $search
        );
    }

    public function printPdf()
    {
        $data = $this->report_data;

        $pdf = Pdf::loadView('reports.accounts-payable-report-pdf', [
            'data' => $data,
        ])->setPaper('a4', 'landscape');

        return response()->streamDownload(
            fn () => print($pdf->output()),
            'Laporan-Hutang-Usaha-Per-' . ($data['as_of_date'] ?? '') . '.pdf'
        );
    }

    public function exportExcel()
    {
        $data = $this->report_data;
        $filename = 'Laporan-Hutang-Usaha-Per-' . ($data['as_of_date'] ?? '') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($data) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF");

            fputcsv($file, ['DIEGO MUSIC STORE - LAPORAN HUTANG USAHA (ACCOUNTS PAYABLE)']);
            fputcsv($file, ['Per Tanggal:', $data['as_of_date']]);
            fputcsv($file, ['Cabang:', $data['branch_name']]);
            fputcsv($file, ['Supplier:', $data['supplier_name']]);
            fputcsv($file, []);

            if ($data['mode'] === 'summary_supplier') {
                fputcsv($file, ['Supplier', 'Telepon', 'Jumlah Faktur', 'Total Pembelian (Rp)', 'Terbayar (Rp)', 'Sisa Hutang (Rp)']);
                foreach ($data['suppliers'] as $sup) {
                    fputcsv($file, [
                        $sup['supplier_name'],
                        $sup['supplier_phone'],
                        $sup['count_invoices'],
                        $sup['grand_total'],
                        $sup['paid_amount'],
                        $sup['unpaid_amount'],
                    ]);
                }
            } elseif ($data['mode'] === 'aging') {
                fputcsv($file, ['Supplier', 'Belum Jatuh Tempo (Rp)', '1 - 30 Hari (Rp)', '31 - 60 Hari (Rp)', '61 - 90 Hari (Rp)', '> 90 Hari (Rp)', 'Total Sisa Hutang (Rp)']);
                foreach ($data['suppliers'] as $sup) {
                    fputcsv($file, [
                        $sup['supplier_name'],
                        $sup['current'],
                        $sup['aging_1_30'],
                        $sup['aging_31_60'],
                        $sup['aging_61_90'],
                        $sup['aging_90_plus'],
                        $sup['unpaid_amount'],
                    ]);
                }
            } else {
                fputcsv($file, ['No. Faktur', 'No. Inv Supplier', 'Tanggal Pembelian', 'Jatuh Tempo', 'Supplier', 'Status Overdue', 'Terlambat (Hari)', 'Grand Total (Rp)', 'Terbayar (Rp)', 'Sisa Hutang (Rp)']);
                foreach ($data['invoices'] as $inv) {
                    fputcsv($file, [
                        $inv['transaction_no'],
                        $inv['invoice_number'],
                        $inv['date'],
                        $inv['due_date'],
                        $inv['supplier_name'],
                        $inv['is_overdue'] ? 'JATUH TEMPO' : 'BELUM JATUH TEMPO',
                        $inv['overdue_days'],
                        $inv['grand_total'],
                        $inv['paid_amount'],
                        $inv['unpaid_amount'],
                    ]);
                }
            }

            fputcsv($file, []);
            fputcsv($file, ['RINGKASAN TOTAL HUTANG USAHA']);
            fputcsv($file, ['Total Faktur Belum Lunas:', $data['total_invoices']]);
            fputcsv($file, ['Total Hutang Belum Jatuh Tempo:', $data['total_current']]);
            fputcsv($file, ['Total Hutang Sudah Jatuh Tempo:', $data['total_overdue']]);
            fputcsv($file, ['Grand Total Sisa Hutang Usaha:', $data['total_unpaid']]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
