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
        Schema::create('kpi_evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('kpi_template_id')->nullable()->constrained('kpi_templates')->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->string('period'); // YYYY-MM

            // Actual values & Scores
            $table->decimal('actual_sales_amount', 15, 2)->default(0);
            $table->decimal('sales_score', 5, 2)->default(0); // 0 - 100%

            $table->decimal('actual_atv_amount', 15, 2)->default(0);
            $table->decimal('atv_score', 5, 2)->default(0);

            $table->decimal('actual_attendance_pct', 5, 2)->default(0);
            $table->decimal('attendance_score', 5, 2)->default(0);

            $table->decimal('actual_punctuality_pct', 5, 2)->default(0);
            $table->decimal('punctuality_score', 5, 2)->default(0);

            // Composite Final Score & Earned Bonus
            $table->decimal('final_kpi_score', 5, 2)->default(0); // Weighted composite score 0-100%
            $table->decimal('earned_bonus_amount', 15, 2)->default(0);

            $table->enum('status', ['draft', 'approved'])->default('draft');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->unique(['employee_id', 'period']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kpi_evaluations');
    }
};
