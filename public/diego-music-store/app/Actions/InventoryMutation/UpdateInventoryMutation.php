<?php

namespace App\Actions\InventoryMutation;

use App\Models\InventoryMutation;
use App\Models\InventoryMutationItem;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class UpdateInventoryMutation
{
    /**
     * Execute the action to update an Inventory Mutation.
     *
     * @param  InventoryMutation  $mutation
     * @param  array<string, mixed>  $data
     * @return InventoryMutation
     */
    public function execute(InventoryMutation $mutation, array $data): InventoryMutation
    {
        return DB::transaction(function () use ($mutation, $data) {
            // Check status: cannot edit if already transit or received
            if ($mutation->status !== 'draft') {
                throw new InvalidArgumentException('Mutasi yang sudah dikirim (transit) atau diterima tidak dapat diubah.');
            }

            // 1. Update header details
            $mutation->update([
                'sender_branch_id' => $data['sender_branch_id'] ?? $mutation->sender_branch_id,
                'receiver_branch_id' => $data['receiver_branch_id'] ?? $mutation->receiver_branch_id,
                'mutation_date' => $data['mutation_date'] ?? $mutation->mutation_date,
                'notes' => $data['notes'] ?? $mutation->notes,
            ]);

            // 2. Sync items (delete existing and recreate)
            if (isset($data['items'])) {
                $mutation->items()->delete();
                foreach ($data['items'] as $item) {
                    InventoryMutationItem::create([
                        'inventory_mutation_id' => $mutation->id,
                        'product_variant_id' => $item['product_variant_id'],
                        'quantity' => intval($item['quantity']),
                    ]);
                }
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
