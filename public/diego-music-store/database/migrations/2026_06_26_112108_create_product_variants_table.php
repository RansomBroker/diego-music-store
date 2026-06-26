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
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('sku')->nullable()->unique();
            $table->string('barcode')->nullable();
            $table->string('name')->nullable(); // name of variant, e.g. 'Red', '3/4 Size', or null for default variant
            $table->bigInteger('price')->default(0); // base retail selling price
            $table->bigInteger('cost_price')->default(0); // buying price
            $table->bigInteger('hpp')->default(0); // weighted average HPP
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};
