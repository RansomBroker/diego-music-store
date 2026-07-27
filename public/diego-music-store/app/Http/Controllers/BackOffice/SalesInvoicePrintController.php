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
