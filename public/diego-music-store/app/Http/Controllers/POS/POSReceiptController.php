<?php

namespace App\Http\Controllers\POS;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class POSReceiptController extends Controller
{
    /**
     * Show the printable thermal receipt for a specific sale.
     */
    public function show(Sale $sale)
    {
        // Enforce that only authenticated users can view/print POS receipts
        if (!Auth::check()) {
            abort(403, 'Unauthorized.');
        }

        // Load relations
        $sale->load(['branch', 'customer', 'salesRep', 'items.variant.product']);

        return view('pos.receipt', [
            'sale' => $sale,
        ]);
    }
}
