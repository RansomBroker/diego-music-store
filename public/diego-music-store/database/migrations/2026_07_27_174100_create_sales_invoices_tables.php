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
        Schema::create('sales_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->foreignId('sales_quotation_id')->nullable()->constrained('sales_quotations')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('posted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('invoice_number')->unique();
            $table->date('invoice_date');
            $table->date('due_date')->nullable();
            $table->string('payment_type')->default('Tunai'); // Tunai, Kredit
            $table->string('status')->default('draft'); // draft, posted, cancelled
            $table->bigInteger('subtotal')->default(0);
            $table->string('discount_type')->default('fixed'); // fixed, percent
            $table->bigInteger('discount_value')->default(0);
            $table->bigInteger('discount_amount')->default(0);
            $table->integer('tax_rate')->default(0);
            $table->bigInteger('tax_amount')->default(0);
            $table->bigInteger('shipping_cost')->default(0);
            $table->bigInteger('grand_total')->default(0);
            $table->text('notes')->nullable();
            $table->timestamp('posted_at')->nullable();
            $table->string('journal_no')->nullable();
            $table->timestamps();
        });

        Schema::create('sales_invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_invoice_id')->constrained('sales_invoices')->cascadeOnDelete();
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
        Schema::dropIfExists('sales_invoice_items');
        Schema::dropIfExists('sales_invoices');
    }
};
