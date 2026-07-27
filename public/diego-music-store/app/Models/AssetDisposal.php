<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetDisposal extends Model
{
    use HasFactory;

    protected $fillable = [
        'disposal_number',
        'disposal_date',
        'asset_id',
        'branch_id',
        'disposal_type',
        'book_value',
        'disposal_amount',
        'gain_loss_amount',
        'account_id',
        'journal_entry_id',
        'status',
        'notes',
    ];

    protected $casts = [
        'disposal_date'    => 'date',
        'book_value'       => 'decimal:2',
        'disposal_amount'  => 'decimal:2',
        'gain_loss_amount' => 'decimal:2',
    ];

    protected static function booted()
    {
        static::creating(function ($disposal) {
            if (empty($disposal->disposal_number)) {
                $disposal->disposal_number = static::generateDisposalNumber();
            }
        });
    }

    public static function generateDisposalNumber(): string
    {
        $prefix = 'DSP-' . now()->format('Ym') . '-';
        $last = static::where('disposal_number', 'LIKE', $prefix . '%')
            ->orderBy('disposal_number', 'desc')
            ->first();

        if ($last) {
            $num = (int) substr($last->disposal_number, -4) + 1;
        } else {
            $num = 1;
        }

        return $prefix . str_pad($num, 4, '0', STR_PAD_LEFT);
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
    }
}
