<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'prefix',
        'start_alphabet',
        'end_alphabet',
        'digit_length',
    ];

    /**
     * Invalidate POS sale categories cache whenever categories change.
     */
    protected static function booted(): void
    {
        $clearCache = static fn () => cache()->forget('pos_sale_categories');

        static::saved($clearCache);
        static::deleted($clearCache);
    }

    /**
     * Customers that default to this sale category.
     */
    public function customers()
    {
        return $this->hasMany(Customer::class, 'sale_category_id');
    }
}
