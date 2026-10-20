<?php

namespace App\Actions\StockOpname;

use App\Models\StockOpnameAssignment;
use App\Models\StockOpnamePeriod;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ApproveStockOpnameAssignment
{
    public function execute(StockOpnameAssignment $assignment, ?string $reviewNotes = null): StockOpnameAssignment
    {
        if ($assignment->status !== StockOpnameAssignment::STATUS_SUBMITTED) {
            throw ValidationException::withMessages([
                'reviewNotes' => 'Hanya tugas yang sudah dikirim karyawan yang dapat disetujui.',
            ]);
        }

        return DB::transaction(function () use ($assignment, $reviewNotes): StockOpnameAssignment {
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

            $cleanNotes = $reviewNotes !== null ? trim($reviewNotes) : null;

            $lockedAssignment->update([
                'status' => StockOpnameAssignment::STATUS_COMPLETED,
                'reviewed_at' => now(),
                'reviewed_by' => auth()->id(),
                'review_notes' => $cleanNotes !== '' ? $cleanNotes : null,
            ]);

            return $lockedAssignment->fresh();
        });
    }
}
