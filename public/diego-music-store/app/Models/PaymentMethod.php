<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentMethod extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'parent_id',
        'account_id',
        'is_active',
        'is_default',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'parent_id' => 'integer',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(PaymentMethod::class, 'parent_id')
            ->orderByDesc('is_default')
            ->orderBy('id');
    }

    public function getEffectiveAccountId(): ?int
    {
        return $this->account_id ?? $this->parent?->account_id;
    }

    /**
     * Resolve payment method (and optional sub-method) for checkout & journal routing.
     */
    public static function resolveForCheckout(string $code, ?string $subMethodName = null): ?self
    {
        $parent = static::with(['children' => fn ($q) => $q->where('is_active', true), 'parent'])
            ->where('code', $code)
            ->first();

        if (! $parent) {
            return null;
        }

        if ($subMethodName !== null && trim($subMethodName) !== '') {
            $needle = trim($subMethodName);
            $child = $parent->children->first(
                fn (self $item) => strcasecmp($item->name, $needle) === 0 || strcasecmp($item->code, $needle) === 0
            );
            if ($child) {
                $child->setRelation('parent', $parent);
                return $child;
            }
        }

        $defaultChild = $parent->children->firstWhere('is_default', true);
        if ($defaultChild) {
            $defaultChild->setRelation('parent', $parent);
            return $defaultChild;
        }

        return $parent;
    }

    /**
     * Format human-readable method label for journal entries and Z-Report breakdown.
     */
    public function formatCheckoutLabel(?string $ref = null): string
    {
        $label = $this->parent
            ? "{$this->parent->name} - {$this->name}"
            : $this->name;

        return $label . ($ref ? " (Ref: {$ref})" : '');
    }
}
