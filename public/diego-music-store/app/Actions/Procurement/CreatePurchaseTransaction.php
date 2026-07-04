<?php

namespace App\Actions\Procurement;

use App\Models\PurchaseTransaction;
use App\Models\PurchaseTransactionDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class CreatePurchaseTransaction
{
    /**
     * Execute the action to create a Purchase Transaction.
     *
     * @param  array<string, mixed>  $data
     * @return PurchaseTransaction
     */
    public function execute(array $data): PurchaseTransaction
    {
        return DB::transaction(function () use ($data) {
            $items = $data['items'] ?? [];

            $transactionNo = $data['transaction_no'] ?? null;
            if (empty($transactionNo)) {
                $transactionNo = PurchaseTransaction::generateTransactionNo();
            }

            // 1. Create the base record
            $pt = PurchaseTransaction::create([
                'transaction_no' => $transactionNo,
                'transaction_date' => $data['transaction_date'],
                'po_id' => $data['po_id'] ?? null,
                'supplier_id' => $data['supplier_id'],
                'branch_id' => $data['branch_id'],
                'warehouse_id' => $data['warehouse_id'] ?? $data['branch_id'], // fallback to branch if warehouse is empty
                'purchase_type' => $data['purchase_type'],
                'invoice_number' => $data['invoice_number'] ?? null,
                'delivery_note_number' => $data['delivery_note_number'] ?? null,
                'invoice_date' => $data['invoice_date'] ?? null,
                'due_date' => $data['due_date'] ?? null,
                'status' => 'draft', // always create as draft first
                'created_by' => Auth::id(),
            ]);

            $subtotal = 0;
            $taxAmount = 0;

            // 2. Add details
            foreach ($items as $item) {
                $qtyReceived = intval($item['qty_received'] ?? 0);
                $price = intval($item['price'] ?? 0);
                $disc = intval($item['discount'] ?? 0);
                $taxRate = intval($item['tax_rate'] ?? 0);

                $itemSubtotalBeforeTax = ($qtyReceived * $price) - $disc;
                $itemTaxAmount = (int) round($itemSubtotalBeforeTax * ($taxRate / 100));
                $itemSubtotal = $itemSubtotalBeforeTax + $itemTaxAmount;

                PurchaseTransactionDetail::create([
                    'purchase_transaction_id' => $pt->id,
                    'product_variant_id' => $item['product_variant_id'],
                    'qty_po' => isset($item['qty_po']) ? intval($item['qty_po']) : null,
                    'qty_received' => $qtyReceived,
                    'unit_id' => $item['unit_id'] ?? null,
                    'price' => $price,
                    'discount' => $disc,
                    'tax_rate' => $taxRate,
                    'tax_amount' => $itemTaxAmount,
                    'subtotal' => $itemSubtotal,
                ]);

                $subtotal += $itemSubtotalBeforeTax;
                $taxAmount += $itemTaxAmount;
            }

            // 3. Compute totals
            $discount = intval($data['discount'] ?? 0);
            $shippingCost = intval($data['shipping_cost'] ?? 0);
            $otherCost = intval($data['other_cost'] ?? 0);
            $pphAmount = intval($data['pph_amount'] ?? 0); // PPh amount is passed or calculated
            $shippingBorneBy = $data['shipping_borne_by'] ?? 'self_direct';
            $shippingCarrierName = $data['shipping_carrier_name'] ?? null;

            $shippingCostInGrandTotal = ($shippingBorneBy === 'self_direct') ? $shippingCost : 0;
            $grandTotal = $subtotal - $discount + $taxAmount + $shippingCostInGrandTotal + $otherCost - $pphAmount;

            $pt->update([
                'subtotal' => $subtotal,
                'discount' => $discount,
                'shipping_cost' => $shippingCost,
                'other_cost' => $otherCost,
                'shipping_borne_by' => $shippingBorneBy,
                'shipping_carrier_name' => $shippingCarrierName,
                'tax_amount' => $taxAmount,
                'pph_amount' => $pphAmount,
                'grand_total' => $grandTotal,
            ]);

            // 4. Trigger posting if status was set to posted in input
            if (($data['status'] ?? 'draft') === 'posted') {
                app(PostPurchaseTransaction::class)->execute($pt);
            }

            return $pt;
        });
    }
}
