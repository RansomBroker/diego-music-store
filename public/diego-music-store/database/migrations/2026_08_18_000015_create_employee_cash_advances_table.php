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
        Schema::create('employee_cash_advances', function (Blueprint $table) {
            $table->id();
            $table->string('advance_number')->unique(); // e.g. ADV-202608-0001
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->date('request_date');
            
            $table->decimal('amount', 15, 2)->default(0); // Nominal kasbon disetujui e.g. 1200000
            $table->integer('tenor_months')->default(1); // Jumlah bulan cicilan e.g. 3
            $table->decimal('monthly_installment', 15, 2)->default(0); // Nominal cicilan per bulan e.g. 400000
            $table->decimal('paid_amount', 15, 2)->default(0); // Total terpotong via payroll
            $table->decimal('remaining_amount', 15, 2)->default(0); // Sisa saldo hutang kasbon

            $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled', 'paid_off'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('disbursed_at')->nullable();
            
            $table->text('reason')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_cash_advances');
    }
};
