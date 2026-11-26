<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('pricing_tiers', 'censor_prices')) {
            Schema::table('pricing_tiers', function (Blueprint $table) {
                $table->boolean('censor_prices')->default(false)->after('price_follows_hpp');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('pricing_tiers', 'censor_prices')) {
            Schema::table('pricing_tiers', function (Blueprint $table) {
                $table->dropColumn('censor_prices');
            });
        }
    }
};

