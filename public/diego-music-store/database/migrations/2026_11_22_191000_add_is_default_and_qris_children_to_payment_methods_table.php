<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('payment_methods', function (Blueprint $table) {
            $table->boolean('is_default')->default(false)->after('is_active');
        });

        $qrisParentId = DB::table('payment_methods')->where('code', 'qris')->value('id');
        if ($qrisParentId) {
            $bankBcaId = DB::table('accounts')->where('code', '111201001')->value('id');
            $bankMandiriId = DB::table('accounts')->where('code', '111201002')->value('id');
            $bankBriId = DB::table('accounts')->where('code', '111201003')->value('id');

            $children = [
                ['code' => 'qris-bca', 'name' => 'BCA', 'account_id' => $bankBcaId, 'is_default' => true],
                ['code' => 'qris-mandiri', 'name' => 'Mandiri', 'account_id' => $bankMandiriId, 'is_default' => false],
                ['code' => 'qris-bri', 'name' => 'BRI', 'account_id' => $bankBriId, 'is_default' => false],
            ];

            foreach ($children as $child) {
                $existing = DB::table('payment_methods')->where('code', $child['code'])->first();
                if ($existing) {
                    DB::table('payment_methods')->where('id', $existing->id)->update([
                        'name' => $child['name'],
                        'parent_id' => $qrisParentId,
                        'account_id' => $child['account_id'] ?? $existing->account_id,
                        'is_active' => true,
                        'is_default' => $child['is_default'],
                        'updated_at' => now(),
                    ]);
                } else {
                    DB::table('payment_methods')->insert([
                        'name' => $child['name'],
                        'code' => $child['code'],
                        'parent_id' => $qrisParentId,
                        'account_id' => $child['account_id'],
                        'is_active' => true,
                        'is_default' => $child['is_default'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('payment_methods')
            ->whereIn('code', ['qris-bca', 'qris-mandiri', 'qris-bri'])
            ->delete();

        Schema::table('payment_methods', function (Blueprint $table) {
            $table->dropColumn('is_default');
        });
    }
};

