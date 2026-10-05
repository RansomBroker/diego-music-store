<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class FocusProductRecommendation extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'product_variant_id',
        'rule_id',
        'score',
        'current_stock',
        'recent_sales_qty',
        'aging_days',
        'reason',
        'status',
        'evaluated_at',
        'dismissed_at',
        'dismissed_by',
    ];

    protected $casts = [
        'score' => 'integer',
        'current_stock' => 'integer',
        'recent_sales_qty' => 'integer',
        'aging_days' => 'integer',
        'evaluated_at' => 'datetime',
        'dismissed_at' => 'datetime',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function rule(): BelongsTo
    {
        return $this->belongsTo(FocusProductRule::class, 'rule_id');
    }

    public function dismissedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dismissed_by');
    }

    public function focusProduct(): HasOne
    {
        return $this->hasOne(FocusProduct::class, 'recommendation_id');
    }
}
