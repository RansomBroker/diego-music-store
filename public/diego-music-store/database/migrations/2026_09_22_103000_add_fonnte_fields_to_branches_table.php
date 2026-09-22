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
        Schema::table('branches', function (Blueprint $table) {
            $table->string('fonnte_token')->nullable()->after('receipt_footer');
            $table->string('fonnte_whatsapp_number')->nullable()->after('fonnte_token');
            $table->boolean('is_whatsapp_enabled')->default(true)->after('fonnte_whatsapp_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->dropColumn([
                'fonnte_token',
                'fonnte_whatsapp_number',
                'is_whatsapp_enabled',
            ]);
        });
    }
};
