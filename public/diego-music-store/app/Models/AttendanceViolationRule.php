<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AttendanceViolationRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'violation_type',
        'min_minutes',
        'max_minutes',
        'deduction_type',
        'deduction_amount',
        'is_active',
    ];

    protected $casts = [
        'min_minutes' => 'integer',
        'max_minutes' => 'integer',
        'deduction_amount' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function violationLogs(): HasMany
    {
        return $this->hasMany(AttendanceViolationLog::class, 'violation_rule_id');
    }
}
