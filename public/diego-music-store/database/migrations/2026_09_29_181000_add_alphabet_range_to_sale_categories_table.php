<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sale_categories', function (Blueprint $table) {
            $table->string('start_alphabet', 5)->default('A')->after('prefix');
            $table->string('end_alphabet', 5)->default('Z')->after('start_alphabet');
        });
    }

    public function down(): void
    {
        Schema::table('sale_categories', function (Blueprint $table) {
            $table->dropColumn(['start_alphabet', 'end_alphabet']);
        });
    }
};
