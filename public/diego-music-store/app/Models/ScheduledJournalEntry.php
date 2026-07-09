<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ScheduledJournalEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'description',
        'start_date',
        'frequency',
        'interval',
        'duration_months',
        'end_date',
        'status', // active, paused, completed
        'last_run_at',
        'next_run_at',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'last_run_at' => 'date',
        'next_run_at' => 'date',
        'interval' => 'integer',
        'duration_months' => 'integer',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ScheduledJournalItem::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function journalEntries(): HasMany
    {
        return $this->hasMany(JournalEntry::class, 'reference_id')
            ->where('reference_type', 'ScheduledJournalEntry');
    }
}
