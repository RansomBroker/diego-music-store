<?php

namespace App\Actions\StockOpname;

use App\Models\StockOpnamePeriod;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ActivateStockOpnamePeriod
{
    public function execute(StockOpnamePeriod $period): StockOpnamePeriod
    {
        return DB::transaction(function () use ($period): StockOpnamePeriod {
            $lockedPeriod = StockOpnamePeriod::query()
                ->lockForUpdate()
                ->findOrFail($period->id);

            if ($lockedPeriod->status !== StockOpnamePeriod::STATUS_DRAFT) {
                throw ValidationException::withMessages([
                    'status' => 'Hanya periode draft yang dapat diaktifkan.',
                ]);
            }

            if (!$lockedPeriod->assignments()->exists()) {
                throw ValidationException::withMessages([
                    'assignments' => 'Tambahkan minimal satu lokasi dan karyawan sebelum mengaktifkan periode.',
                ]);
            }

            $lockedPeriod->update(['status' => StockOpnamePeriod::STATUS_ACTIVE]);

            return $lockedPeriod->fresh();
        });
    }
}
