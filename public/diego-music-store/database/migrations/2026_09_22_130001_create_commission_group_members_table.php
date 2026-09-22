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
        Schema::create('commission_group_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('commission_group_id')->constrained('commission_groups')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->decimal('monthly_target_amount', 15, 2)->default(0);
            $table->timestamps();

            $table->unique(['commission_group_id', 'employee_id'], 'comm_grp_member_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commission_group_members');
    }
};
