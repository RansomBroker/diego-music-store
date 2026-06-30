<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryMutation extends Model
{
    use HasFactory;

    protected $fillable = [
        'sender_branch_id',
        'receiver_branch_id',
        'mutation_number',
        'mutation_date',
        'status', // draft, transit, received
        'notes',
    ];

    protected $casts = [
        'mutation_date' => 'date',
    ];

    protected static function booted()
    {
        static::creating(function ($mutation) {
            if (empty($mutation->mutation_number)) {
                $mutation->mutation_number = static::generateMutationNumber();
            }
        });
    }

    public static function generateMutationNumber(): string
    {
        $date = now()->format('Ymd');
        $prefix = 'MUT-' . $date . '-';

        $lastMutation = static::where('mutation_number', 'like', $prefix . '%')
            ->orderBy('mutation_number', 'desc')
            ->first();

        if ($lastMutation) {
            $lastNum = intval(substr($lastMutation->mutation_number, strlen($prefix)));
            $nextNum = str_pad($lastNum + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $nextNum = '0001';
        }

        return $prefix . $nextNum;
    }

    public function senderBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'sender_branch_id');
    }

    public function receiverBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'receiver_branch_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(InventoryMutationItem::class);
    }
}
