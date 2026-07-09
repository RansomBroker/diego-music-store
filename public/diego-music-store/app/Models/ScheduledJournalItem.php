<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScheduledJournalItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'scheduled_journal_entry_id',
        'account_id',
        'debit',
        'credit',
        'notes',
    ];

    protected $casts = [
        'debit' => 'integer',
        'credit' => 'integer',
    ];

    public function scheduledJournalEntry(): BelongsTo
    {
        return $this->belongsTo(ScheduledJournalEntry::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}
