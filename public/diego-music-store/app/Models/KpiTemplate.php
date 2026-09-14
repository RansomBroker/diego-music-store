<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KpiTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'position',
        'employee_id',
        'max_bonus_amount',
        'target_sales_amount',
        'weight_sales',
        'target_atv_amount',
        'weight_atv',
        'target_attendance_pct',
        'weight_attendance',
        'target_punctuality_pct',
        'weight_punctuality',
        'bonus_tiering_rules',
        'is_active',
    ];

    protected $casts = [
        'max_bonus_amount' => 'float',
        'target_sales_amount' => 'float',
        'weight_sales' => 'float',
        'target_atv_amount' => 'float',
        'weight_atv' => 'float',
        'target_attendance_pct' => 'float',
        'weight_attendance' => 'float',
        'target_punctuality_pct' => 'float',
        'weight_punctuality' => 'float',
        'bonus_tiering_rules' => 'array',
        'is_active' => 'boolean',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function evaluations(): HasMany
    {
        return $this->hasMany(KpiEvaluation::class);
    }
}
