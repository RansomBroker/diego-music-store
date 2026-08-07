<?php

namespace App\Actions\Sales;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SalesReturn;
use App\Models\SalesReturnItem;
use App\Models\ProductVariant;
use App\Models\ProductBranchStock;
use App\Models\StockMovement;
use App\Models\JournalEntry;
use App\Models\JournalItem;
use App\Models\Account;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class CreateSalesReturn
{
    /**
     * Execute the Sales Return process.
     *
     * @param  array  $data
     * @return SalesReturn
     */
    public function execute(array $data): SalesReturn
    {
        return DB::transaction(function () use ($data) {
            $saleId = $data['sale_id'];
            $sale = Sale::findOrFail($saleId);

            if ($sale->status === 'cancelled') {
                throw new \Exception('Tidak dapat melakukan retur pada transaksi yang sudah dibatalkan.');
            }

            $items = $data['items'] ?? [];
            if (empty($items)) {
                throw new \Exception('Pilih minimal satu barang untuk diretur.');
            }

            // Generate Return Number
            $returnNumber = SalesReturn::generateReturnNumber();
            $status = $data['status'] ?? 'posted';

            // Create Sales Return Header
            $salesReturn = SalesReturn::create([
                'sale_id' => $sale->id,
                'branch_id' => $sale->branch_id,
                'cash_session_id' => $data['cash_session_id'] ?? null,
                'return_number' => $returnNumber,
                'return_date' => now()->toDateString(),
                'total_refund' => 0, // Updated below
                'status' => 'draft',
                'reason' => $data['reason'] ?? null,
                'created_by' => Auth::id(),
            ]);

            $totalRefund = 0;

            foreach ($items as $item) {
                $saleItemId = $item['sale_item_id'];
                $qty = intval($item['quantity'] ?? 0);

                if ($qty <= 0) {
                    continue;
                }

                $saleItem = SaleItem::findOrFail($saleItemId);

                // Validation
                if ($qty > $saleItem->available_qty_for_return) {
                    throw new \Exception("Jumlah retur untuk barang {$saleItem->variant->product->name} ({$qty}) melebihi sisa barang yang dapat diretur ({$saleItem->available_qty_for_return}).");
                }

                // Compute refund amount: actual price paid per unit after prorated discount
                $refundPerUnit = intval(round($saleItem->total_price / $saleItem->quantity));
                $refundAmount = $refundPerUnit * $qty;
                $totalRefund += $refundAmount;

                // Create Sales Return Item
                SalesReturnItem::create([
                    'sales_return_id' => $salesReturn->id,
                    'sale_item_id' => $saleItem->id,
                    'product_variant_id' => $saleItem->product_variant_id,
                    'quantity' => $qty,
                    'unit_price' => $saleItem->unit_price,
                    'refund_amount' => $refundAmount,
                ]);
            }

            // Update total refund on return header
            $salesReturn->update([
                'total_refund' => $totalRefund,
            ]);

            if ($status === 'posted') {
                app(PostSalesReturn::class)->execute($salesReturn->fresh(['items.variant.product', 'sale']));
            }

            return $salesReturn->fresh();
        });
    }
}
