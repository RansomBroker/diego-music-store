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

    /**
     * Show the printable draft thermal receipt.
     */
    public function showDraft(Request $request)
    {
        if (!Auth::check()) {
            abort(403, 'Unauthorized.');
        }

        $data = json_decode(base64_decode($request->query('data')), true);
        if (!$data) {
            abort(400, 'Invalid data.');
        }

        $branch = \App\Models\Branch::find($data['branch_id']);
        $customerName = $data['customer_name'] ?? 'Umum / Walk-in';
        
        $items = [];
        foreach ($data['cart'] as $item) {
            $variant = \App\Models\ProductVariant::with('product')->find($item['variant_id']);
            if ($variant) {
                $items[] = (object)[
                    'quantity' => $item['qty'],
                    'unit_price' => $item['price'],
                    'total_price' => ($item['price'] * $item['qty']) - ($item['discount_amount'] ?? 0),
                    'discount_amount' => $item['discount_amount'] ?? 0,
                    'variant' => $variant,
                ];
            }
        }

        $draft = (object)[
            'invoice_number' => 'DRAFT-' . now()->format('YmdHis'),
            'created_at' => now(),
            'branch' => $branch,
            'customer_name' => $customerName,
            'salesRep' => Auth::user(),
            'items' => $items,
            'subtotal' => collect($items)->sum(fn($i) => ($i->unit_price * $i->quantity) - $i->discount_amount),
            'discount_amount' => intval($data['discount_amount'] ?? 0) + intval($data['point_discount_amount'] ?? 0),
            'tax_amount' => intval($data['tax_amount'] ?? 0),
            'grand_total' => intval($data['grand_total'] ?? 0),
            'payment_method' => 'PREVIEW TAGIHAN',
        ];

        return view('pos.receipt-draft', [
            'draft' => $draft,
        ]);
    }
}
