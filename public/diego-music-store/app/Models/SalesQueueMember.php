<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalesQueueMember extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'user_id',
        'queue_order',
        'status',
        'duty_started_at',
        'current_duty_log_id',
        'turns_count',
        'is_active',
        'date',
    ];

    protected $casts = [
        'queue_order' => 'integer',
        'duty_started_at' => 'datetime',
        'turns_count' => 'integer',
        'is_active' => 'boolean',
        'date' => 'date',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function currentDutyLog(): BelongsTo
    {
        return $this->belongsTo(SalesDutyLog::class, 'current_duty_log_id');
    }

    public function dutyLogs(): HasMany
    {
        return $this->hasMany(SalesDutyLog::class, 'queue_member_id');
    }

    public function scopeForBranchAndDate($query, int $branchId, ?string $date = null)
    {
        $targetDate = $date ?? now()->toDateString();
        return $query->where('branch_id', $branchId)->where('date', $targetDate);
    }

    public function scopeWaiting($query)
    {
        return $query->where('status', 'waiting')->where('is_active', true)->orderBy('queue_order', 'asc');
    }

    public function scopeOnDuty($query)
    {
        return $query->where('status', 'on_duty')->where('is_active', true)->orderBy('duty_started_at', 'asc');
    }

    public function scopeOffDuty($query)
    {
        return $query->where('status', 'off_duty');
    }
}
