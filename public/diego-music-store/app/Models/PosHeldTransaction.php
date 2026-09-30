<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PosHeldTransaction extends Model
{
    protected $table = 'pos_held_transactions';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'branch_id',
        'user_id',
        'customer_id',
        'customer_name',
        'cart_data',
        'discount_amount',
        'discount_type',
        'discount_value',
        'use_points',
        'is_loyalty',
        'pricing_tier_id'
    ];

    protected $casts = [
        'cart_data' => 'array',
        'use_points' => 'boolean',
        'is_loyalty' => 'boolean',
        'discount_amount' => 'integer',
        'discount_value' => 'integer'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function getGrandTotalAttribute()
    {
        $subtotal = collect($this->cart_data)->sum(function ($item) {
            $price = $item['price'] ?? 0;
            $qty = $item['qty'] ?? 1;
            $discount = $item['discount_amount'] ?? 0;
            $tax = $item['tax_amount'] ?? 0;
            return ($price * $qty) - $discount + $tax;
        });

        return max(0, $subtotal - ($this->discount_amount ?? 0));
    }
}
