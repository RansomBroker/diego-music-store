<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class MonthlyClosing extends Model
{
    use HasFactory;

    protected $fillable = [
        'year',
        'month',
        'period_key',
        'branch_id',
        'closed_at',
        'closed_by',
        'closing_journal_id',
        'total_revenue',
        'total_expense',
        'net_income',
        'status',
        'reopened_at',
        'reopened_by',
        'notes',
    ];

    protected $casts = [
        'year'          => 'integer',
        'month'         => 'integer',
        'closed_at'     => 'datetime',
        'reopened_at'   => 'datetime',
        'total_revenue' => 'integer',
        'total_expense' => 'integer',
        'net_income'    => 'integer',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function closedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function reopenedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reopened_by');
    }

    public function closingJournal(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class, 'closing_journal_id');
    }

    /**
     * Check if a specific date/period is closed.
     *
     * @param  string|Carbon  $date
     * @param  int|null  $branchId
     * @return bool
     */
    public static function isPeriodClosed(string|Carbon $date, ?int $branchId = null): bool
    {
        $dt = Carbon::parse($date);
        $periodKey = $dt->format('Y-m');

        $query = static::where('period_key', $periodKey)
            ->where('status', 'closed');

        if ($branchId) {
            $query->where(function ($q) use ($branchId) {
                $q->whereNull('branch_id')->orWhere('branch_id', $branchId);
            });
        }

        return $query->exists();
    }
}
