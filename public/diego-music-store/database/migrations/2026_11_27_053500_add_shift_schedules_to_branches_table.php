<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('branches', 'shift_schedules')) {
            Schema::table('branches', function (Blueprint $table) {
                $table->json('shift_schedules')->nullable()->after('shift_end_time');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('branches', 'shift_schedules')) {
            Schema::table('branches', function (Blueprint $table) {
                $table->dropColumn('shift_schedules');
            });
        }
    }
};

