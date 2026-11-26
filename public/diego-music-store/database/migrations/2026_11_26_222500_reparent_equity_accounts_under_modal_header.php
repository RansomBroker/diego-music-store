<?php

use App\Models\Account;
use App\Models\JournalItem;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Re-parent Equity detail accounts (Prive, Laba Ditahan, Laba Tahun Berjalan, etc.)
     * under the 311100000 (MODAL) header per branch, and remove redundant empty
     * sub-headers (311200000, 311300000, 311400000) when unreferenced.
     */
    public function up(): void
    {
        if (app()->environment('testing')) {
            return;
        }

        $legacyHeaderCodes = ['311200000', '311300000', '311400000'];

        $legacyHeaderIds = Account::withoutGlobalScopes()
            ->whereIn('code', $legacyHeaderCodes)
            ->pluck('id')
            ->all();

        $equityDetailAccounts = Account::withoutGlobalScopes()
            ->where('is_header', false)
            ->where(function ($query) use ($legacyHeaderIds) {
                $query->where('classification', 'equity')
                    ->orWhere('code', 'like', '3%');

                if (! empty($legacyHeaderIds)) {
                    $query->orWhereIn('parent_id', $legacyHeaderIds);
                }
            })
            ->get();

        foreach ($equityDetailAccounts as $account) {
            $modalHeader = Account::withoutGlobalScopes()
                ->where('code', '311100000')
                ->where('is_header', true)
                ->when(
                    $account->branch_id === null,
                    fn ($q) => $q->whereNull('branch_id'),
                    fn ($q) => $q->where('branch_id', $account->branch_id)
                )
                ->first();

            if ($modalHeader && (int) $account->parent_id !== (int) $modalHeader->id) {
                $account->update(['parent_id' => $modalHeader->id]);
            }
        }

        foreach ($legacyHeaderCodes as $code) {
            $headers = Account::withoutGlobalScopes()
                ->where('code', $code)
                ->where('is_header', true)
                ->orderByDesc('id')
                ->get();

            foreach ($headers as $header) {
                $inUse = JournalItem::where('account_id', $header->id)->exists()
                    || Account::withoutGlobalScopes()->where('parent_id', $header->id)->exists();

                if (! $inUse) {
                    $header->delete();
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

