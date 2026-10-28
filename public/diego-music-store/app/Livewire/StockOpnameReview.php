<?php

namespace App\Livewire;

use App\Actions\StockOpname\FinalizeStockOpnamePeriod;
use App\Models\StockOpnameAssignment;
use App\Models\StockOpnamePeriod;
use App\Models\Branch;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class StockOpnameReview extends Component
{
    public ?int $selectedPeriodId = null;
    public ?int $selectedAssignmentId = null;
    public string $reviewNotes = '';
    public bool $showFinalizeModal = false;
    public ?int $periodIdToFinalize = null;

    public function mount(): void
    {
        abort_unless(auth()->check() && auth()->user()->hasRole([
            'owner', 'admin', 'super_admin', 'Owner', 'Admin', 'Super Admin',
        ]), 403);
    }

    public function selectPeriod(int $periodId): void
    {
        $period = $this->findReviewablePeriod($periodId);
        $this->selectedPeriodId = $period->id;
        $this->selectedAssignmentId = null;
        $this->reviewNotes = '';
        $this->closeFinalizeModal();
    }

    public function selectAssignment(int $assignmentId): void
    {
        $assignment = $this->findReviewableAssignment($assignmentId);
        $this->selectedPeriodId = $assignment->stock_opname_period_id;
        $this->selectedAssignmentId = $assignment->id;
        $this->reviewNotes = (string) $assignment->review_notes;
    }

    public function approveAssignment(int $assignmentId): void
    {
        $assignment = $this->findReviewableAssignment($assignmentId);

        app(\App\Actions\StockOpname\ApproveStockOpnameAssignment::class)->execute($assignment, $this->reviewNotes);

        $this->dispatchToast('success', 'Tugas disetujui', 'Hitungan lokasi disetujui dan menunggu finalisasi periode.');
        $this->selectedAssignmentId = null;
        $this->reviewNotes = '';
    }

    public function requestRevision(int $assignmentId): void
    {
        $validated = $this->validate([
            'reviewNotes' => ['required', 'string', 'min:3', 'max:2000'],
        ], [
            'reviewNotes.required' => 'Tuliskan alasan revisi agar karyawan tahu apa yang perlu diperbaiki.',
        ]);

        $assignment = $this->findReviewableAssignment($assignmentId);

        app(\App\Actions\StockOpname\RequestStockOpnameRevision::class)->execute($assignment, $validated['reviewNotes']);

        $this->dispatchToast('warning', 'Revisi dikirim', 'Karyawan dapat memperbaiki hitungan berdasarkan catatan pemeriksa.');
        $this->selectedAssignmentId = null;
    }

    public function openFinalizeModal(int $periodId): void
    {
        $this->periodIdToFinalize = $periodId;
        $this->showFinalizeModal = true;
    }

    public function closeFinalizeModal(): void
    {
        $this->showFinalizeModal = false;
        $this->periodIdToFinalize = null;
    }

    public function finalizePeriod(?int $periodId = null): void
    {
        $id = $periodId ?? $this->periodIdToFinalize ?? $this->selectedPeriodId;
        if (!$id) {
            return;
        }

        $period = $this->findReviewablePeriod($id);

        try {
            $opname = app(FinalizeStockOpnamePeriod::class)->execute($period);
        } catch (ValidationException $exception) {
            foreach ($exception->errors() as $field => $messages) {
                foreach ($messages as $message) {
                    $this->addError($field === 'period' ? 'reviewNotes' : $field, $message);
                }
            }
            $this->closeFinalizeModal();
            return;
        }

        $this->closeFinalizeModal();

        $this->dispatchToast(
            'success',
            'Periode berhasil difinalisasi',
            'Dokumen stok opname: ' . $opname->opname_number . '. Stok cabang dan jurnal telah diproses.'
        );

        $this->selectedPeriodId = $id;
        $this->selectedAssignmentId = null;
        $this->reviewNotes = '';
    }

    private function findReviewablePeriod(int $periodId): StockOpnamePeriod
    {
        $activeBranchId = \App\Helpers\BranchHelper::getActiveBranchId();

        return StockOpnamePeriod::query()
            ->with(['branch', 'assignments.preset', 'assignments.assignedUser', 'assignments.stockOpname.items.productVariant.product', 'finalStockOpname'])
            ->when($activeBranchId, fn ($q) => $q->where('branch_id', $activeBranchId))
            ->whereKey($periodId)
            ->whereIn('status', [
                StockOpnamePeriod::STATUS_ACTIVE,
                StockOpnamePeriod::STATUS_REVIEW,
                StockOpnamePeriod::STATUS_COMPLETED,
            ])
            ->firstOrFail();
    }

    private function findReviewableAssignment(int $assignmentId): StockOpnameAssignment
    {
        $activeBranchId = \App\Helpers\BranchHelper::getActiveBranchId();

        $assignment = StockOpnameAssignment::query()
            ->with(['period.branch', 'preset', 'assignedUser', 'stockOpname.items.productVariant.product'])
            ->when($activeBranchId, fn ($q) => $q->whereHas('period', fn ($sq) => $sq->where('branch_id', $activeBranchId)))
            ->whereKey($assignmentId)
            ->firstOrFail();

        abort_unless(in_array($assignment->period->status, [
            StockOpnamePeriod::STATUS_ACTIVE,
            StockOpnamePeriod::STATUS_REVIEW,
        ], true), 404);

        return $assignment;
    }

    private function dispatchToast(string $type, string $title, string $body): void
    {
        $this->dispatch('toast', [
            'type' => $type,
            'title' => $title,
            'body' => $body,
        ]);
    }

    public function render()
    {
        $activeBranchId = \App\Helpers\BranchHelper::getActiveBranchId();

        $periods = StockOpnamePeriod::query()
            ->with(['branch', 'assignments.preset', 'assignments.assignedUser', 'assignments.stockOpname.items', 'finalStockOpname'])
            ->when($activeBranchId, fn ($q) => $q->where('branch_id', $activeBranchId))
            ->whereIn('status', [
                StockOpnamePeriod::STATUS_ACTIVE,
                StockOpnamePeriod::STATUS_REVIEW,
                StockOpnamePeriod::STATUS_COMPLETED,
            ])
            ->orderByDesc('period')
            ->get();

        $selectedPeriod = $periods->firstWhere('id', $this->selectedPeriodId);
        if (!$selectedPeriod && $this->selectedPeriodId) {
            $selectedPeriod = $this->findReviewablePeriod($this->selectedPeriodId);
        }

        $selectedAssignment = $this->selectedAssignmentId
            ? $this->findReviewableAssignment($this->selectedAssignmentId)
            : null;

        $selectedLogoUrl = null;
        if ($activeBranchId) {
            $branch = Branch::find($activeBranchId);
            $selectedLogoUrl = ($branch && $branch->logo_path) ? Storage::url($branch->logo_path) : null;
        }

        return view('livewire.stock-opname-review', [
            'periods' => $periods,
            'selectedPeriod' => $selectedPeriod,
            'selectedAssignment' => $selectedAssignment,
            'selectedLogoUrl' => $selectedLogoUrl,
        ])->layout('layouts.pos', ['title' => 'Review Stok Opname']);
    }
}
