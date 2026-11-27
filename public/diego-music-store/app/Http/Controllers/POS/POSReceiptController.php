<?php

namespace App\Http\Controllers\POS;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class POSReceiptController extends Controller
{
    /**
     * Show the printable thermal receipt or invoice for a specific sale.
     */
    public function show(Sale $sale, Request $request)
    {
        // Enforce that only authenticated users can view/print POS receipts
        if (!Auth::check()) {
            abort(403, 'Unauthorized.');
        }

        // Enforce permission to view/print POS receipts
        abort_unless(\App\Helpers\PermissionHelper::canAccessFrontofficeFeature(Auth::user(), ['fo.pos.print', 'fo.transactions.view']), 403);

        abort_unless((int) $sale->branch_id === (int) \App\Helpers\BranchHelper::getActiveBranchId(), 403);

        // Load relations
        $sale->load(['branch', 'customer', 'salesRep', 'creator', 'cashSession.user', 'payments.paymentMethod.parent', 'items.variant.product', 'items.salesRep']);

        if ($request->query('format') === 'invoice') {
            $draft = (object)[
                'invoice_number' => $sale->invoice_number,
                'created_at' => $sale->created_at,
                'branch' => $sale->branch,
                'customer_name' => $sale->customer ? $sale->customer->name : 'Umum / Walk-in',
                'cashier_name' => $sale->receipt_cashier_name,
                'salesRep' => $sale->salesRep ?? Auth::user(),
                'items' => $sale->items,
                'subtotal' => (int) $sale->subtotal,
                'discount_amount' => (int) $sale->discount_amount,
                'tax_amount' => (int) $sale->tax_amount,
                'grand_total' => (int) $sale->grand_total,
                'payment_method' => $sale->payment_method_display ?: 'LUNAS / CASH',
            ];

            return view('pos.receipt-draft', [
                'draft' => $draft,
                'format' => 'invoice',
            ]);
        }

        return view('pos.receipt', [
            'sale' => $sale,
        ]);
    }

    /**
     * Return clean thermal plain-text receipt formatted according to branch ReceiptSetting
     * for Direct Print Agent (ESC/POS).
     */
    public function rawText(Sale $sale)
    {
        if (! Auth::check()) {
            abort(403, 'Unauthorized.');
        }

        abort_unless(\App\Helpers\PermissionHelper::canAccessFrontofficeFeature(Auth::user(), ['fo.pos.print', 'fo.transactions.view']), 403);
        abort_unless((int) $sale->branch_id === (int) \App\Helpers\BranchHelper::getActiveBranchId(), 403);

        $sale->load(['branch', 'customer', 'salesRep', 'creator', 'cashSession.user', 'payments.paymentMethod.parent', 'items.variant.product', 'items.salesRep']);

        $setting = \App\Models\ReceiptSetting::where('branch_id', $sale->branch_id)->first();
        $rawText = \App\Helpers\ReceiptFormatterHelper::formatThermalText($sale, $setting);

        return response($rawText, 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
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

        abort_unless(\App\Helpers\PermissionHelper::canAccessFrontofficeFeature(Auth::user(), 'fo.pos.print'), 403);

        $data = json_decode(base64_decode($request->query('data')), true);
        if (!$data) {
            abort(400, 'Invalid data.');
        }

        $activeBranchId = \App\Helpers\BranchHelper::getActiveBranchId();
        abort_unless((int) ($data['branch_id'] ?? 0) === (int) $activeBranchId, 403);
        abort_unless(isset($data['cart']) && is_array($data['cart']), 400, 'Invalid cart data.');

        $branch = \App\Models\Branch::findOrFail($activeBranchId);
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

        $invoiceNumber = !empty($data['invoice_number']) 
            ? $data['invoice_number'] 
            : 'DRAFT-' . now()->format('YmdHis');

        $draft = (object)[
            'invoice_number' => $invoiceNumber,
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

        $format = $request->query('format', 'bill');

        return view('pos.receipt-draft', [
            'draft' => $draft,
            'format' => $format,
        ]);
    }
}
