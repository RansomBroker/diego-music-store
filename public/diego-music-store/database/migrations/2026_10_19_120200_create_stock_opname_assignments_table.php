<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_opname_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_opname_period_id')->constrained('stock_opname_periods')->cascadeOnDelete();
            $table->foreignId('stock_opname_preset_id')->constrained('stock_opname_presets')->restrictOnDelete();
            $table->foreignId('assigned_user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('stock_opname_id')->nullable()->unique()->constrained('stock_opnames')->restrictOnDelete();
            $table->string('status')->default('assigned');
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('review_notes')->nullable();
            $table->timestamps();

            $table->unique(['stock_opname_period_id', 'stock_opname_preset_id'], 'so_assignment_period_preset_unique');
            $table->index(['assigned_user_id', 'status']);
            $table->index(['stock_opname_period_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_opname_assignments');
    }
};
