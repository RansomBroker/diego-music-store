<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. sales.sale_category_id -> sale_categories.id
        if (! Schema::hasColumn('sales', 'sale_category_id')) {
            Schema::table('sales', function (Blueprint $table) {
                $table->foreignId('sale_category_id')
                    ->nullable()
                    ->after('sale_category')
                    ->constrained('sale_categories')
                    ->nullOnDelete();
            });
        }

        // 2. customer_deposits.payment_method_id & settlement_payment_method_id -> payment_methods.id
        Schema::table('customer_deposits', function (Blueprint $table) {
            if (! Schema::hasColumn('customer_deposits', 'payment_method_id')) {
                $table->foreignId('payment_method_id')
                    ->nullable()
                    ->after('payment_method')
                    ->constrained('payment_methods')
                    ->nullOnDelete();
            }
            if (! Schema::hasColumn('customer_deposits', 'settlement_payment_method_id')) {
                $table->foreignId('settlement_payment_method_id')
                    ->nullable()
                    ->after('settlement_payment_method')
                    ->constrained('payment_methods')
                    ->nullOnDelete();
            }
        });

        // 3. supplier_payments.payment_method_id & journal_entry_id
        Schema::table('supplier_payments', function (Blueprint $table) {
            if (! Schema::hasColumn('supplier_payments', 'payment_method_id')) {
                $table->foreignId('payment_method_id')
                    ->nullable()
                    ->after('payment_method')
                    ->constrained('payment_methods')
                    ->nullOnDelete();
            }
            if (! Schema::hasColumn('supplier_payments', 'journal_entry_id')) {
                $table->foreignId('journal_entry_id')
                    ->nullable()
                    ->after('journal_no')
                    ->constrained('journal_entries')
                    ->nullOnDelete();
            }
        });

        // 4. accounts.account_classification_id -> account_classifications.id
        if (! Schema::hasColumn('accounts', 'account_classification_id')) {
            Schema::table('accounts', function (Blueprint $table) {
                $table->foreignId('account_classification_id')
                    ->nullable()
                    ->after('classification')
                    ->constrained('account_classifications')
                    ->nullOnDelete();
            });
        }

        // 5. journal_entry_id / dp_journal_entry_id on purchase_transactions, sales_invoices, purchase_orders
        if (! Schema::hasColumn('purchase_transactions', 'journal_entry_id')) {
            Schema::table('purchase_transactions', function (Blueprint $table) {
                $table->foreignId('journal_entry_id')
                    ->nullable()
                    ->after('journal_no')
                    ->constrained('journal_entries')
                    ->nullOnDelete();
            });
        }

        if (! Schema::hasColumn('sales_invoices', 'journal_entry_id')) {
            Schema::table('sales_invoices', function (Blueprint $table) {
                $table->foreignId('journal_entry_id')
                    ->nullable()
                    ->after('journal_no')
                    ->constrained('journal_entries')
                    ->nullOnDelete();
            });
        }

        if (! Schema::hasColumn('purchase_orders', 'dp_journal_entry_id')) {
            Schema::table('purchase_orders', function (Blueprint $table) {
                $table->foreignId('dp_journal_entry_id')
                    ->nullable()
                    ->after('dp_journal_no')
                    ->constrained('journal_entries')
                    ->nullOnDelete();
            });
        }

        // ── Additive Backfill (never alters amounts, balances, or journals) ──
        $this->backfillSaleCategories();
        $this->backfillAccountClassifications();
        $this->backfillPaymentMethodIds();
        $this->backfillJournalEntryIds();
    }

    private function backfillSaleCategories(): void
    {
        $categories = DB::table('sale_categories')->get(['id', 'name']);
        foreach ($categories as $cat) {
            DB::table('sales')
                ->whereNull('sale_category_id')
                ->whereRaw('LOWER(TRIM(sale_category)) = ?', [strtolower(trim((string) $cat->name))])
                ->update(['sale_category_id' => $cat->id]);
        }
    }

    private function backfillAccountClassifications(): void
    {
        $classifications = DB::table('account_classifications')->get(['id', 'key', 'name']);
        foreach ($classifications as $cls) {
            DB::table('accounts')
                ->whereNull('account_classification_id')
                ->where(function ($q) use ($cls) {
                    $q->whereRaw('LOWER(TRIM(classification)) = ?', [strtolower(trim((string) $cls->key))])
                        ->orWhereRaw('LOWER(TRIM(classification)) = ?', [strtolower(trim((string) $cls->name))]);
                })
                ->update(['account_classification_id' => $cls->id]);
        }
    }

    private function backfillPaymentMethodIds(): void
    {
        $methods = DB::table('payment_methods')->get(['id', 'code', 'name']);
        if ($methods->isEmpty()) {
            return;
        }

        $resolveId = function (?string $raw) use ($methods): ?int {
            $clean = strtolower(trim((string) $raw));
            if ($clean === '') {
                return null;
            }
            if (in_array($clean, ['cash', 'tunai', 'kas'], true)) {
                $cash = $methods->first(fn ($m) => strtolower((string) $m->code) === 'cash');
                if ($cash) {
                    return (int) $cash->id;
                }
            }
            $matched = $methods->first(
                fn ($m) => strtolower(trim((string) $m->name)) === $clean
                    || strtolower(trim((string) $m->code)) === $clean
            );
            return $matched ? (int) $matched->id : null;
        };

        DB::table('customer_deposits')->orderBy('id')->chunkById(200, function ($rows) use ($resolveId) {
            foreach ($rows as $row) {
                $updates = [];
                if ($row->payment_method_id === null && ! empty($row->payment_method)) {
                    $id = $resolveId($row->payment_method);
                    if ($id) {
                        $updates['payment_method_id'] = $id;
                    }
                }
                if ($row->settlement_payment_method_id === null && ! empty($row->settlement_payment_method)) {
                    $id = $resolveId($row->settlement_payment_method);
                    if ($id) {
                        $updates['settlement_payment_method_id'] = $id;
                    }
                }
                if (! empty($updates)) {
                    DB::table('customer_deposits')->where('id', $row->id)->update($updates);
                }
            }
        });

        DB::table('supplier_payments')->orderBy('id')->chunkById(200, function ($rows) use ($resolveId) {
            foreach ($rows as $row) {
                if ($row->payment_method_id === null && ! empty($row->payment_method)) {
                    $id = $resolveId($row->payment_method);
                    if ($id) {
                        DB::table('supplier_payments')->where('id', $row->id)->update(['payment_method_id' => $id]);
                    }
                }
            }
        });
    }

    private function backfillJournalEntryIds(): void
    {
        $entriesByNo = DB::table('journal_entries')->pluck('id', 'entry_no');
        if ($entriesByNo->isEmpty()) {
            return;
        }

        // purchase_transactions
        DB::table('purchase_transactions')->whereNull('journal_entry_id')->whereNotNull('journal_no')
            ->orderBy('id')->chunkById(200, function ($rows) use ($entriesByNo) {
                foreach ($rows as $row) {
                    $jeId = $entriesByNo->get($row->journal_no);
                    if ($jeId) {
                        DB::table('purchase_transactions')->where('id', $row->id)->update(['journal_entry_id' => $jeId]);
                    }
                }
            });

        // sales_invoices
        DB::table('sales_invoices')->whereNull('journal_entry_id')->whereNotNull('journal_no')
            ->orderBy('id')->chunkById(200, function ($rows) use ($entriesByNo) {
                foreach ($rows as $row) {
                    $jeId = $entriesByNo->get($row->journal_no);
                    if ($jeId) {
                        DB::table('sales_invoices')->where('id', $row->id)->update(['journal_entry_id' => $jeId]);
                    }
                }
            });

        // supplier_payments
        DB::table('supplier_payments')->whereNull('journal_entry_id')->whereNotNull('journal_no')
            ->orderBy('id')->chunkById(200, function ($rows) use ($entriesByNo) {
                foreach ($rows as $row) {
                    $jeId = $entriesByNo->get($row->journal_no);
                    if ($jeId) {
                        DB::table('supplier_payments')->where('id', $row->id)->update(['journal_entry_id' => $jeId]);
                    }
                }
            });

        // purchase_orders (DP)
        DB::table('purchase_orders')->whereNull('dp_journal_entry_id')->whereNotNull('dp_journal_no')
            ->orderBy('id')->chunkById(200, function ($rows) use ($entriesByNo) {
                foreach ($rows as $row) {
                    $jeId = $entriesByNo->get($row->dp_journal_no);
                    if ($jeId) {
                        DB::table('purchase_orders')->where('id', $row->id)->update(['dp_journal_entry_id' => $jeId]);
                    }
                }
            });
    }

    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            if (Schema::hasColumn('purchase_orders', 'dp_journal_entry_id')) {
                $table->dropConstrainedForeignId('dp_journal_entry_id');
            }
        });
        Schema::table('sales_invoices', function (Blueprint $table) {
            if (Schema::hasColumn('sales_invoices', 'journal_entry_id')) {
                $table->dropConstrainedForeignId('journal_entry_id');
            }
        });
        Schema::table('purchase_transactions', function (Blueprint $table) {
            if (Schema::hasColumn('purchase_transactions', 'journal_entry_id')) {
                $table->dropConstrainedForeignId('journal_entry_id');
            }
        });
        Schema::table('accounts', function (Blueprint $table) {
            if (Schema::hasColumn('accounts', 'account_classification_id')) {
                $table->dropConstrainedForeignId('account_classification_id');
            }
        });
        Schema::table('supplier_payments', function (Blueprint $table) {
            if (Schema::hasColumn('supplier_payments', 'journal_entry_id')) {
                $table->dropConstrainedForeignId('journal_entry_id');
            }
            if (Schema::hasColumn('supplier_payments', 'payment_method_id')) {
                $table->dropConstrainedForeignId('payment_method_id');
            }
        });
        Schema::table('customer_deposits', function (Blueprint $table) {
            if (Schema::hasColumn('customer_deposits', 'settlement_payment_method_id')) {
                $table->dropConstrainedForeignId('settlement_payment_method_id');
            }
            if (Schema::hasColumn('customer_deposits', 'payment_method_id')) {
                $table->dropConstrainedForeignId('payment_method_id');
            }
        });
        Schema::table('sales', function (Blueprint $table) {
            if (Schema::hasColumn('sales', 'sale_category_id')) {
                $table->dropConstrainedForeignId('sale_category_id');
            }
        });
    }
};

