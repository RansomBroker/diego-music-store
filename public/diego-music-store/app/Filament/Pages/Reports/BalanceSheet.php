<?php

namespace App\Filament\Pages\Reports;

use App\Actions\Accounting\ExportBalanceSheetToExcel;
use App\Actions\Accounting\GenerateBalanceSheetReport;
use App\Filament\Concerns\HasTemporaryOpeningBalanceActions;
use App\Helpers\BranchHelper;
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
    use HasTemporaryOpeningBalanceActions;
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedScale;

    protected static \UnitEnum|string|null $navigationGroup = 'Laporan Keuangan';

    protected static ?string $navigationLabel = 'Balance Sheet (Neraca)';

    protected static ?string $title = 'Laporan Balance Sheet (Neraca)';

    protected string $view = 'filament.pages.reports.balance-sheet';

    public static function canAccess(): bool
    {
        return \App\Helpers\PermissionHelper::canAccessBackofficeFeature(auth()->user(), 'bo.reports.balance_sheet');
    }

    public function boot(): void
    {
        abort_unless(static::canAccess(), 403);
    }

    public ?array $data = [];

    // Properties for Drill-Down Transaction Ledger Modal
    public bool $showLedgerModal = false;
    public ?array $selectedAccount = null;
    public array $ledgerTransactions = [];

    public function mount(): void
    {
        $canAccessAll = BranchHelper::canAccessAllBranches();
        $defaultBranch = $canAccessAll ? null : BranchHelper::getActiveBranchId();

        $this->form->fill([
            'as_of_date'    => now()->format('Y-m-d'),
            'branch_id'     => $defaultBranch,
            'view_type'     => 'detail',
            'account_level' => 'all',
        ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            ...$this->temporaryOpeningBalanceHeaderActions(),
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
                                ->options(fn () => BranchHelper::getAllowedBranchesQuery()->pluck('name', 'id'))
                                ->placeholder(fn () => BranchHelper::canAccessAllBranches() ? 'Semua Cabang (Konsolidasi)' : null)
                                ->required(fn () => !BranchHelper::canAccessAllBranches())
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

        if (!BranchHelper::canAccessAllBranches()) {
            $allowedBranchIds = BranchHelper::getAllowedBranchesQuery()->pluck('id')->toArray();
            if (!$branchId || !in_array($branchId, $allowedBranchIds, true)) {
                $branchId = BranchHelper::getActiveBranchId();
            }
        }

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

        $matchingAccountIds = Account::where('code', $account->code)->pluck('id')->all();
        if (empty($matchingAccountIds)) {
            $matchingAccountIds = [$accountId];
        }

        // Query posted journal items for this account code up to asOfDate
        $query = DB::table('journal_items')
            ->join('journal_entries', 'journal_items.journal_entry_id', '=', 'journal_entries.id')
            ->whereIn('journal_items.account_id', $matchingAccountIds)
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
        $isDebitNormal = \App\Helpers\AccountHelper::isGroupDebitNormal($account);

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
        return app(ExportBalanceSheetToExcel::class)->execute($this->report_data);
    }
}
