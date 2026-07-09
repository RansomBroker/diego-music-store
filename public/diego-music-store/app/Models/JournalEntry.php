<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JournalEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'entry_no',
        'date',
        'description',
        'reference_type',
        'reference_id',
        'status', // draft, posted
        'created_by',
        'posted_at',
        'posted_by',
    ];

    protected $casts = [
        'date' => 'date',
        'posted_at' => 'datetime',
        'reference_id' => 'integer',
    ];

    protected static function booted()
    {
        static::creating(function ($entry) {
            if (empty($entry->entry_no)) {
                $entry->entry_no = static::generateEntryNo();
            }
        });
    }

    public static function generateEntryNo(): string
    {
        $date = now()->format('Ymd');
        $prefix = 'JV-' . $date . '-';

        $lastEntry = static::where('entry_no', 'like', $prefix . '%')
            ->orderBy('entry_no', 'desc')
            ->first();

        if ($lastEntry) {
            $lastNum = intval(substr($lastEntry->entry_no, strlen($prefix)));
            $nextNum = str_pad($lastNum + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $nextNum = '0001';
        }

        return $prefix . $nextNum;
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(JournalItem::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function postedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'posted_by');
    }

    public function reference()
    {
        switch ($this->reference_type) {
            case 'Purchase':
                return $this->belongsTo(PurchaseTransaction::class, 'reference_id');
            case 'Opname':
                return $this->belongsTo(StockOpname::class, 'reference_id');
            case 'ScheduledJournalEntry':
                return $this->belongsTo(ScheduledJournalEntry::class, 'reference_id');
            default:
                return null;
        }
    }
}
