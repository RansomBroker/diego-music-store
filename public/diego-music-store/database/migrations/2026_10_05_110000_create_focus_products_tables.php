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
        // 1. Focus Product Rules
        Schema::create('focus_product_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('code', 50)->unique();
            $table->text('description')->nullable();
            $table->json('conditions')->nullable();
            $table->integer('priority')->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Focus Product Recommendations
        Schema::create('focus_product_recommendations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->foreignId('product_variant_id')->constrained('product_variants')->cascadeOnDelete();
            $table->foreignId('rule_id')->constrained('focus_product_rules')->cascadeOnDelete();
            $table->integer('score')->default(0);
            $table->integer('current_stock')->default(0);
            $table->integer('recent_sales_qty')->default(0);
            $table->integer('aging_days')->nullable();
            $table->text('reason');
            $table->string('status', 20)->default('PENDING'); // PENDING, ACCEPTED, DISMISSED
            $table->timestamp('evaluated_at');
            $table->timestamp('dismissed_at')->nullable();
            $table->foreignId('dismissed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['branch_id', 'product_variant_id', 'status'], 'fp_rec_branch_var_status_idx');
            $table->index(['branch_id', 'status'], 'fp_rec_branch_status_idx');
        });

        // 3. Focus Products
        Schema::create('focus_products', function (Blueprint $table) {
            $table->id();
            $table->string('focus_number', 30)->unique();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->foreignId('product_variant_id')->constrained('product_variants')->cascadeOnDelete();
            $table->string('source', 20)->default('MANUAL'); // MANUAL, RULE
            $table->foreignId('rule_id')->nullable()->constrained('focus_product_rules')->nullOnDelete();
            $table->foreignId('recommendation_id')->nullable()->constrained('focus_product_recommendations')->nullOnDelete();
            $table->string('status', 20)->default('ACTIVE'); // ACTIVE, EXPIRED, RESOLVED, DISMISSED
            $table->text('reason');
            $table->text('note')->nullable();
            $table->timestamp('active_from');
            $table->timestamp('active_until')->nullable();
            $table->timestamp('focused_at');
            $table->foreignId('focused_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('resolution_note')->nullable();
            $table->timestamp('dismissed_at')->nullable();
            $table->foreignId('dismissed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('dismissal_note')->nullable();
            $table->timestamps();

            $table->index(['branch_id', 'product_variant_id', 'status'], 'fp_branch_var_status_idx');
            $table->index(['branch_id', 'status'], 'fp_branch_status_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('focus_products');
        Schema::dropIfExists('focus_product_recommendations');
        Schema::dropIfExists('focus_product_rules');
    }
};
