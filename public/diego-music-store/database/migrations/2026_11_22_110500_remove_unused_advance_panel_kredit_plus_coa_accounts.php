<?php

use App\Models\Account;
use App\Models\Branch;
use App\Models\JournalItem;
use App\Models\PaymentMethod;
use App\Models\Product;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Remove unused COA accounts (111201006 Uang Muka Pembelian, 111202005 Kredit Plus,
     * 111202008 Panel) and their resulting empty headers (111300000, 111400000)
     * strictly when unreferenced by any journals, payment methods, products, or branches.
     */
    public function up(): void
    {
        if (app()->environment('testing')) {
            return;
        }

        $this->deleteUnusedAccounts(['111201006', '111202005', '111202008'], false);
        $this->deleteUnusedAccounts(['111300000', '111400000'], true);
    }

    /**
     * @param  array<int, string>  $codes
     */
    private function deleteUnusedAccounts(array $codes, bool $headersOnly): void
    {
        $query = Account::query()->whereIn('code', $codes);
        if ($headersOnly) {
            $query->where('is_header', true);
        }

        foreach ($query->orderByDesc('id')->get() as $account) {
            $isReferenced = JournalItem::where('account_id', $account->id)->exists()
                || PaymentMethod::where('account_id', $account->id)->exists()
                || Account::where('parent_id', $account->id)->exists()
                || Branch::where('inventory_account_id', $account->id)
                    ->orWhere('interbranch_receivable_account_id', $account->id)
                    ->orWhere('interbranch_payable_account_id', $account->id)
                    ->exists()
                || Product::where('inventory_account_id', $account->id)
                    ->orWhere('sales_account_id', $account->id)
                    ->orWhere('cogs_account_id', $account->id)
                    ->exists();

            if (! $isReferenced) {
                $account->delete();
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

