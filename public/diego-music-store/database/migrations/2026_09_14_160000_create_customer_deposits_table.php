<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Account;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Ensure deposit_balance column exists on customers table
        if (Schema::hasTable('customers') && !Schema::hasColumn('customers', 'deposit_balance')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->decimal('deposit_balance', 15, 2)->default(0.00)->after('loyalty_points');
            });
        }

        // 2. Create customer_deposits table
        if (!Schema::hasTable('customer_deposits')) {
            Schema::create('customer_deposits', function (Blueprint $table) {
                $table->id();
                $table->string('deposit_number', 50)->unique()->index();
                $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
                $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->date('deposit_date')->index();

                // Product details (Existing katalog vs Manual PO)
                $table->string('product_type', 20)->default('existing'); // 'existing' | 'manual'
                $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
                $table->foreignId('product_variant_id')->nullable()->constrained('product_variants')->nullOnDelete();
                $table->string('product_name');
                $table->decimal('price', 15, 2)->default(0.00);
                $table->integer('qty')->default(1);
                $table->decimal('total_amount', 15, 2)->default(0.00);

                // Deposit payment details
                $table->decimal('deposit_amount', 15, 2)->default(0.00);
                $table->decimal('remaining_amount', 15, 2)->default(0.00);
                $table->foreignId('account_id')->nullable()->constrained('accounts')->nullOnDelete();
                $table->string('payment_method', 50)->default('Tunai');
                $table->string('payment_reference')->nullable();
                $table->text('notes')->nullable();

                // Status
                $table->string('status', 30)->default('pending')->index(); // 'pending', 'settled', 'cancelled'

                // Settlement details (Pelunasan)
                $table->dateTime('settled_at')->nullable();
                $table->foreignId('settled_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('settlement_account_id')->nullable()->constrained('accounts')->nullOnDelete();
                $table->string('settlement_payment_method', 50)->nullable();
                $table->string('settlement_reference')->nullable();
                $table->text('settlement_notes')->nullable();

                // Accounting Journal Entry references
                $table->foreignId('deposit_journal_entry_id')->nullable()->constrained('journal_entries')->nullOnDelete();
                $table->foreignId('settlement_journal_entry_id')->nullable()->constrained('journal_entries')->nullOnDelete();

                $table->timestamps();
            });
        }

        // 3. Ensure COA account "Penitipan Dana" (2-1200) exists
        if (Schema::hasTable('accounts')) {
            $liabilityHeader = Account::where('code', '2-0000')->first();
            Account::firstOrCreate(
                ['code' => '2-1200'],
                [
                    'name' => 'Penitipan Dana',
                    'classification' => 'liability',
                    'is_header' => false,
                    'parent_id' => $liabilityHeader?->id,
                    'is_active' => true,
                ]
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_deposits');
    }
};
