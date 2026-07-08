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
        Schema::table('purchase_transactions', function (Blueprint $table) {
            $table->string('tax_invoice_no')->nullable()->after('invoice_number');
        });

        Schema::table('purchase_transaction_details', function (Blueprint $table) {
            $table->boolean('update_cost_price')->default(false)->after('price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_transactions', function (Blueprint $table) {
            $table->dropColumn('tax_invoice_no');
        });

        Schema::table('purchase_transaction_details', function (Blueprint $table) {
            $table->dropColumn('update_cost_price');
        });
    }
};
