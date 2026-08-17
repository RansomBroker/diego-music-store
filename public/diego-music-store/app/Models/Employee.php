<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'branch_id',
        'nik',
        'name',
        'phone',
        'email',
        'address',
        'join_date',
        'monthly_off_days_quota',
        'basic_salary',
        'is_active',
    ];

    protected $casts = [
        'join_date' => 'date',
        'monthly_off_days_quota' => 'integer',
        'basic_salary' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Get the user account linked to this employee.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the primary branch assigned to this employee.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
}
