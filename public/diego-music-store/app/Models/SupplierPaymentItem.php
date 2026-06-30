<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplierPaymentItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_payment_id',
        'purchase_transaction_id',
        'amount_due',
        'amount_paid',
    ];

    protected $casts = [
        'amount_due' => 'integer',
        'amount_paid' => 'integer',
    ];

    public function supplierPayment(): BelongsTo
    {
        return $this->belongsTo(SupplierPayment::class);
    }

    public function purchaseTransaction(): BelongsTo
    {
        return $this->belongsTo(PurchaseTransaction::class);
    }
}
