<?php

namespace App\Actions\Sales;

use App\Models\SalesInvoice;
use App\Models\SalesInvoiceItem;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class UpdateSalesInvoice
{
    /**
     * Execute the action to update a Sales Invoice.
     *
     * @param SalesInvoice $invoice
     * @param array<string, mixed> $data
     * @return SalesInvoice
     */
    public function execute(SalesInvoice $invoice, array $data): SalesInvoice
    {
        if ($invoice->status === 'posted') {
            throw new InvalidArgumentException("Faktur Penjualan yang sudah diposting tidak dapat diubah.");
        }

        return DB::transaction(function () use ($invoice, $data) {
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

            $discountType = $data['discount_type'] ?? $invoice->discount_type;
            $discountVal = intval($data['discount_value'] ?? 0);
            $discountAmount = ($discountType === 'percent')
                ? (int) round(($subtotal * $discountVal) / 100)
                : $discountVal;

            $afterDiscount = max(0, $subtotal - $discountAmount);
            $taxRate = intval($data['tax_rate'] ?? 0);
            $taxAmount = (int) round(($afterDiscount * $taxRate) / 100);
            $shippingCost = intval($data['shipping_cost'] ?? 0);
            $grandTotal = $afterDiscount + $taxAmount + $shippingCost;

            $invNo = !empty($data['invoice_number']) ? $data['invoice_number'] : ($invoice->invoice_number ?: SalesInvoice::generateInvoiceNumber());

            $invoice->update([
                'customer_id' => $data['customer_id'] ?? $invoice->customer_id,
                'branch_id' => $data['branch_id'] ?? $invoice->branch_id,
                'sales_quotation_id' => $data['sales_quotation_id'] ?? $invoice->sales_quotation_id,
                'invoice_number' => $invNo,
                'invoice_date' => $data['invoice_date'] ?? $invoice->invoice_date,
                'due_date' => $data['due_date'] ?? $invoice->due_date,
                'payment_type' => $data['payment_type'] ?? $invoice->payment_type,
                'subtotal' => $subtotal,
                'discount_type' => $discountType,
                'discount_value' => $discountVal,
                'discount_amount' => $discountAmount,
                'tax_rate' => $taxRate,
                'tax_amount' => $taxAmount,
                'shipping_cost' => $shippingCost,
                'grand_total' => $grandTotal,
                'notes' => $data['notes'] ?? $invoice->notes,
            ]);

            $invoice->items()->delete();

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

            if (($data['status'] ?? 'draft') === 'posted') {
                app(PostSalesInvoice::class)->execute($invoice);
            }

            return $invoice;
        });
    }
}
