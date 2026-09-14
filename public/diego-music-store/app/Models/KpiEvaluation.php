<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KpiEvaluation extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'kpi_template_id',
        'branch_id',
        'period',
        'actual_sales_amount',
        'sales_score',
        'actual_atv_amount',
        'atv_score',
        'actual_attendance_pct',
        'attendance_score',
        'actual_punctuality_pct',
        'punctuality_score',
        'final_kpi_score',
        'earned_bonus_amount',
        'status',
        'approved_by',
        'approved_at',
        'notes',
    ];

    protected $casts = [
        'actual_sales_amount' => 'float',
        'sales_score' => 'float',
        'actual_atv_amount' => 'float',
        'atv_score' => 'float',
        'actual_attendance_pct' => 'float',
        'attendance_score' => 'float',
        'actual_punctuality_pct' => 'float',
        'punctuality_score' => 'float',
        'final_kpi_score' => 'float',
        'earned_bonus_amount' => 'float',
        'approved_at' => 'datetime',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(KpiTemplate::class, 'kpi_template_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
