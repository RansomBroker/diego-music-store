<?php

use App\Actions\Accounting\ResetOpeningBalances;
use App\Actions\Accounting\SeedOpeningBalances;
use App\Models\Account;
use App\Models\JournalEntry;
use App\Models\JournalItem;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Re-parent 128801002 under 122100000 (ASET TIDAK BERWUJUD header), remove duplicate 122101001
     * detail account and empty 122200000 header when unreferenced by operational journals, and
     * refresh OpeningBalance if it previously posted to 122101001.
     */
    public function up(): void
    {
        if (app()->environment('testing')) {
            return;
        }

        // 1. Move 128801002 (Akumulasi Penyusutan Aset Tidak Berwujud) under 122100000 per branch
        foreach (Account::query()->where('code', '128801002')->get() as $amortAcc) {
            $intangibleHeader = Account::query()
                ->where('code', '122100000')
                ->where('branch_id', $amortAcc->branch_id)
                ->first()
                ?? Account::query()->where('code', '122100000')->first();

            $updates = ['account_subtype' => 'intangible_asset'];
            if ($intangibleHeader) {
                $updates['parent_id'] = $intangibleHeader->id;
            }
            $amortAcc->update($updates);
        }

        // 2. If an existing OpeningBalance journal used 122101001, refresh it without 122101001
        $legacyDetailIds = Account::query()->where('code', '122101001')->pluck('id');
        if ($legacyDetailIds->isNotEmpty()) {
            $hasOpeningItem = JournalItem::query()
                ->whereIn('account_id', $legacyDetailIds)
                ->whereHas('journalEntry', fn ($q) => $q->where('reference_type', 'OpeningBalance'))
                ->exists();

            if ($hasOpeningItem) {
                app(ResetOpeningBalances::class)->execute();
                app(SeedOpeningBalances::class)->execute();
            }
        }

        // 3. Delete unused 122101001 detail rows and empty 122200000 header rows
        foreach (['122101001', '122200000'] as $code) {
            foreach (Account::query()->where('code', $code)->orderByDesc('id')->get() as $account) {
                $inUse = JournalItem::where('account_id', $account->id)->exists()
                    || Account::where('parent_id', $account->id)->exists();

                if (! $inUse) {
                    $account->delete();
                }
            }
        }
    }

    /**
     * Non-destructive no-op.
     */
    public function down(): void
    {
        // Non-destructive no-op
    }
};

