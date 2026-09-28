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
            $table->unsignedBigInteger('dp_amount')->default(0)->after('grand_total');
            $table->foreignId('dp_account_id')->nullable()->after('dp_amount')->constrained('accounts')->nullOnDelete();
            $table->date('dp_paid_at')->nullable()->after('dp_account_id');
            $table->string('dp_journal_no')->nullable()->after('dp_paid_at');
            $table->string('dp_reference_no')->nullable()->after('dp_journal_no');
            $table->text('dp_notes')->nullable()->after('dp_reference_no');
        });

        Schema::table('purchase_transactions', function (Blueprint $table) {
            $table->unsignedBigInteger('down_payment_amount')->default(0)->after('grand_total');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropForeign(['dp_account_id']);
            $table->dropColumn([
                'dp_amount',
                'dp_account_id',
                'dp_paid_at',
                'dp_journal_no',
                'dp_reference_no',
                'dp_notes',
            ]);
        });

        Schema::table('purchase_transactions', function (Blueprint $table) {
            $table->dropColumn(['down_payment_amount']);
        });
    }
};
