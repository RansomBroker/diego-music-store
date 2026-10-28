<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            if (!Schema::hasColumn('accounts', 'branch_id')) {
                $table->foreignId('branch_id')->nullable()->after('id')->constrained('branches')->cascadeOnDelete();
            }
        });

        // Set existing accounts to primary branch if branches table has records
        try {
            $firstBranchId = DB::table('branches')->orderBy('id')->value('id');
            if ($firstBranchId) {
                DB::table('accounts')->whereNull('branch_id')->update(['branch_id' => $firstBranchId]);
            }
        } catch (\Throwable $e) {
            // Silently continue if branches table is empty
        }

        Schema::table('accounts', function (Blueprint $table) {
            // Drop global unique on code
            try {
                $table->dropUnique(['code']);
            } catch (\Throwable $e) {
                try {
                    $table->dropUnique('accounts_code_unique');
                } catch (\Throwable $e2) {
                    // Ignore if already dropped or named differently
                }
            }

            // Add branch-scoped unique index
            $table->unique(['code', 'branch_id'], 'accounts_code_branch_unique');
            $table->index(['branch_id', 'is_active'], 'accounts_branch_is_active_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            try {
                $table->dropUnique('accounts_code_branch_unique');
            } catch (\Throwable $e) {
                // Ignore
            }

            try {
                $table->dropIndex('accounts_branch_is_active_idx');
            } catch (\Throwable $e) {
                // Ignore
            }

            if (Schema::hasColumn('accounts', 'branch_id')) {
                $table->dropForeign(['branch_id']);
                $table->dropColumn('branch_id');
            }

            $table->unique('code', 'accounts_code_unique');
        });
    }
};
