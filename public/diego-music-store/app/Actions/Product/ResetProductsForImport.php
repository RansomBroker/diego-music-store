<?php

namespace App\Actions\Product;

use App\Helpers\PermissionHelper;
use App\Models\Customer;
use App\Models\JournalEntry;
use App\Models\JournalItem;
use App\Models\Product;
use App\Models\ProductBranchPrice;
use App\Models\ProductBranchStock;
use App\Models\ProductBundle;
use App\Models\ProductTierPrice;
use App\Models\ProductVariant;
use App\Models\PurchaseTransaction;
use App\Models\Sale;
use App\Models\StockMovement;
use App\Models\StockTransfer;
use App\Models\Supplier;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ResetProductsForImport
{
    /**
     * Nullable foreign-key references that should be detached cleanly before removing products.
     *
     * @var array<string, array<int, string>>
     */
    private const NULLABLE_PRODUCT_COLUMNS = [
        'customer_deposits' => ['product_id', 'product_variant_id'],
        'commission_schemes' => ['target_product_id'],
    ];

    /**
     * Nullable sale references that should be detached before removing sales.
     *
     * @var array<int, string>
     */
    private const NULLABLE_SALE_TABLES = [
        'sales_commission_logs',
        'service_orders',
        'sales_duty_logs',
    ];

    /**
     * Delete all existing product catalog records along with dependent POS sales,
     * procurement product transactions, operational stock movements, and their journals
     * so products can be cleanly recreated from a fresh import.
     *
     * @return array{deleted_products: int, deleted_variants: int, deleted_journals: int, deleted_sales: int, deleted_stock_movements: int}
     */
    public function execute(): array
    {
        PermissionHelper::enforceActionPermission(Auth::user(), 'bo.products.delete');

        return DB::transaction(function (): array {
            $deletedProducts = Product::count();
            $deletedVariants = ProductVariant::count();

            $journalIds = [];

            [$deletedSales, $salesJournalIds] = $this->resetSalesHistory();
            $procurementJournalIds = $this->resetProcurementProductHistory();
            [$deletedMovements, $inventoryJournalIds] = $this->resetInventoryOperations();

            $journalIds = array_values(array_unique(array_merge(
                $journalIds,
                $salesJournalIds,
                $procurementJournalIds,
                $inventoryJournalIds,
            )));

            $deletedJournals = $this->deleteJournalEntries($journalIds);

            $this->deleteProductCatalog();

            return [
                'deleted_products' => $deletedProducts,
                'deleted_variants' => $deletedVariants,
                'deleted_journals' => $deletedJournals,
                'deleted_sales' => $deletedSales,
                'deleted_stock_movements' => $deletedMovements,
            ];
        });
    }

    /**
     * Reset POS sales, quotations, invoices, delivery orders, sales returns, and related journals.
     *
     * @return array{0: int, 1: array<int, int>}
     */
    private function resetSalesHistory(): array
    {
        $deletedSales = 0;

        if (Schema::hasTable('sales')) {
            if (Schema::hasTable('customers') && Schema::hasColumn('customers', 'outstanding_debt')) {
                $customerDebtReductions = [];
                foreach (Sale::with('customer')->cursor() as $sale) {
                    if (! $sale->customer_id) {
                        continue;
                    }
                    $piutang = max(0.0, (float) $sale->getPiutangAmount());
                    if ($piutang > 0) {
                        $customerDebtReductions[$sale->customer_id] = ($customerDebtReductions[$sale->customer_id] ?? 0.0) + $piutang;
                    }
                }

                if ($customerDebtReductions !== []) {
                    $customers = Customer::whereIn('id', array_keys($customerDebtReductions))->get();
                    foreach ($customers as $customer) {
                        $reduction = $customerDebtReductions[$customer->id] ?? 0.0;
                        if ($reduction > 0) {
                            $customer->update([
                                'outstanding_debt' => max(0, (float) $customer->outstanding_debt - $reduction),
                            ]);
                        }
                    }
                }
            }

            foreach (self::NULLABLE_SALE_TABLES as $table) {
                if (Schema::hasTable($table) && Schema::hasColumn($table, 'sale_id')) {
                    DB::table($table)->whereNotNull('sale_id')->update(['sale_id' => null]);
                }
            }

            $deletedSales = Sale::count();
        }

        foreach ([
            'sales_return_items',
            'sales_returns',
            'sale_items',
            'sales',
            'sales_invoice_items',
            'sales_invoices',
            'sales_quotation_items',
            'sales_quotations',
            'delivery_order_items',
            'delivery_orders',
            'pos_held_transactions',
        ] as $table) {
            if (Schema::hasTable($table)) {
                DB::table($table)->delete();
            }
        }

        $journalIds = JournalEntry::query()
            ->whereIn('reference_type', ['Sales', 'SalesReturn', 'SalesInvoice', 'AR_Payment'])
            ->pluck('id')
            ->all();

        return [$deletedSales, $journalIds];
    }

    /**
     * Reset purchase orders and product-linked purchase transactions (preserving pure InitialDebt entries).
     *
     * @return array<int, int>
     */
    private function resetProcurementProductHistory(): array
    {
        $journalIds = [];

        $ptIdsFromDetails = Schema::hasTable('purchase_transaction_details')
            ? DB::table('purchase_transaction_details')->pluck('purchase_transaction_id')->all()
            : [];
        $ptIdsFromReturns = Schema::hasTable('purchase_returns')
            ? DB::table('purchase_returns')->pluck('purchase_transaction_id')->all()
            : [];

        $productPtIds = array_values(array_unique(array_filter(array_merge($ptIdsFromDetails, $ptIdsFromReturns))));

        if ($productPtIds !== []) {
            $returnIds = Schema::hasTable('purchase_returns')
                ? DB::table('purchase_returns')->whereIn('purchase_transaction_id', $productPtIds)->pluck('id')->all()
                : [];

            // Hapus pembayaran supplier yang terkait transaksi pembelian produk
            $paidByPt = [];
            if (Schema::hasTable('supplier_payment_items')) {
                $paymentItems = DB::table('supplier_payment_items')
                    ->whereIn('purchase_transaction_id', $productPtIds)
                    ->get(['supplier_payment_id', 'purchase_transaction_id', 'amount_paid']);

                $affectedPaymentIds = [];
                foreach ($paymentItems as $item) {
                    $paidByPt[$item->purchase_transaction_id] = ($paidByPt[$item->purchase_transaction_id] ?? 0.0) + (float) $item->amount_paid;
                    $affectedPaymentIds[] = $item->supplier_payment_id;
                }

                if ($affectedPaymentIds !== []) {
                    DB::table('supplier_payment_items')
                        ->whereIn('purchase_transaction_id', $productPtIds)
                        ->delete();

                    $emptyPaymentIds = DB::table('supplier_payments')
                        ->whereIn('id', array_unique($affectedPaymentIds))
                        ->whereNotExists(function ($query): void {
                            $query->select(DB::raw(1))
                                ->from('supplier_payment_items')
                                ->whereColumn('supplier_payment_items.supplier_payment_id', 'supplier_payments.id');
                        })
                        ->pluck('id')
                        ->all();

                    if ($emptyPaymentIds !== []) {
                        $paymentJournalNos = DB::table('supplier_payments')
                            ->whereIn('id', $emptyPaymentIds)
                            ->pluck('journal_no')
                            ->filter()
                            ->all();

                        $journalIds = array_merge(
                            $journalIds,
                            JournalEntry::query()
                                ->where(function ($q) use ($emptyPaymentIds, $paymentJournalNos): void {
                                    $q->where('reference_type', 'SupplierPayment')
                                        ->whereIn('reference_id', $emptyPaymentIds);
                                    if ($paymentJournalNos !== []) {
                                        $q->orWhereIn('entry_no', $paymentJournalNos);
                                    }
                                })
                                ->pluck('id')
                                ->all()
                        );

                        DB::table('supplier_payments')->whereIn('id', $emptyPaymentIds)->delete();
                    }
                }
            }

            $transactions = PurchaseTransaction::whereIn('id', $productPtIds)->get([
                'id',
                'supplier_id',
                'purchase_type',
                'status',
                'grand_total',
                'down_payment_amount',
                'journal_no',
            ]);

            $debtReductionsBySupplier = [];
            foreach ($transactions as $transaction) {
                if ($transaction->status === 'posted' && $transaction->purchase_type === 'Kredit' && $transaction->supplier_id) {
                    $initialDebt = max(0, (int) $transaction->grand_total - (int) ($transaction->down_payment_amount ?? 0));
                    $remainingUnpaid = max(0.0, $initialDebt - ($paidByPt[$transaction->id] ?? 0.0));
                    if ($remainingUnpaid > 0) {
                        $debtReductionsBySupplier[$transaction->supplier_id] = ($debtReductionsBySupplier[$transaction->supplier_id] ?? 0.0) + $remainingUnpaid;
                    }
                }
            }

            if ($debtReductionsBySupplier !== [] && Schema::hasTable('suppliers')) {
                foreach (Supplier::whereIn('id', array_keys($debtReductionsBySupplier))->get() as $supplier) {
                    $reduction = $debtReductionsBySupplier[$supplier->id] ?? 0.0;
                    if ($reduction > 0) {
                        $supplier->update([
                            'outstanding_debt' => max(0, (float) $supplier->outstanding_debt - $reduction),
                        ]);
                    }
                }
            }

            $ptJournalNos = $transactions->pluck('journal_no')->filter()->unique()->values()->all();

            $journalIds = array_merge(
                $journalIds,
                JournalEntry::query()
                    ->where(function ($q) use ($productPtIds, $ptJournalNos, $returnIds): void {
                        $q->where(function ($sub) use ($productPtIds): void {
                            $sub->where('reference_type', 'Purchase')
                                ->whereIn('reference_id', $productPtIds);
                        });

                        if ($ptJournalNos !== []) {
                            $q->orWhereIn('entry_no', $ptJournalNos);
                        }

                        if ($returnIds !== []) {
                            $q->orWhere(function ($sub) use ($returnIds): void {
                                $sub->where('reference_type', 'PurchaseReturn')
                                    ->whereIn('reference_id', $returnIds);
                            });
                        }
                    })
                    ->pluck('id')
                    ->all()
            );

            if (Schema::hasTable('purchase_return_items')) {
                DB::table('purchase_return_items')->delete();
            }
            if (Schema::hasTable('purchase_returns')) {
                DB::table('purchase_returns')->whereIn('purchase_transaction_id', $productPtIds)->delete();
            }
            DB::table('purchase_transaction_details')->whereIn('purchase_transaction_id', $productPtIds)->delete();
            DB::table('purchase_transactions')->whereIn('id', $productPtIds)->delete();
        }

        if (Schema::hasTable('purchase_transactions') && Schema::hasColumn('purchase_transactions', 'po_id')) {
            DB::table('purchase_transactions')->whereNotNull('po_id')->update(['po_id' => null]);
        }

        foreach (['purchase_order_items', 'purchase_orders'] as $table) {
            if (Schema::hasTable($table)) {
                DB::table($table)->delete();
            }
        }

        $journalIds = array_merge(
            $journalIds,
            JournalEntry::query()->where('reference_type', 'PurchaseOrderDP')->pluck('id')->all()
        );

        return array_values(array_unique($journalIds));
    }

    /**
     * Reset all operational & initial stock movements, transfers, mutations, and opnames.
     *
     * @return array{0: int, 1: array<int, int>}
     */
    private function resetInventoryOperations(): array
    {
        $transferJournalIds = [];
        if (Schema::hasTable('stock_transfers') && Schema::hasColumn('stock_transfers', 'journal_entry_id')) {
            $transferJournalIds = DB::table('stock_transfers')
                ->whereNotNull('journal_entry_id')
                ->pluck('journal_entry_id')
                ->all();

            DB::table('stock_transfers')->whereNotNull('journal_entry_id')->update(['journal_entry_id' => null]);
        }

        if (Schema::hasTable('stock_opname_assignments') && Schema::hasColumn('stock_opname_assignments', 'stock_opname_id')) {
            DB::table('stock_opname_assignments')->whereNotNull('stock_opname_id')->update(['stock_opname_id' => null]);
        }

        if (Schema::hasTable('stock_opname_periods') && Schema::hasColumn('stock_opname_periods', 'final_stock_opname_id')) {
            DB::table('stock_opname_periods')->whereNotNull('final_stock_opname_id')->update(['final_stock_opname_id' => null]);
        }

        $deletedMovements = StockMovement::query()->delete();

        foreach ([
            'stock_transfer_items',
            'stock_transfers',
            'inventory_mutation_items',
            'inventory_mutations',
            'stock_opname_items',
            'stock_opnames',
        ] as $table) {
            if (Schema::hasTable($table)) {
                DB::table($table)->delete();
            }
        }

        $inventoryJournalIds = JournalEntry::query()
            ->whereIn('reference_type', ['InitialStock', 'Mutation', 'Opname', 'DO', StockTransfer::class])
            ->pluck('id')
            ->all();

        return [$deletedMovements, array_values(array_unique(array_merge($transferJournalIds, $inventoryJournalIds)))];
    }

    /**
     * Delete collected journal items and entries safely.
     *
     * @param  array<int, int>  $journalIds
     */
    private function deleteJournalEntries(array $journalIds): int
    {
        $uniqueIds = array_values(array_unique(array_filter($journalIds)));
        if ($uniqueIds === []) {
            return 0;
        }

        JournalItem::query()->whereIn('journal_entry_id', $uniqueIds)->delete();

        return JournalEntry::query()->whereIn('id', $uniqueIds)->delete();
    }

    /**
     * Delete all product master, variant, pricing, and branch stock tables cleanly.
     */
    private function deleteProductCatalog(): void
    {
        ProductBundle::query()->delete();
        ProductTierPrice::query()->delete();
        ProductBranchPrice::query()->delete();
        ProductBranchStock::query()->delete();

        foreach (['focus_products', 'focus_product_recommendations'] as $optionalTable) {
            if (Schema::hasTable($optionalTable)) {
                DB::table($optionalTable)->delete();
            }
        }

        foreach (self::NULLABLE_PRODUCT_COLUMNS as $table => $columns) {
            if (! Schema::hasTable($table)) {
                continue;
            }
            foreach ($columns as $column) {
                if (Schema::hasColumn($table, $column)) {
                    DB::table($table)->whereNotNull($column)->update([$column => null]);
                }
            }
        }

        ProductVariant::query()->delete();
        Product::query()->delete();
    }
}
