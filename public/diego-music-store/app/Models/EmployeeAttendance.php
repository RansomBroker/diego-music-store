<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeAttendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'branch_id',
        'date',
        'clock_in',
        'clock_out',
        'status',
        'late_minutes',
        'notes',
        'latitude',
        'longitude',
        'distance_meters',
        'is_out_of_radius',
        'clock_in_photo_path',
        'clock_out_photo_path',
        'is_backdate',
        'approved_by',
    ];

    protected $casts = [
        'date' => 'date',
        'clock_in' => 'datetime',
        'clock_out' => 'datetime',
        'late_minutes' => 'integer',
        'latitude' => 'float',
        'longitude' => 'float',
        'distance_meters' => 'integer',
        'is_out_of_radius' => 'boolean',
        'is_backdate' => 'boolean',
    ];

    /**
     * Get the employee associated with this attendance record.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get the branch associated with this attendance record.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the user who approved this backdate attendance.
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
