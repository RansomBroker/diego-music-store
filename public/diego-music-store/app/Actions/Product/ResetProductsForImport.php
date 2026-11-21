<?php

namespace App\Actions\Product;

use App\Helpers\PermissionHelper;
use App\Models\JournalEntry;
use App\Models\JournalItem;
use App\Models\Product;
use App\Models\ProductBranchPrice;
use App\Models\ProductBranchStock;
use App\Models\ProductBundle;
use App\Models\ProductTierPrice;
use App\Models\ProductVariant;
use App\Models\StockMovement;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ResetProductsForImport
{
    /**
     * Transactional tables whose existing references prevent unsafe hard reset of products.
     *
     * @var array<string, string>
     */
    private const BLOCKING_TRANSACTION_TABLES = [
        'sale_items' => 'Penjualan POS',
        'sales_quotation_items' => 'Penawaran Penjualan',
        'sales_invoice_items' => 'Faktur Penjualan',
        'sales_return_items' => 'Retur Penjualan',
        'purchase_order_items' => 'Purchase Order',
        'purchase_transaction_details' => 'Transaksi Pembelian',
        'delivery_order_items' => 'Surat Jalan / DO',
        'purchase_return_items' => 'Retur Pembelian',
        'stock_opname_items' => 'Stok Opname',
        'stock_transfer_items' => 'Transfer Stok',
        'inventory_mutation_items' => 'Mutasi Inventori',
    ];

    /**
     * Nullable foreign-key references that should be detached cleanly before removing products.
     *
     * @var array<string, array<int, string>>
     */
    private const NULLABLE_REFERENCE_COLUMNS = [
        'customer_deposits' => ['product_id', 'product_variant_id'],
        'commission_schemes' => ['target_product_id'],
    ];

    /**
     * Delete all existing product catalog records, initial stocks, and opening stock journals
     * so products can be cleanly recreated from a fresh import.
     *
     * @return array{deleted_products: int, deleted_variants: int, deleted_journals: int}
     *
     * @throws Exception
     */
    public function execute(): array
    {
        PermissionHelper::enforceActionPermission(Auth::user(), 'bo.products.delete');

        return DB::transaction(function (): array {
            $blockers = $this->findBlockingTransactions();
            if ($blockers !== []) {
                throw new Exception(
                    'Tidak dapat menghapus seluruh data produk karena sebagian produk sudah memiliki riwayat transaksi: '
                    . implode(', ', $blockers)
                    . '.'
                );
            }

            $deletedProducts = Product::count();
            $deletedVariants = ProductVariant::count();

            $initialJournalIds = JournalEntry::query()
                ->where('reference_type', 'InitialStock')
                ->pluck('id');

            $deletedJournals = $initialJournalIds->count();
            if ($initialJournalIds->isNotEmpty()) {
                JournalItem::query()->whereIn('journal_entry_id', $initialJournalIds)->delete();
                JournalEntry::query()->whereIn('id', $initialJournalIds)->delete();
            }

            StockMovement::query()->delete();

            ProductBundle::query()->delete();
            ProductTierPrice::query()->delete();
            ProductBranchPrice::query()->delete();
            ProductBranchStock::query()->delete();

            foreach (['focus_products', 'focus_product_recommendations'] as $optionalTable) {
                if (Schema::hasTable($optionalTable)) {
                    DB::table($optionalTable)->delete();
                }
            }

            foreach (self::NULLABLE_REFERENCE_COLUMNS as $table => $columns) {
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

            return [
                'deleted_products' => $deletedProducts,
                'deleted_variants' => $deletedVariants,
                'deleted_journals' => $deletedJournals,
            ];
        });
    }

    /**
     * Identify operational transaction tables currently referencing product variants.
     *
     * @return array<int, string>
     */
    private function findBlockingTransactions(): array
    {
        $blockers = [];

        foreach (self::BLOCKING_TRANSACTION_TABLES as $table => $label) {
            if (Schema::hasTable($table) && DB::table($table)->whereNotNull('product_variant_id')->exists()) {
                $blockers[] = $label;
            }
        }

        $hasOperationalMovements = StockMovement::query()
            ->where(function ($query) {
                $query->where('reference_type', '!=', 'InitialStock')
                    ->orWhereNull('reference_type');
            })
            ->exists();

        if ($hasOperationalMovements) {
            $blockers[] = 'Pergerakan Stok Operasional';
        }

        return $blockers;
    }
}

