<?php

namespace App\Actions\PosOtp;

use App\Models\PosActionOtp;
use App\Models\PosActionOtpLog;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class VerifyPosActionOtp
{
    public function execute(string $code, string $action, Sale $sale, ?User $actor = null): PosActionOtp
    {
        $actor ??= Auth::user();
        $cleanCode = preg_replace('/\D/', '', trim($code)) ?? '';

        if (strlen($cleanCode) !== 6) {
            throw new InvalidArgumentException('Kode OTP harus terdiri dari 6 digit angka.');
        }

        $activeOtp = PosActionOtp::query()
            ->whereNull('revoked_at')
            ->where('expires_at', '>', now())
            ->latest('id')
            ->first();

        if (! $activeOtp || ! hash_equals($activeOtp->code, $cleanCode)) {
            throw new InvalidArgumentException('Kode OTP tidak valid atau sudah kedaluwarsa. Minta kode baru dari Owner di Backoffice.');
        }

        return DB::transaction(function () use ($activeOtp, $action, $sale, $actor): PosActionOtp {
            PosActionOtpLog::create([
                'pos_action_otp_id' => $activeOtp->id,
                'user_id' => $actor?->id,
                'branch_id' => $sale->branch_id,
                'action' => $action,
                'sale_id' => $sale->id,
                'invoice_number' => $sale->invoice_number,
                'used_at' => now(),
            ]);

            return $activeOtp;
        });
    }
}

