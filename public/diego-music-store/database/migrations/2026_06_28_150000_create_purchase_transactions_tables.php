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
        // Create purchase_transactions table
        Schema::create('purchase_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_no')->unique();
            $table->date('transaction_date');
            $table->foreignId('po_id')->nullable()->constrained('purchase_orders')->onDelete('set null');
            $table->foreignId('supplier_id')->constrained('suppliers')->onDelete('restrict');
            $table->foreignId('branch_id')->constrained('branches')->onDelete('restrict');
            $table->foreignId('warehouse_id')->nullable()->constrained('branches')->onDelete('restrict'); // Maps to branches as destination
            $table->string('purchase_type'); // Tunai, Kredit
            $table->string('invoice_number')->nullable();
            $table->string('delivery_note_number')->nullable();
            $table->date('invoice_date')->nullable();
            $table->date('due_date')->nullable();
            
            // Financial fields
            $table->bigInteger('subtotal')->default(0);
            $table->bigInteger('discount')->default(0);
            $table->bigInteger('shipping_cost')->default(0);
            $table->bigInteger('other_cost')->default(0);
            $table->bigInteger('tax_amount')->default(0); // PPN
            $table->bigInteger('pph_amount')->default(0); // PPh
            $table->bigInteger('grand_total')->default(0);
            
            $table->string('status')->default('draft'); // draft, posted, cancelled
            $table->timestamp('posted_at')->nullable();
            $table->string('journal_no')->nullable();
            
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });

        // Create purchase_transaction_details table
        Schema::create('purchase_transaction_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_transaction_id')->constrained('purchase_transactions')->onDelete('cascade');
            $table->foreignId('product_variant_id')->constrained('product_variants')->onDelete('restrict');
            $table->integer('qty_po')->nullable();
            $table->integer('qty_received');
            $table->foreignId('unit_id')->nullable()->constrained('units')->onDelete('restrict');
            $table->bigInteger('price');
            $table->bigInteger('discount')->default(0);
            $table->integer('tax_rate')->default(0);
            $table->bigInteger('tax_amount')->default(0);
            $table->bigInteger('subtotal');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_transaction_details');
        Schema::dropIfExists('purchase_transactions');
    }
};
