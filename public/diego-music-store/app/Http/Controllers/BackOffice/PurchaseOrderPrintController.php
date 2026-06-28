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
        $purchaseOrder->load([
            'supplier',
            'items.productVariant.product.unit'
        ]);

        return view('backoffice.purchase-orders.print', compact('purchaseOrder'));
    }
}
