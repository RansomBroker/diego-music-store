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
        // 1. Sales & POS Transactions
        Schema::table('sales', function (Blueprint $table) {
            $table->index('cash_session_id', 'idx_sales_cash_session');
            $table->index(['branch_id', 'status', 'invoice_date'], 'idx_sales_branch_status_date');
            $table->index('invoice_date', 'idx_sales_invoice_date');
            $table->index('status', 'idx_sales_status');
        });

        // 2. Cash Sessions & Transactions
        Schema::table('cash_sessions', function (Blueprint $table) {
            $table->index(['branch_id', 'status'], 'idx_cash_sessions_branch_status');
        });

        Schema::table('cash_transactions', function (Blueprint $table) {
            $table->index(['branch_id', 'transaction_date'], 'idx_cash_trans_branch_date');
            $table->index('status', 'idx_cash_trans_status');
        });

        // 3. Stock Movements (Stock Card & Polymorphic References)
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->index(['branch_id', 'product_variant_id', 'created_at'], 'idx_movements_branch_variant_date');
            $table->index(['reference_type', 'reference_id'], 'idx_movements_morph_ref');
        });

        // 4. Accounting (Journal Entries)
        Schema::table('journal_entries', function (Blueprint $table) {
            $table->index(['date', 'status'], 'idx_journal_date_status');
            $table->index(['branch_id', 'date'], 'idx_journal_branch_date');
            $table->index(['reference_type', 'reference_id'], 'idx_journal_morph_ref');
        });

        // 5. Purchases
        Schema::table('purchase_transactions', function (Blueprint $table) {
            $table->index(['branch_id', 'status', 'transaction_date'], 'idx_purchases_branch_status_date');
            $table->index(['status', 'due_date'], 'idx_purchases_status_due_date');
        });

        // 6. Customers
        Schema::table('customers', function (Blueprint $table) {
            $table->index('name', 'idx_customers_name');
        });

        // 7. POS Held Transactions
        Schema::table('pos_held_transactions', function (Blueprint $table) {
            $table->index(['user_id', 'branch_id', 'updated_at'], 'idx_held_user_branch_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pos_held_transactions', function (Blueprint $table) {
            $table->dropIndex('idx_held_user_branch_date');
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->dropIndex('idx_customers_name');
        });

        Schema::table('purchase_transactions', function (Blueprint $table) {
            $table->dropIndex('idx_purchases_branch_status_date');
            $table->dropIndex('idx_purchases_status_due_date');
        });

        Schema::table('journal_entries', function (Blueprint $table) {
            $table->dropIndex('idx_journal_date_status');
            $table->dropIndex('idx_journal_branch_date');
            $table->dropIndex('idx_journal_morph_ref');
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropIndex('idx_movements_branch_variant_date');
            $table->dropIndex('idx_movements_morph_ref');
        });

        Schema::table('cash_transactions', function (Blueprint $table) {
            $table->dropIndex('idx_cash_trans_branch_date');
            $table->dropIndex('idx_cash_trans_status');
        });

        Schema::table('cash_sessions', function (Blueprint $table) {
            $table->dropIndex('idx_cash_sessions_branch_status');
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->dropIndex('idx_sales_cash_session');
            $table->dropIndex('idx_sales_branch_status_date');
            $table->dropIndex('idx_sales_invoice_date');
            $table->dropIndex('idx_sales_status');
        });
    }
};
