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
        Schema::create('kpi_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('position')->nullable(); // e.g. Sales Executive, Cashier, Store Manager
            $table->foreignId('employee_id')->nullable()->constrained('employees')->nullOnDelete(); // Override per specific employee
            
            // Maximum Bonus Amount
            $table->decimal('max_bonus_amount', 15, 2)->default(0);

            // 4 Key Indicators: Target Values & Weight Percentages (Total weights = 100%)
            $table->decimal('target_sales_amount', 15, 2)->default(0);
            $table->decimal('weight_sales', 5, 2)->default(40.00); // 40%

            $table->decimal('target_atv_amount', 15, 2)->default(0); // Average Transaction Value
            $table->decimal('weight_atv', 5, 2)->default(20.00); // 20%

            $table->decimal('target_attendance_pct', 5, 2)->default(95.00); // % Presence
            $table->decimal('weight_attendance', 5, 2)->default(20.00); // 20%

            $table->decimal('target_punctuality_pct', 5, 2)->default(95.00); // % Punctual Attendance
            $table->decimal('weight_punctuality', 5, 2)->default(20.00); // 20%

            // Tiering Rules JSON: e.g. [{"min_score": 90, "bonus_pct": 100}, {"min_score": 75, "bonus_pct": 75}, {"min_score": 0, "bonus_pct": 0}]
            $table->json('bonus_tiering_rules')->nullable();

            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kpi_templates');
    }
};
