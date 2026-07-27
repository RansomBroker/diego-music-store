<?php

namespace App\Filament\Pages\Reports;

use App\Actions\Accounting\GenerateTrialBalanceReport;
use App\Models\Branch;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class TrialBalance extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedScale;

    protected static \UnitEnum|string|null $navigationGroup = 'Laporan Keuangan';

    protected static ?string $navigationLabel = 'Neraca Saldo (Trial Balance)';

    protected static ?string $title = 'Laporan Neraca Saldo (Trial Balance 6-Kolom)';

    protected string $view = 'filament.pages.reports.trial-balance';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'from_date'          => now()->startOfMonth()->format('Y-m-d'),
            'to_date'            => now()->format('Y-m-d'),
            'branch_id'          => null,
            'account_level'      => 'all',
            'hide_zero_balances' => true,
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
                Section::make('Parameter & Filter Neraca Saldo')
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

                            Select::make('account_level')
                                ->label('Level Kedalaman Akun')
                                ->options([
                                    'all' => 'Semua Level (Full)',
                                    '1'   => 'Level 1 (Kelompok Utama)',
                                    '2'   => 'Level 2 (Sub-Kelompok)',
                                    '3'   => 'Level 3 (Detail Akun)',
                                ])
                                ->default('all')
                                ->live(),

                            Toggle::make('hide_zero_balances')
                                ->label('Sembunyikan Saldo Nol')
                                ->default(true)
                                ->inline(false)
                                ->live(),
                        ]),
                    ]),
            ]);
    }

    public function getReportDataProperty(): array
    {
        $state = $this->form->getRawState();

        $fromDate         = $state['from_date'] ?? now()->startOfMonth()->format('Y-m-d');
        $toDate           = $state['to_date'] ?? now()->format('Y-m-d');
        $branchId         = !empty($state['branch_id']) ? (int) $state['branch_id'] : null;
        $accountLevel     = $state['account_level'] ?? 'all';
        $hideZeroBalances = isset($state['hide_zero_balances']) ? (bool) $state['hide_zero_balances'] : true;

        return (new GenerateTrialBalanceReport())->execute($fromDate, $toDate, $branchId, $hideZeroBalances, $accountLevel);
    }

    public function printPdf()
    {
        $data = $this->report_data;

        $pdf = Pdf::loadView('reports.trial-balance-pdf', [
            'data' => $data,
        ])->setPaper('a4', 'landscape');

        return response()->streamDownload(
            fn () => print($pdf->output()),
            'Laporan-Neraca-Saldo-' . ($data['from_date'] ?? '') . '-s.d.-' . ($data['to_date'] ?? '') . '.pdf'
        );
    }

    public function exportExcel()
    {
        $data = $this->report_data;
        $filename = 'Laporan-Neraca-Saldo-' . ($data['from_date'] ?? '') . '-s.d.-' . ($data['to_date'] ?? '') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($data) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF");

            fputcsv($file, ['DIEGO MUSIC STORE - LAPORAN NERACA SALDO (TRIAL BALANCE 6-KOLOM)']);
            fputcsv($file, ['Periode:', $data['from_date'] . ' s.d. ' . $data['to_date']]);
            fputcsv($file, ['Cabang:', $data['branch_name']]);
            fputcsv($file, ['Status:', $data['is_balanced'] ? 'BALANCED (SEIMBANG)' : 'OUT OF BALANCE']);
            fputcsv($file, []);

            fputcsv($file, ['Kode Akun', 'Nama Akun', 'Saldo Awal Debit (Rp)', 'Saldo Awal Kredit (Rp)', 'Mutasi Debit (Rp)', 'Mutasi Kredit (Rp)', 'Saldo Akhir Debit (Rp)', 'Saldo Akhir Kredit (Rp)']);

            foreach ($data['items'] as $item) {
                $indent = str_repeat('   ', max(0, $item['level'] - 1));
                fputcsv($file, [
                    $item['code'],
                    $indent . $item['name'],
                    $item['beginning_debit'],
                    $item['beginning_credit'],
                    $item['period_debit'],
                    $item['period_credit'],
                    $item['ending_debit'],
                    $item['ending_credit'],
                ]);
            }

            fputcsv($file, [
                'TOTAL',
                'GRAND TOTAL DEBIT & KREDIT',
                $data['total_beginning_debit'],
                $data['total_beginning_credit'],
                $data['total_period_debit'],
                $data['total_period_credit'],
                $data['total_ending_debit'],
                $data['total_ending_credit'],
            ]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
