<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PricingTier extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'price_follows_hpp',
        'censor_prices',
    ];

    protected $casts = [
        'price_follows_hpp' => 'boolean',
        'censor_prices' => 'boolean',
    ];

    protected static function booted(): void
    {
        $clearCache = static fn () => cache()->forget('pos_pricing_tiers');

        static::saved($clearCache);
        static::deleted($clearCache);
    }

    /**
     * Get the prices associated with this tier.
     */
    public function prices(): HasMany
    {
        return $this->hasMany(ProductTierPrice::class);
    }
}
