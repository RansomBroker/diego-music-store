<?php

namespace App\Actions\Purchases;

use App\Models\PurchaseTransaction;
use App\Models\PurchaseTransactionDetail;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;
use App\Models\ProductBranchStock;
use App\Models\StockMovement;
use App\Models\JournalEntry;
use App\Models\JournalItem;
use App\Models\Account;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class CreatePurchaseReturn
{
    /**
     * Execute the Purchase Return process.
     *
     * @param  array  $data
     * @return PurchaseReturn
     */
    public function execute(array $data): PurchaseReturn
    {
        return DB::transaction(function () use ($data) {
            $purchaseId = $data['purchase_transaction_id'];
            $pt = PurchaseTransaction::findOrFail($purchaseId);

            if ($pt->status === 'cancelled') {
                throw new \Exception('Tidak dapat melakukan retur pada transaksi pembelian yang sudah dibatalkan.');
            }

            $items = $data['items'] ?? [];
            if (empty($items)) {
                throw new \Exception('Pilih minimal satu barang untuk diretur ke supplier.');
            }

            $returnNo = PurchaseReturn::generateReturnNo();
            $status = $data['status'] ?? 'posted';
            $returnType = $data['return_type'] ?? (strtolower($pt->purchase_type) === 'kredit' ? 'invoice_deduction' : 'refund');
            $refundAccountId = $data['refund_account_id'] ?? null;
            $replacementStatus = ($returnType === 'replacement') ? ($data['replacement_status'] ?? 'received') : 'none';

            if ($returnType === 'refund' && empty($refundAccountId)) {
                $refundAccountId = Account::where('code', '1-1000')->value('id') 
                    ?: Account::where('is_header', false)->where('classification', 'asset')->value('id');
            }

            if ($returnType === 'invoice_deduction' && strtolower($pt->purchase_type) !== 'kredit') {
                throw new \Exception('Metode Penyesuaian Faktur hanya berlaku untuk transaksi pembelian Kredit (Tempo). Untuk pembelian Tunai, pilih metode Refund Dana atau Tukar Guling.');
            }

            $purchaseReturn = PurchaseReturn::create([
                'purchase_transaction_id' => $pt->id,
                'branch_id'               => $pt->branch_id,
                'supplier_id'             => $pt->supplier_id,
                'return_no'               => $returnNo,
                'return_date'             => now()->toDateString(),
                'total_amount'            => 0, // updated below
                'status'                  => 'draft',
                'return_type'             => $returnType,
                'refund_account_id'       => $refundAccountId,
                'replacement_status'      => $replacementStatus,
                'reason'                  => $data['reason'] ?? null,
                'created_by'              => Auth::id(),
            ]);

            $totalRefundAmount = 0;

            foreach ($items as $item) {
                $detailId = $item['purchase_transaction_detail_id'];
                $qty = intval($item['quantity'] ?? 0);

                if ($qty <= 0) {
                    continue;
                }

                $detail = PurchaseTransactionDetail::findOrFail($detailId);

                if ($qty > $detail->available_qty_for_return) {
                    throw new \Exception("Jumlah retur untuk barang {$detail->productVariant->name} ({$qty}) melebihi sisa yang dapat diretur ({$detail->available_qty_for_return}).");
                }

                $unitPrice = $detail->price;
                $lineTotal = $unitPrice * $qty;
                $totalRefundAmount += $lineTotal;

                PurchaseReturnItem::create([
                    'purchase_return_id'           => $purchaseReturn->id,
                    'purchase_transaction_detail_id' => $detail->id,
                    'product_variant_id'           => $detail->product_variant_id,
                    'quantity'                     => $qty,
                    'unit_price'                   => $unitPrice,
                    'total_price'                  => $lineTotal,
                ]);
            }

            if ($returnType === 'invoice_deduction' && $totalRefundAmount > $pt->getRemainingUnpaidAmount()) {
                $remaining = number_format($pt->getRemainingUnpaidAmount(), 0, ',', '.');
                $totalFormatted = number_format($totalRefundAmount, 0, ',', '.');
                throw new \Exception("Nilai retur (Rp {$totalFormatted}) melebihi sisa tagihan hutang faktur ini (Rp {$remaining}). Silakan pilih metode Refund Kas/Bank atau Saldo Deposit Supplier.");
            }

            $purchaseReturn->update([
                'total_amount' => $totalRefundAmount,
            ]);

            if ($status === 'posted') {
                app(PostPurchaseReturn::class)->execute($purchaseReturn->fresh(['items', 'purchaseTransaction']));
            }

            return $purchaseReturn->fresh();
        });
    }
}
