<?php

namespace App\Actions\CashAdvance;

use App\Models\EmployeeCashAdvance;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ApproveCashAdvance
{
    /**
     * Approve cash advance request and mark remaining balance active.
     *
     * @param int $advanceId
     * @param User|null $approver
     * @param string|null $notes
     * @return EmployeeCashAdvance
     */
    public function execute(int $advanceId, ?User $approver = null, ?string $notes = null): EmployeeCashAdvance
    {
        return DB::transaction(function () use ($advanceId, $approver, $notes) {
            $advance = EmployeeCashAdvance::findOrFail($advanceId);

            if ($advance->status !== 'pending') {
                throw new \Exception('Hanya pengajuan kasbon berstatus Pending yang dapat disetujui.');
            }

            $advance->update([
                'status' => 'approved',
                'approved_by' => $approver?->id,
                'approved_at' => now(),
                'disbursed_at' => now(),
                'remaining_amount' => $advance->amount - $advance->paid_amount,
                'notes' => $notes ?: $advance->notes,
            ]);

            return $advance;
        });
    }
}
