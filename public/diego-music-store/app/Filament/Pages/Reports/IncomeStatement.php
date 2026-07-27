<?php

namespace App\Filament\Pages\Reports;

use App\Actions\Accounting\GenerateIncomeStatementReport;
use App\Models\Account;
use App\Models\Branch;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\DB;

class IncomeStatement extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentChartBar;

    protected static \UnitEnum|string|null $navigationGroup = 'Laporan Keuangan';

    protected static ?string $navigationLabel = 'Laba Rugi (Income Statement)';

    protected static ?string $title = 'Laporan Laba Rugi (Income Statement)';

    protected string $view = 'filament.pages.reports.income-statement';

    public ?array $data = [];

    // Properties for Drill-Down Transaction Ledger Modal
    public bool $showLedgerModal = false;
    public ?array $selectedAccount = null;
    public array $ledgerTransactions = [];

    public function mount(): void
    {
        $this->form->fill([
            'from_date'     => now()->startOfMonth()->format('Y-m-d'),
            'to_date'       => now()->format('Y-m-d'),
            'branch_id'     => null,
            'account_level' => 'all',
            'view_type'     => 'detail',
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
                Section::make('Parameter & Filter Laporan Laba Rugi')
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

                            ToggleButtons::make('view_type')
                                ->label('Mode Tampilan')
                                ->options([
                                    'summary' => 'Ringkas',
                                    'detail'  => 'Detail Rinci',
                                ])
                                ->default('detail')
                                ->inline()
                                ->live(),
                        ]),
                    ]),
            ]);
    }

    public function getReportDataProperty(): array
    {
        $state = $this->form->getRawState();

        $fromDate     = $state['from_date'] ?? now()->startOfMonth()->format('Y-m-d');
        $toDate       = $state['to_date'] ?? now()->format('Y-m-d');
        $branchId     = !empty($state['branch_id']) ? (int) $state['branch_id'] : null;
        $viewType     = $state['view_type'] ?? 'detail';
        $accountLevel = $state['account_level'] ?? 'all';

        return (new GenerateIncomeStatementReport())->execute($fromDate, $toDate, $branchId, $viewType, $accountLevel);
    }

    /**
     * Open Drill-Down Transaction Ledger Modal for a specific account within date range.
     */
    public function openAccountLedgerModal(int $accountId): void
    {
        $account = Account::find($accountId);
        if (!$account) {
            return;
        }

        $state    = $this->form->getRawState();
        $fromDate = $state['from_date'] ?? now()->startOfMonth()->format('Y-m-d');
        $toDate   = $state['to_date'] ?? now()->format('Y-m-d');
        $branchId = !empty($state['branch_id']) ? (int) $state['branch_id'] : null;

        // Query posted journal items for this account within [fromDate, toDate]
        $query = DB::table('journal_items')
            ->join('journal_entries', 'journal_items.journal_entry_id', '=', 'journal_entries.id')
            ->where('journal_items.account_id', $accountId)
            ->where('journal_entries.status', 'posted')
            ->whereDate('journal_entries.date', '>=', $fromDate)
            ->whereDate('journal_entries.date', '<=', $toDate);

        if ($branchId) {
            $query->where('journal_entries.branch_id', $branchId);
        }

        $items = $query->select(
            'journal_entries.entry_no',
            'journal_entries.date',
            'journal_entries.description',
            'journal_items.debit',
            'journal_items.credit'
        )
        ->orderBy('journal_entries.date', 'asc')
        ->orderBy('journal_entries.id', 'asc')
        ->get();

        $runningBalance = 0.0;
        $transactions = [];
        $classification = strtolower($account->classification);
        $code = strtolower($account->code);

        // Revenue (4, 7): Normal balance is Credit (Credit - Debit)
        $isCreditNormal = ($classification === 'revenue' || str_starts_with($code, '4') || str_starts_with($code, '7'));

        foreach ($items as $item) {
            $debit = (float) $item->debit;
            $credit = (float) $item->credit;

            if ($isCreditNormal) {
                $runningBalance += ($credit - $debit);
            } else {
                $runningBalance += ($debit - $credit);
            }

            $transactions[] = [
                'entry_no'        => $item->entry_no,
                'date'            => $item->date,
                'description'     => $item->description,
                'debit'           => $debit,
                'credit'          => $credit,
                'running_balance' => $runningBalance,
            ];
        }

        $this->selectedAccount = [
            'id'             => $account->id,
            'code'           => $account->code,
            'name'           => $account->name,
            'classification' => $account->classification,
            'total_balance'  => $runningBalance,
        ];

        $this->ledgerTransactions = $transactions;
        $this->showLedgerModal = true;
    }

    public function closeLedgerModal(): void
    {
        $this->showLedgerModal = false;
        $this->selectedAccount = null;
        $this->ledgerTransactions = [];
    }

    public function printPdf()
    {
        $data = $this->report_data;

        $pdf = Pdf::loadView('reports.income-statement-pdf', [
            'data' => $data,
        ])->setPaper('a4', 'portrait');

        return response()->streamDownload(
            fn () => print($pdf->output()),
            'Laporan-Laba-Rugi-' . ($data['from_date'] ?? '') . '-s.d.-' . ($data['to_date'] ?? '') . '.pdf'
        );
    }

    public function exportExcel()
    {
        $data = $this->report_data;
        $filename = 'Laporan-Laba-Rugi-' . ($data['from_date'] ?? '') . '-s.d.-' . ($data['to_date'] ?? '') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($data) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF");

            fputcsv($file, ['DIEGO MUSIC STORE - LAPORAN LABA RUGI (INCOME STATEMENT)']);
            fputcsv($file, ['Periode:', $data['from_date'] . ' s.d. ' . $data['to_date']]);
            fputcsv($file, ['Cabang:', $data['branch_name']]);
            fputcsv($file, ['Mode Tampilan:', $data['view_type']]);
            fputcsv($file, ['Hasil:', $data['is_profit'] ? 'LABA BERSIH' : 'RUGI BERSIH']);
            fputcsv($file, []);

            fputcsv($file, ['Kode Akun', 'Level', 'Nama Akun / Kategori', 'Nominal (Rp)']);

            fputcsv($file, ['PENDAPATAN OPERASIONAL']);
            foreach ($data['revenue']['items'] as $item) {
                $indent = str_repeat('   ', max(0, $item['level'] - 1));
                fputcsv($file, [$item['code'], $item['level'], $indent . $item['name'], $item['balance']]);
            }
            fputcsv($file, ['', '', 'TOTAL PENDAPATAN OPERASIONAL', $data['revenue']['total']]);
            fputcsv($file, []);

            fputcsv($file, ['HARGA POKOK PENJUALAN (COGS)']);
            foreach ($data['cogs']['items'] as $item) {
                $indent = str_repeat('   ', max(0, $item['level'] - 1));
                fputcsv($file, [$item['code'], $item['level'], $indent . $item['name'], $item['balance']]);
            }
            fputcsv($file, ['', '', 'TOTAL HARGA POKOK PENJUALAN', $data['cogs']['total']]);
            fputcsv($file, []);

            fputcsv($file, ['', '', 'LABA KOTOR (GROSS PROFIT)', $data['gross_profit']]);
            fputcsv($file, []);

            fputcsv($file, ['BEBAN OPERASIONAL']);
            foreach ($data['operating_expenses']['items'] as $item) {
                $indent = str_repeat('   ', max(0, $item['level'] - 1));
                fputcsv($file, [$item['code'], $item['level'], $indent . $item['name'], $item['balance']]);
            }
            fputcsv($file, ['', '', 'TOTAL BEBAN OPERASIONAL', $data['operating_expenses']['total']]);
            fputcsv($file, []);

            fputcsv($file, ['', '', 'LABA OPERASIONAL (OPERATING INCOME)', $data['operating_income']]);
            fputcsv($file, []);

            if (count($data['other_revenue']['items']) > 0 || count($data['other_expenses']['items']) > 0) {
                fputcsv($file, ['PENDAPATAN & BEBAN LAIN-LAIN (NON-OPERASIONAL)']);
                foreach ($data['other_revenue']['items'] as $item) {
                    $indent = str_repeat('   ', max(0, $item['level'] - 1));
                    fputcsv($file, [$item['code'], $item['level'], $indent . $item['name'], $item['balance']]);
                }
                foreach ($data['other_expenses']['items'] as $item) {
                    $indent = str_repeat('   ', max(0, $item['level'] - 1));
                    fputcsv($file, [$item['code'], $item['level'], $indent . $item['name'], -$item['balance']]);
                }
                fputcsv($file, ['', '', 'NET NON-OPERATIONAL', $data['net_other']]);
                fputcsv($file, []);
            }

            fputcsv($file, ['', '', 'LABA / (RUGI) BERSIH (NET INCOME)', $data['net_income']]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
