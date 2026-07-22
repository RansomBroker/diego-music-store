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
        'category',
        'brand',
        'supplier_id',
        'unit_id',
        'image_path',
        'is_active',
        'minimum_stock',
        'inventory_account_id',
        'sales_account_id',
        'cogs_account_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'minimum_stock' => 'integer',
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

    /**
     * Get the supplier for the product.
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Get total stock across all variants.
     */
    public function getTotalStock(): int
    {
        if ($this->isService()) {
            return 999999;
        }

        $total = 0;
        foreach ($this->variants as $variant) {
            $total += $variant->totalStock();
        }
        return $total;
    }

    protected static function booted()
    {
        static::saving(function ($product) {
            if ($product->supplier_id && !is_numeric($product->supplier_id)) {
                $supplierName = $product->supplier_id;
                $supplier = Supplier::create([
                    'name' => $supplierName,
                    'outstanding_debt' => 0,
                ]);
                $product->supplier_id = $supplier->id;
            }
        });
    }
}
