<?php

namespace App\Filament\Pages\Accounting;

use App\Actions\Accounting\ExecuteMonthlyClosing;
use App\Actions\Accounting\GenerateIncomeStatementReport;
use App\Actions\Accounting\ReopenMonthlyClosing;
use App\Models\Branch;
use App\Models\MonthlyClosing;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class MonthlyClosingPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedLockClosed;

    protected static \UnitEnum|string|null $navigationGroup = 'Akuntansi';

    protected static ?string $navigationLabel = 'Tutup Buku Bulanan';

    protected static ?string $title = 'Tutup Buku Bulanan (Monthly Closing)';

    protected string $view = 'filament.pages.accounting.monthly-closing-page';

    public ?array $data = [];

    // Modal state properties
    public bool $showClosingModal = false;
    public bool $showReopenModal = false;
    public ?int $selectedClosingIdToReopen = null;

    public function mount(): void
    {
        $this->form->fill([
            'year'      => (int) now()->format('Y'),
            'month'     => (int) now()->format('m'),
            'branch_id' => null,
        ]);
    }

    public function openClosingModal(): void
    {
        $this->showClosingModal = true;
    }

    public function closeClosingModal(): void
    {
        $this->showClosingModal = false;
    }

    public function openReopenModal(?int $closingId = null): void
    {
        $this->selectedClosingIdToReopen = $closingId;
        $this->showReopenModal = true;
    }

    public function closeReopenModal(): void
    {
        $this->showReopenModal = false;
        $this->selectedClosingIdToReopen = null;
    }

    public function form(Schema $schema): Schema
    {
        $years = [];
        $currentYear = (int) now()->format('Y');
        for ($y = $currentYear - 2; $y <= $currentYear + 2; $y++) {
            $years[$y] = (string) $y;
        }

        $months = [
            1  => '01 - Januari',
            2  => '02 - Februari',
            3  => '03 - Maret',
            4  => '04 - April',
            5  => '05 - Mei',
            6  => '06 - Juni',
            7  => '07 - Juli',
            8  => '08 - Agustus',
            9  => '09 - September',
            10 => '10 - Oktober',
            11 => '11 - November',
            12 => '12 - Desember',
        ];

        return $schema
            ->statePath('data')
            ->components([
                Section::make('Pilih Periode Keuangan')
                    ->compact()
                    ->schema([
                        Grid::make([
                            'default' => 1,
                            'sm'      => 3,
                        ])->schema([
                            Select::make('year')
                                ->label('Tahun')
                                ->options($years)
                                ->default($currentYear)
                                ->required()
                                ->live(),

                            Select::make('month')
                                ->label('Bulan')
                                ->options($months)
                                ->default((int) now()->format('m'))
                                ->required()
                                ->live(),

                            Select::make('branch_id')
                                ->label('Cabang')
                                ->options(Branch::orderBy('name')->pluck('name', 'id'))
                                ->placeholder('Semua Cabang (Konsolidasi)')
                                ->searchable()
                                ->live(),
                        ]),
                    ]),
            ]);
    }

    public function getPeriodInfoProperty(): array
    {
        $state = $this->form->getRawState();
        $year  = (int) ($state['year'] ?? now()->format('Y'));
        $month = (int) ($state['month'] ?? now()->format('m'));
        $branchId = !empty($state['branch_id']) ? (int) $state['branch_id'] : null;

        $periodKey = sprintf('%04d-%02d', $year, $month);
        $fromDate  = Carbon::createFromDate($year, $month, 1)->startOfMonth()->format('Y-m-d');
        $toDate    = Carbon::createFromDate($year, $month, 1)->endOfMonth()->format('Y-m-d');

        // Check closing status
        $closing = MonthlyClosing::with(['closedBy', 'reopenedBy', 'closingJournal'])
            ->where('period_key', $periodKey)
            ->first();

        // Calculate preview P&L data
        $incomeReport = (new GenerateIncomeStatementReport())->execute($fromDate, $toDate, $branchId, 'summary', 'all');

        return [
            'year'          => $year,
            'month'         => $month,
            'period_key'    => $periodKey,
            'from_date'     => $fromDate,
            'to_date'       => $toDate,
            'branch_id'     => $branchId,
            'is_closed'     => $closing && $closing->status === 'closed',
            'closing'       => $closing,
            'total_revenue' => $incomeReport['revenue']['total'],
            'total_cogs'    => $incomeReport['cogs']['total'],
            'gross_profit'  => $incomeReport['gross_profit'],
            'total_expense' => $incomeReport['operating_expenses']['total'],
            'net_income'    => $incomeReport['net_income'],
            'is_profit'     => $incomeReport['is_profit'],
        ];
    }

    public function getHistoryProperty()
    {
        return MonthlyClosing::with(['closedBy', 'reopenedBy', 'branch', 'closingJournal'])
            ->orderBy('period_key', 'desc')
            ->limit(12)
            ->get();
    }

    public function executeClosing(): void
    {
        $info = $this->period_info;

        try {
            $closing = (new ExecuteMonthlyClosing())->execute(
                $info['year'],
                $info['month'],
                $info['branch_id'],
                Auth::id(),
                "Tutup Buku Periode {$info['period_key']} oleh " . (Auth::user()?->name ?? 'Admin')
            );

            $this->closeClosingModal();

            Notification::make()
                ->title('Tutup Buku Bulanan Berhasil')
                ->body("Periode {$closing->period_key} telah berhasil dikunci dan Jurnal Penutup #{$closing->closingJournal?->entry_no} telah diterbitkan.")
                ->success()
                ->send();
        } catch (\Exception $e) {
            $this->closeClosingModal();

            Notification::make()
                ->title('Gagal Mengeksekusi Tutup Buku')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function reopenPeriod(): void
    {
        $info = $this->period_info;

        $closing = $this->selectedClosingIdToReopen
            ? MonthlyClosing::find($this->selectedClosingIdToReopen)
            : $info['closing'];

        if (!$closing) {
            return;
        }

        try {
            (new ReopenMonthlyClosing())->execute(
                $closing,
                Auth::id(),
                "Pembukaan Kembali Periode oleh " . (Auth::user()?->name ?? 'Admin')
            );

            $this->closeReopenModal();

            Notification::make()
                ->title('Buka Kembali Periode Berhasil')
                ->body("Periode {$closing->period_key} kini dalam status TERBUKA dan Jurnal Penutup telah dibatalkan.")
                ->warning()
                ->send();
        } catch (\Exception $e) {
            $this->closeReopenModal();

            Notification::make()
                ->title('Gagal Membuka Kembali Periode')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
}
