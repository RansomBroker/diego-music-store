<?php

namespace App\Filament\Widgets;

use App\Actions\PosOtp\GeneratePosActionOtp;
use App\Helpers\PermissionHelper;
use App\Models\PosActionOtp;
use App\Models\PosActionOtpLog;
use Filament\Notifications\Notification;
use Filament\Widgets\Widget;
use Illuminate\Support\Collection;

class PosActionOtpWidget extends Widget
{
    protected string $view = 'filament.widgets.pos-action-otp-widget';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = -5;

    public string $currentCode = '';

    public int $expiresAtTimestamp = 0;

    public string $expiresAtFormatted = '-';

    public static function canView(): bool
    {
        return PermissionHelper::canAccessBackofficeFeature(auth()->user(), 'bo.pos_action_otp.view');
    }

    public function boot(): void
    {
        abort_unless(static::canView(), 403);
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
            ->limit(10)
            ->get();
    }

    private function hydrateOtpState(PosActionOtp $otp): void
    {
        $this->currentCode = $otp->code;
        $this->expiresAtTimestamp = $otp->expires_at->timestamp;
        $this->expiresAtFormatted = $otp->expires_at->format('H:i:s');
    }
}

