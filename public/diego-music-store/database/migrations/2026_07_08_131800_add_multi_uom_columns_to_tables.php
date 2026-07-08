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
        // 1. Add base_unit_id and conversion_factor to units table
        Schema::table('units', function (Blueprint $table) {
            $table->foreignId('base_unit_id')->nullable()->after('code')->constrained('units')->onDelete('restrict');
            $table->integer('conversion_factor')->default(1)->after('base_unit_id');
        });

        // 2. Add unit_id to purchase_order_items table
        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->foreignId('unit_id')->nullable()->after('quantity')->constrained('units')->onDelete('restrict');
        });

        // 3. Add unit_id and original_quantity to stock_movements table
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->foreignId('unit_id')->nullable()->after('quantity')->constrained('units')->onDelete('restrict');
            $table->integer('original_quantity')->nullable()->after('unit_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            if (Schema::hasColumn('stock_movements', 'unit_id')) {
                $table->dropForeign(['unit_id']);
                $table->dropColumn(['unit_id', 'original_quantity']);
            }
        });

        Schema::table('purchase_order_items', function (Blueprint $table) {
            if (Schema::hasColumn('purchase_order_items', 'unit_id')) {
                $table->dropForeign(['unit_id']);
                $table->dropColumn(['unit_id']);
            }
        });

        Schema::table('units', function (Blueprint $table) {
            if (Schema::hasColumn('units', 'base_unit_id')) {
                $table->dropForeign(['base_unit_id']);
                $table->dropColumn(['base_unit_id', 'conversion_factor']);
            }
        });
    }
};
