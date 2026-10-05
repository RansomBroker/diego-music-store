<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockTransfer extends Model
{
    use HasFactory;

    protected $fillable = [
        'transfer_number',
        'from_branch_id',
        'to_branch_id',
        'transfer_date',
        'description',
        'status',
        'total_qty',
        'total_cost',
        'journal_entry_id',
        'submitted_at',
        'approved_at',
        'completed_at',
        'cancelled_at',
    ];

    protected $casts = [
        'transfer_date' => 'date',
        'total_qty' => 'integer',
        'total_cost' => 'decimal:2',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    /**
     * Get the origin branch.
     */
    public function fromBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'from_branch_id');
    }

    /**
     * Get the destination branch.
     */
    public function toBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'to_branch_id');
    }

    /**
     * Get the items in this transfer.
     */
    public function items(): HasMany
    {
        return $this->hasMany(StockTransferItem::class);
    }

    /**
     * Get the associated journal entry.
     */
    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class, 'journal_entry_id');
    }

    /**
     * Generate a new transfer number.
     */
    public static function generateTransferNumber(): string
    {
        $prefix = 'TRF-' . now()->format('Ym') . '-';
        $lastRecord = self::where('transfer_number', 'like', $prefix . '%')
            ->orderBy('transfer_number', 'desc')
            ->first();

        if (!$lastRecord) {
            return $prefix . '00001';
        }

        $lastNumber = intval(substr($lastRecord->transfer_number, -5));
        return $prefix . str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);
    }
}
