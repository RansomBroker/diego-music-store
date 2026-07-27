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
