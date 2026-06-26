<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'type', // 'physical', 'bundle', 'service'
        'image_path',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the variants for the product.
     */
    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    /**
     * Helper to get the default/first variant.
     */
    public function getDefaultVariantAttribute(): ?ProductVariant
    {
        return $this->variants()->first();
    }

    /**
     * Check if product is physical.
     */
    public function isPhysical(): bool
    {
        return $this->type === 'physical';
    }

    /**
     * Check if product is a bundle.
     */
    public function isBundle(): bool
    {
        return $this->type === 'bundle';
    }

    /**
     * Check if product is a service.
     */
    public function isService(): bool
    {
        return $this->type === 'service';
    }
}
