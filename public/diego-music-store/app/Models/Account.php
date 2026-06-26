<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'classification',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the classification details.
     */
    public function classificationRelation()
    {
        return $this->belongsTo(AccountClassification::class, 'classification', 'key');
    }

    protected static function booted()
    {
        static::saving(function ($account) {
            if ($account->classification) {
                // Generate a key slug for the classification
                $key = \Illuminate\Support\Str::slug($account->classification);
                
                // Ensure it exists in the account_classifications database table
                $exists = AccountClassification::where('key', $key)->exists();
                if (!$exists) {
                    AccountClassification::create([
                        'key' => $key,
                        'name' => $account->classification,
                    ]);
                }
                
                // Store the normalized key slug
                $account->classification = $key;
            }
        });
    }
}
