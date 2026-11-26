<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('sale_categories', 'censor_prices')) {
            Schema::table('sale_categories', function (Blueprint $table) {
                $table->boolean('censor_prices')->default(false)->after('is_default');
            });
        }

        if (Schema::hasTable('permissions')) {
            Permission::firstOrCreate([
                'name' => 'fo.pos.view_censored_prices',
                'guard_name' => 'web',
            ]);
            app(PermissionRegistrar::class)->forgetCachedPermissions();
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('sale_categories', 'censor_prices')) {
            Schema::table('sale_categories', function (Blueprint $table) {
                $table->dropColumn('censor_prices');
            });
        }
    }
};

