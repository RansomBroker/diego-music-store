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

            // Create Sales Return Header
            $salesReturn = SalesReturn::create([
                'sale_id' => $sale->id,
                'branch_id' => $sale->branch_id,
                'cash_session_id' => $data['cash_session_id'] ?? null,
                'return_number' => $returnNumber,
                'return_date' => now()->toDateString(),
                'total_refund' => 0, // Updated below
                'reason' => $data['reason'] ?? null,
                'created_by' => Auth::id(),
            ]);

            $totalRefund = 0;
            $totalReturnedCOGS = 0;

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

                // Update physical stock if it is a physical product
                $variant = $saleItem->variant;
                if ($variant->product->isPhysical()) {
                    $branchStock = ProductBranchStock::firstOrCreate([
                        'product_variant_id' => $variant->id,
                        'branch_id' => $sale->branch_id,
                    ], [
                        'stock' => 0,
                        'hpp' => $variant->hpp ?: $variant->cost_price ?: 0,
                    ]);

                    $branchStock->update([
                        'stock' => $branchStock->stock + $qty,
                    ]);

                    $itemHPP = $branchStock->hpp ?: $variant->hpp ?: $variant->cost_price ?: 0;
                    $totalReturnedCOGS += $itemHPP * $qty;

                    // Log Stock Movement
                    StockMovement::create([
                        'product_variant_id' => $variant->id,
                        'branch_id' => $sale->branch_id,
                        'type' => 'in',
                        'quantity' => $qty,
                        'unit_cost' => $itemHPP,
                        'hpp' => $itemHPP,
                        'reference_type' => 'SalesReturn',
                        'reference_id' => $salesReturn->id,
                    ]);
                }
            }

            // Update total refund on return header
            $salesReturn->update([
                'total_refund' => $totalRefund,
            ]);

            // Create Journal Entries
            if ($totalRefund > 0) {
                $journalNo = 'JV-SR-' . now()->format('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

                $journalEntry = JournalEntry::create([
                    'branch_id' => $sale->branch_id,
                    'entry_no' => $journalNo,
                    'date' => now()->toDateString(),
                    'description' => "Retur Penjualan POS: No. Retur {$returnNumber} (Ref Invoice: {$sale->invoice_number})",
                    'reference_type' => 'SalesReturn',
                    'reference_id' => $salesReturn->id,
                    'status' => 'posted',
                    'created_by' => Auth::id(),
                    'posted_at' => now(),
                    'posted_by' => Auth::id(),
                ]);

                // Helper to resolve accounts
                $resolveAccount = function($code, $defaultName, $classification) {
                    return Account::firstOrCreate(
                        ['code' => $code],
                        [
                            'name' => $defaultName,
                            'classification' => $classification,
                            'is_active' => true,
                        ]
                    )->id;
                };

                // 1. Debit: Retur Penjualan (Contra-Revenue Account)
                $returAccId = $resolveAccount('4-1100', 'Retur & Potongan Penjualan', 'Revenue');
                JournalItem::create([
                    'journal_entry_id' => $journalEntry->id,
                    'account_id' => $returAccId,
                    'debit' => $totalRefund,
                    'credit' => 0,
                    'notes' => "Pembalikan Pendapatan Retur Penjualan",
                ]);

                // 2. Credit: Cash or Bank BCA depending on original payment method
                // Standard mapping: if debit -> Bank BCA, else Kas Utama
                $payMethod = strtolower($sale->payment_method);
                if (str_contains($payMethod, 'debit')) {
                    $creditAccId = $resolveAccount('1-1110', 'Bank BCA', 'Asset');
                    $methodName = 'Bank BCA';
                } else {
                    $creditAccId = $resolveAccount('1-1000', 'Kas Utama', 'Asset');
                    $methodName = 'Kas Utama';
                }

                JournalItem::create([
                    'journal_entry_id' => $journalEntry->id,
                    'account_id' => $creditAccId,
                    'debit' => 0,
                    'credit' => $totalRefund,
                    'notes' => "Pengembalian Dana POS via {$methodName}",
                ]);

                // 3. Reverse COGS & Inventory (if physical goods returned)
                if ($totalReturnedCOGS > 0) {
                    $cogsAccId = $resolveAccount('5-1000', 'Harga Pokok Penjualan', 'Expense');
                    $inventoryAccId = $resolveAccount('1-1300', 'Persediaan Barang', 'Asset');

                    // Debit Inventory (increasing stock assets)
                    JournalItem::create([
                        'journal_entry_id' => $journalEntry->id,
                        'account_id' => $inventoryAccId,
                        'debit' => $totalReturnedCOGS,
                        'credit' => 0,
                        'notes' => "Pengembalian Persediaan Retur POS",
                    ]);

                    // Credit COGS (decreasing COGS expenses)
                    JournalItem::create([
                        'journal_entry_id' => $journalEntry->id,
                        'account_id' => $cogsAccId,
                        'debit' => 0,
                        'credit' => $totalReturnedCOGS,
                        'notes' => "Pembalikan HPP Retur POS",
                    ]);
                }
            }

            return $salesReturn;
        });
    }
}
