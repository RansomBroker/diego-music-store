<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CommissionScheme extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'employee_id',
        'name',
        'calculation_type',
        'rate',
        'applies_to',
        'target_product_id',
        'target_sale_category_id',
        'min_monthly_sales_target',
        'is_active',
    ];

    protected $casts = [
        'rate' => 'decimal:2',
        'min_monthly_sales_target' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function employees(): BelongsToMany
    {
        return $this->belongsToMany(Employee::class, 'commission_scheme_employee');
    }

    public function targetProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'target_product_id');
    }

    public function targetCategory(): BelongsTo
    {
        return $this->belongsTo(SaleCategory::class, 'target_sale_category_id');
    }

    public function commissionLogs(): HasMany
    {
        return $this->hasMany(SalesCommissionLog::class, 'commission_scheme_id');
    }
}
