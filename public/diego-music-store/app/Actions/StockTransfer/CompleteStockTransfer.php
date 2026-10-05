<?php

namespace App\Actions\StockTransfer;

use App\Models\StockTransfer;
use App\Models\StockMovement;
use App\Models\JournalEntry;
use App\Models\JournalItem;
use App\Models\Account;
use Illuminate\Support\Facades\DB;
use Exception;

class CompleteStockTransfer
{
    public function execute(StockTransfer $transfer): StockTransfer
    {
        if ($transfer->status !== 'APPROVED') {
            throw new Exception("Hanya transfer dengan status APPROVED yang dapat diproses menjadi COMPLETED.");
        }

        return DB::transaction(function () use ($transfer) {
            $transfer->load(['fromBranch', 'toBranch', 'items.productVariant']);

            $fromBranch = $transfer->fromBranch;
            $toBranch = $transfer->toBranch;

            // 1. Validasi COA
            if (!$fromBranch->inventory_account_id || !$fromBranch->interbranch_receivable_account_id) {
                throw new Exception("Cabang Asal ({$fromBranch->name}) belum memiliki pengaturan Akun COA yang lengkap untuk proses transfer.");
            }
            if (!$toBranch->inventory_account_id || !$toBranch->interbranch_payable_account_id) {
                throw new Exception("Cabang Tujuan ({$toBranch->name}) belum memiliki pengaturan Akun COA yang lengkap untuk proses transfer.");
            }

            // 2. Buat Journal Entry
            $journal = JournalEntry::create([
                'entry_no' => JournalEntry::generateEntryNo($fromBranch->id),
                'date' => $transfer->transfer_date,
                'description' => "Jurnal Transfer Barang: {$transfer->transfer_number} ({$fromBranch->name} -> {$toBranch->name})",
                'reference_type' => StockTransfer::class,
                'reference_id' => $transfer->id,
                'status' => 'posted',
                'branch_id' => $fromBranch->id,
                'posted_at' => now(),
            ]);

            $totalValue = 0;

            // 3. Proses Stock Movements
            foreach ($transfer->items as $item) {
                $variant = $item->productVariant;
                
                // Ambil nilai unit_cost dari item (yang disnaphot waktu transfer dibuat)
                $unitCost = (float) $item->unit_cost;
                $lineTotal = (float) ($item->qty * $unitCost);
                $totalValue += $lineTotal;

                // Pengurangan Stok Cabang Asal
                StockMovement::create([
                    'product_variant_id' => $variant->id,
                    'branch_id' => $fromBranch->id,
                    'type' => 'out',
                    'quantity' => $item->qty,
                    'reference_type' => StockTransfer::class,
                    'reference_id' => $transfer->id,
                    'description' => "Transfer Keluar ke {$toBranch->name} (No: {$transfer->transfer_number})",
                    'unit_cost' => $unitCost,
                    'total_cost' => $lineTotal,
                ]);

                // Penambahan Stok Cabang Tujuan
                StockMovement::create([
                    'product_variant_id' => $variant->id,
                    'branch_id' => $toBranch->id,
                    'type' => 'in',
                    'quantity' => $item->qty,
                    'reference_type' => StockTransfer::class,
                    'reference_id' => $transfer->id,
                    'description' => "Transfer Masuk dari {$fromBranch->name} (No: {$transfer->transfer_number})",
                    'unit_cost' => $unitCost,
                    'total_cost' => $lineTotal,
                ]);

                // Update saldo qty fisik di ProductBranchStock
                // Kurangi dari asal
                $originStock = $variant->branchStocks()->firstOrCreate(
                    ['branch_id' => $fromBranch->id],
                    ['stock' => 0, 'hpp' => (int) $unitCost]
                );
                $originStock->decrement('stock', $item->qty);

                // Tambah ke tujuan
                $destStock = $variant->branchStocks()->firstOrCreate(
                    ['branch_id' => $toBranch->id],
                    ['stock' => 0, 'hpp' => (int) $unitCost]
                );
                
                // Perbarui HPP tujuan jika belum ada
                if (!$destStock->hpp || $destStock->hpp == 0) {
                    $destStock->hpp = (int) $unitCost;
                }
                $destStock->increment('stock', $item->qty);
                $destStock->save();
            }

            // 4. Proses Jurnal Items (Pola 4 Akun)
            
            // --- Sisi Cabang Asal ---
            // Debit: Piutang Antar Cabang
            JournalItem::create([
                'journal_entry_id' => $journal->id,
                'account_id' => $fromBranch->interbranch_receivable_account_id,
                'notes' => "Piutang Antar Cabang - Transfer ke {$toBranch->name}",
                'debit' => $totalValue,
                'credit' => 0,
            ]);
            // Credit: Persediaan Cabang Asal
            JournalItem::create([
                'journal_entry_id' => $journal->id,
                'account_id' => $fromBranch->inventory_account_id,
                'notes' => "Persediaan Barang Dagang ({$fromBranch->name}) - Keluar ke {$toBranch->name}",
                'debit' => 0,
                'credit' => $totalValue,
            ]);

            // --- Sisi Cabang Tujuan ---
            // Debit: Persediaan Cabang Tujuan
            JournalItem::create([
                'journal_entry_id' => $journal->id,
                'account_id' => $toBranch->inventory_account_id,
                'notes' => "Persediaan Barang Dagang ({$toBranch->name}) - Masuk dari {$fromBranch->name}",
                'debit' => $totalValue,
                'credit' => 0,
            ]);
            // Credit: Hutang Antar Cabang
            JournalItem::create([
                'journal_entry_id' => $journal->id,
                'account_id' => $toBranch->interbranch_payable_account_id,
                'notes' => "Hutang Antar Cabang - Diterima dari {$fromBranch->name}",
                'debit' => 0,
                'credit' => $totalValue,
            ]);

            // 5. Update Status Transfer
            $transfer->update([
                'status' => 'COMPLETED',
                'completed_at' => now(),
                'journal_entry_id' => $journal->id,
                'total_cost' => $totalValue,
            ]);

            return $transfer;
        });
    }
}
