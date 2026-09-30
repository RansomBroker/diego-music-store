<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sale_categories', function (Blueprint $table) {
            $table->string('prefix', 50)->nullable()->after('name');
            $table->integer('digit_length')->default(4)->after('prefix');
        });
    }

    public function down(): void
    {
        Schema::table('sale_categories', function (Blueprint $table) {
            $table->dropColumn(['prefix', 'digit_length']);
        });
    }
};
