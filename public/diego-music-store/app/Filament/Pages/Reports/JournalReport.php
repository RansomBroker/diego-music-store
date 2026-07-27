<?php

namespace App\Filament\Pages\Reports;

use App\Actions\Accounting\GenerateJournalReport;
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

class JournalReport extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;

    protected static \UnitEnum|string|null $navigationGroup = 'Laporan Keuangan';

    protected static ?string $navigationLabel = 'Laporan Jurnal';

    protected static ?string $title = 'Laporan Jurnal Umum (Journal Report)';

    protected string $view = 'filament.pages.reports.journal-report';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'from_date'  => now()->startOfMonth()->format('Y-m-d'),
            'to_date'    => now()->format('Y-m-d'),
            'status'     => 'posted',
            'branch_id'  => null,
            'account_id' => null,
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
        return $schema
            ->statePath('data')
            ->components([
                Section::make('Parameter & Filter Laporan Jurnal')
                    ->compact()
                    ->schema([
                        Grid::make([
                            'default' => 1,
                            'sm'      => 6,
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

                            Select::make('status')
                                ->label('Status Jurnal')
                                ->options([
                                    'posted' => 'Posted (Resmi)',
                                    'draft'  => 'Draft (Konsep)',
                                    'all'    => 'Semua Status',
                                ])
                                ->default('posted')
                                ->live(),

                            Select::make('account_id')
                                ->label('Filter Akun')
                                ->options(
                                    Account::where('is_active', true)
                                        ->where('is_header', false)
                                        ->orderBy('code')
                                        ->get()
                                        ->mapWithKeys(fn ($acc) => [$acc->id => "{$acc->code} - {$acc->name}"])
                                )
                                ->placeholder('Semua Akun')
                                ->searchable()
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

        $fromDate  = $state['from_date'] ?? now()->startOfMonth()->format('Y-m-d');
        $toDate    = $state['to_date'] ?? now()->format('Y-m-d');
        $status    = $state['status'] ?? 'posted';
        $branchId  = !empty($state['branch_id']) ? (int) $state['branch_id'] : null;
        $accountId = !empty($state['account_id']) ? (int) $state['account_id'] : null;
        $search    = $state['search'] ?? null;

        return (new GenerateJournalReport())->execute($fromDate, $toDate, $status, $branchId, $accountId, $search);
    }

    public function printPdf()
    {
        $data = $this->report_data;

        $pdf = Pdf::loadView('reports.journal-report-pdf', [
            'data' => $data,
        ])->setPaper('a4', 'portrait');

        return response()->streamDownload(
            fn () => print($pdf->output()),
            'Laporan-Jurnal-' . ($data['from_date'] ?? '') . '-s.d.-' . ($data['to_date'] ?? '') . '.pdf'
        );
    }

    public function exportExcel()
    {
        $data = $this->report_data;
        $filename = 'Laporan-Jurnal-' . ($data['from_date'] ?? '') . '-s.d.-' . ($data['to_date'] ?? '') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($data) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF");

            fputcsv($file, ['DIEGO MUSIC STORE - LAPORAN JURNAL UMUM (JOURNAL REPORT)']);
            fputcsv($file, ['Periode:', $data['from_date'] . ' s.d. ' . $data['to_date']]);
            fputcsv($file, ['Status:', strtoupper($data['status'])]);
            fputcsv($file, ['Cabang:', $data['branch_name']]);
            fputcsv($file, ['Total Jurnal:', $data['total_entries']]);
            fputcsv($file, []);

            foreach ($data['entries'] as $entry) {
                fputcsv($file, ['NO BUKTI:', $entry['entry_no'], 'Tanggal:', $entry['date'], 'Status:', strtoupper($entry['status']), 'Cabang:', $entry['branch_name']]);
                fputcsv($file, ['Keterangan:', $entry['description']]);
                fputcsv($file, ['Kode Akun', 'Nama Akun', 'Memo', 'Debit (Rp)', 'Kredit (Rp)']);

                foreach ($entry['items'] as $item) {
                    fputcsv($file, [
                        $item['account_code'],
                        $item['account_name'],
                        $item['notes'],
                        $item['debit'],
                        $item['credit'],
                    ]);
                }

                fputcsv($file, ['TOTAL BUKTI JURNAL', '', '', $entry['total_debit'], $entry['total_credit']]);
                fputcsv($file, []);
            }

            fputcsv($file, ['GRAND TOTAL DEBIT & KREDIT', '', '', $data['grand_total_debit'], $data['grand_total_credit']]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
