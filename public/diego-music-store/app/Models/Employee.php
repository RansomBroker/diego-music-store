<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
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

    /**
     * Get all attendance records for this employee.
     */
    public function attendances(): HasMany
    {
        return $this->hasMany(EmployeeAttendance::class);
    }

    /**
     * Get all sales commission logs for this employee.
     */
    public function commissionLogs(): HasMany
    {
        return $this->hasMany(SalesCommissionLog::class);
    }

    /**
     * Get all commission schemes assigned to this employee.
     */
    public function commissionSchemes(): BelongsToMany
    {
        return $this->belongsToMany(CommissionScheme::class, 'commission_scheme_employee');
    }

    /**
     * Get all attendance violation logs for this employee.
     */
    public function violationLogs(): HasMany
    {
        return $this->hasMany(AttendanceViolationLog::class);
    }

    /**
     * Get all KPI evaluations for this employee.
     */
    public function kpiEvaluations(): HasMany
    {
        return $this->hasMany(KpiEvaluation::class);
    }

    /**
     * Get all payroll items for this employee.
     */
    public function payrollItems(): HasMany
    {
        return $this->hasMany(PayrollItem::class);
    }

    /**
     * Get all overtime logs for this employee.
     */
    public function overtimes(): HasMany
    {
        return $this->hasMany(EmployeeOvertime::class);
    }

    /**
     * Get all cash advances for this employee.
     */
    public function cashAdvances(): HasMany
    {
        return $this->hasMany(EmployeeCashAdvance::class);
    }

    /**
     * Get total active remaining cash advance debt balance.
     */
    public function getActiveCashAdvanceBalanceAttribute(): float
    {
        return (float) $this->cashAdvances()
            ->whereIn('status', ['approved'])
            ->where('remaining_amount', '>', 0)
            ->sum('remaining_amount');
    }

    /**
     * Get the number of off days taken by this employee in the specified (or current) month.
     */
    public function getUsedOffDaysThisMonthAttribute(): int
    {
        $year = now()->year;
        $month = now()->month;

        return $this->attendances()
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->where('status', 'off_day')
            ->count();
    }

    /**
     * Check if the employee has exceeded their monthly off days quota.
     */
    public function getIsOffDaysOverQuotaAttribute(): bool
    {
        return $this->used_off_days_this_month > $this->monthly_off_days_quota;
    }

    /**
     * Get the number of off days over the quota.
     */
    public function getOffDaysOverCountAttribute(): int
    {
        return max(0, $this->used_off_days_this_month - $this->monthly_off_days_quota);
    }

    /**
     * Get all commission groups led by this employee.
     */
    public function ledCommissionGroups(): HasMany
    {
        return $this->hasMany(CommissionGroup::class, 'leader_employee_id');
    }

    /**
     * Get all commission group memberships for this employee.
     */
    public function commissionGroupMemberships(): HasMany
    {
        return $this->hasMany(CommissionGroupMember::class);
    }
}
