<?php

namespace App\Actions\Notification;

use App\Helpers\FonnteHelper;
use App\Models\Branch;

class TestFonnteConnection
{
    /**
     * Execute a test WhatsApp message send to verify Fonnte API connection for a branch.
     *
     * @param string $targetNumber Target test WhatsApp phone number
     * @param string|null $testMessage Custom test message content
     * @param Branch|null $branch Target branch instance
     * @param string|null $token Explicit token if testing unsaved token
     * @return array
     */
    public function execute(
        string $targetNumber,
        ?string $testMessage = null,
        ?Branch $branch = null,
        ?string $token = null
    ): array {
        $branchName = $branch?->name ?? 'Cabang Utama';
        $defaultMessage = "[Diego Music Store ERP]\n\n"
            . "Halo, ini adalah pesan uji coba (Test Connection) WhatsApp API Fonnte untuk cabang: *{$branchName}*.\n\n"
            . "✅ Integrasi WhatsApp Fonnte terhubung dan berfungsi dengan baik pada " . now()->format('d M Y H:i:s') . ".";

        $message = !empty($testMessage) ? $testMessage : $defaultMessage;

        return FonnteHelper::sendTextMessage($targetNumber, $message, $token, $branch);
    }
}
