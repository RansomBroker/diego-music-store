<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Unit extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'base_unit_id',
        'conversion_factor',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'conversion_factor' => 'integer',
    ];

    /**
     * Get the products for the unit.
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Get the base unit for this conversion unit.
     */
    public function baseUnit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'base_unit_id');
    }

    /**
     * Get the conversion units that reference this unit.
     */
    public function conversionUnits(): HasMany
    {
        return $this->hasMany(Unit::class, 'base_unit_id');
    }
}
