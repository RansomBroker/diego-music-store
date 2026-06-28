<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseTransactionDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_transaction_id',
        'product_variant_id',
        'qty_po',
        'qty_received',
        'unit_id',
        'price',
        'discount',
        'tax_rate',
        'tax_amount',
        'subtotal',
    ];

    protected $casts = [
        'qty_po' => 'integer',
        'qty_received' => 'integer',
        'price' => 'integer',
        'discount' => 'integer',
        'tax_rate' => 'integer',
        'tax_amount' => 'integer',
        'subtotal' => 'integer',
    ];

    public function purchaseTransaction(): BelongsTo
    {
        return $this->belongsTo(PurchaseTransaction::class);
    }

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }
}
