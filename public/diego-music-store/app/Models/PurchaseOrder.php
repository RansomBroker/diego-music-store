<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_id',
        'branch_id',
        'created_by_id',
        'po_number',
        'currency',
        'payment_term',
        'order_date',
        'eta_date',
        'status', // draft, approved, closed
        'total_amount',
        'discount_amount',
        'other_cost',
        'tax_mode', // GLOBAL, ITEM
        'tax_rate',
        'tax_amount',
        'grand_total',
        'notes',
    ];

    protected $casts = [
        'order_date' => 'date',
        'eta_date' => 'date',
        'total_amount' => 'integer',
        'discount_amount' => 'integer',
        'other_cost' => 'integer',
        'tax_rate' => 'integer',
        'tax_amount' => 'integer',
        'grand_total' => 'integer',
    ];

    protected static function booted()
    {
        static::creating(function ($purchaseOrder) {
            if (empty($purchaseOrder->po_number)) {
                $purchaseOrder->po_number = static::generatePoNumber();
            }
        });
    }

    public static function generatePoNumber(): string
    {
        $date = now()->format('Ymd');
        $prefix = 'PO-' . $date . '-';

        $lastPo = static::where('po_number', 'like', $prefix . '%')
            ->orderBy('po_number', 'desc')
            ->first();

        if ($lastPo) {
            $lastNum = intval(substr($lastPo->po_number, strlen($prefix)));
            $nextNum = str_pad($lastNum + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $nextNum = '0001';
        }

        return $prefix . $nextNum;
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function purchaseTransactions(): HasMany
    {
        return $this->hasMany(PurchaseTransaction::class, 'po_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }
}
