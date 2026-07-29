<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Voucher extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'type',
        'value',
        'min_spend',
        'valid_until',
        'max_uses',
        'used_count',
        'is_active',
    ];

    protected $casts = [
        'value' => 'float',
        'min_spend' => 'float',
        'valid_until' => 'datetime',
        'max_uses' => 'integer',
        'used_count' => 'integer',
        'is_active' => 'boolean',
    ];

    public function isValidForSubtotal(float $subtotal, ?string &$errorMessage = null): bool
    {
        if (!$this->is_active) {
            $errorMessage = 'Voucher ini sudah tidak aktif.';
            return false;
        }

        if ($this->valid_until && Carbon::now()->gt($this->valid_until)) {
            $errorMessage = 'Voucher ini telah kadaluarsa (' . $this->valid_until->format('d M Y H:i') . ').';
            return false;
        }

        if ($this->max_uses !== null && $this->used_count >= $this->max_uses) {
            $errorMessage = 'Kuota penggunaan voucher ini telah habis.';
            return false;
        }

        if ($subtotal < $this->min_spend) {
            $errorMessage = 'Minimal belanja untuk voucher ini adalah Rp ' . number_format($this->min_spend, 0, ',', '.') . '.';
            return false;
        }

        return true;
    }

    public function calculateDiscountAmount(float $subtotal): float
    {
        if ($this->type === 'percent') {
            $discount = ($subtotal * $this->value) / 100;
        } else {
            $discount = $this->value;
        }

        return min($discount, $subtotal);
    }
}
