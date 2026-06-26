<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AccountClassification extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'name',
    ];

    /**
     * Get the accounts associated with this classification.
     */
    public function accounts(): HasMany
    {
        return $this->hasMany(Account::class, 'classification', 'key');
    }
}
