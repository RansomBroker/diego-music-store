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
        'status', // draft, received
        'shipping_cost',
        'notes',
    ];

    protected $casts = [
        'received_date' => 'date',
        'shipping_cost' => 'integer',
    ];

    protected static function booted()
    {
        static::creating(function ($deliveryOrder) {
            if (empty($deliveryOrder->do_number)) {
                $deliveryOrder->do_number = static::generateDoNumber();
            }
        });
    }

    public static function generateDoNumber(): string
    {
        $date = now()->format('Ymd');
        $prefix = 'DO-' . $date . '-';

        $lastDo = static::where('do_number', 'like', $prefix . '%')
            ->orderBy('do_number', 'desc')
            ->first();

        if ($lastDo) {
            $lastNum = intval(substr($lastDo->do_number, strlen($prefix)));
            $nextNum = str_pad($lastNum + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $nextNum = '0001';
        }

        return $prefix . $nextNum;
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(DeliveryOrderItem::class);
    }
}
