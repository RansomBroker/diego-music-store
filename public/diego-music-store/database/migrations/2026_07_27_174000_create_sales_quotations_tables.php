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
        Schema::create('sales_quotations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('quotation_number')->unique();
            $table->date('quotation_date');
            $table->date('valid_until')->nullable();
            $table->string('status')->default('draft'); // draft, sent, approved, rejected, closed
            $table->bigInteger('subtotal')->default(0);
            $table->string('discount_type')->default('fixed'); // fixed, percent
            $table->bigInteger('discount_value')->default(0);
            $table->bigInteger('discount_amount')->default(0);
            $table->integer('tax_rate')->default(0);
            $table->bigInteger('tax_amount')->default(0);
            $table->bigInteger('grand_total')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('sales_quotation_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_quotation_id')->constrained('sales_quotations')->cascadeOnDelete();
            $table->foreignId('product_variant_id')->constrained('product_variants')->cascadeOnDelete();
            $table->foreignId('unit_id')->nullable()->constrained('units')->nullOnDelete();
            $table->integer('quantity')->default(1);
            $table->bigInteger('price')->default(0);
            $table->string('discount_type')->default('fixed');
            $table->bigInteger('discount_value')->default(0);
            $table->bigInteger('discount_amount')->default(0);
            $table->bigInteger('subtotal')->default(0);
            $table->string('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_quotation_items');
        Schema::dropIfExists('sales_quotations');
    }
};
