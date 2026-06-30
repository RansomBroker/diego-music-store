<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockOpname extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'opname_number',
        'opname_date',
        'status', // draft, completed
        'notes',
    ];

    protected $casts = [
        'opname_date' => 'date',
    ];

    protected static function booted()
    {
        static::creating(function ($opname) {
            if (empty($opname->opname_number)) {
                $opname->opname_number = static::generateOpnameNumber();
            }
        });
    }

    public static function generateOpnameNumber(): string
    {
        $date = now()->format('Ymd');
        $prefix = 'OPN-' . $date . '-';

        $lastOpname = static::where('opname_number', 'like', $prefix . '%')
            ->orderBy('opname_number', 'desc')
            ->first();

        if ($lastOpname) {
            $lastNum = intval(substr($lastOpname->opname_number, strlen($prefix)));
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
        return $this->hasMany(StockOpnameItem::class);
    }
}
