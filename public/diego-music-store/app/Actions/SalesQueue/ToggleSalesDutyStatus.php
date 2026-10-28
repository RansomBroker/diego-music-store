<?php

namespace App\Actions\SalesQueue;

use App\Models\SalesQueueMember;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ToggleSalesDutyStatus
{
    /**
     * Toggle a member's availability between waiting and off_duty (istirahat).
     *
     * @param  SalesQueueMember  $member
     * @param  string|null  $targetStatus
     * @return SalesQueueMember
     */
    public function execute(SalesQueueMember $member, ?string $targetStatus = null): SalesQueueMember
    {
        if ($member->status === 'on_duty') {
            throw new InvalidArgumentException("Sales {$member->user->name} sedang melayani. Harap selesaikan sesi melayani terlebih dahulu.");
        }

        return DB::transaction(function () use ($member, $targetStatus) {
            $newStatus = $targetStatus ?: ($member->status === 'off_duty' ? 'waiting' : 'off_duty');

            if ($newStatus === 'waiting' && $member->status === 'off_duty') {
                // When returning from off_duty, place at the back of the queue
                $maxOrder = SalesQueueMember::where('branch_id', $member->branch_id)
                    ->where('date', $member->date)
                    ->where('status', 'waiting')
                    ->max('queue_order') ?? 0;

                $member->update([
                    'status' => 'waiting',
                    'queue_order' => $maxOrder + 1,
                ]);
            } else {
                $member->update([
                    'status' => $newStatus,
                ]);
            }

            // Re-normalize waiting queue numbers
            $waitingMembers = SalesQueueMember::where('branch_id', $member->branch_id)
                ->where('date', $member->date)
                ->where('status', 'waiting')
                ->where('is_active', true)
                ->orderBy('queue_order', 'asc')
                ->get();

            $order = 1;
            foreach ($waitingMembers as $waiting) {
                $waiting->update(['queue_order' => $order++]);
            }

            return $member->fresh();
        });
    }
}
