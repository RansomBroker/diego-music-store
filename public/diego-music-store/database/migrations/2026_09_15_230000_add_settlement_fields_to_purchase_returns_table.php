<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_returns', function (Blueprint $table) {
            if (!Schema::hasColumn('purchase_returns', 'return_type')) {
                $table->string('return_type')->default('invoice_deduction')->after('status');
            }
            if (!Schema::hasColumn('purchase_returns', 'refund_account_id')) {
                $table->foreignId('refund_account_id')->nullable()->after('return_type')->constrained('accounts')->nullOnDelete();
            }
            if (!Schema::hasColumn('purchase_returns', 'replacement_status')) {
                $table->string('replacement_status')->default('none')->after('refund_account_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('purchase_returns', function (Blueprint $table) {
            if (Schema::hasColumn('purchase_returns', 'refund_account_id')) {
                $table->dropConstrainedForeignId('refund_account_id');
            }
            if (Schema::hasColumn('purchase_returns', 'replacement_status')) {
                $table->dropColumn('replacement_status');
            }
            if (Schema::hasColumn('purchase_returns', 'return_type')) {
                $table->dropColumn('return_type');
            }
        });
    }
};
