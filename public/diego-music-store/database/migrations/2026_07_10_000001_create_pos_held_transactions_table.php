<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pos_held_transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->string('customer_name')->nullable();
            $table->json('cart_data');
            $table->integer('discount_amount')->default(0);
            $table->string('discount_type')->default('fixed');
            $table->integer('discount_value')->default(0);
            $table->boolean('use_points')->default(false);
            $table->boolean('is_loyalty')->default(false);
            $table->foreignId('pricing_tier_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pos_held_transactions');
    }
};
