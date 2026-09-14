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
        Schema::create('attendance_violation_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('employee_attendance_id')->nullable()->constrained('employee_attendances')->nullOnDelete();
            $table->foreignId('violation_rule_id')->nullable()->constrained('attendance_violation_rules')->nullOnDelete();
            $table->date('date');
            $table->enum('violation_type', ['late_in', 'early_out', 'unexcused_absence', 'leave_over_quota', 'custom']);
            $table->integer('late_early_minutes')->default(0);
            $table->decimal('deduction_amount', 12, 2)->default(0);
            $table->string('notes')->nullable();
            $table->enum('status', ['pending', 'approved', 'waived', 'deducted_in_payroll'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('payroll_period')->nullable(); // YYYY-MM
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_violation_logs');
    }
};
