<?php

namespace App\Actions\Procurement;

use App\Helpers\PermissionHelper;
use App\Models\PurchaseTransaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class DeletePurchaseTransaction
{
    public function __construct(
        private readonly RevertPurchaseTransactionPosting $revertPosting
    ) {}

    /**
     * Hapus transaksi pembelian (baik draft maupun posted).
     * Untuk transaksi posted, efek stok, jurnal akuntansi, hutang supplier, dan status PO akan di-rollback.
     */
    public function execute(PurchaseTransaction $pt): void
    {
        if ($pt->status === 'cancelled') {
            throw new InvalidArgumentException('Transaksi Pembelian yang dibatalkan tidak dapat dihapus.');
        }

        $requiredPermission = $pt->status === 'posted'
            ? 'bo.purchase_transactions.delete_posted'
            : 'bo.purchase_transactions.delete';

        if (Auth::check()) {
            PermissionHelper::enforceActionPermission(Auth::user(), $requiredPermission);
        }

        DB::transaction(function () use ($pt): void {
            $wasPosted = $pt->status === 'posted';
            $poId = $pt->po_id;

            if ($wasPosted) {
                $this->revertPosting->execute($pt);
            }

            $pt->details()->delete();
            $pt->delete();

            if ($wasPosted && $poId) {
                $this->revertPosting->syncPurchaseOrderStatus($poId);
            }
        });
    }
}

