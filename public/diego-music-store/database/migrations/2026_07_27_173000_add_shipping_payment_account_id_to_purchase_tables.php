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
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->foreignId('shipping_payment_account_id')->nullable()->constrained('accounts')->nullOnDelete();
        });

        Schema::table('purchase_transactions', function (Blueprint $table) {
            $table->foreignId('shipping_payment_account_id')->nullable()->constrained('accounts')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropForeign(['shipping_payment_account_id']);
            $table->dropColumn('shipping_payment_account_id');
        });

        Schema::table('purchase_transactions', function (Blueprint $table) {
            $table->dropForeign(['shipping_payment_account_id']);
            $table->dropColumn('shipping_payment_account_id');
        });
    }
};
