<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $pettyCashAccountId = DB::table('accounts')
            ->where('code', '111101002')
            ->where('is_header', false)
            ->value('id');

        if ($pettyCashAccountId) {
            DB::table('payment_methods')
                ->where('code', 'cash')
                ->update(['account_id' => $pettyCashAccountId]);
        }
    }

    public function down(): void
    {
        $mainCashAccountId = DB::table('accounts')
            ->where('code', '111101001')
            ->where('is_header', false)
            ->value('id');

        if ($mainCashAccountId) {
            DB::table('payment_methods')
                ->where('code', 'cash')
                ->update(['account_id' => $mainCashAccountId]);
        }
    }
};

