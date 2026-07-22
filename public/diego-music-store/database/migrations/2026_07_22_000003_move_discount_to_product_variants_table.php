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
            $table->dropColumn(['discount_value', 'discount_type']);
        });

        Schema::table('product_variants', function (Blueprint $table) {
            $table->decimal('discount_value', 15, 2)->default(0)->after('price');
            $table->string('discount_type')->default('fixed')->after('discount_value'); // 'fixed', 'percent'
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropColumn(['discount_value', 'discount_type']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->decimal('discount_value', 15, 2)->default(0)->after('supplier_id');
            $table->string('discount_type')->default('fixed')->after('discount_value');
        });
    }
};
