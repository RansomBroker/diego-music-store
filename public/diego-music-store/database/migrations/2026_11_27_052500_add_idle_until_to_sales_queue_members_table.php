<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('sales_queue_members', 'idle_until')) {
            Schema::table('sales_queue_members', function (Blueprint $table) {
                $table->timestamp('idle_until')->nullable()->after('duty_started_at');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('sales_queue_members', 'idle_until')) {
            Schema::table('sales_queue_members', function (Blueprint $table) {
                $table->dropColumn('idle_until');
            });
        }
    }
};

