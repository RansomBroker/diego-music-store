<?php

namespace App\Actions\Sales;

use App\Helpers\BranchHelper;
use App\Helpers\PermissionHelper;
use App\Models\Customer;
use App\Models\JournalEntry;
use App\Models\ProductBranchStock;
use App\Models\Sale;
use App\Models\SalesCommissionLog;
use App\Models\SalesReturn;
use App\Models\StockMovement;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;

class DeletePOSSale
{
    public function execute(Sale $sale): void
    {
        PermissionHelper::enforceActionPermission(Auth::user(), 'fo.transactions.delete');
        abort_unless(Auth::check(), 403);

        $allowedBranchIds = BranchHelper::getAllowedBranchesQuery()
            ->pluck('branches.id')
            ->map(fn ($id) => (int) $id)
            ->all();
        abort_unless(in_array((int) $sale->branch_id, $allowedBranchIds, true), 403);

        if (SalesReturn::where('sale_id', $sale->id)->exists()) {
            throw new InvalidArgumentException("Transaksi {$sale->invoice_number} sudah memiliki retur penjualan sehingga tidak dapat dihapus.");
        }

        $hasReceivableSettlement = JournalEntry::where('reference_type', 'Sales')
            ->where('reference_id', $sale->id)
            ->where('description', 'like', 'Pelunasan Piutang%')
            ->exists();

        if ($hasReceivableSettlement) {
            throw new InvalidArgumentException("Transaksi {$sale->invoice_number} sudah memiliki pelunasan piutang sehingga tidak dapat dihapus.");
        }

        DB::transaction(function () use ($sale): void {
            $sale->loadMissing(['items.variant.product']);
            $branchId = $sale->branch_id;

            // 1. Kembalikan stok fisik
            foreach ($sale->items as $item) {
                $variant = $item->variant;
                if ($variant && $variant->product && $variant->product->isPhysical()) {
                    $branchStock = ProductBranchStock::where('product_variant_id', $variant->id)
                        ->where('branch_id', $branchId)
                        ->first();

                    if ($branchStock) {
                        $branchStock->update([
                            'stock' => $branchStock->stock + $item->quantity,
                        ]);
                    }
                }
            }

            // 2. Kurangi piutang customer jika transaksi masih memiliki piutang
            $piutangAmount = max(0, (float) $sale->getPiutangAmount());
            if ($piutangAmount > 0 && $sale->customer_id) {
                try {
                    $customer = Customer::find($sale->customer_id);
                    if ($customer && Schema::hasColumn('customers', 'outstanding_debt') && $customer->outstanding_debt > 0) {
                        $customer->decrement('outstanding_debt', min((float) $customer->outstanding_debt, $piutangAmount));
                    }
                } catch (\Throwable $e) {
                    Log::warning("Gagal memperbarui outstanding_debt saat menghapus transaksi #{$sale->id}: " . $e->getMessage());
                }
            }

            // 3. Hapus mutasi stok POS
            StockMovement::where('reference_type', 'POS')
                ->where('reference_id', $sale->id)
                ->delete();

            // 4. Hapus jurnal & detail jurnal penjualan
            $journalEntries = JournalEntry::where('reference_type', 'Sales')
                ->where('reference_id', $sale->id)
                ->get();

            foreach ($journalEntries as $je) {
                $je->items()->delete();
                $je->delete();
            }

            // 5. Hapus log komisi sales terkait (jika ada)
            SalesCommissionLog::where('sale_id', $sale->id)->delete();

            // 6. Hapus item transaksi, pembayaran & header transaksi (hard delete)
            $sale->payments()->delete();
            $sale->items()->delete();
            $sale->delete();
        });
    }
}

