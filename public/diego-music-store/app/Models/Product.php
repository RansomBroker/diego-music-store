<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'type', // 'physical', 'bundle', 'service'
        'unit_id',
        'image_path',
        'is_active',
        'inventory_account_id',
        'sales_account_id',
        'cogs_account_id',
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
     * Get the unit of measure for the product.
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * Get the mapped inventory account.
     */
    public function inventoryAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'inventory_account_id');
    }

    /**
     * Get the mapped sales account.
     */
    public function salesAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'sales_account_id');
    }

    /**
     * Get the mapped COGS account.
     */
    public function cogsAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'cogs_account_id');
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
