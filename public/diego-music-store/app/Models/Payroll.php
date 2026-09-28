<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payroll extends Model
{
    use HasFactory;

    protected $fillable = [
        'payroll_code',
        'period',
        'branch_id',
        'total_employees',
        'total_basic_salary',
        'total_allowances',
        'total_commissions',
        'total_kpi_bonuses',
        'total_deductions',
        'total_net_salary',
        'status',
        'created_by',
        'approved_by',
        'approved_at',
        'paid_at',
        'payment_account_id',
        'journal_entry_id',
        'journal_no',
        'notes',
    ];

    protected $casts = [
        'total_basic_salary' => 'float',
        'total_allowances' => 'float',
        'total_commissions' => 'float',
        'total_kpi_bonuses' => 'float',
        'total_deductions' => 'float',
        'total_net_salary' => 'float',
        'approved_at' => 'datetime',
        'paid_at' => 'datetime',
        'payment_account_id' => 'integer',
        'journal_entry_id' => 'integer',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function paymentAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'payment_account_id');
    }

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class, 'journal_entry_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PayrollItem::class);
    }

    public static function generatePayrollCode(string $period): string
    {
        $periodClean = str_replace('-', '', $period);
        $prefix = 'PAY-' . $periodClean . '-';

        $last = self::where('payroll_code', 'like', $prefix . '%')
            ->orderBy('payroll_code', 'desc')
            ->first();

        if ($last) {
            $lastNum = (int) substr($last->payroll_code, -4);
            $nextNum = str_pad($lastNum + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $nextNum = '0001';
        }

        return $prefix . $nextNum;
    }
}
