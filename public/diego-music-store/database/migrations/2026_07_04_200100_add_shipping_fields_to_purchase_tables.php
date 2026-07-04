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
            $table->string('shipping_borne_by')->default('self_direct')->after('other_cost');
            $table->string('shipping_carrier_name')->nullable()->after('shipping_borne_by');
        });

        Schema::table('purchase_transactions', function (Blueprint $table) {
            $table->string('shipping_borne_by')->default('self_direct')->after('shipping_cost');
            $table->string('shipping_carrier_name')->nullable()->after('shipping_borne_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_transactions', function (Blueprint $table) {
            $table->dropColumn(['shipping_borne_by', 'shipping_carrier_name']);
        });

        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropColumn(['shipping_borne_by', 'shipping_carrier_name']);
        });
    }
};
