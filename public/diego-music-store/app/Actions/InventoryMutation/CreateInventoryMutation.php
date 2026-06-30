<?php

namespace App\Actions\InventoryMutation;

use App\Models\InventoryMutation;
use App\Models\InventoryMutationItem;
use Illuminate\Support\Facades\DB;

class CreateInventoryMutation
{
    /**
     * Execute the action to create an Inventory Mutation.
     *
     * @param  array<string, mixed>  $data
     * @return InventoryMutation
     */
    public function execute(array $data): InventoryMutation
    {
        return DB::transaction(function () use ($data) {
            $items = $data['items'] ?? [];

            $mutationNumber = $data['mutation_number'] ?? null;
            if (empty($mutationNumber)) {
                $mutationNumber = InventoryMutation::generateMutationNumber();
            }

            // 1. Create the base record as draft
            $mutation = InventoryMutation::create([
                'sender_branch_id' => $data['sender_branch_id'],
                'receiver_branch_id' => $data['receiver_branch_id'],
                'mutation_number' => $mutationNumber,
                'mutation_date' => $data['mutation_date'],
                'status' => 'draft',
                'notes' => $data['notes'] ?? null,
            ]);

            // 2. Create mutation items
            foreach ($items as $item) {
                InventoryMutationItem::create([
                    'inventory_mutation_id' => $mutation->id,
                    'product_variant_id' => $item['product_variant_id'],
                    'quantity' => intval($item['quantity']),
                ]);
            }

            // 3. Process status transition if requested
            $targetStatus = $data['status'] ?? 'draft';
            if ($targetStatus === 'transit') {
                app(ProcessInventoryMutationTransit::class)->execute($mutation);
            } elseif ($targetStatus === 'received') {
                // Must transit first to deduct sender's stock
                app(ProcessInventoryMutationTransit::class)->execute($mutation);
                app(ProcessInventoryMutationReceived::class)->execute($mutation);
            }

            return $mutation;
        });
    }
}
