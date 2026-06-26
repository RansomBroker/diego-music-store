<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_labels', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('name');
            $table->timestamps();
        });

        // Seed default labels
        DB::table('customer_labels')->insert([
            ['key' => 'perorangan', 'name' => 'Perorangan', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'instansi', 'name' => 'Instansi', 'created_at' => now(), 'updated_at' => now()],
        ]);

        Schema::table('customers', function (Blueprint $table) {
            $table->date('date_of_birth')->nullable()->after('address');
            $table->foreignId('customer_label_id')->nullable()->constrained('customer_labels')->nullOnDelete()->after('date_of_birth');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropForeign(['customer_label_id']);
            $table->dropColumn(['customer_label_id', 'date_of_birth']);
        });

        Schema::dropIfExists('customer_labels');
    }
};
