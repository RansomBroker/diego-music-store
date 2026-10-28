<?php

namespace App\Actions\SalesQueue;

use App\Models\SalesDutyLog;
use App\Models\SalesQueueMember;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class CompleteSalesDuty
{
    /**
     * Complete a sales rep's serving duty and place them at the back of the waiting queue.
     *
     * @param  SalesQueueMember  $member
     * @param  int|null  $saleId
     * @param  float|int|null  $closingAmount
     * @param  string|null  $notes
     * @return SalesDutyLog|null
     */
    public function execute(
        SalesQueueMember $member,
        ?int $saleId = null,
        $closingAmount = null,
        ?string $notes = null
    ): ?SalesDutyLog {
        if ($member->status !== 'on_duty') {
            throw new InvalidArgumentException("Sales {$member->user->name} tidak sedang dalam status melayani.");
        }

        return DB::transaction(function () use ($member, $saleId, $closingAmount, $notes) {
            $now = now();
            $dutyLog = $member->currentDutyLog ?: SalesDutyLog::where('queue_member_id', $member->id)
                ->where('status', 'serving')
                ->latest()
                ->first();

            if ($dutyLog) {
                $startedAt = $dutyLog->started_at ?? $member->duty_started_at ?? $now;
                $duration = max(1, (int) $now->diffInSeconds($startedAt));

                $updateData = [
                    'ended_at' => $now,
                    'duration_seconds' => $duration,
                    'status' => 'completed',
                ];

                if ($saleId) {
                    $updateData['sale_id'] = $saleId;
                }
                if ($closingAmount !== null) {
                    $updateData['closing_amount'] = (float) $closingAmount;
                }
                if ($notes) {
                    $updateData['notes'] = $notes;
                }

                $dutyLog->update($updateData);
            }

            // Find current maximum queue_order for this branch and date
            $maxOrder = SalesQueueMember::where('branch_id', $member->branch_id)
                ->where('date', $member->date)
                ->max('queue_order') ?? 0;

            // Move this member to the end of the line
            $member->update([
                'status' => 'waiting',
                'queue_order' => $maxOrder + 1,
                'duty_started_at' => null,
                'current_duty_log_id' => null,
                'turns_count' => $member->turns_count + 1,
            ]);

            // Re-normalize waiting queue numbers for clean display (1, 2, 3...)
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

            return $dutyLog;
        });
    }
}
