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
        Schema::create('cash_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_no')->unique();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->string('type'); // in, out, transfer
            $table->date('transaction_date');
            
            // source_account_id is credited
            $table->foreignId('source_account_id')->constrained('accounts')->cascadeOnDelete();
            
            // destination_account_id is debited
            $table->foreignId('destination_account_id')->constrained('accounts')->cascadeOnDelete();
            
            $table->bigInteger('amount');
            $table->text('notes')->nullable();
            $table->string('status')->default('draft'); // draft, posted, cancelled
            
            $table->foreignId('journal_entry_id')->nullable()->constrained('journal_entries')->nullOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            
            $table->dateTime('posted_at')->nullable();
            $table->foreignId('posted_by')->nullable()->constrained('users')->nullOnDelete();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cash_transactions');
    }
};
