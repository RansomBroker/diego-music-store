<?php

use App\Models\Account;
use App\Models\JournalItem;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Re-parent 631101003 (SELISIH PENYESUAIAN STOK) under 631100000 (BIAYA LAIN-LAIN)
     * per branch and remove empty 631200000 (PENYESUAIAN STOK) header rows.
     */
    public function up(): void
    {
        if (app()->environment('testing')) {
            return;
        }

        $adjustmentAccounts = Account::withoutGlobalScopes()
            ->where('code', '631101003')
            ->get();

        foreach ($adjustmentAccounts as $account) {
            $otherExpenseHeader = Account::withoutGlobalScopes()
                ->where('code', '631100000')
                ->where('is_header', true)
                ->when(
                    $account->branch_id === null,
                    fn ($q) => $q->whereNull('branch_id'),
                    fn ($q) => $q->where('branch_id', $account->branch_id)
                )
                ->first();

            if ($otherExpenseHeader && (int) $account->parent_id !== (int) $otherExpenseHeader->id) {
                $account->update(['parent_id' => $otherExpenseHeader->id]);
            }
        }

        $legacyHeaders = Account::withoutGlobalScopes()
            ->where('code', '631200000')
            ->where('is_header', true)
            ->orderByDesc('id')
            ->get();

        foreach ($legacyHeaders as $header) {
            $inUse = JournalItem::where('account_id', $header->id)->exists()
                || Account::withoutGlobalScopes()->where('parent_id', $header->id)->exists();

            if (! $inUse) {
                $header->delete();
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

