<?php

namespace App\Http\Controllers\BackOffice;

use App\Http\Controllers\Controller;
use App\Models\SalesInvoice;
use Illuminate\Contracts\View\View;

class SalesInvoicePrintController extends Controller
{
    /**
     * Show the print document for a specific Sales Invoice.
     *
     * @param SalesInvoice $salesInvoice
     * @return View
     */
    public function show(SalesInvoice $salesInvoice): View
    {
        $user = auth()->user();
        abort_unless(\App\Helpers\PermissionHelper::canAccessBackofficeFeature($user, 'bo.sales_invoices.view'), 403);

        $activeBranchId = \App\Helpers\BranchHelper::getActiveBranchId();
        abort_unless((int) $salesInvoice->branch_id === (int) $activeBranchId, 403);

        $salesInvoice->load([
            'customer',
            'branch',
            'items.productVariant.product.unit',
        ]);

        return view('backoffice.sales-invoices.print', [
            'invoice' => $salesInvoice,
        ]);
    }
}
