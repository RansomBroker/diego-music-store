<?php

namespace App\Filament\Widgets;

use App\Actions\Accounting\CalculateCoaComparisonBalances;
use App\Helpers\BranchHelper;
use App\Models\Account;
use Filament\Widgets\ChartWidget;

class FinancialComparisonChartWidget extends ChartWidget
{
    protected string $view = 'filament.widgets.financial-comparison-chart-widget';

    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 1;

    protected ?string $heading = 'Komparasi Keuangan (COA)';

    protected ?string $description = 'Bandingkan proporsi akun keuangan (misal: Liabilitas terhadap Aset)';

    /**
     * Selected account codes for comparison. Default: Aset & Liabilitas.
     */
    public array $selectedAccounts = ['100000000', '200000000'];

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getData(): array
    {
        $comparison = $this->getComparisonData();
        $items = $comparison['items'] ?? [];

        $labels = [];
        $data = [];
        $backgroundColors = [];

        foreach ($items as $item) {
            $labels[] = $item['name'];
            $data[] = $item['chart_value'];
            $backgroundColors[] = $item['color'];
        }

        // Handle case when no accounts selected or all balances are 0
        if (empty($data) || array_sum($data) == 0) {
            if (empty($labels)) {
                $labels = ['Belum ada akun dipilih'];
                $data = [1];
                $backgroundColors = ['#94a3b8'];
            } else {
                // Keep slice visually balanced when nominal is 0
                $data = array_fill(0, count($labels), 1);
            }
        }

        return [
            'datasets' => [
                [
                    'label' => 'Saldo',
                    'data' => $data,
                    'backgroundColor' => $backgroundColors,
                    'borderWidth' => 2,
                    'hoverOffset' => 6,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
                'tooltip' => [
                    'enabled' => true,
                ],
            ],
            'cutout' => '65%',
            'maintainAspectRatio' => false,
            'animation' => [
                'animateScale' => true,
                'animateRotate' => true,
            ],
        ];
    }

    public function updatedSelectedAccounts(): void
    {
        $this->cachedData = null;
        $this->updateChartData();
    }

    public function toggleAccount(string $code): void
    {
        if (in_array($code, $this->selectedAccounts, true)) {
            $this->selectedAccounts = array_values(array_filter($this->selectedAccounts, fn ($c) => $c !== $code));
        } else {
            $this->selectedAccounts[] = $code;
        }

        $this->cachedData = null;
        $this->updateChartData();
    }

    public function removeAccount(string $code): void
    {
        $this->selectedAccounts = array_values(array_filter($this->selectedAccounts, fn ($c) => $c !== $code));
        $this->cachedData = null;
        $this->updateChartData();
    }

    public function applyPreset(string $preset): void
    {
        $this->selectedAccounts = match ($preset) {
            'liabilities_vs_assets' => ['100000000', '200000000'],
            'balance_sheet' => ['100000000', '200000000', '300000000'],
            'income_statement' => ['400000000', '500000000', '600000000'],
            'liquid_assets' => ['111100000', '111200000', '111600000', '111800000'],
            default => ['100000000', '200000000'],
        };

        $this->cachedData = null;
        $this->updateChartData();
    }

    public function getComparisonData(): array
    {
        $action = app(CalculateCoaComparisonBalances::class);
        $activeBranchId = BranchHelper::getActiveBranchId();

        return $action->execute($this->selectedAccounts, $activeBranchId);
    }

    public function getCoaOptions(): array
    {
        $accounts = Account::where('is_active', true)
            ->orderBy('code')
            ->get();

        $groups = [
            'Kategori Utama (Level 1)' => [],
            'Sub-Kelompok (Level 2)' => [],
            'Akun Detail (Level 3)' => [],
        ];

        foreach ($accounts as $acc) {
            $label = "{$acc->code} - {$acc->name}";
            if ($acc->is_header && str_ends_with($acc->code, '00000000')) {
                $groups['Kategori Utama (Level 1)'][$acc->code] = $label;
            } elseif ($acc->is_header) {
                $groups['Sub-Kelompok (Level 2)'][$acc->code] = $label;
            } else {
                $groups['Akun Detail (Level 3)'][$acc->code] = $label;
            }
        }

        return $groups;
    }

    protected function getViewData(): array
    {
        return [
            'heading' => $this->getHeading(),
            'description' => $this->getDescription(),
            'cachedData' => $this->getCachedData(),
            'chartOptions' => $this->getOptions(),
            'comparison' => $this->getComparisonData(),
            'coaOptions' => $this->getCoaOptions(),
            'selectedAccounts' => $this->selectedAccounts,
            'activeBranchId' => BranchHelper::getActiveBranchId(),
        ];
    }
}
