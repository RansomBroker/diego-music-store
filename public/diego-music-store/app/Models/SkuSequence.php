<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SkuSequence extends Model
{
    protected $table = 'sku_sequences';

    protected $fillable = [
        'branch_id',
        'prefix',
        'last_number',
        'min_digits',
    ];

    protected $casts = [
        'branch_id' => 'integer',
        'last_number' => 'integer',
        'min_digits' => 'integer',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
}

