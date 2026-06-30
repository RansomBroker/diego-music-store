<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockOpnameItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'stock_opname_id',
        'product_variant_id',
        'system_qty',
        'physical_qty',
        'difference',
        'cost_price',
    ];

    protected $casts = [
        'system_qty' => 'integer',
        'physical_qty' => 'integer',
        'difference' => 'integer',
        'cost_price' => 'integer',
    ];

    public function stockOpname(): BelongsTo
    {
        return $this->belongsTo(StockOpname::class);
    }

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }
}
