<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FocusProductRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'conditions',
        'priority',
        'is_active',
    ];

    protected $casts = [
        'conditions' => 'array',
        'priority' => 'integer',
        'is_active' => 'boolean',
    ];

    public function recommendations(): HasMany
    {
        return $this->hasMany(FocusProductRecommendation::class, 'rule_id');
    }

    public function focusProducts(): HasMany
    {
        return $this->hasMany(FocusProduct::class, 'rule_id');
    }
}
