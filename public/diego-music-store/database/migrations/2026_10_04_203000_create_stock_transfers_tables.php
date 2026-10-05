<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add COA columns to branches
        Schema::table('branches', function (Blueprint $table) {
            $table->unsignedBigInteger('inventory_account_id')->nullable()->after('manager_id');
            $table->unsignedBigInteger('interbranch_receivable_account_id')->nullable()->after('inventory_account_id');
            $table->unsignedBigInteger('interbranch_payable_account_id')->nullable()->after('interbranch_receivable_account_id');

            $table->foreign('inventory_account_id')->references('id')->on('accounts')->nullOnDelete();
            $table->foreign('interbranch_receivable_account_id')->references('id')->on('accounts')->nullOnDelete();
            $table->foreign('interbranch_payable_account_id')->references('id')->on('accounts')->nullOnDelete();
        });

        // 2. Auto-generate COAs for existing branches
        $branches = DB::table('branches')->get();
        
        foreach ($branches as $branch) {
            // Persediaan
            $invName = 'Persediaan Barang Dagang - ' . $branch->name;
            $invAccount = DB::table('accounts')->where('name', $invName)->first();
            if (!$invAccount) {
                $baseCode = '11410' . str_pad($branch->id, 3, '0', STR_PAD_LEFT);
                while (DB::table('accounts')->where('code', $baseCode)->exists()) {
                    $baseCode = (string)((int)$baseCode + 1);
                }
                $inventoryAccountId = DB::table('accounts')->insertGetId([
                    'code' => $baseCode,
                    'name' => $invName,
                    'classification' => 'asset',
                    'account_subtype' => 'inventory',
                    'normal_balance' => 'debit',
                    'is_active' => true,
                    'is_header' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                $inventoryAccountId = $invAccount->id;
            }

            // Piutang Antar Cabang
            $recName = 'Piutang Antar Cabang - ' . $branch->name;
            $recAccount = DB::table('accounts')->where('name', $recName)->first();
            if (!$recAccount) {
                $baseCode = '14110' . str_pad($branch->id, 3, '0', STR_PAD_LEFT);
                while (DB::table('accounts')->where('code', $baseCode)->exists()) {
                    $baseCode = (string)((int)$baseCode + 1);
                }
                $receivableAccountId = DB::table('accounts')->insertGetId([
                    'code' => $baseCode,
                    'name' => $recName,
                    'classification' => 'asset',
                    'account_subtype' => 'receivable',
                    'normal_balance' => 'debit',
                    'is_active' => true,
                    'is_header' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                $receivableAccountId = $recAccount->id;
            }

            // Hutang Antar Cabang
            $payName = 'Hutang Antar Cabang - ' . $branch->name;
            $payAccount = DB::table('accounts')->where('name', $payName)->first();
            if (!$payAccount) {
                $baseCode = '21110' . str_pad($branch->id, 3, '0', STR_PAD_LEFT);
                while (DB::table('accounts')->where('code', $baseCode)->exists()) {
                    $baseCode = (string)((int)$baseCode + 1);
                }
                $payableAccountId = DB::table('accounts')->insertGetId([
                    'code' => $baseCode,
                    'name' => $payName,
                    'classification' => 'liability',
                    'account_subtype' => 'payable',
                    'normal_balance' => 'credit',
                    'is_active' => true,
                    'is_header' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                $payableAccountId = $payAccount->id;
            }

            // Update branch
            DB::table('branches')->where('id', $branch->id)->update([
                'inventory_account_id' => $inventoryAccountId,
                'interbranch_receivable_account_id' => $receivableAccountId,
                'interbranch_payable_account_id' => $payableAccountId,
            ]);
        }

        // 3. Create stock_transfers table
        Schema::create('stock_transfers', function (Blueprint $table) {
            $table->id();
            $table->string('transfer_number')->unique();
            $table->unsignedBigInteger('from_branch_id');
            $table->unsignedBigInteger('to_branch_id');
            $table->date('transfer_date');
            $table->text('description')->nullable();
            
            $table->string('status')->default('DRAFT'); // DRAFT, PENDING, APPROVED, COMPLETED, CANCELLED
            
            $table->integer('total_qty')->default(0);
            $table->decimal('total_cost', 15, 2)->default(0);
            
            $table->unsignedBigInteger('journal_entry_id')->nullable();
            
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            
            $table->timestamps();

            $table->foreign('from_branch_id')->references('id')->on('branches')->restrictOnDelete();
            $table->foreign('to_branch_id')->references('id')->on('branches')->restrictOnDelete();
            $table->foreign('journal_entry_id')->references('id')->on('journal_entries')->nullOnDelete();
        });

        // 4. Create stock_transfer_items table
        Schema::create('stock_transfer_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('stock_transfer_id');
            $table->unsignedBigInteger('product_variant_id');
            
            $table->integer('qty');
            $table->decimal('unit_cost', 15, 2)->default(0);
            $table->decimal('total_cost', 15, 2)->default(0);
            
            $table->timestamps();

            $table->foreign('stock_transfer_id')->references('id')->on('stock_transfers')->cascadeOnDelete();
            $table->foreign('product_variant_id')->references('id')->on('product_variants')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_transfer_items');
        Schema::dropIfExists('stock_transfers');
        
        Schema::table('branches', function (Blueprint $table) {
            $table->dropForeign(['interbranch_payable_account_id']);
            $table->dropForeign(['interbranch_receivable_account_id']);
            $table->dropForeign(['inventory_account_id']);
            
            $table->dropColumn([
                'inventory_account_id',
                'interbranch_receivable_account_id',
                'interbranch_payable_account_id'
            ]);
        });
    }
};
