<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Asset extends Model
{
    use HasFactory;

    protected $fillable = [
        'asset_code',
        'name',
        'category',
        'branch_id',
        'purchase_date',
        'purchase_cost',
        'salvage_value',
        'useful_life_years',
        'accumulated_depreciation',
        'status',
        'notes',
    ];

    protected $casts = [
        'purchase_date'            => 'date',
        'purchase_cost'            => 'decimal:2',
        'salvage_value'            => 'decimal:2',
        'useful_life_years'        => 'integer',
        'accumulated_depreciation' => 'decimal:2',
    ];

    protected static function booted()
    {
        static::creating(function ($asset) {
            if (empty($asset->asset_code)) {
                $asset->asset_code = static::generateAssetCode();
            }
        });
    }

    public static function generateAssetCode(): string
    {
        $prefix = 'AST-' . now()->format('Ym') . '-';
        $last = static::where('asset_code', 'LIKE', $prefix . '%')
            ->orderBy('asset_code', 'desc')
            ->first();

        if ($last) {
            $num = (int) substr($last->asset_code, -4) + 1;
        } else {
            $num = 1;
        }

        return $prefix . str_pad($num, 4, '0', STR_PAD_LEFT);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function disposals(): HasMany
    {
        return $this->hasMany(AssetDisposal::class);
    }

    public function getBookValueAttribute(): float
    {
        return max(0, (float) $this->purchase_cost - (float) $this->accumulated_depreciation);
    }
}
