<?php

namespace App\Actions\SalesQueue;

use App\Models\SalesDutyLog;
use App\Models\SalesQueueMember;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class StartSalesDuty
{
    /**
     * Start a sales rep's serving duty (move to on_duty and log started_at).
     *
     * @param  SalesQueueMember  $member
     * @param  int|null  $cashSessionId
     * @param  string|null  $notes
     * @return SalesDutyLog
     */
    public function execute(SalesQueueMember $member, ?int $cashSessionId = null, ?string $notes = null): SalesDutyLog
    {
        if ($member->status === 'on_duty') {
            throw new InvalidArgumentException("Sales {$member->user->name} sudah dalam status sedang melayani.");
        }

        return DB::transaction(function () use ($member, $cashSessionId, $notes) {
            $now = now();

            // Create duty log
            $dutyLog = SalesDutyLog::create([
                'branch_id' => $member->branch_id,
                'cash_session_id' => $cashSessionId,
                'user_id' => $member->user_id,
                'queue_member_id' => $member->id,
                'started_at' => $now,
                'status' => 'serving',
                'notes' => $notes,
                'created_by' => Auth::id(),
            ]);

            // Update queue member status
            $member->update([
                'status' => 'on_duty',
                'duty_started_at' => $now,
                'current_duty_log_id' => $dutyLog->id,
            ]);

            return $dutyLog;
        });
    }
}
