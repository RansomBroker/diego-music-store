<?php

namespace App\Filament\Pages\Reports;

use App\Actions\Accounting\GenerateVendorLedgerReport;
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

class VendorLedger extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static \UnitEnum|string|null $navigationGroup = 'Laporan Keuangan';

    protected static ?string $navigationLabel = 'Buku Vendor (Kartu Hutang)';

    protected static ?string $title = 'Laporan Buku Vendor (Kartu Hutang Supplier)';

    protected string $view = 'filament.pages.reports.vendor-ledger';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'from_date'   => now()->startOfMonth()->format('Y-m-d'),
            'to_date'     => now()->format('Y-m-d'),
            'branch_id'   => null,
            'supplier_id' => null,
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
        return $schema
            ->statePath('data')
            ->components([
                Section::make('Parameter & Filter Buku Vendor (Kartu Hutang)')
                    ->compact()
                    ->schema([
                        Grid::make([
                            'default' => 1,
                            'sm'      => 5,
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

                            Select::make('supplier_id')
                                ->label('Pilih Supplier / Vendor')
                                ->options(
                                    Supplier::orderBy('name')
                                        ->pluck('name', 'id')
                                )
                                ->placeholder('Semua Supplier (Kartu Hutang Lengkap)')
                                ->searchable()
                                ->live(),

                            TextInput::make('search')
                                ->label('Pencarian')
                                ->placeholder('Cari No. Faktur / Bukti / Ref...')
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
        $supplierId = !empty($state['supplier_id']) ? (int) $state['supplier_id'] : null;
        $search     = $state['search'] ?? null;

        return (new GenerateVendorLedgerReport())->execute($fromDate, $toDate, $supplierId, $branchId, $search);
    }

    public function printPdf()
    {
        $data = $this->report_data;

        $pdf = Pdf::loadView('reports.vendor-ledger-pdf', [
            'data' => $data,
        ])->setPaper('a4', 'portrait');

        return response()->streamDownload(
            fn () => print($pdf->output()),
            'Laporan-Buku-Vendor-' . ($data['from_date'] ?? '') . '-s.d.-' . ($data['to_date'] ?? '') . '.pdf'
        );
    }

    public function exportExcel()
    {
        $data = $this->report_data;
        $filename = 'Laporan-Buku-Vendor-' . ($data['from_date'] ?? '') . '-s.d.-' . ($data['to_date'] ?? '') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($data) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF");

            fputcsv($file, ['DIEGO MUSIC STORE - LAPORAN BUKU VENDOR (KARTU HUTANG SUPPLIER)']);
            fputcsv($file, ['Periode:', $data['from_date'] . ' s.d. ' . $data['to_date']]);
            fputcsv($file, ['Cabang:', $data['branch_name']]);
            fputcsv($file, ['Supplier:', $data['selected_supplier'] ? $data['selected_supplier']->name : 'Semua Supplier']);
            fputcsv($file, []);

            foreach ($data['vendors'] as $vendor) {
                fputcsv($file, ['SUPPLIER:', $vendor['supplier_name'], 'Telepon:', $vendor['supplier_phone']]);
                fputcsv($file, ['SALDO AWAL HUTANG:', '', '', '', '', $vendor['beginning_balance']]);
                fputcsv($file, ['Tanggal', 'Jenis Transaksi', 'No. Bukti / Ref', 'Keterangan', 'Pembelian / Hutang Baru (Rp)', 'Pelunasan / Pembayaran (Rp)', 'Saldo Hutang (Rp)']);

                foreach ($vendor['transactions'] as $tx) {
                    fputcsv($file, [
                        $tx['date'],
                        $tx['type'],
                        $tx['ref_no'],
                        $tx['description'],
                        $tx['addition'],
                        $tx['payment'],
                        $tx['running_balance'],
                    ]);
                }

                fputcsv($file, ['TOTAL MUTASI SUPPLIER', '', '', '', $vendor['total_additions'], $vendor['total_payments'], '']);
                fputcsv($file, ['SALDO AKHIR HUTANG SUPPLIER', '', '', '', '', '', $vendor['ending_balance']]);
                fputcsv($file, []);
            }

            fputcsv($file, ['REKAPITULASI GRAND TOTAL']);
            fputcsv($file, ['Total Saldo Awal Hutang:', $data['grand_total_beginning']]);
            fputcsv($file, ['Total Pembelian Baru:', $data['grand_total_additions']]);
            fputcsv($file, ['Total Pelunasan Hutang:', $data['grand_total_payments']]);
            fputcsv($file, ['Total Saldo Akhir Hutang:', $data['grand_total_ending']]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
