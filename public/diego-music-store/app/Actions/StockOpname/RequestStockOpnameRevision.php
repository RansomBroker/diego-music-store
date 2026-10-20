<?php

namespace App\Actions\StockOpname;

use App\Models\StockOpnameAssignment;
use App\Models\StockOpnamePeriod;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RequestStockOpnameRevision
{
    public function execute(StockOpnameAssignment $assignment, string $reviewNotes): StockOpnameAssignment
    {
        $cleanNotes = trim($reviewNotes);
        if ($cleanNotes === '') {
            throw ValidationException::withMessages([
                'reviewNotes' => 'Tuliskan alasan revisi agar karyawan tahu apa yang perlu diperbaiki.',
            ]);
        }

        if ($assignment->status !== StockOpnameAssignment::STATUS_SUBMITTED) {
            throw ValidationException::withMessages([
                'reviewNotes' => 'Hanya tugas yang sudah dikirim karyawan yang dapat diminta revisi.',
            ]);
        }

        return DB::transaction(function () use ($assignment, $cleanNotes): StockOpnameAssignment {
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
                'review_notes' => $cleanNotes,
            ]);

            $period->update(['status' => StockOpnamePeriod::STATUS_ACTIVE]);

            return $locked->fresh();
        });
    }
}
