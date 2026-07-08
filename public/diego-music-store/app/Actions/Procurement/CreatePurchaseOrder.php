<?php

namespace App\Actions\Procurement;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class CreatePurchaseOrder
{
    /**
     * Execute the action to create a Purchase Order.
     *
     * @param  array<string, mixed>  $data
     * @return PurchaseOrder
     */
    public function execute(array $data): PurchaseOrder
    {
        return DB::transaction(function () use ($data) {
            $enableTax = (bool)($data['enable_tax'] ?? false);
            $enableItemDisc = true;
            $itemDiscTypeGlobal = $data['item_discount_type'] ?? 'fixed';
            $items = $data['items'] ?? [];
            $taxMode = 'GLOBAL';
            $globalTaxRate = $enableTax ? intval($data['tax_rate'] ?? 0) : 0;
            
            $poNumber = $data['po_number'] ?? null;
            if (empty($poNumber)) {
                $poNumber = PurchaseOrder::generatePoNumber();
            }

            // 1. First, create the PO with empty totals so we get the ID
            $po = PurchaseOrder::create([
                'supplier_id' => $data['supplier_id'],
                'branch_id' => $data['branch_id'] ?? null,
                'created_by_id' => Auth::id(), // set current logged in user as creator
                'po_number' => $poNumber,
                'currency' => $data['currency'] ?? 'IDR',
                'payment_term' => $data['payment_term'] ?? null,
                'order_date' => $data['order_date'],
                'eta_date' => $data['eta_date'] ?? null,
                'status' => $data['status'] ?? 'draft',
                'notes' => $data['notes'] ?? null,
                'enable_tax' => $enableTax,
                'enable_item_discount' => $enableItemDisc,
                'item_discount_type' => $itemDiscTypeGlobal,
            ]);

            // 2. Loop through items, calculate subtotal and tax, and create items
            $totalAmount = 0; // Subtotal header
            $totalTaxAmount = 0;

            foreach ($items as $item) {
                $qty = intval($item['quantity'] ?? 0);
                $price = intval($item['price'] ?? 0);
                $itemDiscType = $enableItemDisc ? $itemDiscTypeGlobal : 'fixed';
                $itemDiscVal = $enableItemDisc ? intval($item['discount_value'] ?? 0) : 0;
                $discItem = $itemDiscType === 'percent' ? (int) round(($qty * $price) * ($itemDiscVal / 100)) : $itemDiscVal;
                
                // Subtotal before tax
                $subtotalBeforeTax = ($qty * $price) - $discItem;
                
                // Tax rate selection
                $itemTaxRate = $globalTaxRate;
                $itemTaxAmount = (int) round($subtotalBeforeTax * ($itemTaxRate / 100));
                
                $itemSubtotal = $subtotalBeforeTax + $itemTaxAmount;

                $variant = \App\Models\ProductVariant::find($item['product_variant_id']);
                $unitId = $item['unit_id'] ?? ($variant?->product?->unit_id);

                PurchaseOrderItem::create([
                    'purchase_order_id' => $po->id,
                    'product_variant_id' => $item['product_variant_id'],
                    'unit_id' => $unitId,
                    'quantity' => $qty,
                    'price' => $price,
                    'discount_amount' => $discItem,
                    'discount_type' => $itemDiscType,
                    'discount_value' => $itemDiscVal,
                    'tax_rate' => $itemTaxRate,
                    'tax_amount' => $itemTaxAmount,
                    'subtotal' => $itemSubtotal,
                    'notes' => $item['notes'] ?? null,
                ]);

                $totalAmount += $subtotalBeforeTax;
                $totalTaxAmount += $itemTaxAmount;
            }

            // 3. Calculate grand total and update PO
            $discHeaderType = $data['discount_type'] ?? 'fixed';
            $discHeaderVal = intval($data['discount_value'] ?? 0);
            $discHeader = $discHeaderType === 'percent' ? (int) round($totalAmount * ($discHeaderVal / 100)) : $discHeaderVal;
            $otherCost = intval($data['other_cost'] ?? 0); // Ongkir / biaya lain
            $shippingBorneBy = $data['shipping_borne_by'] ?? 'self_direct';
            $shippingCarrierName = $data['shipping_carrier_name'] ?? null;
            
            $shippingCostInGrandTotal = ($shippingBorneBy === 'self_direct') ? $otherCost : 0;
            $grandTotal = $totalAmount - $discHeader + $totalTaxAmount + $shippingCostInGrandTotal;

            $po->update([
                'total_amount' => $totalAmount,
                'discount_amount' => $discHeader,
                'discount_type' => $discHeaderType,
                'discount_value' => $discHeaderVal,
                'other_cost' => $otherCost,
                'shipping_borne_by' => $shippingBorneBy,
                'shipping_carrier_name' => $shippingCarrierName,
                'tax_mode' => $taxMode,
                'tax_rate' => $taxMode === 'GLOBAL' ? $globalTaxRate : 0,
                'tax_amount' => $totalTaxAmount,
                'grand_total' => $grandTotal,
            ]);

            return $po;
        });
    }
}
