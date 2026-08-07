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
        'discount_type',
        'discount_value',
        'tax_rate',
        'tax_amount',
        'subtotal',
        'update_cost_price',
        'qty_bonus',
    ];

    protected $casts = [
        'qty_po' => 'integer',
        'qty_received' => 'integer',
        'qty_bonus' => 'integer',
        'price' => 'integer',
        'discount' => 'integer',
        'discount_value' => 'integer',
        'tax_rate' => 'integer',
        'tax_amount' => 'integer',
        'subtotal' => 'integer',
        'update_cost_price' => 'boolean',
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

    public function returnItems(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PurchaseReturnItem::class, 'purchase_transaction_detail_id');
    }

    public function getReturnedQtyAttribute(): int
    {
        return (int) $this->returnItems()->sum('quantity');
    }

    public function getAvailableQtyForReturnAttribute(): int
    {
        return max(0, $this->qty_received - $this->returned_qty);
    }
}
