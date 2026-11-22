<?php

use App\Actions\Branch\EnsureBranchCoaAccounts;
use App\Models\Branch;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Scope generated branch inventory and inter-branch accounts strictly to their owning branch
     * so one branch's accounts never appear under another branch's accounting view/reports.
     */
    public function up(): void
    {
        if (app()->environment('testing')) {
            return;
        }

        foreach (Branch::query()->orderBy('id')->get() as $branch) {
            EnsureBranchCoaAccounts::ensureGeneratedAccounts($branch);
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

