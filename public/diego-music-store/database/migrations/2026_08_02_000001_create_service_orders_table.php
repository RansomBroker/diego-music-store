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
        Schema::create('service_orders', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_code')->unique();
            $table->foreignId('sale_id')->nullable()->constrained('sales')->nullOnDelete();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->string('customer_name');
            $table->string('customer_phone')->nullable();
            $table->string('device_name'); // e.g. Gitar Akustik Yamaha F310, Amp Marshall JCM800
            $table->string('serial_number')->nullable();
            $table->text('complaint')->nullable(); // Keluhan / kerusakan
            $table->foreignId('technician_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', [
                'received',       // Diterima
                'diagnosing',     // Diagnosa
                'in_progress',    // Dikerjakan
                'waiting_parts',  // Menunggu Sparepart
                'completed',      // Selesai
                'picked_up',      // Siap/Sudah Diambil
                'cancelled'       // Dibatalkan
            ])->default('received');
            $table->decimal('estimated_cost', 15, 2)->default(0);
            $table->json('additional_charges')->nullable(); // Spareparts / biaya tambahan
            $table->decimal('total_cost', 15, 2)->default(0);
            $table->text('notes')->nullable(); // Catatan teknisi
            $table->timestamp('completion_date')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_orders');
    }
};
