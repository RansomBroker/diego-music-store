<?php

namespace App\Filament\Pages\Reports;

use App\Actions\Accounting\GenerateBalanceSheetReport;
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

class BalanceSheet extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedScale;

    protected static \UnitEnum|string|null $navigationGroup = 'Laporan Keuangan';

    protected static ?string $navigationLabel = 'Balance Sheet (Neraca)';

    protected static ?string $title = 'Laporan Balance Sheet (Neraca)';

    protected string $view = 'filament.pages.reports.balance-sheet';

    public ?array $data = [];

    // Properties for Drill-Down Transaction Ledger Modal
    public bool $showLedgerModal = false;
    public ?array $selectedAccount = null;
    public array $ledgerTransactions = [];

    public function mount(): void
    {
        $this->form->fill([
            'as_of_date'    => now()->format('Y-m-d'),
            'branch_id'     => null,
            'view_type'     => 'detail',
            'account_level' => 'all',
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
                Section::make('Parameter & Filter Laporan')
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

        $asOfDate     = $state['as_of_date'] ?? now()->format('Y-m-d');
        $branchId     = !empty($state['branch_id']) ? (int) $state['branch_id'] : null;
        $viewType     = $state['view_type'] ?? 'detail';
        $accountLevel = $state['account_level'] ?? 'all';

        return (new GenerateBalanceSheetReport())->execute($asOfDate, $branchId, $viewType, $accountLevel);
    }

    /**
     * Open Drill-Down Transaction Ledger Modal for a specific account.
     */
    public function openAccountLedgerModal(int $accountId): void
    {
        $account = Account::find($accountId);
        if (!$account) {
            return;
        }

        $state = $this->form->getRawState();
        $asOfDate = $state['as_of_date'] ?? now()->format('Y-m-d');
        $branchId = !empty($state['branch_id']) ? (int) $state['branch_id'] : null;

        // Query posted journal items for this account up to asOfDate
        $query = DB::table('journal_items')
            ->join('journal_entries', 'journal_items.journal_entry_id', '=', 'journal_entries.id')
            ->where('journal_items.account_id', $accountId)
            ->where('journal_entries.status', 'posted')
            ->whereDate('journal_entries.date', '<=', $asOfDate);

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
        $isDebitNormal = ($classification === 'asset' || str_starts_with($code, '1') || $classification === 'expense' || str_starts_with($code, '5') || str_starts_with($code, '6'));

        foreach ($items as $item) {
            $debit = (float) $item->debit;
            $credit = (float) $item->credit;

            if ($isDebitNormal) {
                $runningBalance += ($debit - $credit);
            } else {
                $runningBalance += ($credit - $debit);
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

        $pdf = Pdf::loadView('reports.balance-sheet-pdf', [
            'data' => $data,
        ])->setPaper('a4', 'portrait');

        return response()->streamDownload(
            fn () => print($pdf->output()),
            'Laporan-Balance-Sheet-' . ($data['as_of_date'] ?? now()->format('Y-m-d')) . '.pdf'
        );
    }

    public function exportExcel()
    {
        $data = $this->report_data;
        $filename = 'Laporan-Balance-Sheet-' . ($data['as_of_date'] ?? now()->format('Y-m-d')) . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($data) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF");

            fputcsv($file, ['DIEGO MUSIC STORE - LAPORAN BALANCE SHEET (NERACA)']);
            fputcsv($file, ['Per Tanggal:', $data['as_of_date']]);
            fputcsv($file, ['Cabang:', $data['branch_name']]);
            fputcsv($file, ['Mode Tampilan:', $data['view_type']]);
            fputcsv($file, ['Status Keseimbangan:', $data['is_balanced'] ? 'SEIMBANG (BALANCED)' : 'TIDAK SEIMBANG']);
            fputcsv($file, []);

            fputcsv($file, ['Kode Akun', 'Level', 'Nama Akun / Kategori', 'Saldo (Rp)']);
            fputcsv($file, ['ASET']);
            foreach ($data['assets']['items'] as $item) {
                $indent = str_repeat('   ', max(0, $item['level'] - 1));
                fputcsv($file, [$item['code'], $item['level'], $indent . $item['name'], $item['balance']]);
            }
            fputcsv($file, ['', '', 'TOTAL ASET', $data['total_assets']]);
            fputcsv($file, []);

            fputcsv($file, ['KEWAJIBAN (LIABILITIES) & EKUITAS']);
            fputcsv($file, ['KEWAJIBAN']);
            foreach ($data['liabilities']['items'] as $item) {
                $indent = str_repeat('   ', max(0, $item['level'] - 1));
                fputcsv($file, [$item['code'], $item['level'], $indent . $item['name'], $item['balance']]);
            }
            fputcsv($file, ['', '', 'TOTAL KEWAJIBAN', $data['total_liabilities']]);
            fputcsv($file, []);

            fputcsv($file, ['EKUITAS']);
            foreach ($data['equity']['items'] as $item) {
                $indent = str_repeat('   ', max(0, $item['level'] - 1));
                fputcsv($file, [$item['code'], $item['level'], $indent . $item['name'], $item['balance']]);
            }
            fputcsv($file, ['-', '-', 'Laba / (Rugi) Periode Berjalan', $data['equity']['current_net_income']]);
            fputcsv($file, ['', '', 'TOTAL EKUITAS', $data['total_equity']]);
            fputcsv($file, []);

            fputcsv($file, ['', '', 'TOTAL KEWAJIBAN & EKUITAS', $data['total_liabilities_and_equity']]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
