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
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->boolean('enable_tax')->default(false)->after('status');
        });

        Schema::table('purchase_transactions', function (Blueprint $table) {
            $table->boolean('enable_tax')->default(false)->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_transactions', function (Blueprint $table) {
            if (Schema::hasColumn('purchase_transactions', 'enable_tax')) {
                $table->dropColumn('enable_tax');
            }
        });

        Schema::table('purchase_orders', function (Blueprint $table) {
            if (Schema::hasColumn('purchase_orders', 'enable_tax')) {
                $table->dropColumn('enable_tax');
            }
        });
    }
};
