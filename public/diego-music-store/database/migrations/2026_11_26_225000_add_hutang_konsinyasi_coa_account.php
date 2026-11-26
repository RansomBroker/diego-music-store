<?php

use App\Models\Account;
use App\Models\Branch;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Add 211101002 (HUTANG KONSINYASI) under 211100000 (HUTANG DAGANG)
     * for global master COA and all existing branches.
     */
    public function up(): void
    {
        if (app()->environment('testing')) {
            return;
        }

        $branchIds = array_merge([null], Branch::pluck('id')->all());

        foreach ($branchIds as $branchId) {
            $header = Account::withoutGlobalScopes()
                ->where('code', '211100000')
                ->where('is_header', true)
                ->when(
                    $branchId === null,
                    fn ($q) => $q->whereNull('branch_id'),
                    fn ($q) => $q->where('branch_id', $branchId)
                )
                ->first();

            if (! $header) {
                continue;
            }

            Account::withoutGlobalScopes()->updateOrCreate(
                [
                    'code' => '211101002',
                    'branch_id' => $branchId,
                ],
                [
                    'name' => 'HUTANG KONSINYASI',
                    'classification' => 'liability',
                    'account_subtype' => 'trade_payable',
                    'normal_balance' => 'credit',
                    'is_header' => false,
                    'parent_id' => $header->id,
                    'is_active' => true,
                ]
            );
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

