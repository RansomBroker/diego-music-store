<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'sku',
        'barcode',
        'name',
        'price',
        'discount_value',
        'discount_type',
        'tax_value',
        'tax_type',
        'cost_price',
        'hpp',
        'is_active',
    ];

    protected $casts = [
        'price' => 'integer',
        'discount_value' => 'float',
        'tax_value' => 'float',
        'cost_price' => 'integer',
        'hpp' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Get the parent product.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the pricing tier overrides.
     */
    public function tierPrices(): HasMany
    {
        return $this->hasMany(ProductTierPrice::class);
    }

    /**
     * Get the branch price overrides.
     */
    public function branchPrices(): HasMany
    {
        return $this->hasMany(ProductBranchPrice::class);
    }

    /**
     * Get the branch stock entries.
     */
    public function branchStocks(): HasMany
    {
        return $this->hasMany(ProductBranchStock::class);
    }

    /**
     * If this variant is a bundle, get its component definitions.
     */
    public function bundleItems(): HasMany
    {
        return $this->hasMany(ProductBundle::class, 'parent_variant_id');
    }

    /**
     * Get the child variants directly if this is a bundle.
     */
    public function childVariants(): BelongsToMany
    {
        return $this->belongsToMany(ProductVariant::class, 'product_bundles', 'parent_variant_id', 'child_variant_id')
            ->withPivot('quantity')
            ->withTimestamps();
    }

    /**
     * Get stock for a specific branch.
     */
    public function stockForBranch(int $branchId): int
    {
        if ($this->product->isService()) {
            return 999999; // Unlimited indicator
        }

        if ($this->product->isBundle()) {
            $components = $this->bundleItems;
            if ($components->isEmpty()) {
                return 0;
            }

            $minStock = null;
            foreach ($components as $component) {
                $childVariant = $component->childVariant;
                if (!$childVariant) {
                    continue;
                }
                $childStock = $childVariant->stockForBranch($branchId);
                $possibleBundles = intval($childStock / $component->quantity);
                if ($minStock === null || $possibleBundles < $minStock) {
                    $minStock = $possibleBundles;
                }
            }

            return $minStock ?? 0;
        }

        // Physical product stock
        $branchStock = $this->branchStocks()->where('branch_id', $branchId)->first();
        return $branchStock ? $branchStock->stock : 0;
    }

    /**
     * Get total stock across all active branches.
     */
    public function totalStock(): int
    {
        if ($this->product->isService()) {
            return 999999;
        }

        if ($this->product->isBundle()) {
            $branches = Branch::where('is_active', true)->get();
            $total = 0;
            foreach ($branches as $branch) {
                $total += $this->stockForBranch($branch->id);
            }
            return $total;
        }

        return $this->branchStocks()->sum('stock');
    }

    /**
     * Get price for a specific tier, falling back to base price.
     */
    public function priceForTier(int $pricingTierId): int
    {
        $tierPrice = $this->tierPrices()->where('pricing_tier_id', $pricingTierId)->first();
        return $tierPrice ? $tierPrice->price : $this->price;
    }

    /**
     * Get price for a specific branch, falling back to base price.
     */
    public function priceForBranch(int $branchId): int
    {
        $branchPrice = $this->branchPrices()->where('branch_id', $branchId)->first();
        return $branchPrice ? $branchPrice->price : $this->price;
    }
}
