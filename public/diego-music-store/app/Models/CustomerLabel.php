<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomerLabel extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'name',
    ];

    /**
     * Get the customers associated with this label.
     */
    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class, 'customer_label_id');
    }
}
