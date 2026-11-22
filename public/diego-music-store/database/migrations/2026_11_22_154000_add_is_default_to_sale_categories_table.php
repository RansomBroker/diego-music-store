<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('sale_categories', function (Blueprint $table) {
            $table->boolean('is_default')->default(false)->after('digit_length');
        });

        if (DB::table('sale_categories')->where('is_default', true)->doesntExist()) {
            DB::table('sale_categories')
                ->where('name', 'Store')
                ->update(['is_default' => true]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sale_categories', function (Blueprint $table) {
            $table->dropColumn('is_default');
        });
    }
};

