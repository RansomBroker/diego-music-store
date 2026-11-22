<?php

namespace App\Filament\Pages\Settings;

use App\Actions\PosOtp\GeneratePosActionOtp;
use App\Helpers\PermissionHelper;
use App\Models\PosActionOtp;
use App\Models\PosActionOtpLog;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;

class PosActionOtpPage extends Page
{
    protected static \UnitEnum|string|null $navigationGroup = 'Pengaturan';

    protected static ?string $navigationLabel = 'OTP Otorisasi POS';

    protected static ?string $title = 'OTP Otorisasi Transaksi POS';

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldCheck;

    protected string $view = 'filament.pages.settings.pos-action-otp';

    public string $currentCode = '';

    public int $expiresAtTimestamp = 0;

    public string $expiresAtFormatted = '-';

    public static function canAccess(): bool
    {
        return PermissionHelper::canAccessBackofficeFeature(auth()->user(), 'bo.pos_action_otp.view');
    }

    public function boot(): void
    {
        abort_unless(static::canAccess(), 403);
    }

    public function mount(GeneratePosActionOtp $generator): void
    {
        $otp = $generator->execute(auth()->user(), false);
        $this->hydrateOtpState($otp);
    }

    public function refreshOtp(GeneratePosActionOtp $generator): void
    {
        abort_unless(PermissionHelper::canAccessBackofficeFeature(auth()->user(), 'bo.pos_action_otp.refresh'), 403);

        $otp = $generator->execute(auth()->user(), true);
        $this->hydrateOtpState($otp);

        Notification::make()
            ->title('OTP Baru Dibuat')
            ->body('Kode OTP 6 digit baru aktif selama 1 menit.')
            ->success()
            ->send();
    }

    public function getRecentLogsProperty(): Collection
    {
        return PosActionOtpLog::query()
            ->with(['user', 'branch'])
            ->latest('used_at')
            ->limit(15)
            ->get();
    }

    private function hydrateOtpState(PosActionOtp $otp): void
    {
        $this->currentCode = $otp->code;
        $this->expiresAtTimestamp = $otp->expires_at->timestamp;
        $this->expiresAtFormatted = $otp->expires_at->format('H:i:s');
    }
}

