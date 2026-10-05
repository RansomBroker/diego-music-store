<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FocusProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'focus_number',
        'branch_id',
        'product_variant_id',
        'source',
        'rule_id',
        'recommendation_id',
        'status',
        'reason',
        'note',
        'active_from',
        'active_until',
        'focused_at',
        'focused_by',
        'resolved_at',
        'resolved_by',
        'resolution_note',
        'dismissed_at',
        'dismissed_by',
        'dismissal_note',
    ];

    protected $casts = [
        'active_from' => 'datetime',
        'active_until' => 'datetime',
        'focused_at' => 'datetime',
        'resolved_at' => 'datetime',
        'dismissed_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function ($focus) {
            if (empty($focus->focus_number)) {
                $focus->focus_number = static::generateFocusNumber();
            }
            if (empty($focus->active_from)) {
                $focus->active_from = now();
            }
            if (empty($focus->focused_at)) {
                $focus->focused_at = now();
            }
        });
    }

    public static function generateFocusNumber(): string
    {
        $prefix = 'FP-' . now()->format('Ym') . '-';
        $lastRecord = static::where('focus_number', 'like', $prefix . '%')
            ->orderBy('focus_number', 'desc')
            ->first();

        if ($lastRecord) {
            $lastNumber = (int) substr($lastRecord->focus_number, strlen($prefix));
            $nextNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $nextNumber = '0001';
        }

        return $prefix . $nextNumber;
    }

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

    public function recommendation(): BelongsTo
    {
        return $this->belongsTo(FocusProductRecommendation::class, 'recommendation_id');
    }

    public function focusedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'focused_by');
    }

    public function resolvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function dismissedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dismissed_by');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'ACTIVE');
    }

    public function scopeExpired(Builder $query): Builder
    {
        return $query->where('status', 'EXPIRED');
    }

    public function scopeResolved(Builder $query): Builder
    {
        return $query->where('status', 'RESOLVED');
    }

    public function scopeDismissed(Builder $query): Builder
    {
        return $query->where('status', 'DISMISSED');
    }
}
