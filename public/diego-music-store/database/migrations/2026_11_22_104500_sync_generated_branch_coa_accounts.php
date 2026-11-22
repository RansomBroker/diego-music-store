<?php

use App\Actions\Branch\EnsureBranchCoaAccounts;
use App\Models\Branch;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Ensure every branch has dedicated generated inventory and inter-branch accounts
     * (preserving legacy 11410001/14110001/21110001 accounts) and move any InitialStock
     * journal items away from generic 111401001 onto the branch's generated account.
     */
    public function up(): void
    {
        if (app()->environment('testing')) {
            return;
        }

        foreach (Branch::query()->orderBy('id')->get() as $branch) {
            $updatedBranch = EnsureBranchCoaAccounts::execute($branch);
            EnsureBranchCoaAccounts::relinkInitialStockToGeneratedInventory($updatedBranch);
        }
    }

    /**
     * Non-destructive no-op: generated accounts and journal links are preserved on rollback.
     */
    public function down(): void
    {
        // Non-destructive no-op
    }
};

