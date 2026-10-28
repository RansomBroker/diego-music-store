<?php

namespace App\Actions\SalesQueue;

use App\Models\SalesQueueMember;
use Illuminate\Support\Facades\DB;

class ReorderSalesQueue
{
    /**
     * Reorder waiting queue members.
     *
     * @param  array<int>  $orderedMemberIds
     * @return void
     */
    public function execute(array $orderedMemberIds): void
    {
        DB::transaction(function () use ($orderedMemberIds) {
            foreach ($orderedMemberIds as $index => $id) {
                SalesQueueMember::where('id', $id)->update([
                    'queue_order' => $index + 1,
                ]);
            }
        });
    }
}
