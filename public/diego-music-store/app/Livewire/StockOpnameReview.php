<?php

namespace App\Livewire;

use App\Actions\StockOpname\FinalizeStockOpnamePeriod;
use App\Models\StockOpnameAssignment;
use App\Models\StockOpnamePeriod;
use App\Models\Branch;
use Illuminate\Support\Facades\Storage;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class StockOpnameReview extends Component
{
    public ?int $selectedPeriodId = null;
    public ?int $selectedAssignmentId = null;
    public string $reviewNotes = '';

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

        if ($assignment->status !== StockOpnameAssignment::STATUS_SUBMITTED) {
            throw ValidationException::withMessages([
                'reviewNotes' => 'Hanya tugas yang sudah dikirim karyawan yang dapat disetujui.',
            ]);
        }

        DB::transaction(function () use ($assignment): void {
            $lockedPeriod = StockOpnamePeriod::query()
                ->whereKey($assignment->stock_opname_period_id)
                ->lockForUpdate()
                ->firstOrFail();

            if (!in_array($lockedPeriod->status, [
                StockOpnamePeriod::STATUS_ACTIVE,
                StockOpnamePeriod::STATUS_REVIEW,
            ], true)) {
                throw ValidationException::withMessages([
                    'reviewNotes' => 'Periode tidak lagi menerima review.',
                ]);
            }

            $lockedAssignment = StockOpnameAssignment::query()
                ->whereKey($assignment->id)
                ->where('stock_opname_period_id', $lockedPeriod->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedAssignment->status !== StockOpnameAssignment::STATUS_SUBMITTED) {
                throw ValidationException::withMessages([
                    'reviewNotes' => 'Status tugas berubah. Muat ulang halaman sebelum melanjutkan.',
                ]);
            }

            $lockedAssignment->update([
                'status' => StockOpnameAssignment::STATUS_COMPLETED,
                'reviewed_at' => now(),
                'reviewed_by' => auth()->id(),
                'review_notes' => trim($this->reviewNotes) !== '' ? trim($this->reviewNotes) : null,
            ]);
        });

        Notification::make()->title('Tugas disetujui')->success()->send();
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

        if ($assignment->status !== StockOpnameAssignment::STATUS_SUBMITTED) {
            throw ValidationException::withMessages([
                'reviewNotes' => 'Hanya tugas yang sudah dikirim karyawan yang dapat diminta revisi.',
            ]);
        }

        DB::transaction(function () use ($assignment, $validated): void {
            $period = StockOpnamePeriod::query()
                ->whereKey($assignment->stock_opname_period_id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($period->status === StockOpnamePeriod::STATUS_COMPLETED || $period->status === StockOpnamePeriod::STATUS_CANCELLED) {
                throw ValidationException::withMessages([
                    'reviewNotes' => 'Periode sudah ditutup dan tidak menerima revisi.',
                ]);
            }

            $locked = StockOpnameAssignment::query()
                ->whereKey($assignment->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($locked->status !== StockOpnameAssignment::STATUS_SUBMITTED) {
                throw ValidationException::withMessages([
                    'reviewNotes' => 'Status tugas berubah. Muat ulang halaman sebelum melanjutkan.',
                ]);
            }

            $locked->update([
                'status' => StockOpnameAssignment::STATUS_NEEDS_REVISION,
                'reviewed_at' => now(),
                'reviewed_by' => auth()->id(),
                'review_notes' => trim($validated['reviewNotes']),
            ]);

            $period->update(['status' => StockOpnamePeriod::STATUS_ACTIVE]);
        });

        Notification::make()->title('Revisi dikirim ke karyawan')->warning()->send();
        $this->selectedAssignmentId = null;
    }

    public function finalizePeriod(int $periodId): void
    {
        $period = $this->findReviewablePeriod($periodId);

        try {
            $opname = app(FinalizeStockOpnamePeriod::class)->execute($period);
        } catch (ValidationException $exception) {
            foreach ($exception->errors() as $field => $messages) {
                foreach ($messages as $message) {
                    $this->addError($field === 'period' ? 'reviewNotes' : $field, $message);
                }
            }
            return;
        }

        Notification::make()
            ->title('Periode berhasil difinalisasi')
            ->body('Dokumen stok opname: ' . $opname->opname_number . '. Stok cabang dan jurnal telah diproses.')
            ->success()
            ->send();

        $this->selectedPeriodId = $periodId;
        $this->selectedAssignmentId = null;
        $this->reviewNotes = '';
    }

    private function findReviewablePeriod(int $periodId): StockOpnamePeriod
    {
        return StockOpnamePeriod::query()
            ->with(['branch', 'assignments.preset', 'assignments.assignedUser', 'assignments.stockOpname.items.productVariant.product'])
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
        $assignment = StockOpnameAssignment::query()
            ->with(['period.branch', 'preset', 'assignedUser', 'stockOpname.items.productVariant.product'])
            ->whereKey($assignmentId)
            ->firstOrFail();

        abort_unless(in_array($assignment->period->status, [
            StockOpnamePeriod::STATUS_ACTIVE,
            StockOpnamePeriod::STATUS_REVIEW,
        ], true), 404);

        return $assignment;
    }

    public function render()
    {
        $periods = StockOpnamePeriod::query()
            ->with(['branch', 'assignments.preset', 'assignments.assignedUser', 'assignments.stockOpname.items'])
            ->whereIn('status', [
                StockOpnamePeriod::STATUS_ACTIVE,
                StockOpnamePeriod::STATUS_REVIEW,
                StockOpnamePeriod::STATUS_COMPLETED,
            ])
            ->when($this->selectedPeriodId, fn ($query) => $query->whereKey($this->selectedPeriodId))
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
        $activeBranchId = session('pos_active_branch_id') ?: auth()->user()?->employee?->branch_id;
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
