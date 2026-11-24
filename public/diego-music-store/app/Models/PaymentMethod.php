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

    /**
     * Format distinct parent-child label for filter dropdowns (e.g. Transfer - BCA, QRIS - BCA).
     */
    public function formatFilterLabel(): string
    {
        $name = trim((string) $this->name);
        if (! $this->parent) {
            return $name;
        }

        $parentName = trim((string) $this->parent->name);
        $childName = $this->stripParentPrefix($parentName, $name);

        return "{$parentName} - {$childName}";
    }

    /**
     * Candidate text labels matched against legacy sales without relational sale_payments rows.
     *
     * @return array<int, string>
     */
    public function getLegacyFilterLabels(): array
    {
        if (! $this->parent) {
            $labels = [trim((string) $this->name)];
            if (strtolower((string) $this->code) === 'cash') {
                $labels[] = 'Tunai';
            }

            return array_values(array_unique(array_filter($labels)));
        }

        $parentName = trim((string) $this->parent->name);
        $rawChild = trim((string) $this->name);
        $cleanChild = $this->stripParentPrefix($parentName, $rawChild);

        return array_values(array_unique(array_filter([
            $this->formatFilterLabel(),
            $this->formatCheckoutLabel(),
            "{$parentName} ({$cleanChild})",
            "{$parentName} ({$rawChild})",
        ])));
    }

    /**
     * Build separated filter options for multiselect dropdowns, expanding parent categories into Parent - Child entries.
     *
     * @return array<int, array{value: string, label: string}>
     */
    public static function getFilterOptions(): array
    {
        $methods = static::with(['parent', 'children' => fn ($q) => $q->where('is_active', true)])
            ->where('is_active', true)
            ->get();

        $options = [];

        foreach ($methods as $pm) {
            // Skip container parents that already have active child sub-methods
            if ($pm->parent_id === null && $pm->children->isNotEmpty()) {
                continue;
            }

            // Skip orphan children whose parent is inactive
            if ($pm->parent_id !== null && $pm->parent && ! $pm->parent->is_active) {
                continue;
            }

            $options[] = [
                'value' => (string) $pm->id,
                'label' => $pm->formatFilterLabel(),
            ];
        }

        usort($options, fn (array $a, array $b): int => strcasecmp($a['label'], $b['label']));

        return $options;
    }

    private function stripParentPrefix(string $parentName, string $childName): string
    {
        if ($parentName === '' || $childName === '') {
            return $childName;
        }

        $stripped = preg_replace('/^' . preg_quote($parentName, '/') . '\s*[-:]?\s+/i', '', $childName);

        return ($stripped !== null && trim($stripped) !== '') ? trim($stripped) : $childName;
    }
}
