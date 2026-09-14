<?php

namespace App\Livewire;

use App\Actions\Kpi\ApproveKpiEvaluation;
use App\Actions\Kpi\CalculateEmployeeKpi;
use App\Helpers\BranchHelper;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\KpiEvaluation;
use App\Models\KpiTemplate;
use Filament\Notifications\Notification;
use Livewire\Component;
use Livewire\WithPagination;

class PosKpiPerformance extends Component
{
    use WithPagination;

    public string $activeTab = 'recap'; // 'recap', 'dashboard', 'templates'
    public string $filterMonth = '';
    public string $filterBranchId = '';

    // Modal state for Template CRUD
    public bool $showTemplateModal = false;
    public ?int $editingTemplateId = null;
    public string $templateName = '';
    public string $templatePosition = '';
    public ?int $templateEmployeeId = null;
    public float $maxBonusAmount = 500000;
    public float $targetSalesAmount = 10000000;
    public float $weightSales = 40.0;
    public float $targetAtvAmount = 250000;
    public float $weightAtv = 20.0;
    public float $targetAttendancePct = 95.0;
    public float $weightAttendance = 20.0;
    public float $targetPunctualityPct = 95.0;
    public float $weightPunctuality = 20.0;
    public bool $isActiveTemplate = true;

    public function mount(): void
    {
        $this->filterMonth = now()->format('Y-m');
        $this->filterBranchId = (string) (BranchHelper::getActiveBranchId() ?: '');
    }

    public function updatedFilterMonth(): void
    {
        $this->resetPage();
    }

    public function updatedFilterBranchId(): void
    {
        $this->resetPage();
    }

    // ── Realtime Recalculate All Employees ───────────────────────────────
    public function recalculateAllKpi(): void
    {
        $action = app(CalculateEmployeeKpi::class);
        $employees = Employee::where('is_active', true)->get();

        $count = 0;
        foreach ($employees as $emp) {
            $action->execute($emp, $this->filterMonth);
            $count++;
        }

        Notification::make()
            ->title('Kalkulasi KPI Selesai')
            ->body("Berhasil memperbarui kalkulasi skor KPI & insentif bonus untuk {$count} karyawan.")
            ->success()
            ->send();
    }

    // ── Template Modal Handlers ──────────────────────────────────────────
    public function openTemplateModal(?int $templateId = null): void
    {
        $this->resetValidation();

        if ($templateId) {
            $tpl = KpiTemplate::findOrFail($templateId);
            $this->editingTemplateId = $tpl->id;
            $this->templateName = $tpl->name;
            $this->templatePosition = $tpl->position ?: '';
            $this->templateEmployeeId = $tpl->employee_id;
            $this->maxBonusAmount = (float) $tpl->max_bonus_amount;
            $this->targetSalesAmount = (float) $tpl->target_sales_amount;
            $this->weightSales = (float) $tpl->weight_sales;
            $this->targetAtvAmount = (float) $tpl->target_atv_amount;
            $this->weightAtv = (float) $tpl->weight_atv;
            $this->targetAttendancePct = (float) $tpl->target_attendance_pct;
            $this->weightAttendance = (float) $tpl->weight_attendance;
            $this->targetPunctualityPct = (float) $tpl->target_punctuality_pct;
            $this->weightPunctuality = (float) $tpl->weight_punctuality;
            $this->isActiveTemplate = (bool) $tpl->is_active;
        } else {
            $this->editingTemplateId = null;
            $this->templateName = '';
            $this->templatePosition = 'Sales Executive';
            $this->templateEmployeeId = null;
            $this->maxBonusAmount = 500000;
            $this->targetSalesAmount = 10000000;
            $this->weightSales = 40.0;
            $this->targetAtvAmount = 250000;
            $this->weightAtv = 20.0;
            $this->targetAttendancePct = 95.0;
            $this->weightAttendance = 20.0;
            $this->targetPunctualityPct = 95.0;
            $this->weightPunctuality = 20.0;
            $this->isActiveTemplate = true;
        }

        $this->showTemplateModal = true;
    }

    public function saveTemplate(): void
    {
        $this->validate([
            'templateName' => 'required|string|max:255',
            'maxBonusAmount' => 'required|numeric|min:0',
            'targetSalesAmount' => 'required|numeric|min:0',
            'weightSales' => 'required|numeric|min:0|max:100',
            'targetAtvAmount' => 'required|numeric|min:0',
            'weightAtv' => 'required|numeric|min:0|max:100',
            'targetAttendancePct' => 'required|numeric|min:0|max:100',
            'weightAttendance' => 'required|numeric|min:0|max:100',
            'targetPunctualityPct' => 'required|numeric|min:0|max:100',
            'weightPunctuality' => 'required|numeric|min:0|max:100',
        ]);

        KpiTemplate::updateOrCreate(
            ['id' => $this->editingTemplateId],
            [
                'name' => $this->templateName,
                'position' => $this->templatePosition ?: null,
                'employee_id' => $this->templateEmployeeId ?: null,
                'max_bonus_amount' => $this->maxBonusAmount,
                'target_sales_amount' => $this->targetSalesAmount,
                'weight_sales' => $this->weightSales,
                'target_atv_amount' => $this->targetAtvAmount,
                'weight_atv' => $this->weightAtv,
                'target_attendance_pct' => $this->targetAttendancePct,
                'weight_attendance' => $this->weightAttendance,
                'target_punctuality_pct' => $this->targetPunctualityPct,
                'weight_punctuality' => $this->weightPunctuality,
                'bonus_tiering_rules' => [
                    ['min_score' => 90, 'bonus_pct' => 100],
                    ['min_score' => 75, 'bonus_pct' => 75],
                    ['min_score' => 0, 'bonus_pct' => 0],
                ],
                'is_active' => $this->isActiveTemplate,
            ]
        );

        $this->showTemplateModal = false;

        Notification::make()
            ->title('Template KPI Disimpan')
            ->body('Data template KPI & tiering bonus berhasil diperbarui.')
            ->success()
            ->send();
    }

    public function render()
    {
        $branches = Branch::where('is_active', true)->orderBy('name')->get();
        $employees = Employee::where('is_active', true)->orderBy('name')->get();

        // 1. Current logged in employee evaluation data for Dashboard Tab
        $currentEmp = auth()->user()?->employee;
        $myEvaluation = null;
        if ($currentEmp) {
            $myEvaluation = app(CalculateEmployeeKpi::class)->execute($currentEmp, $this->filterMonth);
        }

        // 2. Evaluations list query for Tab 1
        $evaluationsQuery = KpiEvaluation::with(['employee', 'template', 'branch'])
            ->where('period', $this->filterMonth)
            ->orderBy('final_kpi_score', 'desc');

        if (!empty($this->filterBranchId)) {
            $evaluationsQuery->where('branch_id', $this->filterBranchId);
        }

        $evaluations = $evaluationsQuery->paginate(12);

        // 3. Templates list query for Tab 3
        $templates = KpiTemplate::with('employee')
            ->orderBy('id', 'desc')
            ->get();

        // Active logo URL for POS layout
        $activeBranchId = BranchHelper::getActiveBranchId();
        $activeBranch = $activeBranchId ? Branch::find($activeBranchId) : null;
        $selectedLogoUrl = ($activeBranch && !empty($activeBranch->logo_path) && trim($activeBranch->logo_path) !== '')
            ? (str_starts_with($activeBranch->logo_path, 'http') ? $activeBranch->logo_path : asset('storage/' . $activeBranch->logo_path))
            : asset('images/default-store-logo.png');

        return view('livewire.pos-kpi-performance', [
            'branches' => $branches,
            'employees' => $employees,
            'myEvaluation' => $myEvaluation,
            'evaluations' => $evaluations,
            'templates' => $templates,
            'selectedLogoUrl' => $selectedLogoUrl,
        ])->layout('layouts.pos', ['title' => 'Performance & KPI Karyawan']);
    }
}
