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
        Schema::create('commission_schemes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->string('name');
            $table->enum('calculation_type', ['percentage', 'fixed_amount'])->default('percentage');
            $table->decimal('rate', 12, 2)->default(0);
            $table->enum('applies_to', ['all_sales', 'category', 'product'])->default('all_sales');
            $table->foreignId('target_product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->foreignId('target_sale_category_id')->nullable()->constrained('sale_categories')->nullOnDelete();
            $table->decimal('min_monthly_sales_target', 12, 2)->nullable()->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commission_schemes');
    }
};
