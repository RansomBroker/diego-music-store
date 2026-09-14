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
        Schema::create('attendance_violation_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('violation_type', ['late_in', 'early_out', 'unexcused_absence', 'leave_over_quota', 'custom']);
            $table->integer('min_minutes')->default(0);
            $table->integer('max_minutes')->nullable();
            $table->enum('deduction_type', ['fixed_amount', 'percentage_per_minute', 'percentage_daily_salary', 'per_occurrence'])->default('fixed_amount');
            $table->decimal('deduction_amount', 12, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_violation_rules');
    }
};
