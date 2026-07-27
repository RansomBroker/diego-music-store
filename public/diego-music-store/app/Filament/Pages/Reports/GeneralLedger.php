<?php

namespace App\Filament\Pages\Reports;

use App\Actions\Accounting\GenerateGeneralLedgerReport;
use App\Models\Account;
use App\Models\Branch;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class GeneralLedger extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedBookOpen;

    protected static \UnitEnum|string|null $navigationGroup = 'Laporan Keuangan';

    protected static ?string $navigationLabel = 'Buku Besar (General Ledger)';

    protected static ?string $title = 'Laporan Buku Besar (General Ledger)';

    protected string $view = 'filament.pages.reports.general-ledger';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'from_date'  => now()->startOfMonth()->format('Y-m-d'),
            'to_date'    => now()->format('Y-m-d'),
            'branch_id'  => null,
            'account_id' => null,
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
                Section::make('Parameter & Filter Buku Besar')
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
                                ->label('Pilih Akun')
                                ->options(
                                    Account::where('is_active', true)
                                        ->where('is_header', false)
                                        ->orderBy('code')
                                        ->get()
                                        ->mapWithKeys(fn ($acc) => [$acc->id => "{$acc->code} - {$acc->name}"])
                                )
                                ->placeholder('Semua Akun (Buku Besar Lengkap)')
                                ->searchable()
                                ->live(),
                        ]),
                    ]),
            ]);
    }

    public function getReportDataProperty(): array
    {
        $state = $this->form->getRawState();

        $fromDate  = $state['from_date'] ?? now()->startOfMonth()->format('Y-m-d');
        $toDate    = $state['to_date'] ?? now()->format('Y-m-d');
        $branchId  = !empty($state['branch_id']) ? (int) $state['branch_id'] : null;
        $accountId = !empty($state['account_id']) ? (int) $state['account_id'] : null;

        return (new GenerateGeneralLedgerReport())->execute($fromDate, $toDate, $accountId, $branchId);
    }

    public function printPdf()
    {
        $data = $this->report_data;

        $pdf = Pdf::loadView('reports.general-ledger-pdf', [
            'data' => $data,
        ])->setPaper('a4', 'portrait');

        return response()->streamDownload(
            fn () => print($pdf->output()),
            'Laporan-Buku-Besar-' . ($data['from_date'] ?? '') . '-s.d.-' . ($data['to_date'] ?? '') . '.pdf'
        );
    }

    public function exportExcel()
    {
        $data = $this->report_data;
        $filename = 'Laporan-Buku-Besar-' . ($data['from_date'] ?? '') . '-s.d.-' . ($data['to_date'] ?? '') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($data) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF");

            fputcsv($file, ['DIEGO MUSIC STORE - LAPORAN BUKU BESAR (GENERAL LEDGER)']);
            fputcsv($file, ['Periode:', $data['from_date'] . ' s.d. ' . $data['to_date']]);
            fputcsv($file, ['Cabang:', $data['branch_name']]);
            fputcsv($file, ['Akun:', $data['selected_account'] ? ($data['selected_account']->code . ' - ' . $data['selected_account']->name) : 'Semua Akun']);
            fputcsv($file, []);

            foreach ($data['ledgers'] as $ledger) {
                fputcsv($file, ['AKUN:', $ledger['account_code'] . ' - ' . $ledger['account_name'], 'Klasifikasi:', $ledger['classification']]);
                fputcsv($file, ['SALDO AWAL:', '', '', '', '', $ledger['beginning_balance']]);
                fputcsv($file, ['Tanggal', 'No. Bukti', 'Keterangan', 'Debit (Rp)', 'Kredit (Rp)', 'Running Balance (Rp)']);

                foreach ($ledger['transactions'] as $tx) {
                    fputcsv($file, [
                        $tx['date'],
                        $tx['entry_no'],
                        $tx['description'],
                        $tx['debit'],
                        $tx['credit'],
                        $tx['running_balance'],
                    ]);
                }

                fputcsv($file, ['TOTAL MUTASI PERIODE', '', '', $ledger['total_debit'], $ledger['total_credit'], '']);
                fputcsv($file, ['SALDO AKHIR', '', '', '', '', $ledger['ending_balance']]);
                fputcsv($file, []);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
