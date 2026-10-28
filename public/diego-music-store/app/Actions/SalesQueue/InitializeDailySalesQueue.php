<?php

namespace App\Actions\SalesQueue;

use App\Models\Branch;
use App\Models\SalesQueueMember;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class InitializeDailySalesQueue
{
    /**
     * Initialize or sync the daily sales queue for a specific branch.
     *
     * @param  int  $branchId
     * @param  string|null  $date
     * @return Collection
     */
    public function execute(int $branchId, ?string $date = null): Collection
    {
        $targetDate = $date ?? now()->toDateString();

        return DB::transaction(function () use ($branchId, $targetDate) {
            // Find existing queue members for this branch and date
            $existingMembers = SalesQueueMember::with('user')
                ->where('branch_id', $branchId)
                ->where('date', $targetDate)
                ->orderBy('queue_order', 'asc')
                ->get();

            $existingUserIds = $existingMembers->pluck('user_id')->toArray();

            // Find sales users assigned to this branch
            $branchSalesUsers = User::role('sales')
                ->where('is_active', true)
                ->where(function ($query) use ($branchId) {
                    $query->whereHas('branches', function ($b) use ($branchId) {
                        $b->where('branches.id', $branchId);
                    })->orWhereHas('employee', function ($e) use ($branchId) {
                        $e->where('employees.branch_id', $branchId);
                    });
                })
                ->orderBy('name', 'asc')
                ->get();

            // Fallback: If no users mapped specifically to branch, get all active sales users
            if ($branchSalesUsers->isEmpty()) {
                $branchSalesUsers = User::role('sales')
                    ->where('is_active', true)
                    ->orderBy('name', 'asc')
                    ->get();
            }

            $currentMaxOrder = $existingMembers->max('queue_order') ?? 0;

            foreach ($branchSalesUsers as $salesUser) {
                if (!in_array($salesUser->id, $existingUserIds)) {
                    $currentMaxOrder++;
                    SalesQueueMember::create([
                        'branch_id' => $branchId,
                        'user_id' => $salesUser->id,
                        'queue_order' => $currentMaxOrder,
                        'status' => 'waiting',
                        'duty_started_at' => null,
                        'current_duty_log_id' => null,
                        'turns_count' => 0,
                        'is_active' => true,
                        'date' => $targetDate,
                    ]);
                }
            }

            return SalesQueueMember::with(['user', 'currentDutyLog'])
                ->where('branch_id', $branchId)
                ->where('date', $targetDate)
                ->orderBy('queue_order', 'asc')
                ->get();
        });
    }
}
