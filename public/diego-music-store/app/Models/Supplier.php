<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'contact_person',
        'phone',
        'email',
        'address',
        'bank_name',
        'bank_account_number',
        'bank_account_name',
        'outstanding_debt',
    ];

    protected $casts = [
        'outstanding_debt' => 'decimal:2',
    ];

    public function payments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(SupplierPayment::class);
    }
}
