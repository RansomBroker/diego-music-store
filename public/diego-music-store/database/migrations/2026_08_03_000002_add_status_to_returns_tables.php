<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('sales_returns', 'status')) {
            Schema::table('sales_returns', function (Blueprint $table) {
                $table->string('status')->default('posted')->after('total_refund');
            });
        }

        if (!Schema::hasColumn('purchase_returns', 'status')) {
            Schema::table('purchase_returns', function (Blueprint $table) {
                $table->string('status')->default('posted')->after('total_amount');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('sales_returns', 'status')) {
            Schema::table('sales_returns', function (Blueprint $table) {
                $table->dropColumn('status');
            });
        }

        if (Schema::hasColumn('purchase_returns', 'status')) {
            Schema::table('purchase_returns', function (Blueprint $table) {
                $table->dropColumn('status');
            });
        }
    }
};
