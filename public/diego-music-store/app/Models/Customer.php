<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'address',
        'date_of_birth',
        'customer_label_id',
        'pricing_tier_id',
        'is_loyalty_member',
        'loyalty_points',
        'deposit_balance',
        'outstanding_debt',
    ];

    protected $casts = [
        'is_loyalty_member' => 'boolean',
        'loyalty_points' => 'integer',
        'date_of_birth' => 'date',
    ];

    /**
     * Get the customer's label.
     */
    public function label()
    {
        return $this->belongsTo(CustomerLabel::class, 'customer_label_id');
    }

    /**
     * Get the customer's pricing tier.
     */
    public function pricingTier()
    {
        return $this->belongsTo(PricingTier::class, 'pricing_tier_id');
    }

    /**
     * Calculate total outstanding piutang dynamically across all unpaid sales.
     */
    public function getTotalPiutangAttribute(): float
    {
        $unpaidSales = Sale::where('customer_id', $this->id)
            ->where(function ($q) {
                $q->where('payment_method', 'like', '%piutang%')
                  ->orWhere('payment_method', 'like', '%credit%')
                  ->orWhere('status', '!=', 'completed');
            })
            ->get();

        $total = 0;
        foreach ($unpaidSales as $sale) {
            $total += $sale->getPiutangAmount();
        }

        return max(0, $total ?: floatval($this->outstanding_debt ?? 0));
    }

    protected static function booted()
    {
        static::saving(function ($customer) {
            if ($customer->customer_label_id && !is_numeric($customer->customer_label_id)) {
                $labelName = $customer->customer_label_id;
                $key = \Illuminate\Support\Str::slug($labelName);

                $label = CustomerLabel::firstOrCreate(
                    ['key' => $key],
                    ['name' => $labelName]
                );

                $customer->customer_label_id = $label->id;
            }
        });
    }
}
