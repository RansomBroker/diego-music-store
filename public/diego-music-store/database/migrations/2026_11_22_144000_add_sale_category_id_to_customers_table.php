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
        Schema::table('customers', function (Blueprint $table) {
            if (! Schema::hasColumn('customers', 'sale_category_id')) {
                $table->foreignId('sale_category_id')
                    ->nullable()
                    ->after('pricing_tier_id')
                    ->constrained('sale_categories')
                    ->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            if (Schema::hasColumn('customers', 'sale_category_id')) {
                $table->dropForeign(['sale_category_id']);
                $table->dropColumn('sale_category_id');
            }
        });
    }
};

