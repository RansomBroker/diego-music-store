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
        Schema::create('supplier_payments', function (Blueprint $table) {
            $table->id();
            $table->string('payment_no')->unique();
            $table->date('payment_date');
            $table->foreignId('supplier_id')->constrained('suppliers')->onDelete('restrict');
            $table->foreignId('branch_id')->constrained('branches')->onDelete('restrict');
            $table->foreignId('account_id')->constrained('accounts')->onDelete('restrict'); // Cash/Bank Account
            $table->string('payment_method'); // Cash, Transfer, Giro, etc.
            $table->string('payment_reference')->nullable(); // Reference No. / Receipt
            $table->bigInteger('total_amount')->default(0);
            $table->text('notes')->nullable();
            $table->string('status')->default('draft'); // draft, posted, cancelled
            $table->timestamp('posted_at')->nullable();
            $table->string('journal_no')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });

        Schema::create('supplier_payment_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_payment_id')->constrained('supplier_payments')->onDelete('cascade');
            $table->foreignId('purchase_transaction_id')->constrained('purchase_transactions')->onDelete('restrict');
            $table->bigInteger('amount_due')->default(0); // The remaining debt at the time of paying
            $table->bigInteger('amount_paid')->default(0); // The amount paid for this transaction
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplier_payment_items');
        Schema::dropIfExists('supplier_payments');
    }
};
