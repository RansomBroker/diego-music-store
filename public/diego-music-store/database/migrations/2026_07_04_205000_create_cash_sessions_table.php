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
        Schema::create('cash_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->timestamp('opened_at');
            $table->timestamp('closed_at')->nullable();
            $table->bigInteger('opening_cash')->default(0);
            $table->bigInteger('expected_cash')->default(0);
            $table->bigInteger('actual_cash')->nullable();
            $table->bigInteger('difference')->nullable();
            $table->string('status')->default('open'); // 'open', 'closed'
            $table->foreignId('closed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Constrain the existing cash_session_id column in sales table
        Schema::table('sales', function (Blueprint $table) {
            $table->foreign('cash_session_id')->references('id')->on('cash_sessions')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropForeign(['cash_session_id']);
        });

        Schema::dropIfExists('cash_sessions');
    }
};
