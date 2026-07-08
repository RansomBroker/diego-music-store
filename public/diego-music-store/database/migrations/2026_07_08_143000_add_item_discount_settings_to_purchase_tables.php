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
            $table->boolean('enable_item_discount')->default(false)->after('enable_tax');
            $table->string('item_discount_type')->default('fixed')->after('enable_item_discount');
        });

        Schema::table('purchase_transactions', function (Blueprint $table) {
            $table->boolean('enable_item_discount')->default(false)->after('enable_tax');
            $table->string('item_discount_type')->default('fixed')->after('enable_item_discount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropColumn(['enable_item_discount', 'item_discount_type']);
        });

        Schema::table('purchase_transactions', function (Blueprint $table) {
            $table->dropColumn(['enable_item_discount', 'item_discount_type']);
        });
    }
};
