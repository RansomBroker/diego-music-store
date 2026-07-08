<?php

namespace App\Actions\Procurement;

use App\Models\PurchaseTransaction;
use App\Models\PurchaseTransactionDetail;
use App\Models\ProductBranchStock;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class PostPurchaseTransaction
{
    /**
     * Execute the action to post a Purchase Transaction.
     *
     * @param  PurchaseTransaction  $pt
     * @return PurchaseTransaction
     */
    public function execute(PurchaseTransaction $pt): PurchaseTransaction
    {
        return DB::transaction(function () use ($pt) {
            if ($pt->status !== 'draft') {
                throw new InvalidArgumentException('Hanya transaksi draf yang dapat diposting.');
            }

            // 1. Generate Journal Number for bookkeeping simulation
            $date = now()->format('Ymd');
            $prefix = 'JV-' . $date . '-';
            $lastJournal = PurchaseTransaction::where('journal_no', 'like', $prefix . '%')
                ->orderBy('journal_no', 'desc')
                ->first();

            if ($lastJournal) {
                $lastNum = intval(substr($lastJournal->journal_no, strlen($prefix)));
                $nextNum = str_pad($lastNum + 1, 4, '0', STR_PAD_LEFT);
            } else {
                $nextNum = '0001';
            }
            $journalNo = $prefix . $nextNum;

            // Update Transaction Header status
            $pt->update([
                'status' => 'posted',
                'posted_at' => now(),
                'journal_no' => $journalNo,
            ]);

            // 2. Process stock changes and weighted average HPP
            $destinationBranchId = $pt->warehouse_id ?? $pt->branch_id;
            $details = $pt->details;

            // Total goods subtotal (DPP) to distribute header costs proportionally by value
            $totalGoodsSubtotal = $details->sum(fn($d) => ($d->qty_received * $d->price) - $d->discount);
            
            $shippingCostToAttribute = (($pt->shipping_borne_by ?? 'self_direct') !== 'supplier') ? $pt->shipping_cost : 0;
            $headerCostToAttribute = $shippingCostToAttribute + $pt->other_cost - $pt->discount;

            foreach ($details as $detail) {
                $totalQty = $detail->qty_received + ($detail->qty_bonus ?? 0);
                if ($totalQty <= 0) {
                    continue;
                }

                // Fetch conversion factor for chosen unit
                $unit = $detail->unit;
                $conversionFactor = ($unit && $unit->base_unit_id) ? $unit->conversion_factor : 1;
                $baseQtyReceived = $totalQty * $conversionFactor;

                // Find or create branch stock record
                $stock = ProductBranchStock::firstOrCreate(
                    [
                        'branch_id' => $destinationBranchId,
                        'product_variant_id' => $detail->product_variant_id,
                    ],
                    [
                        'stock' => 0,
                        'hpp' => 0,
                    ]
                );

                $oldStock = $stock->stock;
                $oldHpp = $stock->hpp;

                // Attribute header costs proportionally based on value
                $detailValue = ($detail->qty_received * $detail->price) - $detail->discount;
                $attributedHeaderCost = ($totalGoodsSubtotal > 0)
                    ? (int) round(($detailValue / $totalGoodsSubtotal) * $headerCostToAttribute)
                    : 0;

                // Net cost for this line item (inclusive of tax & discount + attributed header cost)
                $itemNetCost = $detail->subtotal + $attributedHeaderCost;
                
                // Unit cost for this transaction line (converted to base unit cost)
                $unitCost = (int) round($itemNetCost / $baseQtyReceived);

                // Compute new weighted average HPP
                $newStockQty = $oldStock + $baseQtyReceived;
                
                if ($newStockQty > 0) {
                    $newHpp = (int) round((($oldStock * $oldHpp) + ($baseQtyReceived * $unitCost)) / $newStockQty);
                } else {
                    $newHpp = $unitCost;
                }

                // Update stock qty and new calculated HPP
                $stock->update([
                    'stock' => $newStockQty,
                    'hpp' => $newHpp,
                ]);

                // Update master cost price & pricing tiers if update_cost_price checkbox is checked
                if ($detail->update_cost_price) {
                    $detail->productVariant->update([
                        'cost_price' => $detail->price,
                        'hpp' => $detail->price,
                    ]);

                    $followingTiers = \App\Models\PricingTier::where('price_follows_hpp', true)->get();
                    foreach ($followingTiers as $tier) {
                        $tierPrice = \App\Models\ProductTierPrice::firstOrCreate([
                            'product_variant_id' => $detail->product_variant_id,
                            'pricing_tier_id' => $tier->id,
                        ], [
                            'price' => 0
                        ]);
                        $tierPrice->update([
                            'price' => $detail->price,
                        ]);
                    }
                }

                // Create StockMovement IN record
                \App\Models\StockMovement::create([
                    'product_variant_id' => $detail->product_variant_id,
                    'branch_id' => $destinationBranchId,
                    'type' => 'in',
                    'quantity' => $baseQtyReceived,
                    'original_quantity' => $totalQty,
                    'unit_id' => $detail->unit_id,
                    'unit_cost' => $unitCost,
                    'hpp' => $newHpp,
                    'reference_type' => 'Purchase',
                    'reference_id' => $pt->id,
                ]);

                // Also sync HPP back to product variant master if destination is Central/Backoffice branch
                $centralBranch = \App\Models\Branch::where('name', 'like', '%Pusat%')
                    ->orWhere('name', 'like', '%Back Office%')
                    ->first();
                if ($centralBranch && $destinationBranchId == $centralBranch->id) {
                    $detail->productVariant->update([
                        'hpp' => $newHpp,
                    ]);
                }
            }

            // 3. Handle debt creation (jika Kredit)
            if ($pt->purchase_type === 'Kredit') {
                $pt->supplier->increment('outstanding_debt', $pt->grand_total);
            }

            // 4. Update parent Purchase Order status if linked
            if ($pt->po_id && ($po = $pt->purchaseOrder)) {
                // Determine if PO is fully received.
                // Sum all qty_received from POSTED transactions for this PO.
                $receivedDetails = PurchaseTransactionDetail::whereHas('purchaseTransaction', function ($query) use ($po) {
                        $query->where('po_id', $po->id)->where('status', 'posted');
                    })->get();

                $receivedBaseQuantities = [];
                foreach ($receivedDetails as $recDetail) {
                    $cFactor = ($recDetail->unit && $recDetail->unit->base_unit_id) ? $recDetail->unit->conversion_factor : 1;
                    $recBaseQty = $recDetail->qty_received * $cFactor;
                    $receivedBaseQuantities[$recDetail->product_variant_id] = ($receivedBaseQuantities[$recDetail->product_variant_id] ?? 0) + $recBaseQty;
                }

                $isFullyReceived = true;
                foreach ($po->items as $item) {
                    $itemCFactor = ($item->unit && $item->unit->base_unit_id) ? $item->unit->conversion_factor : 1;
                    $poItemBaseQty = $item->quantity * $itemCFactor;
                    
                    $receivedBase = $receivedBaseQuantities[$item->product_variant_id] ?? 0;
                    if ($receivedBase < $poItemBaseQty) {
                        $isFullyReceived = false;
                        break;
                    }
                }

                if ($isFullyReceived) {
                    $po->update(['status' => 'closed']);
                }
            }

            // Create automatic journal entry
            $journalEntry = \App\Models\JournalEntry::create([
                'branch_id' => $pt->branch_id,
                'entry_no' => $journalNo,
                'date' => $pt->transaction_date,
                'description' => "Pembelian otomatis: No. Transaksi {$pt->transaction_no}",
                'reference_type' => 'Purchase',
                'reference_id' => $pt->id,
                'status' => 'posted',
                'created_by' => \Illuminate\Support\Facades\Auth::id() ?? $pt->created_by,
                'posted_at' => now(),
                'posted_by' => \Illuminate\Support\Facades\Auth::id() ?? $pt->created_by,
            ]);

            // Resolve Account IDs helper
            $resolveAccount = function($code, $defaultName = 'Default Account') {
                return \App\Models\Account::firstOrCreate(
                    ['code' => $code],
                    [
                        'name' => $defaultName,
                        'classification' => str_starts_with($code, '1') ? 'Asset' : (str_starts_with($code, '2') ? 'Liability' : 'Expense'),
                        'is_active' => true,
                    ]
                )->id;
            };

            // 1. Debits: Persediaan
            foreach ($details as $detail) {
                if ($detail->qty_received <= 0) continue;
                
                $inventoryAccId = $detail->productVariant->product->inventory_account_id 
                    ?? $resolveAccount('1-1300', 'Persediaan Barang Dagang');

                $detailBaseCost = ($detail->qty_received * $detail->price) - $detail->discount;
                $detailValue = ($detail->qty_received * $detail->price) - $detail->discount;
                $attributedHeaderCost = ($totalGoodsSubtotal > 0)
                    ? (int) round(($detailValue / $totalGoodsSubtotal) * $headerCostToAttribute)
                    : 0;

                \App\Models\JournalItem::create([
                    'journal_entry_id' => $journalEntry->id,
                    'account_id' => $inventoryAccId,
                    'debit' => $detailBaseCost + $attributedHeaderCost,
                    'credit' => 0,
                    'notes' => "Persediaan untuk " . $detail->productVariant->sku,
                ]);
            }

            // 2. Debit: Tax (if tax_amount > 0)
            if ($pt->tax_amount > 0) {
                $taxAccId = \App\Models\Account::where('code', '1-1500')
                    ->orWhere('name', 'like', '%PPN%')
                    ->first()?->id ?? $resolveAccount('1-1500', 'PPN Masukan');

                \App\Models\JournalItem::create([
                    'journal_entry_id' => $journalEntry->id,
                    'account_id' => $taxAccId,
                    'debit' => $pt->tax_amount,
                    'credit' => 0,
                    'notes' => "PPN Masukan Pembelian",
                ]);
            }

            // 3. Credit: Hutang Biaya Kirim Belum Ditagih (if third-party logistics and shipping_cost > 0)
            if (($pt->shipping_borne_by ?? 'self_direct') === 'third_party' && $pt->shipping_cost > 0) {
                $shippingAccId = $resolveAccount('2-1500', 'Hutang Biaya Kirim Belum Ditagih');
                \App\Models\JournalItem::create([
                    'journal_entry_id' => $journalEntry->id,
                    'account_id' => $shippingAccId,
                    'debit' => 0,
                    'credit' => $pt->shipping_cost,
                    'notes' => "Akrual Ongkir Pihak Ke-3: " . ($pt->shipping_carrier_name ?? 'Ekspedisi'),
                ]);
            }

            // 4. Credit: PPh amount (if pph_amount > 0)
            if ($pt->pph_amount > 0) {
                $pphAccId = \App\Models\Account::where('code', '2-1100')
                    ->orWhere('name', 'like', '%PPh%')
                    ->first()?->id ?? $resolveAccount('2-1100', 'Hutang PPh');

                \App\Models\JournalItem::create([
                    'journal_entry_id' => $journalEntry->id,
                    'account_id' => $pphAccId,
                    'debit' => 0,
                    'credit' => $pt->pph_amount,
                    'notes' => "Hutang PPh Pembelian",
                ]);
            }

            // 5. Credit: Kas/Bank or Hutang Dagang (Net Grand Total)
            if ($pt->purchase_type === 'Kredit') {
                $payAccId = $resolveAccount('2-1000', 'Hutang Dagang');
            } else {
                $payAccId = $resolveAccount('1-1000', 'Kas Utama');
            }

            \App\Models\JournalItem::create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $payAccId,
                'debit' => 0,
                'credit' => $pt->grand_total,
                'notes' => $pt->purchase_type === 'Kredit' ? "Hutang Supplier" : "Kas/Bank Tunai",
            ]);

            return $pt;
        });
    }
}
