<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('employee_cash_advances', function (Blueprint $table) {
            $table->foreignId('disbursement_account_id')->nullable()->after('branch_id')->constrained('accounts')->nullOnDelete();
            $table->foreignId('journal_entry_id')->nullable()->after('disbursed_at')->constrained('journal_entries')->nullOnDelete();
            $table->string('journal_no')->nullable()->after('journal_entry_id');
        });

        Schema::table('payrolls', function (Blueprint $table) {
            $table->foreignId('payment_account_id')->nullable()->after('branch_id')->constrained('accounts')->nullOnDelete();
            $table->foreignId('journal_entry_id')->nullable()->after('paid_at')->constrained('journal_entries')->nullOnDelete();
            $table->string('journal_no')->nullable()->after('journal_entry_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_cash_advances', function (Blueprint $table) {
            $table->dropForeign(['disbursement_account_id']);
            $table->dropForeign(['journal_entry_id']);
            $table->dropColumn(['disbursement_account_id', 'journal_entry_id', 'journal_no']);
        });

        Schema::table('payrolls', function (Blueprint $table) {
            $table->dropForeign(['payment_account_id']);
            $table->dropForeign(['journal_entry_id']);
            $table->dropColumn(['payment_account_id', 'journal_entry_id', 'journal_no']);
        });
    }
};
