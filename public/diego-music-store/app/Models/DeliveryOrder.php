<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeliveryOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_order_id',
        'branch_id',
        'do_number',
        'received_date',
        'status',
        'shipping_cost',
        'notes',
    ];

    protected $casts = [
        'received_date' => 'date',
        'shipping_cost' => 'integer',
    ];

    /**
     * Get the parent purchase order.
     */
    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    /**
     * Get the branch receiving the order.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the delivery order items.
     */
    public function items(): HasMany
    {
        return $this->hasMany(DeliveryOrderItem::class);
    }
}
