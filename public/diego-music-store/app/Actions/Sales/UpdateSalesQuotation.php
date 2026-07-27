<?php

namespace App\Actions\Sales;

use App\Models\SalesQuotation;
use App\Models\SalesQuotationItem;
use Illuminate\Support\Facades\DB;

class UpdateSalesQuotation
{
    /**
     * Execute the action to update a Sales Quotation.
     *
     * @param SalesQuotation $quotation
     * @param array<string, mixed> $data
     * @return SalesQuotation
     */
    public function execute(SalesQuotation $quotation, array $data): SalesQuotation
    {
        return DB::transaction(function () use ($quotation, $data) {
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

            $discountType = $data['discount_type'] ?? $quotation->discount_type;
            $discountVal = intval($data['discount_value'] ?? 0);
            $discountAmount = ($discountType === 'percent')
                ? (int) round(($subtotal * $discountVal) / 100)
                : $discountVal;

            $afterDiscount = max(0, $subtotal - $discountAmount);
            $taxRate = intval($data['tax_rate'] ?? 0);
            $taxAmount = (int) round(($afterDiscount * $taxRate) / 100);
            $grandTotal = $afterDiscount + $taxAmount;

            $quotationNo = !empty($data['quotation_number']) ? $data['quotation_number'] : ($quotation->quotation_number ?: SalesQuotation::generateQuotationNumber());

            $quotation->update([
                'customer_id' => $data['customer_id'] ?? $quotation->customer_id,
                'branch_id' => $data['branch_id'] ?? $quotation->branch_id,
                'quotation_number' => $quotationNo,
                'quotation_date' => $data['quotation_date'] ?? $quotation->quotation_date,
                'valid_until' => $data['valid_until'] ?? $quotation->valid_until,
                'status' => $data['status'] ?? $quotation->status,
                'subtotal' => $subtotal,
                'discount_type' => $discountType,
                'discount_value' => $discountVal,
                'discount_amount' => $discountAmount,
                'tax_rate' => $taxRate,
                'tax_amount' => $taxAmount,
                'grand_total' => $grandTotal,
                'notes' => $data['notes'] ?? $quotation->notes,
            ]);

            $quotation->items()->delete();

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

                SalesQuotationItem::create([
                    'sales_quotation_id' => $quotation->id,
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

            return $quotation;
        });
    }
}
