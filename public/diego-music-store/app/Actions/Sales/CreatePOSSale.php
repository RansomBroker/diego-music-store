<?php

namespace App\Actions\Sales;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\ProductVariant;
use App\Models\ProductBranchStock;
use App\Models\StockMovement;
use App\Models\JournalEntry;
use App\Models\JournalItem;
use App\Models\Account;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class CreatePOSSale
{
    /**
     * Execute the POS Checkout process.
     *
     * @param  array  $data
     * @return Sale
     */
    public function execute(array $data): Sale
    {
        return DB::transaction(function () use ($data) {
            $items = $data['items'] ?? [];
            if (empty($items)) {
                throw new \Exception('Keranjang belanja kosong.');
            }

            $branchId = $data['branch_id'];
            $salesRepId = $data['sales_rep_id'] ?? Auth::id();
            $customerId = $data['customer_id'] ?? null;
            $paymentMethod = $data['payment_method'] ?? 'cash';
            
            $subtotal = 0;
            $discountAmount = intval($data['discount_amount'] ?? 0);
            $taxAmount = intval($data['tax_amount'] ?? 0);

            // 1. Generate Invoice Number
            $invoiceNumber = Sale::generateInvoiceNumber();

            // 2. Pre-calculate subtotal
            $processedItems = [];
            foreach ($items as $item) {
                $variant = ProductVariant::findOrFail($item['variant_id']);
                $qty = intval($item['qty'] ?? 1);
                $unitPrice = intval($item['price'] ?? $variant->price);
                $itemDiscount = intval($item['discount_amount'] ?? 0);
                
                $itemTotal = ($unitPrice * $qty) - $itemDiscount;
                $subtotal += $itemTotal;

                $processedItems[] = [
                    'variant' => $variant,
                    'qty' => $qty,
                    'unit_price' => $unitPrice,
                    'discount_amount' => $itemDiscount,
                    'total_price' => $itemTotal,
                    'notes' => $item['notes'] ?? null,
                ];
            }

            $grandTotal = $subtotal - $discountAmount + $taxAmount;

            // 3. Create Sale Header
            $sale = Sale::create([
                'branch_id' => $branchId,
                'cash_session_id' => $data['cash_session_id'] ?? null,
                'customer_id' => $customerId,
                'sales_rep_id' => $salesRepId,
                'invoice_number' => $invoiceNumber,
                'invoice_date' => $data['invoice_date'] ?? now()->toDateString(),
                'subtotal' => $subtotal,
                'discount_amount' => $discountAmount,
                'tax_amount' => $taxAmount,
                'grand_total' => $grandTotal,
                'payment_method' => $paymentMethod,
                'status' => 'completed',
                'created_by' => Auth::id(),
            ]);

            // 4. Create Sale Items and handle Stock Deduction & Movement
            $totalCOGS = 0;
            foreach ($processedItems as $pi) {
                $variant = $pi['variant'];
                $qty = $pi['qty'];

                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_variant_id' => $variant->id,
                    'quantity' => $qty,
                    'unit_price' => $pi['unit_price'],
                    'discount_amount' => $pi['discount_amount'],
                    'total_price' => $pi['total_price'],
                    'notes' => $pi['notes'],
                ]);

                // Check and handle physical inventory deductions
                if ($variant->product->isPhysical()) {
                    $branchStock = ProductBranchStock::firstOrCreate([
                        'product_variant_id' => $variant->id,
                        'branch_id' => $branchId,
                    ], [
                        'stock' => 0,
                        'hpp' => $variant->hpp ?: $variant->cost_price ?: 0,
                    ]);

                    if ($branchStock->stock < $qty) {
                        throw new \Exception("Stok barang {$variant->product->name} ({$variant->name}) tidak mencukupi. Sisa stok: {$branchStock->stock}.");
                    }

                    // Deduct stock
                    $branchStock->update([
                        'stock' => $branchStock->stock - $qty,
                    ]);

                    // Accumulate COGS based on current branch HPP
                    $itemHPP = $branchStock->hpp ?: $variant->hpp ?: $variant->cost_price ?: 0;
                    $totalCOGS += $itemHPP * $qty;

                    // Log Stock Movement
                    StockMovement::create([
                        'product_variant_id' => $variant->id,
                        'branch_id' => $branchId,
                        'type' => 'out',
                        'quantity' => $qty,
                        'unit_cost' => $itemHPP,
                        'hpp' => $itemHPP,
                        'reference_type' => 'POS',
                        'reference_id' => $sale->id,
                    ]);
                }
            }

            // 5. Automatic Journal Entry Creation
            $journalNo = 'JV-POS-' . now()->format('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
            
            $journalEntry = JournalEntry::create([
                'branch_id' => $branchId,
                'entry_no' => $journalNo,
                'date' => $data['invoice_date'] ?? now()->toDateString(),
                'description' => "Penjualan Kasir POS: No. Invoice {$invoiceNumber}",
                'reference_type' => 'Sales',
                'reference_id' => $sale->id,
                'status' => 'posted',
                'created_by' => Auth::id(),
                'posted_at' => now(),
                'posted_by' => Auth::id(),
            ]);

            // Resolve Account Helper
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

            // 5a. Debit: Receivables or Bank or Cash (supporting Split Payment)
            $payments = $data['payments'] ?? [
                ['method' => $paymentMethod, 'amount' => $grandTotal, 'ref' => null]
            ];

            foreach ($payments as $pay) {
                $payMethod = $pay['method'];
                $payAmount = intval($pay['amount']);
                $payRef = $pay['ref'] ?? null;
                
                if ($payAmount <= 0) {
                    continue;
                }

                if ($payMethod === 'credit') {
                    $debitAccId = $resolveAccount('1-1200', 'Piutang Dagang', 'Asset');
                    $methodName = 'Piutang';
                } elseif ($payMethod === 'debit') {
                    $debitAccId = $resolveAccount('1-1110', 'Bank BCA', 'Asset');
                    $methodName = 'Debit BCA' . ($payRef ? " (Ref: {$payRef})" : '');
                } else {
                    $debitAccId = $resolveAccount('1-1000', 'Kas Utama', 'Asset');
                    $methodName = 'Tunai';
                }

                JournalItem::create([
                    'journal_entry_id' => $journalEntry->id,
                    'account_id' => $debitAccId,
                    'debit' => $payAmount,
                    'credit' => 0,
                    'notes' => "Penerimaan POS - {$methodName}",
                ]);
            }

            // 5b. Credit: Sales Revenue
            $salesAccId = $resolveAccount('4-1000', 'Pendapatan Penjualan', 'Revenue');
            JournalItem::create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $salesAccId,
                'debit' => 0,
                'credit' => $grandTotal,
                'notes' => "Pendapatan Penjualan POS",
            ]);

            // 5c. COGS Journal (if physical goods sold)
            if ($totalCOGS > 0) {
                $cogsAccId = $resolveAccount('5-1000', 'Harga Pokok Penjualan', 'Expense');
                $inventoryAccId = $resolveAccount('1-1300', 'Persediaan Barang', 'Asset');

                // Debit COGS
                JournalItem::create([
                    'journal_entry_id' => $journalEntry->id,
                    'account_id' => $cogsAccId,
                    'debit' => $totalCOGS,
                    'credit' => 0,
                    'notes' => "HPP Penjualan POS",
                ]);

                // Credit Inventory
                JournalItem::create([
                    'journal_entry_id' => $journalEntry->id,
                    'account_id' => $inventoryAccId,
                    'debit' => 0,
                    'credit' => $totalCOGS,
                    'notes' => "Pengurangan Persediaan POS",
                ]);
            }

            return $sale;
        });
    }
}
