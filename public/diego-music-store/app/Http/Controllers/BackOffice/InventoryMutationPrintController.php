<?php

namespace App\Http\Controllers\BackOffice;

use App\Http\Controllers\Controller;
use App\Models\InventoryMutation;
use Illuminate\Contracts\View\View;

class InventoryMutationPrintController extends Controller
{
    /**
     * Show the print document for a specific Inventory Mutation.
     *
     * @param  InventoryMutation  $inventoryMutation
     * @return View
     */
    public function show(InventoryMutation $inventoryMutation): View
    {
        $user = auth()->user();
        abort_unless($user && $user->hasRole(['owner', 'super_admin', 'Owner', 'Super Admin']), 403);

        $activeBranchId = \App\Helpers\BranchHelper::getActiveBranchId();
        abort_unless((int) $inventoryMutation->sender_branch_id === (int) $activeBranchId || (int) $inventoryMutation->receiver_branch_id === (int) $activeBranchId, 403);

        $inventoryMutation->load([
            'senderBranch',
            'receiverBranch',
            'items.productVariant.product.unit',
        ]);

        return view('backoffice.inventory-mutations.print', [
            'mutation' => $inventoryMutation,
        ]);
    }
}
