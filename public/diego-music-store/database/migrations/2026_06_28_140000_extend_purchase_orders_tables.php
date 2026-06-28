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
            $table->foreignId('branch_id')->nullable()->constrained()->onDelete('restrict')->after('supplier_id');
            $table->foreignId('created_by_id')->nullable()->constrained('users')->onDelete('set null')->after('branch_id');
            $table->string('currency')->default('IDR')->after('po_number');
            $table->string('payment_term')->nullable()->after('currency');
            $table->date('eta_date')->nullable()->after('order_date');
            $table->bigInteger('discount_amount')->default(0)->after('total_amount');
            $table->bigInteger('other_cost')->default(0)->after('discount_amount');
            $table->string('tax_mode')->default('ITEM')->after('other_cost'); // GLOBAL, ITEM
            $table->integer('tax_rate')->default(0)->after('tax_mode');
            $table->bigInteger('tax_amount')->default(0)->after('tax_rate');
            $table->bigInteger('grand_total')->default(0)->after('tax_amount');
        });

        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->bigInteger('discount_amount')->default(0)->after('price');
            $table->integer('tax_rate')->default(0)->after('discount_amount');
            $table->bigInteger('tax_amount')->default(0)->after('tax_rate');
            $table->bigInteger('subtotal')->default(0)->after('tax_amount');
            $table->string('notes')->nullable()->after('subtotal');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->dropColumn(['discount_amount', 'tax_rate', 'tax_amount', 'subtotal', 'notes']);
        });

        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropForeign(['created_by_id']);
            $table->dropColumn([
                'branch_id',
                'created_by_id',
                'currency',
                'payment_term',
                'eta_date',
                'discount_amount',
                'other_cost',
                'tax_mode',
                'tax_rate',
                'tax_amount',
                'grand_total'
            ]);
        });
    }
};
