<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('account_classifications', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('name');
            $table->timestamps();
        });

        // Insert default classifications
        DB::table('account_classifications')->insert([
            ['key' => 'asset', 'name' => 'Aset / Harta', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'liability', 'name' => 'Kewajiban / Hutang', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'equity', 'name' => 'Ekuitas / Modal', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'revenue', 'name' => 'Pendapatan / Penjualan', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'expense', 'name' => 'Beban / Biaya', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('account_classifications');
    }
};
