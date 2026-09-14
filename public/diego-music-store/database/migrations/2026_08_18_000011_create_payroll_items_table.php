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
        Schema::create('payroll_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_id')->constrained('payrolls')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();

            $table->decimal('basic_salary', 15, 2)->default(0);
            $table->decimal('allowance_amount', 15, 2)->default(0);
            $table->json('allowance_details')->nullable(); // e.g. [{"name": "Tunjangan Jabatan", "amount": 250000}]

            $table->decimal('commission_amount', 15, 2)->default(0);
            $table->decimal('kpi_bonus_amount', 15, 2)->default(0);

            $table->decimal('violation_deduction_amount', 15, 2)->default(0);
            $table->decimal('other_deduction_amount', 15, 2)->default(0);
            $table->json('deduction_details')->nullable(); // e.g. [{"name": "Kasbon", "amount": 100000}]

            $table->decimal('net_salary', 15, 2)->default(0);

            $table->string('bank_name')->nullable();
            $table->string('bank_account_number')->nullable();
            $table->string('bank_account_holder')->nullable();

            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_items');
    }
};
