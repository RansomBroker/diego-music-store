<?php

namespace App\Actions\Branch;

use App\Models\Account;
use App\Models\Branch;
use Illuminate\Support\Facades\DB;

class EnsureBranchCoaAccounts
{
    /**
     * Generic CSV COA codes that must never be renamed or hijacked as branch-specific generated accounts.
     *
     * @var array<int, string>
     */
    private const GENERIC_SYSTEM_CODES = [
        '111401001',
        '111601001',
        '211601001',
        '111301001',
        '211101001',
        '111800000',
        '111600000',
        '211300000',
    ];

    /**
     * Ensure a branch has all 97 CSV COA accounts plus its generated branch-specific
     * inventory, inter-branch receivable, and inter-branch payable accounts.
     */
    public static function execute(Branch $branch): Branch
    {
        // ProvisionBranchCoaAccounts finishes by calling ensureGeneratedAccounts($branch)
        app(ProvisionBranchCoaAccounts::class)->execute($branch);

        return $branch->fresh();
    }

    /**
     * Ensure dedicated generated branch accounts exist without overwriting existing CSV provisioning.
     */
    public static function ensureGeneratedAccounts(Branch $branch): Branch
    {
        return DB::transaction(function () use ($branch) {
            $branch->refresh();

            $inventoryParent = Account::where('branch_id', $branch->id)->where('code', '111800000')->first()
                ?? Account::where('code', '111800000')->first();
            $receivableParent = Account::where('branch_id', $branch->id)->where('code', '111600000')->first()
                ?? Account::where('code', '111600000')->first();
            // Inter-branch balances are not supplier trade payables.
            $payableParent = Account::where('branch_id', $branch->id)->where('code', '211300000')->first()
                ?? Account::where('code', '211300000')->first();

            $branch->inventory_account_id = static::ensureBranchAccount(
                    branch: $branch,
                    accountId: $branch->inventory_account_id,
                    parent: $inventoryParent,
                    existingName: 'Persediaan Barang Dagang - ' . $branch->name,
                    codePrefix: '11180',
                    classification: 'asset',
                    subtype: 'inventory',
                    normalBalance: 'debit',
                )->id;

            $branch->interbranch_receivable_account_id = static::ensureBranchAccount(
                    branch: $branch,
                    accountId: $branch->interbranch_receivable_account_id,
                    parent: $receivableParent,
                    existingName: 'Piutang Antar Cabang - ' . $branch->name,
                    codePrefix: '11160',
                    classification: 'asset',
                    subtype: 'receivable',
                    normalBalance: 'debit',
                )->id;

            $branch->interbranch_payable_account_id = static::ensureBranchAccount(
                    branch: $branch,
                    accountId: $branch->interbranch_payable_account_id,
                    parent: $payableParent,
                    existingName: 'Hutang Antar Cabang - ' . $branch->name,
                    codePrefix: '21130',
                    classification: 'liability',
                    subtype: 'other_payable',
                    normalBalance: 'credit',
                )->id;

            $branch->save();

            return $branch->fresh();
        });
    }

    /**
     * Find and repair an existing branch-specific account, or create one.
     * Existing generated/legacy account IDs and codes (e.g. 11410001/14110001) are preserved.
     */
    private static function ensureBranchAccount(
        Branch $branch,
        ?int $accountId,
        ?Account $parent,
        string $existingName,
        string $codePrefix,
        string $classification,
        string $subtype,
        string $normalBalance,
    ): Account {
        $account = $accountId
            ? Account::find($accountId)
            : null;

        if ($account && in_array($account->code, self::GENERIC_SYSTEM_CODES, true)) {
            // Restore generic 111401001 name if a prior bug renamed it into a branch inventory name
            if ($account->code === '111401001' && str_starts_with(mb_strtolower($account->name), 'persediaan barang dagang -')) {
                $account->update(['name' => 'PERSEDIAAN BARANG DAGANG']);
            }
            $account = null;
        }

        // Preserve any non-generic account explicitly linked on the branch (e.g., 11410001, 14110001)
        if ($account) {
            $dirty = false;
            if ($account->branch_id === null) {
                $account->branch_id = $branch->id;
                $dirty = true;
            }
            if ($account->parent_id === null && $parent) {
                $account->parent_id = $parent->id;
                $dirty = true;
            }
            if ($dirty) {
                $account->save();
            }

            return $account;
        }

        $account = Account::query()
            ->whereRaw('LOWER(name) = ?', [mb_strtolower($existingName)])
            ->where(function ($q) use ($branch): void {
                $q->where('branch_id', $branch->id)->orWhereNull('branch_id');
            })
            ->whereNotIn('code', self::GENERIC_SYSTEM_CODES)
            ->first();

        $attributes = [
            'branch_id' => $branch->id,
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
     * Re-link InitialStock journal items on a branch from generic 111401001
     * to the branch's dedicated generated inventory account.
     */
    public static function relinkInitialStockToGeneratedInventory(Branch $branch): int
    {
        $branch->refresh();
        $generatedInventory = $branch->inventoryAccount;

        if (! $generatedInventory || in_array($generatedInventory->code, self::GENERIC_SYSTEM_CODES, true)) {
            return 0;
        }

        $genericIds = Account::query()
            ->where('code', '111401001')
            ->pluck('id');

        if ($genericIds->isEmpty()) {
            return 0;
        }

        $entryIds = \App\Models\JournalEntry::query()
            ->where('branch_id', $branch->id)
            ->where('reference_type', 'InitialStock')
            ->pluck('id');

        if ($entryIds->isEmpty()) {
            return 0;
        }

        return (int) \App\Models\JournalItem::query()
            ->whereIn('journal_entry_id', $entryIds)
            ->whereIn('account_id', $genericIds)
            ->update(['account_id' => $generatedInventory->id]);
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
