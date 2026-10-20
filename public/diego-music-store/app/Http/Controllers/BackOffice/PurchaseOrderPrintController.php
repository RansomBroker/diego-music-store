<?php

namespace App\Http\Controllers\BackOffice;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use Illuminate\Contracts\View\View;

class PurchaseOrderPrintController extends Controller
{
    /**
     * Show the print document for a specific Purchase Order.
     *
     * @param  PurchaseOrder  $purchaseOrder
     * @return View
     */
    public function show(PurchaseOrder $purchaseOrder): View
    {
        $user = auth()->user();
        abort_unless($user && $user->hasRole(['owner', 'super_admin', 'Owner', 'Super Admin']), 403);

        $activeBranchId = \App\Helpers\BranchHelper::getActiveBranchId();
        abort_unless((int) $purchaseOrder->branch_id === (int) $activeBranchId, 403);

        $purchaseOrder->load([
            'supplier',
            'items.productVariant.product.unit'
        ]);

        return view('backoffice.purchase-orders.print', compact('purchaseOrder'));
    }
}
