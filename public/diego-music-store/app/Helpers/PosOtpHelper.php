<?php

namespace App\Helpers;

use App\Models\User;

class PosOtpHelper
{
    public const OTP_TTL_SECONDS = 60;
    public const EDIT_SESSION_KEY = 'pos_otp_edit_sale';

    public static function requiresOtp(?User $user): bool
    {
        return ! PermissionHelper::isOwner($user);
    }

    public static function grantEditSaleSession(int $saleId, int $ttlMinutes = 30): void
    {
        session()->put(self::EDIT_SESSION_KEY . '.' . $saleId, now()->addMinutes($ttlMinutes)->timestamp);
    }

    public static function hasValidEditSaleSession(int $saleId, ?User $user = null): bool
    {
        if (! self::requiresOtp($user)) {
            return true;
        }

        $expiresAt = session()->get(self::EDIT_SESSION_KEY . '.' . $saleId);
        if (! is_numeric($expiresAt)) {
            return false;
        }

        if ((int) $expiresAt <= now()->timestamp) {
            self::consumeEditSaleSession($saleId);
            return false;
        }

        return true;
    }

    public static function consumeEditSaleSession(int $saleId): void
    {
        session()->forget(self::EDIT_SESSION_KEY . '.' . $saleId);
    }
}

