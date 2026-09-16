<?php

namespace App\Actions\Purchases;

use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;
use App\Models\PurchaseTransactionDetail;
use App\Models\Account;
use Illuminate\Support\Facades\DB;

class UpdatePurchaseReturn
{
    /**
     * Execute the update process for a draft Purchase Return.
     *
     * @param  PurchaseReturn  $purchaseReturn
     * @param  array  $data
     * @return PurchaseReturn
     */
    public function execute(PurchaseReturn $purchaseReturn, array $data): PurchaseReturn
    {
        return DB::transaction(function () use ($purchaseReturn, $data) {
            if ($purchaseReturn->status === 'posted') {
                throw new \Exception('Tidak dapat mengubah data retur pembelian yang sudah berstatus Posted.');
            }

            $pt = $purchaseReturn->purchaseTransaction;
            if ($pt->status === 'cancelled') {
                throw new \Exception('Tidak dapat melakukan retur pada transaksi pembelian yang sudah dibatalkan.');
            }

            $returnType = $data['return_type'] ?? $purchaseReturn->return_type;
            $refundAccountId = $data['refund_account_id'] ?? $purchaseReturn->refund_account_id;
            $replacementStatus = ($returnType === 'replacement') ? ($data['replacement_status'] ?? 'received') : 'none';

            if ($returnType === 'refund' && empty($refundAccountId)) {
                $refundAccountId = Account::where('code', '1-1000')->value('id') 
                    ?: Account::where('is_header', false)->where('classification', 'asset')->value('id');
            }

            if ($returnType === 'invoice_deduction' && strtolower($pt->purchase_type) !== 'kredit') {
                throw new \Exception('Metode Penyesuaian Faktur hanya berlaku untuk transaksi pembelian Kredit (Tempo). Untuk pembelian Tunai, pilih metode Refund Dana atau Tukar Guling.');
            }

            // Parse items if provided
            $rawItems = $data['return_items'] ?? $data['items'] ?? null;
            $newTotalAmount = $purchaseReturn->total_amount;

            if ($rawItems !== null) {
                $itemsToReturn = [];
                if (is_array($rawItems)) {
                    foreach ($rawItems as $key => $val) {
                        if (is_array($val) && isset($val['purchase_transaction_detail_id'])) {
                            $q = (int) ($val['quantity'] ?? 0);
                            if ($q > 0) {
                                $itemsToReturn[] = [
                                    'purchase_transaction_detail_id' => $val['purchase_transaction_detail_id'],
                                    'quantity'                       => $q,
                                ];
                            }
                        } else {
                            $q = (int) $val;
                            if ($q > 0) {
                                $itemsToReturn[] = [
                                    'purchase_transaction_detail_id' => $key,
                                    'quantity'                       => $q,
                                ];
                            }
                        }
                    }
                }

                if (empty($itemsToReturn)) {
                    throw new \Exception('Pilih minimal satu barang untuk diretur ke supplier.');
                }

                // Delete old items and recreate
                $purchaseReturn->items()->delete();
                $newTotalAmount = 0;

                foreach ($itemsToReturn as $item) {
                    $detail = PurchaseTransactionDetail::findOrFail($item['purchase_transaction_detail_id']);
                    $qty = $item['quantity'];

                    if ($qty > $detail->available_qty_for_return) {
                        throw new \Exception("Jumlah retur untuk barang {$detail->productVariant->name} ({$qty}) melebihi sisa yang dapat diretur ({$detail->available_qty_for_return}).");
                    }

                    $unitPrice = $detail->price;
                    $lineTotal = $unitPrice * $qty;
                    $newTotalAmount += $lineTotal;

                    PurchaseReturnItem::create([
                        'purchase_return_id'             => $purchaseReturn->id,
                        'purchase_transaction_detail_id' => $detail->id,
                        'product_variant_id'             => $detail->product_variant_id,
                        'quantity'                       => $qty,
                        'unit_price'                     => $unitPrice,
                        'total_price'                    => $lineTotal,
                    ]);
                }
            }

            if ($returnType === 'invoice_deduction' && $newTotalAmount > $pt->getRemainingUnpaidAmount()) {
                $remaining = number_format($pt->getRemainingUnpaidAmount(), 0, ',', '.');
                $totalFormatted = number_format($newTotalAmount, 0, ',', '.');
                throw new \Exception("Nilai retur (Rp {$totalFormatted}) melebihi sisa tagihan hutang faktur ini (Rp {$remaining}). Silakan pilih metode Refund Kas/Bank atau Saldo Deposit Supplier.");
            }

            $purchaseReturn->update([
                'total_amount'       => $newTotalAmount,
                'return_type'        => $returnType,
                'refund_account_id'  => $refundAccountId,
                'replacement_status' => $replacementStatus,
                'reason'             => $data['reason'] ?? $purchaseReturn->reason,
            ]);

            $targetStatus = $data['status'] ?? 'draft';
            if ($targetStatus === 'posted') {
                app(PostPurchaseReturn::class)->execute($purchaseReturn->fresh(['items', 'purchaseTransaction']));
            }

            return $purchaseReturn->fresh();
        });
    }
}
