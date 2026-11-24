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
        if (! Schema::hasColumn('suppliers', 'status')) {
            Schema::table('suppliers', function (Blueprint $table) {
                $table->string('status', 50)->default('SUPPLIER')->after('name');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('suppliers', 'status')) {
            Schema::table('suppliers', function (Blueprint $table) {
                $table->dropColumn('status');
            });
        }
    }
};

