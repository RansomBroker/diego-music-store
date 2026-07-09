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
        Schema::create('scheduled_journal_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->onDelete('restrict');
            $table->text('description')->nullable();
            $table->date('start_date');
            $table->string('frequency')->default('monthly'); // daily, weekly, monthly, yearly
            $table->integer('interval')->default(1); // e.g. every 1 month, every 2 months
            $table->integer('duration_months')->nullable(); // duration in months
            $table->date('end_date')->nullable(); // calculated or explicit end date
            $table->string('status')->default('active'); // active, paused, completed
            $table->date('last_run_at')->nullable();
            $table->date('next_run_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('restrict');
            $table->timestamps();
        });

        Schema::create('scheduled_journal_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scheduled_journal_entry_id')->constrained('scheduled_journal_entries')->onDelete('cascade');
            $table->foreignId('account_id')->constrained('accounts')->onDelete('restrict');
            $table->bigInteger('debit')->default(0);
            $table->bigInteger('credit')->default(0);
            $table->string('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scheduled_journal_items');
        Schema::dropIfExists('scheduled_journal_entries');
    }
};
