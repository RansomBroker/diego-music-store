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
            $table->index(['is_active', 'category'], 'idx_products_active_category');
            $table->index('name', 'idx_products_name');
        });

        Schema::table('product_variants', function (Blueprint $table) {
            $table->index(['is_active', 'product_id'], 'idx_variants_active_product');
            $table->index('barcode', 'idx_variants_barcode');
            $table->index('name', 'idx_variants_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropIndex('idx_variants_active_product');
            $table->dropIndex('idx_variants_barcode');
            $table->dropIndex('idx_variants_name');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('idx_products_active_category');
            $table->dropIndex('idx_products_name');
        });
    }
};
