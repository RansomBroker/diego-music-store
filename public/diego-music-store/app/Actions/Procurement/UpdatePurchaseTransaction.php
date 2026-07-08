<?php

namespace App\Actions\Procurement;

use App\Models\PurchaseTransaction;
use App\Models\PurchaseTransactionDetail;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class UpdatePurchaseTransaction
{
    /**
     * Execute the action to update a Purchase Transaction.
     *
     * @param  PurchaseTransaction  $pt
     * @param  array<string, mixed>  $data
     * @return PurchaseTransaction
     */
    public function execute(PurchaseTransaction $pt, array $data): PurchaseTransaction
    {
        return DB::transaction(function () use ($pt, $data) {
            $enableTax = (bool)($data['enable_tax'] ?? false);
            $enableItemDisc = true;
            $itemDiscTypeGlobal = $data['item_discount_type'] ?? 'fixed';
            $globalTaxRate = $enableTax ? intval($data['tax_rate'] ?? 0) : 0;

            // Guard clause to prevent editing posted/cancelled transactions
            if ($pt->status !== 'draft') {
                throw new InvalidArgumentException('Transaksi Pembelian yang sudah diposting atau dibatalkan tidak dapat diubah.');
            }

            // 1. Update header details
            $pt->update([
                'transaction_no' => $data['transaction_no'] ?? $pt->transaction_no,
                'transaction_date' => $data['transaction_date'],
                'po_id' => $data['po_id'] ?? null,
                'supplier_id' => $data['supplier_id'],
                'branch_id' => $data['branch_id'],
                'warehouse_id' => $data['warehouse_id'] ?? $data['branch_id'],
                'purchase_type' => $data['purchase_type'],
                'invoice_number' => $data['invoice_number'] ?? null,
                'delivery_note_number' => $data['delivery_note_number'] ?? null,
                'invoice_date' => $data['invoice_date'] ?? null,
                'due_date' => $data['due_date'] ?? null,
                'tax_invoice_no' => $data['tax_invoice_no'] ?? null,
                'enable_tax' => $enableTax,
                'enable_item_discount' => $enableItemDisc,
                'item_discount_type' => $itemDiscTypeGlobal,
            ]);

            // 2. Sync details
            $pt->details()->delete();

            $items = $data['items'] ?? [];
            $subtotal = 0;
            $taxAmount = 0;

            foreach ($items as $item) {
                $qtyReceived = intval($item['qty_received'] ?? 0);
                $price = intval($item['price'] ?? 0);
                $itemDiscType = $enableItemDisc ? $itemDiscTypeGlobal : 'fixed';
                $itemDiscVal = $enableItemDisc ? intval($item['discount_value'] ?? 0) : 0;
                $disc = $itemDiscType === 'percent' ? (int) round(($qtyReceived * $price) * ($itemDiscVal / 100)) : $itemDiscVal;
                $taxRate = $globalTaxRate;

                $itemSubtotalBeforeTax = ($qtyReceived * $price) - $disc;
                $itemTaxAmount = (int) round($itemSubtotalBeforeTax * ($taxRate / 100));
                $itemSubtotal = $itemSubtotalBeforeTax + $itemTaxAmount;

                $variant = \App\Models\ProductVariant::find($item['product_variant_id']);
                $unitId = $item['unit_id'] ?? ($variant?->product?->unit_id);

                PurchaseTransactionDetail::create([
                    'purchase_transaction_id' => $pt->id,
                    'product_variant_id' => $item['product_variant_id'],
                    'qty_po' => isset($item['qty_po']) ? intval($item['qty_po']) : null,
                    'qty_received' => $qtyReceived,
                    'unit_id' => $unitId,
                    'price' => $price,
                    'discount' => $disc,
                    'discount_type' => $itemDiscType,
                    'discount_value' => $itemDiscVal,
                    'tax_rate' => $taxRate,
                    'tax_amount' => $itemTaxAmount,
                    'subtotal' => $itemSubtotal,
                    'update_cost_price' => (bool)($item['update_cost_price'] ?? false),
                    'qty_bonus' => isset($item['qty_bonus']) ? intval($item['qty_bonus']) : 0,
                ]);

                $subtotal += $itemSubtotalBeforeTax;
                $taxAmount += $itemTaxAmount;
            }

            // 3. Compute totals
            $discHeaderType = $data['discount_type'] ?? 'fixed';
            $discHeaderVal = intval($data['discount_value'] ?? 0);
            $discount = $discHeaderType === 'percent' ? (int) round($subtotal * ($discHeaderVal / 100)) : $discHeaderVal;
            $shippingCost = intval($data['shipping_cost'] ?? 0);
            $otherCost = $enableTax ? intval($data['other_cost'] ?? 0) : 0;
            $pphAmount = $enableTax ? intval($data['pph_amount'] ?? 0) : 0;
            $shippingBorneBy = $data['shipping_borne_by'] ?? 'self_direct';
            $shippingCarrierName = $data['shipping_carrier_name'] ?? null;

            $shippingCostInGrandTotal = ($shippingBorneBy === 'self_direct') ? $shippingCost : 0;
            $grandTotal = $subtotal - $discount + $taxAmount + $shippingCostInGrandTotal + $otherCost - $pphAmount;

            $pt->update([
                'subtotal' => $subtotal,
                'discount' => $discount,
                'discount_type' => $discHeaderType,
                'discount_value' => $discHeaderVal,
                'shipping_cost' => $shippingCost,
                'other_cost' => $otherCost,
                'shipping_borne_by' => $shippingBorneBy,
                'shipping_carrier_name' => $shippingCarrierName,
                'tax_amount' => $taxAmount,
                'pph_amount' => $pphAmount,
                'grand_total' => $grandTotal,
            ]);

            // 4. Trigger posting if status transitioned to posted
            if (($data['status'] ?? 'draft') === 'posted') {
                app(PostPurchaseTransaction::class)->execute($pt);
            }

            return $pt;
        });
    }
}
