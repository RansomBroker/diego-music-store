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
        Schema::create('sales_queue_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->integer('queue_order')->default(1);
            $table->enum('status', ['waiting', 'on_duty', 'off_duty'])->default('waiting');
            $table->timestamp('duty_started_at')->nullable();
            $table->unsignedBigInteger('current_duty_log_id')->nullable();
            $table->integer('turns_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->date('date');
            $table->timestamps();

            $table->index(['branch_id', 'date']);
            $table->index(['user_id', 'date']);
            $table->index(['branch_id', 'status', 'queue_order']);
        });

        Schema::create('sales_duty_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->foreignId('cash_session_id')->nullable()->constrained('cash_sessions')->nullOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('queue_member_id')->nullable()->constrained('sales_queue_members')->nullOnDelete();
            $table->timestamp('started_at');
            $table->timestamp('ended_at')->nullable();
            $table->integer('duration_seconds')->default(0);
            $table->enum('status', ['serving', 'completed', 'cancelled'])->default('serving');
            $table->foreignId('sale_id')->nullable()->constrained('sales')->nullOnDelete();
            $table->decimal('closing_amount', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['branch_id', 'started_at']);
            $table->index(['user_id', 'started_at']);
            $table->index(['sale_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_duty_logs');
        Schema::dropIfExists('sales_queue_members');
    }
};
