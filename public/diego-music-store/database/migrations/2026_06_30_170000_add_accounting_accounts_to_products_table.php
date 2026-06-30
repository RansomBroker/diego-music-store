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
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('inventory_account_id')->nullable()->constrained('accounts')->onDelete('restrict');
            $table->foreignId('sales_account_id')->nullable()->constrained('accounts')->onDelete('restrict');
            $table->foreignId('cogs_account_id')->nullable()->constrained('accounts')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['inventory_account_id']);
            $table->dropForeign(['sales_account_id']);
            $table->dropForeign(['cogs_account_id']);
            $table->dropColumn(['inventory_account_id', 'sales_account_id', 'cogs_account_id']);
        });
    }
};
