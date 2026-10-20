<?php

namespace App\Filament\Pages;

use App\Actions\StockOpname\ApproveStockOpnameAssignment;
use App\Actions\StockOpname\FinalizeStockOpnamePeriod;
use App\Actions\StockOpname\RequestStockOpnameRevision;
use App\Helpers\BranchHelper;
use App\Models\StockOpnameAssignment;
use App\Models\StockOpnamePeriod;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;

class StockOpnameReview extends Page
{
    protected static \UnitEnum|string|null $navigationGroup = 'Inventori';

    protected static ?string $navigationLabel = 'Review Stok Opname';

    protected static ?string $title = 'Review Stok Opname';

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;

    protected static ?int $navigationSort = 4;

    protected string $view = 'filament.pages.stock-opname-review';

    public ?int $selectedPeriodId = null;
    public ?int $selectedAssignmentId = null;
    public string $reviewNotes = '';

    public static function canAccess(): bool
    {
        $user = auth()->user();
        return (bool) ($user && $user->hasRole(['owner', 'admin', 'super_admin', 'Owner', 'Admin', 'Super Admin']));
    }

    public function mount(): void
    {
        $periodId = request()->query('period_id');
        if ($periodId && is_numeric($periodId)) {
            $period = StockOpnamePeriod::find($periodId);
            if ($period && in_array($period->status, [
                StockOpnamePeriod::STATUS_ACTIVE,
                StockOpnamePeriod::STATUS_REVIEW,
                StockOpnamePeriod::STATUS_COMPLETED,
            ], true)) {
                $this->selectedPeriodId = (int) $periodId;
            }
        }

        if (!$this->selectedPeriodId) {
            $firstPeriod = $this->periods->first();
            if ($firstPeriod) {
                $this->selectedPeriodId = $firstPeriod->id;
            }
        }
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

        try {
            app(ApproveStockOpnameAssignment::class)->execute($assignment, $this->reviewNotes);

            Notification::make()
                ->title('Tugas Lokasi Disetujui')
                ->body('Hasil hitungan lokasi telah disetujui. Siap difinalisasi setelah semua lokasi tuntas.')
                ->success()
                ->send();

            $this->selectedAssignmentId = null;
            $this->reviewNotes = '';
        } catch (ValidationException $e) {
            Notification::make()
                ->title('Gagal Menyetujui Tugas')
                ->body(collect($e->errors())->flatten()->first() ?? 'Terjadi kesalahan.')
                ->danger()
                ->send();
        }
    }

    public function requestRevision(int $assignmentId): void
    {
        $this->validate([
            'reviewNotes' => ['required', 'string', 'min:3', 'max:2000'],
        ], [
            'reviewNotes.required' => 'Tuliskan alasan revisi agar karyawan mengetahui poin perbaikan.',
            'reviewNotes.min' => 'Catatan revisi minimal 3 karakter.',
        ]);

        $assignment = $this->findReviewableAssignment($assignmentId);

        try {
            app(RequestStockOpnameRevision::class)->execute($assignment, $this->reviewNotes);

            Notification::make()
                ->title('Permintaan Revisi Dikirim')
                ->body('Tugas telah dikembalikan ke karyawan untuk diperbaiki hitungannya.')
                ->warning()
                ->send();

            $this->selectedAssignmentId = null;
            $this->reviewNotes = '';
        } catch (ValidationException $e) {
            Notification::make()
                ->title('Gagal Meminta Revisi')
                ->body(collect($e->errors())->flatten()->first() ?? 'Terjadi kesalahan.')
                ->danger()
                ->send();
        }
    }

    public function finalizePeriod(int $periodId): void
    {
        $period = $this->findReviewablePeriod($periodId);

        try {
            $opname = app(FinalizeStockOpnamePeriod::class)->execute($period);

            Notification::make()
                ->title('Periode Berhasil Difinalisasi')
                ->body('Dokumen: ' . $opname->opname_number . '. Stok cabang dan jurnal penyesuaian telah diproses.')
                ->success()
                ->send();

            $this->selectedPeriodId = $periodId;
            $this->selectedAssignmentId = null;
            $this->reviewNotes = '';

            $this->dispatch('close-modal', id: 'finalize-period-modal');
        } catch (ValidationException $exception) {
            $this->dispatch('close-modal', id: 'finalize-period-modal');

            Notification::make()
                ->title('Gagal Memfinalisasi Periode')
                ->body(collect($exception->errors())->flatten()->first() ?? 'Pastikan seluruh varian dan lokasi telah disetujui.')
                ->danger()
                ->send();
        }
    }

    public function getPeriodsProperty(): Collection
    {
        $query = StockOpnamePeriod::query()
            ->with(['branch', 'assignments.preset', 'assignments.assignedUser', 'assignments.stockOpname.items', 'finalStockOpname'])
            ->whereIn('status', [
                StockOpnamePeriod::STATUS_ACTIVE,
                StockOpnamePeriod::STATUS_REVIEW,
                StockOpnamePeriod::STATUS_COMPLETED,
            ]);

        $user = auth()->user();
        if ($user && !$user->hasRole(['owner', 'admin', 'super_admin', 'Owner', 'Admin', 'Super Admin'])) {
            $query->whereIn('branch_id', $user->branches()->pluck('branches.id'));
        }

        $activeBranchId = BranchHelper::getActiveBranchId();
        if ($activeBranchId) {
            $query->where('branch_id', $activeBranchId);
        }

        return $query->orderByDesc('period')->get();
    }

    public function getSelectedPeriodProperty(): ?StockOpnamePeriod
    {
        if (!$this->selectedPeriodId) {
            return null;
        }

        return $this->periods->firstWhere('id', $this->selectedPeriodId)
            ?? $this->findReviewablePeriod($this->selectedPeriodId);
    }

    public function getSelectedAssignmentProperty(): ?StockOpnameAssignment
    {
        if (!$this->selectedAssignmentId) {
            return null;
        }

        return $this->findReviewableAssignment($this->selectedAssignmentId);
    }

    private function findReviewablePeriod(int $periodId): StockOpnamePeriod
    {
        return StockOpnamePeriod::query()
            ->with(['branch', 'assignments.preset', 'assignments.assignedUser', 'assignments.stockOpname.items.productVariant.product', 'finalStockOpname'])
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
            StockOpnamePeriod::STATUS_COMPLETED,
        ], true), 404);

        return $assignment;
    }
}
