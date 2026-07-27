<?php

namespace App\Actions\Accounting;

use App\Models\Account;
use App\Models\AssetDisposal;
use App\Models\JournalEntry;
use App\Models\JournalEntryItem;
use Exception;
use Illuminate\Support\Facades\DB;

class PostAssetDisposal
{
    /**
     * Execute Post Asset Disposal Action.
     *
     * @param  AssetDisposal  $disposal
     * @return AssetDisposal
     * @throws Exception
     */
    public function execute(AssetDisposal $disposal): AssetDisposal
    {
        return DB::transaction(function () use ($disposal) {
            if ($disposal->status === 'posted') {
                throw new Exception("Disposisi aset {$disposal->disposal_number} sudah diposting sebelumnya.");
            }

            $asset = $disposal->asset;
            if (!$asset) {
                throw new Exception("Aset tidak ditemukan.");
            }

            $cost  = (float) $asset->purchase_cost;
            $accum = (float) $asset->accumulated_depreciation;
            $bookValue = max(0, $cost - $accum);

            $isSale = $disposal->disposal_type === 'sale';
            $disposalAmount = $isSale ? (float) $disposal->disposal_amount : 0.0;
            $gainLoss = $disposalAmount - $bookValue;

            // Find Accounts for Journal Posting
            $assetAccount = Account::where('code', 'LIKE', '12%')->where('is_header', false)->first()
                ?? Account::where('classification', 'Asset')->where('is_header', false)->first();

            $accumAccount = Account::where('code', 'LIKE', '129%')->orWhere('name', 'LIKE', '%Akumulasi%')->first()
                ?? $assetAccount;

            $cashAccount = $disposal->account
                ?? Account::where('code', 'LIKE', '11%')->where('is_header', false)->first()
                ?? $assetAccount;

            $gainLossAccount = Account::where('name', 'LIKE', '%Laba%Rugi%Aset%')
                ->orWhere('name', 'LIKE', '%Penjualan%Aset%')
                ->first()
                ?? Account::where('classification', 'Revenue')->where('is_header', false)->first()
                ?? $assetAccount;

            // Create Journal Entry
            $journal = JournalEntry::create([
                'transaction_number' => $disposal->disposal_number,
                'transaction_date'   => $disposal->disposal_date,
                'description'        => "Disposisi Aset: {$asset->asset_code} - {$asset->name} (" . ($isSale ? 'Penjualan' : 'Penghapusan/Write-Off') . ")",
                'status'             => 'posted',
                'branch_id'          => $disposal->branch_id ?: $asset->branch_id,
            ]);

            // 1. Debit Accum Depreciation if exists
            if ($accum > 0) {
                JournalEntryItem::create([
                    'journal_entry_id' => $journal->id,
                    'account_id'       => $accumAccount?->id,
                    'debit'            => $accum,
                    'credit'           => 0,
                    'memo'             => "Eliminasi Akumulasi Penyusutan {$asset->name}",
                ]);
            }

            if ($isSale) {
                // 2. Debit Cash/Bank for Sale Amount
                if ($disposalAmount > 0) {
                    JournalEntryItem::create([
                        'journal_entry_id' => $journal->id,
                        'account_id'       => $cashAccount?->id,
                        'debit'            => $disposalAmount,
                        'credit'           => 0,
                        'memo'             => "Penerimaan Hasil Penjualan Aset {$asset->name}",
                    ]);
                }

                // 3. Credit Fixed Asset Cost
                JournalEntryItem::create([
                    'journal_entry_id' => $journal->id,
                    'account_id'       => $assetAccount?->id,
                    'debit'            => 0,
                    'credit'           => $cost,
                    'memo'             => "Eliminasi Harga Perolehan Aset {$asset->name}",
                ]);

                // 4. Gain or Loss Entry
                if ($gainLoss > 0) {
                    // Credit Gain
                    JournalEntryItem::create([
                        'journal_entry_id' => $journal->id,
                        'account_id'       => $gainLossAccount?->id,
                        'debit'            => 0,
                        'credit'           => $gainLoss,
                        'memo'             => "Laba Penjualan Aset {$asset->name}",
                    ]);
                } elseif ($gainLoss < 0) {
                    // Debit Loss
                    JournalEntryItem::create([
                        'journal_entry_id' => $journal->id,
                        'account_id'       => $gainLossAccount?->id,
                        'debit'            => abs($gainLoss),
                        'credit'           => 0,
                        'memo'             => "Rugi Penjualan Aset {$asset->name}",
                    ]);
                }
            } else {
                // Write-Off
                // Debit Loss Write-Off (Book Value)
                if ($bookValue > 0) {
                    JournalEntryItem::create([
                        'journal_entry_id' => $journal->id,
                        'account_id'       => $gainLossAccount?->id,
                        'debit'            => $bookValue,
                        'credit'           => 0,
                        'memo'             => "Beban Penghapusan Aset {$asset->name}",
                    ]);
                }

                // Credit Fixed Asset Cost
                JournalEntryItem::create([
                    'journal_entry_id' => $journal->id,
                    'account_id'       => $assetAccount?->id,
                    'debit'            => 0,
                    'credit'           => $cost,
                    'memo'             => "Eliminasi Harga Perolehan Aset {$asset->name}",
                ]);
            }

            // Update Asset Status
            $asset->status = $isSale ? 'disposed' : 'written_off';
            $asset->save();

            // Update Disposal Record
            $disposal->update([
                'book_value'       => $bookValue,
                'gain_loss_amount' => $gainLoss,
                'journal_entry_id' => $journal->id,
                'status'           => 'posted',
            ]);

            return $disposal;
        });
    }
}
