<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductBundle extends Model
{
    use HasFactory;

    protected $fillable = [
        'parent_variant_id',
        'child_variant_id',
        'quantity',
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];

    /**
     * Get the parent variant (the bundle itself).
     */
    public function parentVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'parent_variant_id');
    }

    /**
     * Get the child variant (the physical product component).
     */
    public function childVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'child_variant_id');
    }
}
