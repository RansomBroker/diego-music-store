<?php

namespace App\Actions\Accounting;

use App\Models\Account;
use App\Models\AssetDisposal;
use App\Models\JournalEntry;
use App\Models\JournalItem;
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
        if ($disposal->status === 'posted') {
            throw new Exception("Disposisi Aset {$disposal->disposal_number} sudah diposting sebelumnya.");
        }

        return DB::transaction(function () use ($disposal) {
            $asset = $disposal->asset;

            if (! $asset) {
                throw new Exception('Data Aset Tetap tidak ditemukan.');
            }

            $cost = (float) $asset->purchase_cost;
            $accum = (float) $asset->accumulated_depreciation;
            $bookValue = max(0, $cost - $accum);

            $isSale = $disposal->disposal_type === 'sale';
            $disposalAmount = $isSale ? (float) $disposal->disposal_amount : 0.0;
            $gainLoss = $disposalAmount - $bookValue;

            // Find GL accounts using AccountHelper
            $resolveAccount = function($code, $defaultName, $classification) {
                return \App\Helpers\AccountHelper::resolveAccountId($code, $defaultName, $classification);
            };

            $assetAccountId = $resolveAccount('121101001', 'ASET TETAP - PERALATAN', 'Asset');
            $accumAccountId = $resolveAccount('129101001', 'AKUMULASI PENYUSUTAN ASET', 'Asset');
            $cashAccountId = $disposal->account_id ?: $resolveAccount('111101001', 'KAS', 'Asset');
            $gainAccountId = $resolveAccount('711201001', 'LABA PENJUALAN ASET TETAP', 'Revenue');
            $lossAccountId = $resolveAccount('811201001', 'RUGI PELEPASAN/PENGHAPUSAN ASET TETAP', 'Expense');

            // Create Journal Entry
            $journal = JournalEntry::create([
                'entry_no'    => $disposal->disposal_number,
                'date'        => $disposal->disposal_date,
                'description' => "Disposisi Aset: {$asset->asset_code} - {$asset->name} (" . ($isSale ? 'Penjualan' : 'Penghapusan/Write-Off') . ")",
                'status'      => 'posted',
                'branch_id'   => $disposal->branch_id ?: $asset->branch_id,
            ]);

            // 1. Debit Accum Depreciation if exists
            if ($accum > 0) {
                JournalItem::create([
                    'journal_entry_id' => $journal->id,
                    'account_id'       => $accumAccountId,
                    'debit'            => $accum,
                    'credit'           => 0,
                    'notes'             => "Eliminasi Akumulasi Penyusutan {$asset->name}",
                ]);
            }

            if ($isSale) {
                // 2. Debit Cash/Bank for Sale Amount
                if ($disposalAmount > 0) {
                    JournalItem::create([
                        'journal_entry_id' => $journal->id,
                        'account_id'       => $cashAccountId,
                        'debit'            => $disposalAmount,
                        'credit'           => 0,
                        'notes'             => "Penerimaan Hasil Penjualan Aset {$asset->name}",
                    ]);
                }

                // 3. Credit Fixed Asset Cost
                JournalItem::create([
                    'journal_entry_id' => $journal->id,
                    'account_id'       => $assetAccountId,
                    'debit'            => 0,
                    'credit'           => $cost,
                    'notes'             => "Eliminasi Harga Perolehan Aset {$asset->name}",
                ]);

                // 4. Gain or Loss Entry
                if ($gainLoss > 0) {
                    // Credit Gain (Pendapatan Lain-lain)
                    JournalItem::create([
                        'journal_entry_id' => $journal->id,
                        'account_id'       => $gainAccountId,
                        'debit'            => 0,
                        'credit'           => $gainLoss,
                        'notes'             => "Laba Penjualan Aset {$asset->name}",
                    ]);
                } elseif ($gainLoss < 0) {
                    // Debit Loss (Beban Lain-lain)
                    JournalItem::create([
                        'journal_entry_id' => $journal->id,
                        'account_id'       => $lossAccountId,
                        'debit'            => abs($gainLoss),
                        'credit'           => 0,
                        'notes'             => "Rugi Penjualan Aset {$asset->name}",
                    ]);
                }
            } else {
                // Write-Off
                // Debit Loss Write-Off (Book Value)
                if ($bookValue > 0) {
                    JournalItem::create([
                        'journal_entry_id' => $journal->id,
                        'account_id'       => $lossAccountId,
                        'debit'            => $bookValue,
                        'credit'           => 0,
                        'notes'             => "Beban Penghapusan Aset {$asset->name}",
                    ]);
                }

                // Credit Fixed Asset Cost
                JournalItem::create([
                    'journal_entry_id' => $journal->id,
                    'account_id'       => $assetAccountId,
                    'debit'            => 0,
                    'credit'           => $cost,
                    'notes'             => "Eliminasi Harga Perolehan Aset {$asset->name}",
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
