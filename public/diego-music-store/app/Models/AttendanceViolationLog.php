<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceViolationLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'employee_attendance_id',
        'violation_rule_id',
        'date',
        'violation_type',
        'late_early_minutes',
        'deduction_amount',
        'notes',
        'status',
        'approved_by',
        'payroll_period',
    ];

    protected $casts = [
        'date' => 'date',
        'late_early_minutes' => 'integer',
        'deduction_amount' => 'decimal:2',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function attendance(): BelongsTo
    {
        return $this->belongsTo(EmployeeAttendance::class, 'employee_attendance_id');
    }

    public function rule(): BelongsTo
    {
        return $this->belongsTo(AttendanceViolationRule::class, 'violation_rule_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
