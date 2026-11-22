<?php

namespace App\Actions\PosOtp;

use App\Helpers\PosOtpHelper;
use App\Models\PosActionOtp;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GeneratePosActionOtp
{
    public function execute(?User $actor = null, bool $forceRefresh = false): PosActionOtp
    {
        $actor ??= Auth::user();

        if (! $forceRefresh) {
            $active = PosActionOtp::query()
                ->whereNull('revoked_at')
                ->where('expires_at', '>', now())
                ->latest('id')
                ->first();

            if ($active) {
                return $active;
            }
        }

        return DB::transaction(function () use ($actor): PosActionOtp {
            PosActionOtp::query()
                ->whereNull('revoked_at')
                ->where('expires_at', '>', now())
                ->update(['revoked_at' => now()]);

            $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

            return PosActionOtp::create([
                'code' => $code,
                'expires_at' => now()->addSeconds(PosOtpHelper::OTP_TTL_SECONDS),
                'created_by' => $actor?->id,
            ]);
        });
    }
}

