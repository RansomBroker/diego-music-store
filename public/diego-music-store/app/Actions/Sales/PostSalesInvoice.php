<?php

namespace App\Actions\Sales;

use App\Models\JournalEntry;
use App\Models\JournalItem;
use App\Models\ProductBranchStock;
use App\Models\SalesInvoice;
use App\Models\StockMovement;
use App\Models\Account;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class PostSalesInvoice
{
    /**
     * Execute the action to post a Sales Invoice.
     *
     * @param SalesInvoice $invoice
     * @return SalesInvoice
     */
    public function execute(SalesInvoice $invoice): SalesInvoice
    {
        return DB::transaction(function () use ($invoice) {
            if ($invoice->status !== 'draft') {
                throw new InvalidArgumentException('Hanya Faktur Penjualan dengan status draf yang dapat diposting.');
            }

            // 1. Generate Journal Number
            $date = now()->format('Ymd');
            $prefix = 'JV-' . $date . '-';
            $lastJournal = SalesInvoice::where('journal_no', 'like', $prefix . '%')
                ->orderBy('journal_no', 'desc')
                ->first();

            if ($lastJournal) {
                $lastNum = intval(substr($lastJournal->journal_no, strlen($prefix)));
                $nextNum = str_pad($lastNum + 1, 4, '0', STR_PAD_LEFT);
            } else {
                $nextNum = '0001';
            }
            $journalNo = $prefix . $nextNum;

            // 2. Process stock deduction and stock movement
            $totalCogs = 0;
            foreach ($invoice->items as $item) {
                $stock = ProductBranchStock::firstOrCreate(
                    [
                        'branch_id' => $invoice->branch_id,
                        'product_variant_id' => $item->product_variant_id,
                    ],
                    [
                        'stock' => 0,
                        'hpp' => $item->productVariant->hpp ?? 0,
                    ]
                );

                $itemHpp = $stock->hpp ?: ($item->productVariant->hpp ?? 0);
                $stock->decrement('stock', $item->quantity);

                $lineCogs = $item->quantity * $itemHpp;
                $totalCogs += $lineCogs;

                StockMovement::create([
                    'product_variant_id' => $item->product_variant_id,
                    'branch_id' => $invoice->branch_id,
                    'type' => 'out',
                    'quantity' => $item->quantity,
                    'original_quantity' => $item->quantity,
                    'unit_id' => $item->unit_id,
                    'unit_cost' => $itemHpp,
                    'hpp' => $itemHpp,
                    'reference_type' => 'SalesInvoice',
                    'reference_id' => $invoice->id,
                ]);
            }

            // 3. Helper resolve account
            $resolveAccount = function($code, $defaultName = 'Default Account', $classification = 'Asset') {
                return Account::firstOrCreate(
                    ['code' => $code],
                    [
                        'name' => $defaultName,
                        'classification' => $classification,
                        'is_active' => true,
                    ]
                )->id;
            };

            // 4. Create automatic Journal Entry
            $journalEntry = JournalEntry::create([
                'branch_id' => $invoice->branch_id,
                'entry_no' => $journalNo,
                'date' => $invoice->invoice_date,
                'description' => "Faktur Penjualan: No. {$invoice->invoice_number}",
                'reference_type' => 'SalesInvoice',
                'reference_id' => $invoice->id,
                'status' => 'posted',
                'created_by' => Auth::id() ?? $invoice->created_by,
                'posted_at' => now(),
                'posted_by' => Auth::id() ?? $invoice->created_by,
            ]);

            // Debit: Kas/Bank or Piutang Usaha
            $receivableAccId = ($invoice->payment_type === 'Kredit')
                ? $resolveAccount('1-1200', 'Piutang Usaha', 'Asset')
                : $resolveAccount('1-1000', 'Kas Utama', 'Asset');

            JournalItem::create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $receivableAccId,
                'debit' => $invoice->grand_total,
                'credit' => 0,
                'notes' => $invoice->payment_type === 'Kredit' ? 'Piutang Penjualan' : 'Penerimaan Kas Penjualan',
            ]);

            // Credit: Pendapatan Penjualan
            $revenueAccId = $resolveAccount('4-1000', 'Pendapatan Penjualan', 'Revenue');
            $netSales = max(0, $invoice->subtotal - $invoice->discount_amount);
            JournalItem::create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $revenueAccId,
                'debit' => 0,
                'credit' => $netSales,
                'notes' => 'Penjualan Produk',
            ]);

            // Credit: PPN Keluaran
            if ($invoice->tax_amount > 0) {
                $taxAccId = $resolveAccount('2-1200', 'PPN Keluaran', 'Liability');
                JournalItem::create([
                    'journal_entry_id' => $journalEntry->id,
                    'account_id' => $taxAccId,
                    'debit' => 0,
                    'credit' => $invoice->tax_amount,
                    'notes' => 'PPN Keluaran',
                ]);
            }

            // Credit: Biaya Kirim (if any)
            if ($invoice->shipping_cost > 0) {
                $shippingAccId = $resolveAccount('4-2000', 'Pendapatan Ongkir / Pengiriman', 'Revenue');
                JournalItem::create([
                    'journal_entry_id' => $journalEntry->id,
                    'account_id' => $shippingAccId,
                    'debit' => 0,
                    'credit' => $invoice->shipping_cost,
                    'notes' => 'Pendapatan Ongkos Kirim',
                ]);
            }

            // Debit: HPP & Credit: Persediaan (COGS)
            if ($totalCogs > 0) {
                $cogsAccId = $resolveAccount('5-1000', 'Beban Pokok Penjualan (HPP)', 'Expense');
                $invAccId = $resolveAccount('1-1300', 'Persediaan Barang Dagang', 'Asset');

                JournalItem::create([
                    'journal_entry_id' => $journalEntry->id,
                    'account_id' => $cogsAccId,
                    'debit' => $totalCogs,
                    'credit' => 0,
                    'notes' => 'Beban HPP Penjualan',
                ]);

                JournalItem::create([
                    'journal_entry_id' => $journalEntry->id,
                    'account_id' => $invAccId,
                    'debit' => 0,
                    'credit' => $totalCogs,
                    'notes' => 'Pengurangan Persediaan Penjualan',
                ]);
            }

            // Update invoice header status
            $invoice->update([
                'status' => 'posted',
                'posted_at' => now(),
                'posted_by' => Auth::id(),
                'journal_no' => $journalNo,
            ]);

            // Update linked quotation status if applicable
            if ($invoice->sales_quotation_id && ($quotation = $invoice->salesQuotation)) {
                $quotation->update(['status' => 'closed']);
            }

            return $invoice;
        });
    }
}
