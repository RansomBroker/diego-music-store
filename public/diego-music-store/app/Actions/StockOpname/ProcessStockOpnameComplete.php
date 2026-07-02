<?php

namespace App\Actions\StockOpname;

use App\Models\StockOpname;
use App\Models\ProductBranchStock;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ProcessStockOpnameComplete
{
    /**
     * Execute the transition to complete a Stock Opname.
     * Updates branch stock to physical quantity and records stock movements.
     *
     * @param  StockOpname  $opname
     * @return void
     */
    public function execute(StockOpname $opname): void
    {
        DB::transaction(function () use ($opname) {
            // Guard clause: status must be draft
            if ($opname->status !== 'draft') {
                throw new InvalidArgumentException('Hanya stok opname dengan status draft yang dapat diselesaikan.');
            }

            // Process each item
            foreach ($opname->items as $item) {
                // Find or create product branch stock record
                $branchStock = ProductBranchStock::firstOrCreate([
                    'branch_id' => $opname->branch_id,
                    'product_variant_id' => $item->product_variant_id,
                ], [
                    'stock' => 0,
                    'hpp' => $item->cost_price ?: ($item->productVariant->hpp ?? 0),
                ]);

                // Update stock quantity to match physical quantity
                $branchStock->update([
                    'stock' => $item->physical_qty,
                ]);

                // Log discrepancy to stock movements
                $diff = $item->difference;
                if ($diff !== 0) {
                    $hpp = $branchStock->hpp;
                    StockMovement::create([
                        'product_variant_id' => $item->product_variant_id,
                        'branch_id' => $opname->branch_id,
                        'type' => $diff > 0 ? 'in' : 'out',
                        'quantity' => abs($diff),
                        'unit_cost' => $hpp,
                        'hpp' => $hpp,
                        'reference_type' => 'Opname',
                        'reference_id' => $opname->id,
                    ]);
                }
            }

            // Update status to completed
            $opname->update([
                'status' => 'completed',
            ]);

            // Create automatic journal entry if there are differences
            $hasDifferences = false;
            foreach ($opname->items as $item) {
                if ($item->difference !== 0) {
                    $hasDifferences = true;
                    break;
                }
            }

            if ($hasDifferences) {
                $journalEntry = \App\Models\JournalEntry::create([
                    'branch_id' => $opname->branch_id,
                    'date' => now()->format('Y-m-d'),
                    'description' => "Penyesuaian Stok Opname otomatis: No. Dokumen {$opname->opname_number}",
                    'reference_type' => 'Opname',
                    'reference_id' => $opname->id,
                    'status' => 'posted',
                    'created_by' => \Illuminate\Support\Facades\Auth::id(),
                    'posted_at' => now(),
                    'posted_by' => \Illuminate\Support\Facades\Auth::id(),
                ]);

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

                foreach ($opname->items as $item) {
                    $diff = $item->difference;
                    if ($diff === 0) {
                        continue;
                    }

                    // Retrieve branch stock HPP to calculate value
                    $branchStock = ProductBranchStock::where([
                        'branch_id' => $opname->branch_id,
                        'product_variant_id' => $item->product_variant_id,
                    ])->first();

                    $hpp = $branchStock ? $branchStock->hpp : ($item->cost_price ?: ($item->productVariant->hpp ?? 0));
                    $value = abs($diff) * $hpp;

                    if ($value <= 0) {
                        continue;
                    }

                    $inventoryAccId = $item->productVariant->product->inventory_account_id 
                        ?? $resolveAccount('1-1300', 'Persediaan Barang Dagang');
                    $hppAccId = $item->productVariant->product->cogs_account_id 
                        ?? $resolveAccount('5-1000', 'Harga Pokok Penjualan');

                    if ($diff > 0) {
                        // Surplus: Debit Persediaan, Kredit HPP
                        \App\Models\JournalItem::create([
                            'journal_entry_id' => $journalEntry->id,
                            'account_id' => $inventoryAccId,
                            'debit' => $value,
                            'credit' => 0,
                            'notes' => "Surplus Opname " . $item->productVariant->sku,
                        ]);

                        \App\Models\JournalItem::create([
                            'journal_entry_id' => $journalEntry->id,
                            'account_id' => $hppAccId,
                            'debit' => 0,
                            'credit' => $value,
                            'notes' => "Penyesuaian HPP Surplus Opname",
                        ]);
                    } else {
                        // Deficit: Debit HPP, Kredit Persediaan
                        \App\Models\JournalItem::create([
                            'journal_entry_id' => $journalEntry->id,
                            'account_id' => $hppAccId,
                            'debit' => $value,
                            'credit' => 0,
                            'notes' => "Kerugian Defisit Opname " . $item->productVariant->sku,
                        ]);

                        \App\Models\JournalItem::create([
                            'journal_entry_id' => $journalEntry->id,
                            'account_id' => $inventoryAccId,
                            'debit' => 0,
                            'credit' => $value,
                            'notes' => "Pengurangan Persediaan Defisit Opname",
                        ]);
                    }
                }
            }
        });
    }
}
