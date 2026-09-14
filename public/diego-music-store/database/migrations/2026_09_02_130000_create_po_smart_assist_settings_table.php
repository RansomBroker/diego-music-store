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
        Schema::create('po_smart_assist_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_enabled')->default(true);
            $table->integer('min_buffer_percentage')->default(20);
            $table->bigInteger('min_buffer_nominal')->default(5000000);
            $table->boolean('include_pending_pos')->default(true);
            $table->boolean('include_sales_projection')->default(true);
            $table->integer('warning_threshold_days')->default(30);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('po_smart_assist_settings');
    }
};
