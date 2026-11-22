<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pos_action_otps', function (Blueprint $table) {
            $table->id();
            $table->string('code', 6);
            $table->timestamp('expires_at')->index();
            $table->timestamp('revoked_at')->nullable()->index();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('pos_action_otp_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pos_action_otp_id')->nullable()->constrained('pos_action_otps')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->string('action', 32);
            $table->unsignedBigInteger('sale_id')->nullable();
            $table->string('invoice_number', 100)->nullable();
            $table->timestamp('used_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pos_action_otp_logs');
        Schema::dropIfExists('pos_action_otps');
    }
};

