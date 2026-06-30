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
        Schema::create('inventory_mutations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sender_branch_id')->constrained('branches')->onDelete('restrict');
            $table->foreignId('receiver_branch_id')->constrained('branches')->onDelete('restrict');
            $table->string('mutation_number')->unique();
            $table->date('mutation_date');
            $table->enum('status', ['draft', 'transit', 'received'])->default('draft');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('inventory_mutation_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_mutation_id')->constrained('inventory_mutations')->onDelete('cascade');
            $table->foreignId('product_variant_id')->constrained('product_variants')->onDelete('restrict');
            $table->integer('quantity');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_mutation_items');
        Schema::dropIfExists('inventory_mutations');
    }
};
