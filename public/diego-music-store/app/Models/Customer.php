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
        'is_loyalty_member',
        'loyalty_points',
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
