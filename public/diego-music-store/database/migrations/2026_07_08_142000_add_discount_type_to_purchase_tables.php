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
            $table->string('discount_type')->default('fixed')->after('total_amount'); // fixed, percent
            $table->bigInteger('discount_value')->default(0)->after('discount_type');
        });

        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->string('discount_type')->default('fixed')->after('price'); // fixed, percent
            $table->bigInteger('discount_value')->default(0)->after('discount_type');
        });

        Schema::table('purchase_transactions', function (Blueprint $table) {
            $table->string('discount_type')->default('fixed')->after('subtotal'); // fixed, percent
            $table->bigInteger('discount_value')->default(0)->after('discount_type');
        });

        Schema::table('purchase_transaction_details', function (Blueprint $table) {
            $table->string('discount_type')->default('fixed')->after('price'); // fixed, percent
            $table->bigInteger('discount_value')->default(0)->after('discount_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropColumn(['discount_type', 'discount_value']);
        });

        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->dropColumn(['discount_type', 'discount_value']);
        });

        Schema::table('purchase_transactions', function (Blueprint $table) {
            $table->dropColumn(['discount_type', 'discount_value']);
        });

        Schema::table('purchase_transaction_details', function (Blueprint $table) {
            $table->dropColumn(['discount_type', 'discount_value']);
        });
    }
};
