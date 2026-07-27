<?php

namespace App\Actions\Sales;

use App\Models\SalesInvoice;
use App\Models\SalesInvoiceItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CreateSalesInvoice
{
    /**
     * Execute the action to create a Sales Invoice.
     *
     * @param array<string, mixed> $data
     * @return SalesInvoice
     */
    public function execute(array $data): SalesInvoice
    {
        return DB::transaction(function () use ($data) {
            $invoiceNumber = $data['invoice_number'] ?? null;
            if (empty($invoiceNumber)) {
                $invoiceNumber = SalesInvoice::generateInvoiceNumber();
            }

            $items = $data['items'] ?? [];
            $subtotal = 0;
            foreach ($items as $item) {
                $qty = intval($item['quantity'] ?? 1);
                $price = intval($item['price'] ?? 0);
                $discVal = intval($item['discount_value'] ?? 0);
                $discType = $item['discount_type'] ?? 'fixed';

                $itemBaseSubtotal = $qty * $price;
                $discAmount = ($discType === 'percent')
                    ? (int) round(($itemBaseSubtotal * $discVal) / 100)
                    : $discVal;

                $subtotal += max(0, $itemBaseSubtotal - $discAmount);
            }

            $discountType = $data['discount_type'] ?? 'fixed';
            $discountVal = intval($data['discount_value'] ?? 0);
            $discountAmount = ($discountType === 'percent')
                ? (int) round(($subtotal * $discountVal) / 100)
                : $discountVal;

            $afterDiscount = max(0, $subtotal - $discountAmount);
            $taxRate = intval($data['tax_rate'] ?? 0);
            $taxAmount = (int) round(($afterDiscount * $taxRate) / 100);
            $shippingCost = intval($data['shipping_cost'] ?? 0);
            $grandTotal = $afterDiscount + $taxAmount + $shippingCost;

            $invoice = SalesInvoice::create([
                'customer_id' => $data['customer_id'],
                'branch_id' => $data['branch_id'],
                'sales_quotation_id' => $data['sales_quotation_id'] ?? null,
                'created_by' => Auth::id(),
                'invoice_number' => $invoiceNumber,
                'invoice_date' => $data['invoice_date'] ?? now()->toDateString(),
                'due_date' => $data['due_date'] ?? null,
                'payment_type' => $data['payment_type'] ?? 'Tunai',
                'status' => 'draft',
                'subtotal' => $subtotal,
                'discount_type' => $discountType,
                'discount_value' => $discountVal,
                'discount_amount' => $discountAmount,
                'tax_rate' => $taxRate,
                'tax_amount' => $taxAmount,
                'shipping_cost' => $shippingCost,
                'grand_total' => $grandTotal,
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($items as $item) {
                $qty = intval($item['quantity'] ?? 1);
                $price = intval($item['price'] ?? 0);
                $discVal = intval($item['discount_value'] ?? 0);
                $discType = $item['discount_type'] ?? 'fixed';

                $itemBaseSubtotal = $qty * $price;
                $discAmount = ($discType === 'percent')
                    ? (int) round(($itemBaseSubtotal * $discVal) / 100)
                    : $discVal;
                $itemSubtotal = max(0, $itemBaseSubtotal - $discAmount);

                SalesInvoiceItem::create([
                    'sales_invoice_id' => $invoice->id,
                    'product_variant_id' => $item['product_variant_id'],
                    'unit_id' => $item['unit_id'] ?? null,
                    'quantity' => $qty,
                    'price' => $price,
                    'discount_type' => $discType,
                    'discount_value' => $discVal,
                    'discount_amount' => $discAmount,
                    'subtotal' => $itemSubtotal,
                    'notes' => $item['notes'] ?? null,
                ]);
            }

            // Post automatically if requested in status data
            if (($data['status'] ?? 'draft') === 'posted') {
                app(PostSalesInvoice::class)->execute($invoice);
            }

            return $invoice;
        });
    }
}
