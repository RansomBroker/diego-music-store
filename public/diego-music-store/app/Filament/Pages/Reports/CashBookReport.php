<?php

namespace App\Filament\Pages\Reports;

use App\Actions\Accounting\GenerateCashBookReport;
use App\Models\Account;
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

class CashBookReport extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static \UnitEnum|string|null $navigationGroup = 'Laporan Keuangan';

    protected static ?string $navigationLabel = 'Laporan Kas & Bank';

    protected static ?string $title = 'Laporan Kas & Bank (Cash Book)';

    protected string $view = 'filament.pages.reports.cash-book-report';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'from_date'   => now()->startOfMonth()->format('Y-m-d'),
            'to_date'     => now()->format('Y-m-d'),
            'branch_id'   => null,
            'account_id'  => null,
            'type_filter' => 'all',
            'mode'        => 'running_balance',
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
                Section::make('Parameter & Filter Laporan Kas & Bank')
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

                            Select::make('account_id')
                                ->label('Pilih Akun Kas & Bank')
                                ->options(
                                    Account::where('is_active', true)
                                        ->where('is_header', false)
                                        ->where(function ($q) {
                                            $q->where('classification', 'asset')
                                              ->where('code', 'LIKE', '1-1%');
                                        })
                                        ->orderBy('code')
                                        ->get()
                                        ->mapWithKeys(fn ($acc) => [$acc->id => "{$acc->code} - {$acc->name}"])
                                )
                                ->placeholder('Semua Akun Kas & Bank')
                                ->searchable()
                                ->live(),

                            Select::make('type_filter')
                                ->label('Jenis Transaksi')
                                ->options([
                                    'all'     => 'Semua (Kas Masuk & Keluar)',
                                    'inflow'  => 'Kas Masuk (Penerimaan)',
                                    'outflow' => 'Kas Keluar (Pengeluaran)',
                                ])
                                ->default('all')
                                ->live(),

                            Select::make('mode')
                                ->label('Mode Tampilan Data')
                                ->options([
                                    'running_balance'  => 'Buku Kas Berjalan (Running Balance Log)',
                                    'summary_category' => 'Rekapitulasi per Kategori Penerimaan/Pengeluaran',
                                ])
                                ->default('running_balance')
                                ->live(),

                            TextInput::make('search')
                                ->label('Pencarian')
                                ->placeholder('Cari No. Bukti / Keterangan...')
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
        $accountId  = !empty($state['account_id']) ? (int) $state['account_id'] : null;
        $typeFilter = $state['type_filter'] ?? 'all';
        $mode       = $state['mode'] ?? 'running_balance';
        $search     = $state['search'] ?? null;

        return (new GenerateCashBookReport())->execute(
            $fromDate,
            $toDate,
            $branchId,
            $accountId,
            $typeFilter,
            $mode,
            $search
        );
    }

    public function printPdf()
    {
        $data = $this->report_data;

        $pdf = Pdf::loadView('reports.cash-book-report-pdf', [
            'data' => $data,
        ])->setPaper('a4', 'landscape');

        return response()->streamDownload(
            fn () => print($pdf->output()),
            'Laporan-Kas-Bank-' . ($data['from_date'] ?? '') . '-s.d.-' . ($data['to_date'] ?? '') . '.pdf'
        );
    }

    public function exportExcel()
    {
        $data = $this->report_data;
        $filename = 'Laporan-Kas-Bank-' . ($data['from_date'] ?? '') . '-s.d.-' . ($data['to_date'] ?? '') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($data) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF");

            fputcsv($file, ['DIEGO MUSIC STORE - LAPORAN KAS & BANK (CASH BOOK)']);
            fputcsv($file, ['Periode:', $data['from_date'] . ' s.d. ' . $data['to_date']]);
            fputcsv($file, ['Cabang:', $data['branch_name']]);
            fputcsv($file, ['Akun Kas/Bank:', $data['account_name']]);
            fputcsv($file, []);

            if ($data['mode'] === 'summary_category') {
                fputcsv($file, ['Kategori Transaksi', 'Total Masuk (Rp)', 'Total Keluar (Rp)', 'Selisih / Net (Rp)']);
                foreach ($data['categories'] as $cat) {
                    fputcsv($file, [
                        $cat['category_name'],
                        $cat['inflow'],
                        $cat['outflow'],
                        $cat['net_amount'],
                    ]);
                }
            } else {
                fputcsv($file, ['No. Jurnal / Transaksi', 'Tanggal', 'Akun Kas/Bank', 'Lawam Akun / Kategori', 'Keterangan', 'Kas Masuk (Rp)', 'Kas Keluar (Rp)', 'Saldo Berjalan (Rp)']);
                fputcsv($file, ['-', $data['from_date'], $data['account_name'], 'SALDO AWAL', 'Saldo Kas Awal Periode', 0, 0, $data['initial_balance']]);
                foreach ($data['rows'] as $row) {
                    fputcsv($file, [
                        $row['entry_no'],
                        $row['date'],
                        $row['account_name'],
                        $row['opposing_account'],
                        $row['description'],
                        $row['inflow'],
                        $row['outflow'],
                        $row['running_balance'],
                    ]);
                }
            }

            fputcsv($file, []);
            fputcsv($file, ['RINGKASAN SALDO KAS & BANK']);
            fputcsv($file, ['Saldo Awal Periode (Rp):', $data['initial_balance']]);
            fputcsv($file, ['Total Kas Masuk (Penerimaan) (Rp):', $data['total_inflow']]);
            fputcsv($file, ['Total Kas Keluar (Pengeluaran) (Rp):', $data['total_outflow']]);
            fputcsv($file, ['Saldo Akhir Periode (Rp):', $data['ending_balance']]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
