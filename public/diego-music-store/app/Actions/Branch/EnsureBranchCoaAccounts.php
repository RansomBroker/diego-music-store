<?php

namespace App\Actions\Branch;

use App\Models\Account;
use App\Models\Branch;
use Illuminate\Support\Facades\DB;

class EnsureBranchCoaAccounts
{
    /**
     * Ensure a branch has its inventory, inter-branch receivable, and
     * inter-branch payable accounts. The operation is safe to run repeatedly.
     */
    public static function execute(Branch $branch): Branch
    {
        return DB::transaction(function () use ($branch) {
            $branch->refresh();

            $inventoryParent = Account::where('code', '111800000')->first();
            $receivableParent = Account::where('code', '111600000')->first();
            // Inter-branch balances are not supplier trade payables.
            $payableParent = Account::where('code', '211300000')->first();

            if (!$branch->inventory_account_id) {
                $branch->inventory_account_id = static::ensureBranchAccount(
                    branch: $branch,
                    parent: $inventoryParent,
                    existingName: 'Persediaan Barang Dagang - ' . $branch->name,
                    codePrefix: '11180',
                    classification: 'asset',
                    subtype: 'inventory',
                    normalBalance: 'debit',
                )->id;
            }

            if (!$branch->interbranch_receivable_account_id) {
                $branch->interbranch_receivable_account_id = static::ensureBranchAccount(
                    branch: $branch,
                    parent: $receivableParent,
                    existingName: 'Piutang Antar Cabang - ' . $branch->name,
                    codePrefix: '11160',
                    classification: 'asset',
                    subtype: 'receivable',
                    normalBalance: 'debit',
                )->id;
            }

            if (!$branch->interbranch_payable_account_id) {
                $branch->interbranch_payable_account_id = static::ensureBranchAccount(
                    branch: $branch,
                    parent: $payableParent,
                    existingName: 'Hutang Antar Cabang - ' . $branch->name,
                    codePrefix: '21130',
                    classification: 'liability',
                    subtype: 'other_payable',
                    normalBalance: 'credit',
                )->id;
            }

            $branch->save();

            return $branch->fresh();
        });
    }

    /**
     * Find and repair an existing branch-specific account, or create one.
     * Existing account IDs/codes are preserved to avoid breaking journal links.
     */
    private static function ensureBranchAccount(
        Branch $branch,
        ?Account $parent,
        string $existingName,
        string $codePrefix,
        string $classification,
        string $subtype,
        string $normalBalance,
    ): Account {
        $account = Account::query()
            ->where(function ($query) use ($existingName) {
                $query->whereRaw('LOWER(name) = ?', [mb_strtolower($existingName)]);
            })
            ->first();

        $attributes = [
            'name' => $existingName,
            'classification' => $classification,
            'account_subtype' => $subtype,
            'normal_balance' => $normalBalance,
            'is_active' => true,
            'is_header' => false,
        ];

        // Keep an existing parent when the canonical parent is not seeded yet.
        if ($parent) {
            $attributes['parent_id'] = $parent->id;
        }

        if ($account) {
            $account->fill($attributes);
            $account->save();

            return $account;
        }

        // COA codes use a consistent 9-digit format: 5-digit family + 4-digit branch suffix.
        $suffix = max(1, (int) $branch->id);
        do {
            $code = $codePrefix . str_pad((string) $suffix, 4, '0', STR_PAD_LEFT);
            $suffix++;
        } while (Account::where('code', $code)->exists());

        return Account::create($attributes + [
            'code' => $code,
            'parent_id' => $parent?->id,
        ]);
    }

    /**
     * Provision accounts for every branch that is missing any COA link.
     */
    public static function executeAll(): int
    {
        $count = 0;

        foreach (Branch::query()->orderBy('id')->get() as $branch) {
            static::execute($branch);
            $count++;
        }

        return $count;
    }
}
