<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesDutyLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'cash_session_id',
        'user_id',
        'queue_member_id',
        'started_at',
        'ended_at',
        'duration_seconds',
        'status',
        'sale_id',
        'closing_amount',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'duration_seconds' => 'integer',
        'closing_amount' => 'decimal:2',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function cashSession(): BelongsTo
    {
        return $this->belongsTo(CashSession::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function queueMember(): BelongsTo
    {
        return $this->belongsTo(SalesQueueMember::class, 'queue_member_id');
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getDurationMinutesAttribute(): int
    {
        return (int) round($this->duration_seconds / 60);
    }

    public function getDurationTextAttribute(): string
    {
        $seconds = (int) ($this->duration_seconds ?? 0);
        if ($this->status === 'serving' && $this->started_at) {
            $seconds = (int) abs(now()->diffInSeconds($this->started_at));
        }

        $totalMinutes = (int) floor(max(0, $seconds) / 60);

        if ($totalMinutes < 60) {
            return "{$totalMinutes} mnt";
        }

        $hours = intdiv($totalMinutes, 60);
        $minutes = $totalMinutes % 60;
        return "{$hours}j {$minutes}m";
    }
}
