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
        'account_subtype',
        'normal_balance',
        'is_active',
        'parent_id',
        'is_header',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_header' => 'boolean',
    ];

    /**
     * Find account by code or legacy code mapping.
     */
    public static function findByCode(string $code): ?self
    {
        return \App\Helpers\AccountHelper::findByCode($code);
    }

    /**
     * Get the parent account.
     */
    public function parent()
    {
        return $this->belongsTo(Account::class, 'parent_id');
    }

    /**
     * Get the child accounts.
     */
    public function children()
    {
        return $this->hasMany(Account::class, 'parent_id')->orderBy('code');
    }

    /**
     * Get the classification details.
     */
    public function classificationRelation()
    {
        return $this->belongsTo(AccountClassification::class, 'classification', 'key');
    }

    /**
     * Get the journal items affecting this account.
     */
    public function journalItems()
    {
        return $this->hasMany(JournalItem::class, 'account_id');
    }

    /**
     * Determine the normal balance type (debit/credit).
     */
    public function getNormalBalance(): string
    {
        if (!empty($this->normal_balance)) {
            return strtolower($this->normal_balance);
        }

        $code = strtolower($this->code);
        $classification = strtolower($this->classification);

        // Revenue & Equity are always Credit
        if (str_contains($classification, 'revenue') || str_contains($classification, 'pendapatan') || str_contains($classification, 'equity')) {
            return 'credit';
        }

        // Assets (1), Expense (5, 6) are normally Debit
        if (str_starts_with($code, '1') || str_starts_with($code, '5') || str_starts_with($code, '6')) {
            return 'debit';
        }
        if (str_contains($classification, 'asset') || str_contains($classification, 'expense') || str_contains($classification, 'beban') || str_contains($classification, 'cost')) {
            return 'debit';
        }

        return 'credit';
    }

    /**
     * Get current balance of this account (recursive for headers).
     */
    public function getBalanceAttribute(): float
    {
        if ($this->is_header) {
            $sum = 0;
            foreach ($this->children as $child) {
                $sum += $child->balance;
            }
            return $sum;
        }

        $sums = \App\Models\JournalItem::query()
            ->selectRaw('SUM(debit) as total_debit, SUM(credit) as total_credit')
            ->where('account_id', $this->id)
            ->whereHas('journalEntry', fn($q) => $q->where('status', 'posted'))
            ->first();

        $debits = $sums->total_debit ?? 0;
        $credits = $sums->total_credit ?? 0;

        $normal = $this->getNormalBalance();
        return $normal === 'debit' ? ($debits - $credits) : ($credits - $debits);
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
